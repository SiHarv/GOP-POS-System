<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ChargeController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllCustomers() {
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

    public function getAllItems() {
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

    public function processCharge($customerId, $items) {
        try {
            $this->conn->begin_transaction();

            // 1. Create the main charge record
            $totalAmount = array_reduce($items, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);

            $sql = "INSERT INTO charges (customer_id, total_price) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("id", $customerId, $totalAmount);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating charge record");
            }
            
            $chargeId = $this->conn->insert_id;

            // 2. Insert charge items and update stock
            $insertItemSql = "INSERT INTO charge_items (charge_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $updateStockSql = "UPDATE items SET stock = stock - ? WHERE id = ? AND stock >= ?";
            
            $insertStmt = $this->conn->prepare($insertItemSql);
            $updateStmt = $this->conn->prepare($updateStockSql);

            foreach ($items as $item) {
                // Check stock availability first
                $currentStock = $this->getItemStock($item['id']);
                if ($currentStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for item: " . $item['name']);
                }

                // Insert charge item
                $insertStmt->bind_param("iiid", 
                    $chargeId,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                );
                
                if (!$insertStmt->execute()) {
                    throw new Exception("Error adding charge item");
                }

                // Update stock
                $updateStmt->bind_param("iii", 
                    $item['quantity'],
                    $item['id'],
                    $item['quantity']
                );
                
                if (!$updateStmt->execute() || $updateStmt->affected_rows === 0) {
                    throw new Exception("Error updating stock for item: " . $item['name']);
                }
            }

            $this->conn->commit();
            return ['status' => 'success'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getItemStock($itemId) {
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
            $result = $controller->processCharge($_POST['customer_id'], $_POST['items']);
            echo json_encode($result);
            break;
            
        case 'get_items':
            $items = $controller->getAllItems();
            echo json_encode($items);
            break;
    }
}