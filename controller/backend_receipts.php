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
        $sql = "SELECT 
            c.id,
            c.charge_date,
            c.total_price,
            c.po_number,
            cust.name AS customer_name,
            cust.address AS customer_address,
            cust.terms AS customer_terms,
            cust.salesman AS customer_salesman,
            i.name AS item_name,
            i.sold_by AS item_unit,
            ci.quantity,
            ci.price AS unit_price,
            ci.discount_percentage,
            (ci.quantity * ci.price * (1 - ci.discount_percentage/100)) AS subtotal
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
                'customer_name' => $row['customer_name'] ?? '',
                'customer_address' => $row['customer_address'] ?? '',
                'customer_terms' => $row['customer_terms'] ?? '',
                'customer_salesman' => $row['customer_salesman'] ?? '',
                'total_price' => floatval($row['total_price']),
                'po_number' => $row['po_number'] ?? '',
                'items' => []
            ];

            do {
                $receipt['items'][] = [
                    'quantity' => intval($row['quantity']),
                    'name' => $row['item_name'] ?? '',
                    'unit' => $row['item_unit'] ?? 'PCS',
                    'unit_price' => floatval($row['unit_price']),
                    'discount_percentage' => floatval($row['discount_percentage']),
                    'subtotal' => floatval($row['subtotal'])
                ];
            } while ($row = $result->fetch_assoc());
        }

        return $receipt;
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
    
    if ($_POST['action'] === 'search_receipts') {
        $searchParams = [
            'receipt_id' => $_POST['receipt_id'] ?? '',
            'customer_name' => $_POST['customer_name'] ?? '',
            'po_number' => $_POST['po_number'] ?? '',
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? ''
        ];
        
        $receiptsPerPage = 8;
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
                $tableHtml .= '<td><button class="btn btn-sm btn-link view-receipt" data-id="' . $receipt['id'] . '">View</button></td>';
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
