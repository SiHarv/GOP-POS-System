<?php
require_once __DIR__ . '/../connection/DBConnection.php';

header('Content-Type: application/json');

class ComparativeAnalyticsController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function compareMonthly($year, $month1, $month2) {
        $period1Data = $this->getMonthData($year, $month1);
        $period2Data = $this->getMonthData($year, $month2);
        
        return [
            'period1' => $period1Data,
            'period2' => $period2Data,
            'comparison' => $this->calculateComparison($period1Data, $period2Data)
        ];
    }

    public function compareYearly($year1, $year2) {
        $period1Data = $this->getYearData($year1);
        $period2Data = $this->getYearData($year2);
        
        return [
            'period1' => $period1Data,
            'period2' => $period2Data,
            'comparison' => $this->calculateComparison($period1Data, $period2Data)
        ];
    }

    public function compareCustom($start1, $end1, $start2, $end2) {
        $period1Data = $this->getCustomPeriodData($start1, $end1);
        $period2Data = $this->getCustomPeriodData($start2, $end2);
        
        return [
            'period1' => $period1Data,
            'period2' => $period2Data,
            'comparison' => $this->calculateComparison($period1Data, $period2Data)
        ];
    }

    private function getMonthData($year, $month) {
        $sql = "SELECT 
                    COALESCE(SUM(c.total_price), 0) as total_sales,
                    COALESCE(SUM(ci.quantity * i.cost), 0) as total_cost,
                    COALESCE(SUM((ci.price - i.cost) * ci.quantity), 0) as total_profit,
                    COUNT(DISTINCT c.id) as total_transactions,
                    COALESCE(SUM(ci.quantity), 0) as total_items_sold,
                    COUNT(DISTINCT ci.item_id) as unique_items_sold
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE YEAR(c.charge_date) = ? AND MONTH(c.charge_date) = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $year, $month);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $result['avg_transaction'] = $result['total_transactions'] > 0 ? 
            $result['total_sales'] / $result['total_transactions'] : 0;
        
        return $result;
    }

    private function getYearData($year) {
        $sql = "SELECT 
                    COALESCE(SUM(c.total_price), 0) as total_sales,
                    COALESCE(SUM(ci.quantity * i.cost), 0) as total_cost,
                    COALESCE(SUM((ci.price - i.cost) * ci.quantity), 0) as total_profit,
                    COUNT(DISTINCT c.id) as total_transactions,
                    COALESCE(SUM(ci.quantity), 0) as total_items_sold,
                    COUNT(DISTINCT ci.item_id) as unique_items_sold
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE YEAR(c.charge_date) = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $result['avg_transaction'] = $result['total_transactions'] > 0 ? 
            $result['total_sales'] / $result['total_transactions'] : 0;
        
        return $result;
    }

    private function getCustomPeriodData($startDate, $endDate) {
        $sql = "SELECT 
                    COALESCE(SUM(c.total_price), 0) as total_sales,
                    COALESCE(SUM(ci.quantity * i.cost), 0) as total_cost,
                    COALESCE(SUM((ci.price - i.cost) * ci.quantity), 0) as total_profit,
                    COUNT(DISTINCT c.id) as total_transactions,
                    COALESCE(SUM(ci.quantity), 0) as total_items_sold,
                    COUNT(DISTINCT ci.item_id) as unique_items_sold
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $result['avg_transaction'] = $result['total_transactions'] > 0 ? 
            $result['total_sales'] / $result['total_transactions'] : 0;
        
        return $result;
    }

    private function calculateComparison($period1, $period2) {
        $comparison = [];
        
        $metrics = ['total_sales', 'total_cost', 'total_profit', 'total_transactions', 
                   'total_items_sold', 'unique_items_sold', 'avg_transaction'];
        
        foreach ($metrics as $metric) {
            $val1 = floatval($period1[$metric]);
            $val2 = floatval($period2[$metric]);
            $difference = $val1 - $val2;
            $growth = $val2 != 0 ? (($val1 - $val2) / $val2) * 100 : 0;
            
            $comparison[$metric] = [
                'difference' => $difference,
                'growth_percent' => round($growth, 2)
            ];
        }
        
        return $comparison;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new ComparativeAnalyticsController();
    
    try {
        switch ($_POST['action']) {
            case 'compare_monthly':
                $year = $_POST['year'];
                $month1 = $_POST['month1'];
                $month2 = $_POST['month2'];
                $result = $controller->compareMonthly($year, $month1, $month2);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'compare_yearly':
                $year1 = $_POST['year1'];
                $year2 = $_POST['year2'];
                $result = $controller->compareYearly($year1, $year2);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'compare_custom':
                $start1 = $_POST['start1'];
                $end1 = $_POST['end1'];
                $start2 = $_POST['start2'];
                $end2 = $_POST['end2'];
                $result = $controller->compareCustom($start1, $end1, $start2, $end2);
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
