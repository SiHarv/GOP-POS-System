<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ReceiptsController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllReceipts($limit = null, $offset = 0, $searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        // Build search conditions
        if (!empty($searchParams['receipt_id'])) {
            $whereConditions[] = "c.id LIKE ?";
            $params[] = "%" . $searchParams['receipt_id'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['customer_name'])) {
            $whereConditions[] = "cust.name LIKE ?";
            $params[] = "%" . $searchParams['customer_name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['po_number'])) {
            $whereConditions[] = "c.po_number LIKE ?";
            $params[] = "%" . $searchParams['po_number'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['date_from'])) {
            $whereConditions[] = "DATE(c.charge_date) >= ?";
            $params[] = $searchParams['date_from'];
            $types .= "s";
        }
        if (!empty($searchParams['date_to'])) {
            $whereConditions[] = "DATE(c.charge_date) <= ?";
            $params[] = $searchParams['date_to'];
            $types .= "s";
        }

        $query = "SELECT 
            c.id,
            cust.name AS customer_name,
            c.total_price,
            c.po_number,
            c.charge_date
        FROM charges c
        JOIN customers cust ON c.customer_id = cust.id";

        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $query .= " ORDER BY c.charge_date DESC";

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
        $receipts = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }
        }
        return $receipts;
    }

    public function getTotalReceiptsCount($searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        if (!empty($searchParams['receipt_id'])) {
            $whereConditions[] = "c.id LIKE ?";
            $params[] = "%" . $searchParams['receipt_id'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['customer_name'])) {
            $whereConditions[] = "cust.name LIKE ?";
            $params[] = "%" . $searchParams['customer_name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['po_number'])) {
            $whereConditions[] = "c.po_number LIKE ?";
            $params[] = "%" . $searchParams['po_number'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['date_from'])) {
            $whereConditions[] = "DATE(c.charge_date) >= ?";
            $params[] = $searchParams['date_from'];
            $types .= "s";
        }
        if (!empty($searchParams['date_to'])) {
            $whereConditions[] = "DATE(c.charge_date) <= ?";
            $params[] = $searchParams['date_to'];
            $types .= "s";
        }

        $query = "SELECT COUNT(*) FROM charges c JOIN customers cust ON c.customer_id = cust.id";
        
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

    public function getReceiptDetails($id)
    {
        // This method now delegates to the ReceiptPrintController for better separation
        require_once __DIR__ . '/backend_receipt_print.php';
        $printController = new ReceiptPrintController();
        return $printController->getReceiptDetails($id);
    }
    
    public function updateReceiptItems($receiptId, $items) {
        try {
            $this->conn->begin_transaction();
            
            // Check if receipt is already finalized
            $checkSql = "SELECT finalized FROM charges WHERE id = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bind_param("i", $receiptId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $receiptData = $checkResult->fetch_assoc();

            // Don't allow editing if receipt is already finalized
            if ($receiptData && $receiptData['finalized'] == 1) {
                return [
                    'success' => false,
                    'message' => 'Cannot edit finalized receipt. Stock has already been deducted.'
                ];
            }
            
            // Delete existing charge items for this receipt
            $deleteStmt = $this->conn->prepare("DELETE FROM charge_items WHERE charge_id = ?");
            $deleteStmt->bind_param("i", $receiptId);
            $deleteStmt->execute();
            
            // Insert updated items - NO STOCK ADJUSTMENTS during editing
            $insertStmt = $this->conn->prepare("
                INSERT INTO charge_items (charge_id, item_id, quantity, price, discount_percentage) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $totalAmount = 0;
            
            foreach ($items as $item) {
                // Find item_id by name
                $itemId = $this->getItemIdByName($item['description']);
                
                if ($itemId) {
                    // Calculate values
                    $quantity = floatval($item['qty']);
                    $basePrice = floatval($item['basePrice']);
                    $discountPercent = floatval(str_replace('%', '', $item['discount']));
                    $amount = floatval($item['amount']);
                    
                    // Insert charge item - Stock will be deducted only when finalizing/printing
                    $insertStmt->bind_param("iiddd", $receiptId, $itemId, $quantity, $basePrice, $discountPercent);
                    $insertStmt->execute();
                    
                    $totalAmount += $amount;
                }
            }
            
            // Update charge total
            $updateChargeStmt = $this->conn->prepare("
                UPDATE charges SET total_price = ? 
                WHERE id = ?
            ");
            $updateChargeStmt->bind_param("di", $totalAmount, $receiptId);
            $updateChargeStmt->execute();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Receipt updated successfully. Stock will be deducted when receipt is printed/finalized.',
                'total_price' => $totalAmount
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Error updating receipt: ' . $e->getMessage()
            ];
        }
    }
    
    private function getOriginalReceiptItems($receiptId) {
        $stmt = $this->conn->prepare("
            SELECT ci.*, i.name as item_name 
            FROM charge_items ci 
            JOIN items i ON ci.item_id = i.id 
            WHERE ci.charge_id = ?
        ");
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getItemIdByName($itemName) {
        $stmt = $this->conn->prepare("SELECT id FROM items WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $itemName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['id'] : null;
    }
    
    public function getItemStock($itemName) {
        try {
            $stmt = $this->conn->prepare("SELECT stock FROM items WHERE name = ? LIMIT 1");
            $stmt->bind_param("s", $itemName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row) {
                return [
                    'success' => true,
                    'stock' => floatval($row['stock'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Item not found',
                    'stock' => 0
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting item stock: ' . $e->getMessage(),
                'stock' => 0
            ];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new ReceiptsController();

    if ($_POST['action'] === 'get_details' && isset($_POST['id'])) {
        $result = $controller->getReceiptDetails($_POST['id']);
        echo json_encode($result);
        exit;
    }
    
    if ($_POST['action'] === 'update_receipt') {
        $receiptId = $_POST['receipt_id'] ?? '';
        $items = json_decode($_POST['items'], true) ?? [];
        
        if ($receiptId && !empty($items)) {
            $result = $controller->updateReceiptItems($receiptId, $items);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_item_stock') {
        $itemName = $_POST['item_name'] ?? '';
        
        if ($itemName) {
            $result = $controller->getItemStock($itemName);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item name required', 'stock' => 0]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'search_receipts') {
        $searchParams = [
            'receipt_id' => $_POST['receipt_id'] ?? '',
            'customer_name' => $_POST['customer_name'] ?? '',
            'po_number' => $_POST['po_number'] ?? '',
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? ''
        ];
        
        $receiptsPerPage = 9;
        $currentPage = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $offset = ($currentPage - 1) * $receiptsPerPage;
        
        $receipts = $controller->getAllReceipts($receiptsPerPage, $offset, $searchParams);
        $totalReceipts = $controller->getTotalReceiptsCount($searchParams);
        $totalPages = ceil($totalReceipts / $receiptsPerPage);
        
        // Generate table HTML
        $tableHtml = '';
        if (empty($receipts)) {
            $tableHtml = '<tr><td colspan="6" class="text-center">No receipts found</td></tr>';
        } else {
            foreach ($receipts as $receipt) {
                $tableHtml .= '<tr>';
                $tableHtml .= '<td>' . $receipt['id'] . '</td>';
                $tableHtml .= '<td>' . $receipt['customer_name'] . '</td>';
                $tableHtml .= '<td>' . (!empty($receipt['po_number']) ? $receipt['po_number'] : '-') . '</td>';
                $tableHtml .= '<td>₱' . number_format($receipt['total_price'], 2) . '</td>';
                $tableHtml .= '<td>' . date('M d, Y h:i A', strtotime($receipt['charge_date'])) . '</td>';
                $tableHtml .= '<td><button class="btn btn-link view-receipt" data-id="' . $receipt['id'] . '">View</button></td>';
                $tableHtml .= '</tr>';
            }
        }
        
        // Generate pagination HTML
        $paginationHtml = '';
        if ($totalReceipts > 0) {
            // Always show count info
            $paginationHtml .= '<div class="text-center mt-3"><small class="text-muted">';
            if ($totalPages > 1) {
                $paginationHtml .= 'Showing ' . min($offset + 1, $totalReceipts) . ' to ' . min($offset + $receiptsPerPage, $totalReceipts) . ' of ' . $totalReceipts . ' receipts';
            } else {
                $paginationHtml .= 'Showing all ' . $totalReceipts . ' receipt' . ($totalReceipts != 1 ? 's' : '');
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
                    'Showing ' . min($offset + 1, $totalReceipts) . ' to ' . min($offset + $receiptsPerPage, $totalReceipts) . ' of ' . $totalReceipts . ' receipts' .
                    '</small></div>';
            }
        }
        
        echo json_encode([
            'success' => true,
            'tableHtml' => $tableHtml,
            'paginationHtml' => $paginationHtml,
            'totalReceipts' => $totalReceipts
        ]);
        exit;
    }
}
