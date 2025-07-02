<?php
require_once __DIR__ . '/../connection/DBConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_category_analytics') {
    $period = $_POST['period'] ?? 'monthly';
    $year = $_POST['year'] ?? date('Y');
    $month = $_POST['month'] ?? null;
    $week = $_POST['week'] ?? null;

    $db = new DBConnection();
    $conn = $db->getConnection();

    $params = [$year];
    $types = 'i';
    $where = 'WHERE YEAR(c.charge_date) = ?';
    if ($month && $period === 'monthly') {
        $where .= ' AND MONTH(c.charge_date) = ?';
        $params[] = $month;
        $types .= 'i';
    }

    $sql = "SELECT i.category AS category,
                COUNT(DISTINCT i.id) AS items,
                SUM(ci.quantity) AS qty_sold,
                SUM(ci.quantity * ci.price) AS revenue,
                SUM((ci.price - i.cost) * ci.quantity) AS profit,
                CASE WHEN SUM(ci.quantity * ci.price) > 0 THEN ROUND(SUM((ci.price - i.cost) * ci.quantity) / SUM(ci.quantity * ci.price) * 100, 2) ELSE 0 END AS profit_percent
            FROM charge_items ci
            JOIN items i ON ci.item_id = i.id
            JOIN charges c ON ci.charge_id = c.id
            $where
            GROUP BY i.category
            ORDER BY qty_sold DESC
            LIMIT 20";

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
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'count' => count($data)
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
