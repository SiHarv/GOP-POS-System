<?php
require_once __DIR__ . '/../connection/DBConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_item_analytics') {
    $period = $_POST['period'] ?? 'monthly';
    $year = $_POST['year'] ?? date('Y');
    $month = $_POST['month'] ?? null;
    $week = $_POST['week'] ?? null;
    $date = $_POST['date'] ?? null;

    $db = new DBConnection();
    $conn = $db->getConnection();

    // Build WHERE clause based on period
    $params = [$year];
    $types = 'i';
    $where = 'WHERE YEAR(c.charge_date) = ?';
    
    if ($period === 'daily' && $date) {
        $where .= ' AND DATE(c.charge_date) = ?';
        $params[] = $date;
        $types .= 's';
    } elseif ($period === 'weekly' && $week) {
        $where .= ' AND WEEK(c.charge_date, 1) = ?';
        $params[] = $week;
        $types .= 'i';
    } elseif ($period === 'monthly' && $month) {
        $where .= ' AND MONTH(c.charge_date) = ?';
        $params[] = $month;
        $types .= 'i';
    }

    // Enhanced query with new columns: cost, price, gross_sales
    $sql = "SELECT 
                i.name AS item_name, 
                i.category,
                i.cost,
                i.price,
                SUM(ci.quantity) AS qty_sold,
                SUM(ci.quantity * ci.price) AS gross_sales,
                SUM((ci.price - i.cost) * ci.quantity) AS profit,
                CASE 
                    WHEN SUM(ci.quantity * ci.price) > 0 
                    THEN ROUND(SUM((ci.price - i.cost) * ci.quantity) / SUM(ci.quantity * ci.price) * 100, 2) 
                    ELSE 0 
                END AS profit_percent
            FROM charge_items ci
            JOIN items i ON ci.item_id = i.id
            JOIN charges c ON ci.charge_id = c.id
            $where
            GROUP BY i.id, i.name, i.category, i.cost, i.price
            ORDER BY qty_sold DESC
            LIMIT 100";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode([
        'success' => true,
        'data' => $data,
        'debug' => [
            'period' => $period,
            'year' => $year,
            'month' => $month,
            'week' => $week,
            'date' => $date,
            'count' => count($data)
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
