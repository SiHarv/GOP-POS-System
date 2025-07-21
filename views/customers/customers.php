<?php
session_start();
require_once __DIR__ . '/../../auth/check_auth.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../controller/backend_customers.php';
    $customersController = new CustomersController();

    // Pagination parameters
    $customersPerPage = 9;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $customersPerPage;

    // Get paginated customers and total count
    $customers = $customersController->getAllCustomers($customersPerPage, $offset);
    $totalCustomers = $customersController->getTotalCustomersCount();
    $totalPages = ceil($totalCustomers / $customersPerPage);
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
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
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
                            <div class="card">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 fw-bold fs-6">Filters</h5>
                                        <button class="btn btn-sm btn-outline-secondary" id="toggle-filters">
                                            <span class="iconify" data-icon="mdi:filter-outline"></span> Show/Hide
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="filter-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="name-filter" class="form-label">Customer Name</label>
                                            <input type="text" class="form-control form-control-sm" id="name-filter"
                                                placeholder="Filter by name">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phone-filter" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control form-control-sm" id="phone-filter"
                                                placeholder="Filter by phone">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="address-filter" class="form-label">Address</label>
                                            <input type="text" class="form-control form-control-sm" id="address-filter"
                                                placeholder="Filter by address">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="salesman-filter" class="form-label">Salesman</label>
                                            <input type="text" class="form-control form-control-sm" id="salesman-filter"
                                                placeholder="Filter by salesman">
                                        </div>
                                        <div class="col-12 text-end">
                                            <button class="btn btn-danger btn-sm" id="reset-filter">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Search Filter Section -->

                        <div class="table-wrapper">
                            <table class="customers-table" id="customers-table">
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
                                <tbody id="customersTableBody">
                                    <?php
                                    if (empty($customers)) {
                                        echo '<tr><td colspan="6" class="text-center">No customers found</td></tr>';
                                    } else {
                                        foreach ($customers as $customer):
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['terms']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['salesman']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-link edit-btn"
                                                        data-id="<?php echo $customer['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                                        data-phone="<?php echo htmlspecialchars($customer['phone_number']); ?>"
                                                        data-address="<?php echo htmlspecialchars($customer['address']); ?>"
                                                        data-terms="<?php echo htmlspecialchars($customer['terms']); ?>"
                                                        data-salesman="<?php echo htmlspecialchars($customer['salesman']); ?>">
                                                        EDIT
                                                        <!-- <span class="iconify" data-icon="solar:pen-linear" data-width="16"></span> -->
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bootstrap Pagination -->
                        <div id="pagination-container">
                            <?php if ($totalPages > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="#" data-page="<?php echo $currentPage - 1; ?>">Previous</a>
                                        </li>

                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                                <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="#" data-page="<?php echo $currentPage + 1; ?>">Next</a>
                                        </li>
                                    </ul>
                                    <div class="text-center">
                                        <small class="text-muted">
                                            Showing <?php echo min($offset + 1, $totalCustomers); ?> to <?php echo min($offset + $customersPerPage, $totalCustomers); ?> of <?php echo $totalCustomers; ?> customers
                                        </small>
                                    </div>
                                </nav>
                            <?php endif; ?>
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