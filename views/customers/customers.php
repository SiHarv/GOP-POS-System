<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../controller/backend_customers.php';
    $customersController = new CustomersController();
    $customers = $customersController->getAllCustomers();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Management</title>
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/customers.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/customers.js"></script>
</head>
<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="table-header">
                <div class="customers">
                    <button id="addCustomerBtn" class="add-btn btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <span class="iconify" data-icon="solar:add-circle-outline" data-width="24" style="margin-bottom: 2.5px;"></span>
                        ADD CUSTOMER
                    </button>
                    <h2>CUSTOMER LISTS</h2>
                </div>
                <div class="table-wrapper">
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <!-- <th>Customer ID</th> -->
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Terms (days)</th>
                                <th>Salesman</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['name']; ?></td>
                                <td><?php echo $customer['phone_number']; ?></td>
                                <td><?php echo $customer['address']; ?></td>
                                <td><?php echo $customer['terms']; ?></td>
                                <td><?php echo $customer['salesman']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-link edit-btn" 
                                            data-id="<?php echo $customer['id']; ?>"
                                            data-name="<?php echo $customer['name']; ?>"
                                            data-phone="<?php echo $customer['phone_number']; ?>"
                                            data-address="<?php echo $customer['address']; ?>"
                                            data-terms="<?php echo $customer['terms']; ?>"
                                            data-salesman="<?php echo $customer['salesman']; ?>">
                                            EDIT
                                            <!-- <span class="iconify" data-icon="solar:pen-linear" data-width="16"></span> -->
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'customersAddModal.php'; ?>
    <?php require_once 'customersEditModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>
</html>