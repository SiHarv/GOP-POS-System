<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ItemsController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllItems() {
        $sql = "SELECT * FROM items ORDER BY id DESC"; // Added ORDER BY clause
        $result = $this->conn->query($sql);
        $items = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    public function addItem($data) {
        try {
            $sql = "INSERT INTO items (name, stock, sold_by, category, cost, price) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sissdd", 
                $data['name'],
                $data['stock'],
                $data['sold_by'],
                $data['category'],
                $data['cost'],
                $data['price']
            );

            if ($stmt->execute()) {
                $lastInsertId = $this->conn->insert_id;
                return [
                    'status' => 'success',
                    'item' => [
                        'id' => $lastInsertId,
                        'stock' => $data['stock'],
                        'sold_by' => $data['sold_by'],
                        'name' => $data['name'],
                        'category' => $data['category'],
                        'cost' => $data['cost'],
                        'price' => $data['price']
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to add item'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function editItem($data) {
        try {
            $stmt = $this->conn->prepare("UPDATE items SET 
                name = ?, 
                stock = ?, 
                sold_by = ?, 
                category = ?, 
                cost = ?, 
                price = ? 
                WHERE id = ?");

            $stmt->bind_param("sissddi", 
                $data['name'],
                $data['stock'],
                $data['sold_by'],
                $data['category'],
                $data['cost'],
                $data['price'],
                $data['id']
            );

            if ($stmt->execute()) {
                return ['status' => 'success'];
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $controller = new ItemsController();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                echo json_encode($controller->addItem($_POST));
                break;
            case 'edit_item':
                echo json_encode($controller->editItem($_POST));
                break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getAll') {
    header('Content-Type: application/json');
    $controller = new ItemsController();
    echo json_encode($controller->getAllItems());
}
?>