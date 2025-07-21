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
        $sql = "SELECT id, name FROM customers ORDER BY name ASC";
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
        // Add stock to the SELECT statement
        $sql = "SELECT id, name, category, stock, price FROM items ORDER BY name ASC";
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

            // 2. Insert charge items only (NO stock update - this will be done on print)
            $insertItemSql = "INSERT INTO charge_items (charge_id, item_id, quantity, price, discount_percentage) VALUES (?, ?, ?, ?, ?)";
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

                // Insert charge item with discount
                $insertStmt->bind_param(
                    "iiidd",
                    $chargeId,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $discount  // Store discount percentage
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
    }
}
