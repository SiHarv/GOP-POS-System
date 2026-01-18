<?php
require_once __DIR__ . '/../connection/DBConnection.php';

header('Content-Type: application/json');

class ReportsController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    // Sales Summary Report
    public function getSalesSummary($startDate, $endDate) {
        // Use line-item sums for revenue/cost to keep profit consistent with table values
        $sql = "SELECT 
                    DATE(c.charge_date) as date,
                    COUNT(DISTINCT c.id) as transactions,
                    SUM(ci.price * ci.quantity) as revenue,
                    SUM(ci.quantity * i.cost) as cost,
                    SUM((ci.price - i.cost) * ci.quantity) as profit,
                    SUM(ci.quantity) as items_sold
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?
                GROUP BY DATE(c.charge_date)
                ORDER BY c.charge_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Inventory Status Report
    public function getInventoryStatus() {
        $sql = "SELECT 
                    i.id,
                    i.name,
                    i.category,
                    i.stock,
                    i.sold_by as unit,
                    i.cost,
                    i.price,
                    (i.stock * i.cost) as stock_value,
                    CASE 
                        WHEN i.stock <= 5 THEN 'Critical'
                        WHEN i.stock < 15 THEN 'Low'
                        ELSE 'Good'
                    END as stock_status
                FROM items i
                ORDER BY i.stock ASC";
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Profit & Loss Report
    public function getProfitLoss($startDate, $endDate) {
        // Align totals with line-item calculations
        $sql = "SELECT 
                    SUM(ci.price * ci.quantity) as total_revenue,
                    SUM(ci.quantity * i.cost) as total_cost,
                    SUM((ci.price - i.cost) * ci.quantity) as gross_profit,
                    COUNT(DISTINCT c.id) as total_transactions,
                    SUM(ci.quantity) as total_items_sold,
                    AVG(c.total_price) as avg_transaction_value
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Calculate additional metrics
        $result['profit_margin'] = $result['total_revenue'] > 0 ? 
            ($result['gross_profit'] / $result['total_revenue']) * 100 : 0;
        
        return $result;
    }

    // Product Performance Report
    public function getProductPerformance($startDate, $endDate) {
        $sql = "SELECT 
                    i.id,
                    i.name,
                    i.category,
                    SUM(ci.quantity) as qty_sold,
                    i.cost,
                    i.price,
                    SUM(ci.quantity * ci.price) as revenue,
                    SUM((ci.price - i.cost) * ci.quantity) as profit,
                    COUNT(DISTINCT c.id) as transactions,
                    i.stock as current_stock
                FROM charge_items ci
                JOIN items i ON ci.item_id = i.id
                JOIN charges c ON ci.charge_id = c.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?
                GROUP BY i.id
                ORDER BY qty_sold DESC
                LIMIT 50";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Transaction Report
    public function getTransactions($startDate, $endDate) {
        $sql = "SELECT 
                    c.id as receipt_id,
                    c.charge_date,
                    c.total_price,
                    c.customer_id,
                    cust.name as customer_name,
                    COUNT(ci.id) as items_count,
                    SUM(ci.quantity) as total_qty,
                    SUM(ci.quantity * i.cost) as cost,
                    SUM((ci.price - i.cost) * ci.quantity) as profit
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN items i ON ci.item_id = i.id
                LEFT JOIN customers cust ON c.customer_id = cust.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?
                GROUP BY c.id
                ORDER BY c.charge_date DESC
                LIMIT 500";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Customer Sales Report
    public function getCustomerSales($startDate, $endDate) {
        $sql = "SELECT 
                    c.customer_id,
                    cust.name as customer_name,
                    COUNT(DISTINCT c.id) as total_purchases,
                    SUM(c.total_price) as total_spent,
                    AVG(c.total_price) as avg_purchase,
                    SUM(ci.quantity) as total_items,
                    MAX(c.charge_date) as last_purchase
                FROM charges c
                LEFT JOIN charge_items ci ON c.id = ci.charge_id
                LEFT JOIN customers cust ON c.customer_id = cust.id
                WHERE DATE(c.charge_date) BETWEEN ? AND ?
                    AND c.customer_id IS NOT NULL 
                    AND c.customer_id != ''
                GROUP BY c.customer_id, cust.name
                ORDER BY total_spent DESC
                LIMIT 100";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Low Stock Alert Report
    public function getLowStockAlert() {
        $sql = "SELECT 
                    i.id,
                    i.name,
                    i.category,
                    i.stock,
                    i.sold_by as unit,
                    i.cost,
                    i.price,
                    (i.stock * i.cost) as stock_value,
                    COALESCE(SUM(ci.quantity), 0) as sold_last_30_days,
                    CASE 
                        WHEN i.stock <= 5 THEN 'Critical'
                        WHEN i.stock < 15 THEN 'Low'
                        ELSE 'Warning'
                    END as alert_level
                FROM items i
                LEFT JOIN charge_items ci ON i.id = ci.item_id
                LEFT JOIN charges c ON ci.charge_id = c.id AND c.charge_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                WHERE i.stock < 15
                GROUP BY i.id
                ORDER BY i.stock ASC, sold_last_30_days DESC";
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get summary statistics for dashboard
    public function getSummaryStats($startDate, $endDate) {
        $profitLoss = $this->getProfitLoss($startDate, $endDate);
        return [
            'total_revenue' => floatval($profitLoss['total_revenue'] ?? 0),
            'total_cost' => floatval($profitLoss['total_cost'] ?? 0),
            'total_profit' => floatval($profitLoss['gross_profit'] ?? 0),
            'total_transactions' => intval($profitLoss['total_transactions'] ?? 0),
            'profit_margin' => round($profitLoss['profit_margin'] ?? 0, 2)
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new ReportsController();
    
    try {
        $startDate = $_POST['startDate'] ?? date('Y-m-01');
        $endDate = $_POST['endDate'] ?? date('Y-m-d');
        
        switch ($_POST['action']) {
            case 'sales_summary':
                $data = $controller->getSalesSummary($startDate, $endDate);
                $summary = $controller->getSummaryStats($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $data, 'summary' => $summary]);
                break;
                
            case 'inventory':
                $data = $controller->getInventoryStatus();
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            case 'profit_loss':
                $data = $controller->getProfitLoss($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            case 'product_performance':
                $data = $controller->getProductPerformance($startDate, $endDate);
                $summary = $controller->getSummaryStats($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $data, 'summary' => $summary]);
                break;
                
            case 'transaction':
                $data = $controller->getTransactions($startDate, $endDate);
                $summary = $controller->getSummaryStats($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $data, 'summary' => $summary]);
                break;
                
            case 'customer_sales':
                $data = $controller->getCustomerSales($startDate, $endDate);
                $summary = $controller->getSummaryStats($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $data, 'summary' => $summary]);
                break;
                
            case 'low_stock':
                $data = $controller->getLowStockAlert();
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
