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

    public function getConnection()
    {
        return $this->conn;
    }

    public function getAllItems($limit = null, $offset = 0, $searchParams = [])
    {
        $whereConditions = ["(i.status IS NULL OR i.status = 'active')"]; // Exclude deleted items
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

        $query .= " WHERE " . implode(" AND ", $whereConditions);

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
        $whereConditions = ["(i.status IS NULL OR i.status = 'active')"]; // Exclude deleted items
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

        $query = "SELECT COUNT(*) FROM items i WHERE " . implode(" AND ", $whereConditions);

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

            $itemId = isset($data['id']) ? (int)$data['id'] : 0;
            if ($itemId <= 0) {
                throw new Exception("Invalid item ID");
            }

            $baseStock = isset($data['stock']) ? (int)$data['stock'] : 0;
            $newStock = isset($data['new_stock']) ? (int)$data['new_stock'] : 0;

            if ($baseStock < 0 || $newStock < 0) {
                throw new Exception("Stock values cannot be negative");
            }

            $currentSql = "SELECT stock FROM items WHERE id = ? FOR UPDATE";
            $currentStmt = $this->conn->prepare($currentSql);
            $currentStmt->bind_param("i", $itemId);
            $currentStmt->execute();
            $currentResult = $currentStmt->get_result();

            if ($currentResult->num_rows === 0) {
                throw new Exception("Item not found");
            }

            $currentRow = $currentResult->fetch_assoc();
            $existingStock = (int)$currentRow['stock'];

            $finalStock = $baseStock + $newStock;

            // Update main item information
            $sql = "UPDATE items SET 
                    name = ?, 
                    category = ?, 
                    sold_by = ?, 
                    cost = ?, 
                    price = ?,
                    stock = ? 
                    WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssddii",
                $data['name'],
                $data['category'],
                $data['sold_by'],
                $data['cost'],
                $data['price'],
                $finalStock,
                $itemId
            );
            $stmt->execute();

            $addedStock = $finalStock - $existingStock;
            if ($addedStock > 0) {
                $historySql = "INSERT INTO item_stock_history (item_id, quantity_added) VALUES (?, ?)";
                $stmt = $this->conn->prepare($historySql);
                $stmt->bind_param("ii", $itemId, $addedStock);
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
                WHERE i.stock <= ? AND (i.status IS NULL OR i.status = 'active')
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

    public function deleteItem($itemId)
    {
        try {
            // Validate input
            if (empty($itemId)) {
                throw new Exception("Item ID is required");
            }

            // Check if item exists
            $checkSql = "SELECT id FROM items WHERE id = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bind_param("s", $itemId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Item not found");
            }

            // Check if item is referenced in charge_items
            $referenceSql = "SELECT COUNT(*) as ref_count FROM charge_items WHERE item_id = ?";
            $refStmt = $this->conn->prepare($referenceSql);
            $refStmt->bind_param("i", $itemId);
            $refStmt->execute();
            $refResult = $refStmt->get_result();
            $refRow = $refResult->fetch_assoc();

            if ($refRow['ref_count'] > 0) {
                // Item is referenced in charges, so we'll mark it as deleted instead
                // First, check if status column exists, if not add it
                $this->ensureStatusColumn();
                
                $sql = "UPDATE items SET status = 'deleted' WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $itemId);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        return ['status' => 'success', 'message' => 'Item deleted successfully (archived due to transaction history)'];
                    } else {
                        return ['status' => 'error', 'message' => 'No item was deleted'];
                    }
                } else {
                    throw new Exception("Failed to delete item");
                }
            } else {
                // Item is not referenced, safe to delete completely
                $sql = "DELETE FROM items WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $itemId);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        return ['status' => 'success', 'message' => 'Item deleted successfully'];
                    } else {
                        return ['status' => 'error', 'message' => 'No item was deleted'];
                    }
                } else {
                    throw new Exception("Failed to delete item");
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function ensureStatusColumn()
    {
        try {
            // Check if status column exists
            $checkColumnSql = "SHOW COLUMNS FROM items LIKE 'status'";
            $result = $this->conn->query($checkColumnSql);
            
            if ($result->num_rows === 0) {
                // Add status column if it doesn't exist
                $addColumnSql = "ALTER TABLE items ADD COLUMN status ENUM('active', 'deleted') DEFAULT 'active'";
                $this->conn->query($addColumnSql);
            }
        } catch (Exception $e) {
            // Column might already exist, continue
        }
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
            case 'delete_item':
                $itemId = isset($_POST['item_id']) ? $_POST['item_id'] : '';
                $result = $controller->deleteItem($itemId);
                echo json_encode($result);
                break;
            case 'search_items':
                $searchParams = [
                    'id' => $_POST['id'] ?? '',
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
                        $tableHtml .= '<td>' . htmlspecialchars($item['id']) . '</td>';
                        $tableHtml .= '<td>' . (isset($item['date_added']) ? date('m-d-Y', strtotime($item['date_added'])) : '-') . '</td>';
                        $tableHtml .= '<td>' . (isset($item['quantity_added']) ? $item['quantity_added'] : '0') . '</td>';
                        $tableHtml .= '<td class="stock-value">' . $item['stock'] . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['sold_by']) . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['name']) . '</td>';
                        $tableHtml .= '<td>' . htmlspecialchars($item['category']) . '</td>';
                        $tableHtml .= '<td>₱' . number_format($item['cost'], 2) . '</td>';
                        $tableHtml .= '<td>₱' . number_format($item['price'], 2) . '</td>';
                        $tableHtml .= '<td class="action-buttons">';
                        $tableHtml .= '<button class="btn btn-sm btn-link edit-btn" ';
                        $tableHtml .= 'data-id="' . $item['id'] . '" ';
                        $tableHtml .= 'data-name="' . htmlspecialchars($item['name']) . '" ';
                        $tableHtml .= 'data-stock="' . $item['stock'] . '" ';
                        $tableHtml .= 'data-sold-by="' . htmlspecialchars($item['sold_by']) . '" ';
                        $tableHtml .= 'data-category="' . htmlspecialchars($item['category']) . '" ';
                        $tableHtml .= 'data-cost="' . $item['cost'] . '" ';
                        $tableHtml .= 'data-price="' . $item['price'] . '">';
                        $tableHtml .= 'EDIT</button>';
                        $tableHtml .= '<button class="btn btn-sm btn-link delete-btn" ';
                        $tableHtml .= 'data-id="' . $item['id'] . '" ';
                        $tableHtml .= 'data-name="' . htmlspecialchars($item['name']) . '">';
                        $tableHtml .= 'DELETE</button>';
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new ItemsController();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'getCategories':
                // Return unique categories as [{id, name}]
                $conn = $controller->getConnection();
                $result = $conn->query("SELECT DISTINCT category FROM items ORDER BY category ASC");
                $categories = [];
                $id = 1;
                while ($row = $result->fetch_assoc()) {
                    $categories[] = [
                        'id' => $id++,
                        'name' => $row['category']
                    ];
                }
                header('Content-Type: application/json');
                echo json_encode($categories);
                exit;
            case 'getItemsByCategory':
                $category = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';
                $conn = $controller->getConnection();
                $stmt = $conn->prepare("SELECT id, name, category, sold_by, cost, price, stock FROM items WHERE category = ? ORDER BY name ASC");
                $stmt->bind_param("s", $category);
                $stmt->execute();
                $result = $stmt->get_result();
                $items = [];
                while ($row = $result->fetch_assoc()) {
                    $items[] = $row;
                }
                header('Content-Type: application/json');
                echo json_encode($items);
                exit;
            case 'getAll':
                header('Content-Type: application/json');
                echo json_encode($controller->getAllItems());
                exit;
        }
    }
}
