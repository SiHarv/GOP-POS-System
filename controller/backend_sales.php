<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class SalesController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    // This class now only handles basic sales functionality, 
    // specific analytics have been moved to their respective controllers
    // Debug function to check if we have data
    public function debugData() {
        // Check charges table
        $chargesCount = $this->conn->query("SELECT COUNT(*) as count FROM charges")->fetch_assoc();
        
        // Check charge_items table  
        $itemsCount = $this->conn->query("SELECT COUNT(*) as count FROM charge_items")->fetch_assoc();
        
        // Check items table
        $productsCount = $this->conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc();
        
        return [
            'charges_count' => $chargesCount['count'],
            'charge_items_count' => $itemsCount['count'],
            'items_count' => $productsCount['count']
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new SalesController();
    
    try {
        switch ($_POST['action']) {
            case 'debug_data':
                $result = $controller->debugData();
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
}
?>