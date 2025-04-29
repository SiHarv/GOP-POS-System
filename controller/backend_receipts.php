<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ReceiptsController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllReceipts() {
        $sql = "SELECT 
            c.id,
            cust.name AS customer_name,
            c.total_price,
            c.charge_date
        FROM charges c
        JOIN customers cust ON c.customer_id = cust.id
        ORDER BY c.charge_date DESC";

        $result = $this->conn->query($sql);
        $receipts = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }
        }
        return $receipts;
    }

    public function getReceiptDetails($id) {
        $sql = "SELECT 
            c.id,
            c.charge_date,
            c.total_price,
            cust.name AS customer_name,
            cust.address AS customer_address,
            i.name AS item_name,
            ci.quantity,
            ci.price AS unit_price,
            (ci.quantity * ci.price) AS subtotal
        FROM charges c
        JOIN customers cust ON c.customer_id = cust.id
        JOIN charge_items ci ON c.id = ci.charge_id
        JOIN items i ON ci.item_id = i.id
        WHERE c.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $receipt = null;
        
        if ($row = $result->fetch_assoc()) {
            $receipt = [
                'id' => $row['id'],
                'date' => $row['charge_date'],
                'customer_name' => $row['customer_name'],
                'customer_address' => $row['customer_address'],
                'total_price' => $row['total_price'],
                'items' => []
            ];
            
            do {
                $receipt['items'][] = [
                    'quantity' => $row['quantity'],
                    'name' => $row['item_name'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal']
                ];
            } while ($row = $result->fetch_assoc());
        }
        
        return $receipt;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new ReceiptsController();
    
    if ($_POST['action'] === 'get_details' && isset($_POST['id'])) {
        $result = $controller->getReceiptDetails($_POST['id']);
        echo json_encode($result);
    }
}