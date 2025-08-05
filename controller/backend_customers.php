<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class CustomersController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllCustomers($limit = null, $offset = 0, $searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        // Build search conditions
        if (!empty($searchParams['name'])) {
            $whereConditions[] = "name LIKE ?";
            $params[] = "%" . $searchParams['name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['phone'])) {
            $whereConditions[] = "phone_number LIKE ?";
            $params[] = "%" . $searchParams['phone'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['address'])) {
            $whereConditions[] = "address LIKE ?";
            $params[] = "%" . $searchParams['address'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['salesman'])) {
            $whereConditions[] = "salesman LIKE ?";
            $params[] = "%" . $searchParams['salesman'] . "%";
            $types .= "s";
        }

        $query = "SELECT * FROM customers";
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY id DESC";

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
        $customers = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        return $customers;
    }

    public function getTotalCustomersCount($searchParams = [])
    {
        $whereConditions = [];
        $params = [];
        $types = "";

        if (!empty($searchParams['name'])) {
            $whereConditions[] = "name LIKE ?";
            $params[] = "%" . $searchParams['name'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['phone'])) {
            $whereConditions[] = "phone_number LIKE ?";
            $params[] = "%" . $searchParams['phone'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['address'])) {
            $whereConditions[] = "address LIKE ?";
            $params[] = "%" . $searchParams['address'] . "%";
            $types .= "s";
        }
        if (!empty($searchParams['salesman'])) {
            $whereConditions[] = "salesman LIKE ?";
            $params[] = "%" . $searchParams['salesman'] . "%";
            $types .= "s";
        }

        $query = "SELECT COUNT(*) FROM customers";
        
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

    public function addCustomer($data)
    {
        try {
            $sql = "INSERT INTO customers (name, phone_number, address, terms, salesman) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssss", $data['name'], $data['phone_number'], $data['address'], $data['terms'], $data['salesman']);

            if ($stmt->execute()) {
                $lastInsertId = $this->conn->insert_id;
                return [
                    'status' => 'success',
                    'customer' => [
                        'id' => $lastInsertId,
                        'name' => $data['name'],
                        'phone_number' => $data['phone_number'],
                        'address' => $data['address'],
                        'terms' => $data['terms'],
                        'salesman' => $data['salesman']
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to add customer'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function editCustomer($data)
    {
        try {
            $sql = "UPDATE customers SET name = ?, phone_number = ?, address = ?, terms = ?, salesman = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssssi",
                $data['name'],
                $data['phone_number'],
                $data['address'],
                $data['terms'],
                $data['salesman'],
                $data['id'],
            );

            if ($stmt->execute()) {
                return [
                    'status' => 'success',
                    'message' => 'Customer updated successfully'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to update customer'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteCustomer($customerId)
    {
        try {
            // Validate input
            if (empty($customerId)) {
                throw new Exception("Customer ID is required");
            }

            // Check if customer exists
            $checkSql = "SELECT id FROM customers WHERE id = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bind_param("i", $customerId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Customer not found");
            }

            // Delete the customer
            $sql = "DELETE FROM customers WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $customerId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return ['status' => 'success', 'message' => 'Customer deleted successfully'];
                } else {
                    return ['status' => 'error', 'message' => 'No customer was deleted'];
                }
            } else {
                throw new Exception("Failed to delete customer");
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_log('Received POST request: ' . json_encode($_POST));
    header('Content-Type: application/json');
    $controller = new CustomersController();

    if ($_POST['action'] === 'add') {
        error_log('Adding customer with data: ' . json_encode($_POST));
        $result = $controller->addCustomer($_POST);
        error_log('Add customer result: ' . json_encode($result));
        echo json_encode($result);
    } elseif ($_POST['action'] === 'edit_customer') {
        $result = $controller->editCustomer($_POST);
        echo json_encode($result);
    } elseif ($_POST['action'] === 'delete_customer') {
        $customerId = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
        $result = $controller->deleteCustomer($customerId);
        echo json_encode($result);
    } elseif ($_POST['action'] === 'search_customers') {
        $searchParams = [
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'salesman' => $_POST['salesman'] ?? ''
        ];
        
        $customersPerPage = 9;
        $currentPage = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $offset = ($currentPage - 1) * $customersPerPage;
        
        $customers = $controller->getAllCustomers($customersPerPage, $offset, $searchParams);
        $totalCustomers = $controller->getTotalCustomersCount($searchParams);
        $totalPages = ceil($totalCustomers / $customersPerPage);
        
        // Generate table HTML
        $tableHtml = '';
        if (empty($customers)) {
            $tableHtml = '<tr><td colspan="6" class="text-center">No customers found</td></tr>';
        } else {
            foreach ($customers as $customer) {
                $tableHtml .= '<tr>';
                $tableHtml .= '<td>' . htmlspecialchars($customer['name']) . '</td>';
                $tableHtml .= '<td>' . htmlspecialchars($customer['phone_number']) . '</td>';
                $tableHtml .= '<td>' . htmlspecialchars($customer['address']) . '</td>';
                $tableHtml .= '<td>' . htmlspecialchars($customer['terms']) . '</td>';
                $tableHtml .= '<td>' . htmlspecialchars($customer['salesman']) . '</td>';
                $tableHtml .= '<td class="action-buttons">';
                $tableHtml .= '<button class="btn btn-sm btn-link edit-btn" ';
                $tableHtml .= 'data-id="' . $customer['id'] . '" ';
                $tableHtml .= 'data-name="' . htmlspecialchars($customer['name']) . '" ';
                $tableHtml .= 'data-phone="' . htmlspecialchars($customer['phone_number']) . '" ';
                $tableHtml .= 'data-address="' . htmlspecialchars($customer['address']) . '" ';
                $tableHtml .= 'data-terms="' . htmlspecialchars($customer['terms']) . '" ';
                $tableHtml .= 'data-salesman="' . htmlspecialchars($customer['salesman']) . '">';
                $tableHtml .= 'EDIT</button>';
                $tableHtml .= '<button class="btn btn-sm btn-link delete-btn" ';
                $tableHtml .= 'data-id="' . $customer['id'] . '" ';
                $tableHtml .= 'data-name="' . htmlspecialchars($customer['name']) . '">';
                $tableHtml .= 'DELETE</button>';
                $tableHtml .= '</td>';
                $tableHtml .= '</tr>';
            }
        }
        
        // Generate pagination HTML
        $paginationHtml = '';
        if ($totalCustomers > 0) {
            // Always show count info
            $paginationHtml .= '<div class="text-center mt-3"><small class="text-muted">';
            if ($totalPages > 1) {
                $paginationHtml .= 'Showing ' . min($offset + 1, $totalCustomers) . ' to ' . min($offset + $customersPerPage, $totalCustomers) . ' of ' . $totalCustomers . ' customers';
            } else {
                $paginationHtml .= 'Showing all ' . $totalCustomers . ' customer' . ($totalCustomers != 1 ? 's' : '');
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
                    'Showing ' . min($offset + 1, $totalCustomers) . ' to ' . min($offset + $customersPerPage, $totalCustomers) . ' of ' . $totalCustomers . ' customers' .
                    '</small></div>';
            }
        }
        
        echo json_encode([
            'success' => true,
            'tableHtml' => $tableHtml,
            'paginationHtml' => $paginationHtml,
            'totalCustomers' => $totalCustomers
        ]);
        exit;
    }
}
