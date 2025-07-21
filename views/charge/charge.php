<?php
session_start();
require_once __DIR__ . '/../../controller/backend_charge.php';

$chargeController = new ChargeController();
$customers = $chargeController->getAllCustomers();
$items = $chargeController->getAllItems();
$selectedCustomerName = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Management</title>
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/charge.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/libraries/sweetalert2.all.min.js"></script>
    <script src="../../js/charge.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <?php require_once '../../auth/check_auth.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="row">
                <div class="mb-2">
                    <h5 class="text-start fw-bold"><span class="text-danger fw-bolder">Charge</span> Management</h5>
                </div>
                <!-- Left side - Item Selection -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center" style="background-color: #000000; color: white;">
                            <span class="iconify me-1" data-icon="solar:box-linear" data-width="24"></span>
                            <h5 class="card-title mb-0">AVAILABLE ITEMS</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Filter Section -->
                            <div class="search-filter mb-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <span class="iconify" data-icon="solar:magnifer-linear" data-width="16"></span>
                                            </span>
                                            <input type="text" class="form-control" id="item-search" placeholder="Search items by name or category...">
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex">
                                        <button class="btn btn-outline-secondary btn-sm" id="clear-item-search">
                                            <span class="iconify" data-icon="solar:close-circle-linear" data-width="16"></span>
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- End Search Filter Section -->

                            <div class="table-responsive">
                                <table class="table table-hover" id="items-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['name']; ?></td>
                                                <td><?php echo $item['category']; ?></td>
                                                <td><?php echo $item['stock']; ?></td>
                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <?php if ($item['stock'] > 0): ?>
                                                        <button class="btn btn-sm btn-primary add-item"
                                                            data-id="<?php echo $item['id']; ?>"
                                                            data-name="<?php echo $item['name']; ?>"
                                                            data-price="<?php echo $item['price']; ?>"
                                                            data-stock="<?php echo $item['stock']; ?>">
                                                            ADD
                                                            <span class="iconify" data-icon="solar:add-circle-outline" data-width="20" data-height="20"></span>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled>
                                                            Out of Stock
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Cart -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center" style="background-color: #000000; color: white;">
                            <span class="iconify me-1" data-icon="solar:cart-large-linear" data-width="24"></span>
                            <h5 class="card-title mb-0">Current Charge</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="mb-3">
                                    <label for="customer" class="form-label">Select Customer</label>
                                    <div class="dropdown">
                                        <input type="text" class="form-control" id="customer" placeholder="Search Customer Name..." autocomplete="off" value="<?php echo htmlspecialchars($selectedCustomerName); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <input type="hidden" id="customer_id" name="customer_id">
                                        <ul class="dropdown-menu w-100" id="customer-dropdown" style="max-height:200px; overflow-y:auto;">
                                            <?php foreach ($customers as $customer): ?>
                                                <li>
                                                    <a class="dropdown-item customer-option" href="#" data-id="<?php echo $customer['id']; ?>" data-name="<?php echo htmlspecialchars($customer['name']); ?>">
                                                        <?php echo htmlspecialchars($customer['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Add P.O. number input -->
                                <div class="mb-3">
                                    <label for="po_number" class="form-label">P.O. Number</label>
                                    <input type="text" class="form-control" id="po_number" placeholder="Enter P.O. Number">
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button id="process-charge" class="btn btn-success">Process Charge</button>
                                </div>
                            </div>

                            <div id="cart-items" class="mb-3">
                                <!-- Cart items will be added here dynamically -->
                            </div>

                            <div class="total-section">
                                <h4>Total: ₱<span id="total-amount">0.00</span></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/dpdown.js"></script>
</body>

</html>