<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once __DIR__ . '/../connection/DBConnection.php';

class ItemsPrintController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getCategories()
    {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection failed");
            }
            
            // Get distinct categories from items table
            $query = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $categories = [];
            while ($row = $result->fetch_row()) {
                if ($row[0]) { // Make sure category is not empty
                    $categories[] = $row[0];
                }
            }
            
            return [
                'success' => true,
                'categories' => $categories
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ];
        }
    }

    public function getItemsByCategory($category = null)
    {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection failed");
            }
            
            if ($category === null || $category === '' || $category === 'all') {
                // Get all items
                $query = "SELECT id as item_id, name as item_name, category, sold_by, price, stock, cost 
                         FROM items 
                         ORDER BY category ASC, name ASC";
                $result = $this->conn->query($query);
            } else {
                // Get items by specific category
                $query = "SELECT id as item_id, name as item_name, category, sold_by, price, stock, cost 
                         FROM items 
                         WHERE category = ? 
                         ORDER BY name ASC";
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->conn->error);
                }
                $stmt->bind_param('s', $category);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return [
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching items: ' . $e->getMessage()
            ];
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ItemsPrintController();
    
    // Get the raw POST data
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'getCategories':
            echo json_encode($controller->getCategories());
            break;
            
        case 'getItems':
            $category = $input['category'] ?? null;
            echo json_encode($controller->getItemsByCategory($category));
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action: ' . $action
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
}
?>
