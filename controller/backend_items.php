<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ItemsController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllItems($limit = null, $offset = 0)
    {
        $query = "SELECT i.*, 
                h.quantity_added,
                h.date_added
                FROM items i
                LEFT JOIN (
                    SELECT item_id, quantity_added, date_added
                    FROM item_stock_history
                    WHERE (item_id, date_added) IN (
                        SELECT item_id, MAX(date_added)
                        FROM item_stock_history
                        GROUP BY item_id
                    )
                ) h ON i.id = h.item_id
                ORDER BY i.id DESC";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        }

        $result = $stmt->get_result();
        $items = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    public function getTotalItemsCount()
    {
        $query = "SELECT COUNT(*) FROM items";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        return $row[0];
    }

    public function addItem($data)
    {
        try {
            $sql = "INSERT INTO items (name, stock, sold_by, category, cost, price) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sissdd",
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

    public function updateItem($data)
    {
        try {
            $this->conn->begin_transaction();

            // Update main item information
            $sql = "UPDATE items SET 
                    name = ?, 
                    category = ?, 
                    sold_by = ?, 
                    cost = ?, 
                    price = ?,
                    stock = stock + ? 
                    WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssddii",
                $data['name'],
                $data['category'],
                $data['sold_by'],
                $data['cost'],
                $data['price'],
                $data['new_stock'],
                $data['id']
            );
            $stmt->execute();

            // Record new stock addition if any
            if (!empty($data['new_stock']) && $data['new_stock'] > 0) {
                $historySql = "INSERT INTO item_stock_history (item_id, quantity_added) VALUES (?, ?)";
                $stmt = $this->conn->prepare($historySql);
                $stmt->bind_param("ii", $data['id'], $data['new_stock']);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getLowStockItems($stockThreshold = 15)
    {
        $query = "SELECT i.*, 
                h.quantity_added,
                h.date_added
                FROM items i
                LEFT JOIN (
                    SELECT item_id, quantity_added, date_added
                    FROM item_stock_history
                    WHERE (item_id, date_added) IN (
                        SELECT item_id, MAX(date_added)
                        FROM item_stock_history
                        GROUP BY item_id
                    )
                ) h ON i.id = h.item_id
                WHERE i.stock <= ?
                ORDER BY i.stock ASC, i.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $stockThreshold);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
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
                echo json_encode($controller->updateItem($_POST));
                break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getAll') {
    header('Content-Type: application/json');
    $controller = new ItemsController();
    echo json_encode($controller->getAllItems());
}
