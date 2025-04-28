<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class CustomersController {
    private $conn;

    public function __construct() {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getAllCustomers() {
        $sql = "SELECT * FROM customers ORDER BY id DESC";
        $result = $this->conn->query($sql);
        $customers = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        return $customers;
    }

    public function addCustomer($data) {
        try {
            $sql = "INSERT INTO customers (name, phone_number, address) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sss", $data['name'], $data['phone_number'], $data['address']);

            if ($stmt->execute()) {
                $lastInsertId = $this->conn->insert_id;
                return [
                    'status' => 'success',
                    'customer' => [
                        'id' => $lastInsertId,
                        'name' => $data['name'],
                        'phone_number' => $data['phone_number'],
                        'address' => $data['address']
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

    public function editCustomer($data) {
        try {
            $sql = "UPDATE customers SET name = ?, phone_number = ?, address = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", 
                $data['name'],
                $data['phone_number'],
                $data['address'],
                $data['id']
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
    }
}