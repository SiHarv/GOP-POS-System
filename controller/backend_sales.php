<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class SalesController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getMonthlySales($year = null, $month = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $sql = "SELECT 
                    COALESCE(SUM(c.total_price), 0) as total_sales,
                    COALESCE(SUM(ci.quantity * i.cost), 0) as total_cost,
                    COUNT(DISTINCT c.id) as total_transactions
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE YEAR(c.charge_date) = ?";
        
        $params = [$year];
        $types = "i";
        
        if ($month) {
            $sql .= " AND MONTH(c.charge_date) = ?";
            $params[] = $month;
            $types .= "i";
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Query preparation failed: " . $this->conn->error);
                return null;
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Debug log
            error_log("Query result: " . json_encode($row));
            
            return [
                'total_sales' => floatval($row['total_sales']),
                'total_cost' => floatval($row['total_cost']),
                'gross_profit' => floatval($row['total_sales'] - $row['total_cost']),
                'transactions' => intval($row['total_transactions']),
                'avg_transaction' => $row['total_transactions'] > 0 ? 
                    floatval($row['total_sales'] / $row['total_transactions']) : 0
            ];
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new SalesController();
    
    if ($_POST['action'] === 'get_monthly_sales') {
        $year = isset($_POST['year']) ? intval($_POST['year']) : null;
        $month = isset($_POST['month']) ? intval($_POST['month']) : null;
        echo json_encode($controller->getMonthlySales($year, $month));
        exit;
    }
}