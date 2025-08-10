<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ChargeController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllCustomers()
    {
        $sql = "SELECT id, name, COALESCE(salesman, '') as salesman FROM customers ORDER BY name ASC";
        $result = $this->conn->query($sql);
        $customers = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        return $customers;
    }

    public function getAllItems()
    {
        // Use sold_by instead of unit, with fallback to 'PCS' if empty
        $sql = "SELECT id, name, category, stock, price, COALESCE(NULLIF(sold_by, ''), 'PCS') as unit FROM items ORDER BY name ASC";
        $result = $this->conn->query($sql);
        $items = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    public function processCharge($customerId, $items, $poNumber = null)
    {
        try {
            $this->conn->begin_transaction();

            // 1. Create the main charge record
            $totalAmount = array_reduce($items, function ($sum, $item) {
                // Ensure total exists and is valid
                $itemTotal = isset($item['total']) && is_numeric($item['total']) ?
                    $item['total'] : ($item['price'] * $item['quantity']);

                return $sum + $itemTotal;
            }, 0);

            // Modified SQL to include po_number field and finalized status (0 = not finalized, 1 = finalized)
            $sql = "INSERT INTO charges (customer_id, total_price, po_number, finalized) VALUES (?, ?, ?, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ids", $customerId, $totalAmount, $poNumber);

            if (!$stmt->execute()) {
                throw new Exception("Error creating charge record");
            }

            $chargeId = $this->conn->insert_id;

            // 2. Insert charge items with custom price support
            $insertItemSql = "INSERT INTO charge_items (charge_id, item_id, quantity, price, custom_price, discount_percentage) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertItemSql);

            foreach ($items as $item) {
                // Check stock availability first (but don't subtract yet)
                $currentStock = $this->getItemStock($item['id']);
                if ($currentStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for item: " . $item['name']);
                }

                // Ensure discount is a valid number
                $discount = isset($item['discount']) && is_numeric($item['discount']) ?
                    $item['discount'] : 0;

                // Resolve prices
                $originalPrice = floatval($item['price']);
                // Default custom_price to original price to satisfy NOT NULL schema
                $priceToStore = $originalPrice;
                if (isset($item['customPrice']) && is_numeric($item['customPrice'])) {
                    $customPrice = floatval($item['customPrice']);
                    // Use custom if different
                    if ($customPrice != $originalPrice) {
                        $priceToStore = $customPrice;
                    }
                }

                // Insert charge item with custom price and discount
                $insertStmt->bind_param(
                    "iiiddd",
                    $chargeId,
                    $item['id'],
                    $item['quantity'],
                    $originalPrice,    // Original price from database
                    $priceToStore,     // Custom or original price (non-null)
                    $discount          // Store discount percentage
                );

                if (!$insertStmt->execute()) {
                    throw new Exception("Error adding charge item");
                }
            }

            $this->conn->commit();
            return ['status' => 'success', 'charge_id' => $chargeId];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateItemUnit($itemId, $unit)
    {
        try {
            // Validate inputs
            if (empty($itemId) || empty($unit)) {
                throw new Exception("Item ID and unit are required");
            }

            // Sanitize unit input
            $unit = trim($unit);
            if (strlen($unit) > 20) { // Reasonable limit for unit
                throw new Exception("Unit name is too long");
            }

            // Update the sold_by column in database
            $sql = "UPDATE items SET sold_by = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $unit, $itemId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return ['status' => 'success', 'message' => 'Unit updated successfully'];
                } else {
                    return ['status' => 'error', 'message' => 'Item not found or no changes made'];
                }
            } else {
                throw new Exception("Failed to update unit");
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateCustomerSalesman($customerId, $salesman)
    {
        try {
            // Validate inputs
            if (empty($customerId)) {
                throw new Exception("Customer ID is required");
            }

            // Sanitize salesman input (allow empty string to clear salesman)
            $salesman = trim($salesman);
            if (strlen($salesman) > 100) { // Reasonable limit for salesman name
                throw new Exception("Salesman name is too long");
            }

            // Update the salesman column in customers table
            $sql = "UPDATE customers SET salesman = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $salesman, $customerId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return ['status' => 'success', 'message' => 'Salesman updated successfully'];
                } else {
                    return ['status' => 'error', 'message' => 'Customer not found or no changes made'];
                }
            } else {
                throw new Exception("Failed to update salesman");
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getItemStock($itemId)
    {
        $sql = "SELECT stock FROM items WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['stock'] : 0;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new ChargeController();

    switch ($_POST['action']) {
        case 'process_charge':
            // Get P.O. number from the request
            $poNumber = isset($_POST['po_number']) ? $_POST['po_number'] : null;
            $customerId = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
            $items = isset($_POST['items']) ? $_POST['items'] : [];
            if ($customerId <= 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid or missing customer selection.'
                ]);
                exit;
            }
            $result = $controller->processCharge($customerId, $items, $poNumber);
            echo json_encode($result);
            break;

        case 'get_items':
            $items = $controller->getAllItems();
            echo json_encode($items);
            break;

        case 'update_item_unit':
            $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
            $unit = isset($_POST['unit']) ? $_POST['unit'] : '';
            $result = $controller->updateItemUnit($itemId, $unit);
            echo json_encode($result);
            break;

        case 'update_customer_salesman':
            $customerId = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
            $salesman = isset($_POST['salesman']) ? $_POST['salesman'] : '';
            $result = $controller->updateCustomerSalesman($customerId, $salesman);
            echo json_encode($result);
            break;
    }
}
