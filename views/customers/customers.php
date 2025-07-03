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
    <link rel="icon" type="image/x-icon" href="../../icon/temporary-icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/customers.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/customers.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content" style="margin-left: 5.5em; margin-top: 4.5em;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-8">
                    <div class="table-header">
                        <div class="customers">
                            <button id="addCustomerBtn" class="add-btn btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <span class="iconify" data-icon="solar:add-circle-outline" data-width="24" style="margin-bottom: 2.5px;"></span>
                                ADD CUSTOMER
                            </button>
                            <h5 class="text-start fw-bold"><span class="fw-bolder text-danger">Customer</span> Lists</h5>
                        </div>

                        <!-- Search Filter Section -->
                        <div class="search-filter mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="iconify" data-icon="solar:magnifer-linear" data-width="16"></span>
                                        </span>
                                        <input type="text" class="form-control" id="customer-search" placeholder="Search customers by name, phone, address, or salesman...">
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex">
                                    <button class="btn btn-outline-secondary btn-sm" id="clear-search">
                                        <span class="iconify" data-icon="solar:close-circle-linear" data-width="16"></span>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- End Search Filter Section -->

                        <div class="table-wrapper">
                            <table class="customers-table">
                                <thead>
                                    <tr>
                                        <th>Number</th>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Address</th>
                                        <th>Terms (days)</th>
                                        <th>Salesman</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="customersTableBody">
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
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <nav>
                                <ul class="pagination justify-content-end" id="customersTablePagination"></ul>
                            </nav>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-2">
                    <div class="right-sidebar">
                        <!-- Recent Activity Card -->
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0 fw-bold">
                                    <span class="iconify me-1" data-icon="solar:clock-circle-linear" data-width="16"></span>
                                    Recent Customers
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="recent-customers">
                                    <?php
                                    $recentCustomers = array_slice($customers, 0);
                                    foreach ($recentCustomers as $customer):
                                    ?>
                                        <div class="recent-item mb-2 p-2 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                                        <?php echo substr($customer['name'], 0, 20); ?><?php echo strlen($customer['name']) > 20 ? '...' : ''; ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <?php echo $customer['phone_number']; ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">
                                                        <?php echo substr($customer['address'], 0, 25); ?><?php echo strlen($customer['address']) > 25 ? '...' : ''; ?>
                                                    </div>
                                                </div>
                                                <small class="text-success fw-bold"><?php echo $customer['terms']; ?></small>
                                            </div>
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                Salesman: <?php echo $customer['salesman']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (empty($customers)): ?>
                                        <div class="text-center text-muted py-3">
                                            <span class="iconify" data-icon="solar:users-group-rounded-linear" data-width="32"></span>
                                            <div class="mt-2">No customers found</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'customersAddModal.php'; ?>
    <?php require_once 'customersEditModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>

</html>