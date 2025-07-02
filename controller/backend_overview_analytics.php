<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class OverviewAnalyticsController {
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
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $result['gross_profit'] = $result['total_sales'] - $result['total_cost'];
        $result['avg_transaction'] = $result['total_transactions'] > 0 ? 
            $result['total_sales'] / $result['total_transactions'] : 0;
        
        return $result;
    }

    public function getSalesOverview($year = null) {
        if (!$year) $year = date('Y');

        $yearCheck = $this->conn->query("SELECT DISTINCT YEAR(charge_date) as year FROM charges ORDER BY year");
        $years = [];
        while ($row = $yearCheck->fetch_assoc()) {
            $years[] = $row['year'];
        }
        
        if (!in_array($year, $years) && count($years) > 0) {
            $year = $years[0];
        }

        $sql = "SELECT 
                    MONTH(c.charge_date) as month,
                    COALESCE(SUM(c.total_price), 0) as monthly_sales,
                    COALESCE(SUM(ci.quantity * i.cost), 0) as monthly_cost,
                    COUNT(DISTINCT c.id) as monthly_transactions
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE YEAR(c.charge_date) = ?
                GROUP BY MONTH(c.charge_date)
                ORDER BY MONTH(c.charge_date)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $months = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = $row;
        }
        
        return $months;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new OverviewAnalyticsController();
    
    try {
        switch ($_POST['action']) {
            case 'get_monthly_sales':
                $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
                $result = $controller->getMonthlySales($year, $_POST['month'] ?? null);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'get_sales_overview':
                $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
                $result = $controller->getSalesOverview($year);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
