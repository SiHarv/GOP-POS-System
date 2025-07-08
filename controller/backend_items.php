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

    public function getAllItems($limit = null, $offset = 0, $searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        // Build search conditions
        if (!empty($searchParams['name'])) {
            $whereConditions[] = "i.name LIKE ?";
            $params[] = "%" . $searchParams['name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['category'])) {
            $whereConditions[] = "i.category LIKE ?";
            $params[] = "%" . $searchParams['category'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['sold_by'])) {
            $whereConditions[] = "i.sold_by LIKE ?";
            $params[] = "%" . $searchParams['sold_by'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['stock_min'])) {
            $whereConditions[] = "i.stock >= ?";
            $params[] = $searchParams['stock_min'];
            $types .= "i";
        }
        if (!empty($searchParams['stock_max'])) {
            $whereConditions[] = "i.stock <= ?";
            $params[] = $searchParams['stock_max'];
            $types .= "i";
        }

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
                ) h ON i.id = h.item_id";

        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $query .= " ORDER BY i.id DESC";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $types .= "ii";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
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

    public function getTotalItemsCount($searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        if (!empty($searchParams['name'])) {
            $whereConditions[] = "i.name LIKE ?";
            $params[] = "%" . $searchParams['name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['category'])) {
            $whereConditions[] = "i.category LIKE ?";
            $params[] = "%" . $searchParams['category'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['sold_by'])) {
            $whereConditions[] = "i.sold_by LIKE ?";
            $params[] = "%" . $searchParams['sold_by'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['stock_min'])) {
            $whereConditions[] = "i.stock >= ?";
            $params[] = $searchParams['stock_min'];
            $types .= "i";
        }
        if (!empty($searchParams['stock_max'])) {
            $whereConditions[] = "i.stock <= ?";
            $params[] = $searchParams['stock_max'];
            $types .= "i";
        }

        $query = "SELECT COUNT(*) FROM items i";
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

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
            case 'search_items':
                $searchParams = [
                    'name' => $_POST['name'] ?? '',
                    'category' => $_POST['category'] ?? '',
                    'sold_by' => $_POST['sold_by'] ?? '',
                    'stock_min' => $_POST['stock_min'] ?? '',
                    'stock_max' => $_POST['stock_max'] ?? ''
                ];
                
                $itemsPerPage = 9;
                $currentPage = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
                $offset = ($currentPage - 1) * $itemsPerPage;
                
                $items = $controller->getAllItems($itemsPerPage, $offset, $searchParams);
                $totalItems = $controller->getTotalItemsCount($searchParams);
                $totalPages = ceil($totalItems / $itemsPerPage);
                
                // Generate table HTML
                $tableHtml = '';
                if (empty($items)) {
                    $tableHtml = '<tr><td colspan="9" class="text-center">No items found</td></tr>';
                } else {
                    foreach ($items as $item) {
                        $rowClass = '';
                        if ($item['stock'] <= 5) {
                            $rowClass = 'critical-stock-row';
                        } elseif ($item['stock'] < 15) {
                            $rowClass = 'low-stock-row';
                        }
                        
                        $tableHtml .= '<tr class="' . $rowClass . '">';
                        $tableHtml .= '<td>' . (isset($item['date_added']) ? date('m-d-Y', strtotime($item['date_added'])) : '-') . '</td>';
                        $tableHtml .= '<td>' . (isset($item['quantity_added']) ? $item['quantity_added'] : '0') . '</td>';
                        $tableHtml .= '<td class="stock-value">' . $item['stock'] . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['sold_by']) . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['name']) . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['category']) . '</td>';
                        $tableHtml .= '<td>₱' . number_format($item['cost'], 2) . '</td>';
                        $tableHtml .= '<td>₱' . number_format($item['price'], 2) . '</td>';
                        $tableHtml .= '<td>';
                        $tableHtml .= '<button class="btn btn-sm btn-link edit-btn" ';
                        $tableHtml .= 'data-id="' . $item['id'] . '" ';
                        $tableHtml .= 'data-name="' . htmlspecialchars($item['name']) . '" ';
                        $tableHtml .= 'data-stock="' . $item['stock'] . '" ';
                        $tableHtml .= 'data-sold-by="' . htmlspecialchars($item['sold_by']) . '" ';
                        $tableHtml .= 'data-category="' . htmlspecialchars($item['category']) . '" ';
                        $tableHtml .= 'data-cost="' . $item['cost'] . '" ';
                        $tableHtml .= 'data-price="' . $item['price'] . '">';
                        $tableHtml .= 'EDIT</button>';
                        $tableHtml .= '</td>';
                        $tableHtml .= '</tr>';
                    }
                }
                
                // Generate pagination HTML
                $paginationHtml = '';
                if ($totalItems > 0) {
                    // Always show count info
                    $paginationHtml .= '<div class="text-center mt-3"><small class="text-muted">';
                    if ($totalPages > 1) {
                        $paginationHtml .= 'Showing ' . min($offset + 1, $totalItems) . ' to ' . min($offset + $itemsPerPage, $totalItems) . ' of ' . $totalItems . ' items';
                    } else {
                        $paginationHtml .= 'Showing all ' . $totalItems . ' item' . ($totalItems != 1 ? 's' : '');
                    }
                    $paginationHtml .= '</small></div>';
                    
                    // Add pagination buttons if more than one page
                    if ($totalPages > 1) {
                        $paginationHtml = '<nav class="mt-4"><ul class="pagination justify-content-center">' .
                            '<li class="page-item' . (($currentPage <= 1) ? ' disabled' : '') . '">' .
                            '<a class="page-link" href="#" data-page="' . ($currentPage - 1) . '">Previous</a></li>';
                        
                        for ($i = 1; $i <= $totalPages; $i++) {
                            $paginationHtml .= '<li class="page-item' . (($i == $currentPage) ? ' active' : '') . '">';
                            $paginationHtml .= '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                        }
                        
                        $paginationHtml .= '<li class="page-item' . (($currentPage >= $totalPages) ? ' disabled' : '') . '">' .
                            '<a class="page-link" href="#" data-page="' . ($currentPage + 1) . '">Next</a></li>' .
                            '</ul></nav>' .
                            '<div class="text-center mt-3"><small class="text-muted">' .
                            'Showing ' . min($offset + 1, $totalItems) . ' to ' . min($offset + $itemsPerPage, $totalItems) . ' of ' . $totalItems . ' items' .
                            '</small></div>';
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'tableHtml' => $tableHtml,
                    'paginationHtml' => $paginationHtml,
                    'totalItems' => $totalItems
                ]);
                exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getAll') {
    header('Content-Type: application/json');
    $controller = new ItemsController();
    echo json_encode($controller->getAllItems());
}
