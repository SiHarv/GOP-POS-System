<?php
session_start();
require_once '../../auth/check_auth.php';
require_once __DIR__ . '/../../controller/backend_charge.php';

$chargeController = new ChargeController();
$customers = $chargeController->getAllCustomers();
$items = $chargeController->getAllItems();

// Retrieve stored customer data from session
$selectedCustomerName = isset($_SESSION['charge_customer_name']) ? $_SESSION['charge_customer_name'] : "";
$selectedCustomerId = isset($_SESSION['charge_customer_id']) ? $_SESSION['charge_customer_id'] : "";
$selectedPoNumber = isset($_SESSION['charge_po_number']) ? $_SESSION['charge_po_number'] : "";
$selectedSalesman = isset($_SESSION['charge_salesman']) ? $_SESSION['charge_salesman'] : "";
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

    <main class="main-content">
        <div class="container">
            <div class="row gx-3">
                <div class="mb-2">
                    <h5 class="text-start fw-bold"><span class="text-danger fw-bolder">Charge</span> Management</h5>
                </div>
                <!-- Left side - Item Selection -->
                <div class="col-lg-9 col-md-8">
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
                                            <th style="width: 55%;">Name</th>
                                            <th style="width: 15%;">Stock</th>
                                            <th style="width: 15%;">Price</th>
                                            <th style="width: 15%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['name']; ?></td>
                                                <td><?php echo $item['stock']; ?></td>
                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <?php if ($item['stock'] > 0): ?>
                                                        <button class="btn btn-sm btn-primary add-item"
                                                            data-id="<?php echo $item['id']; ?>"
                                                            data-name="<?php echo $item['name']; ?>"
                                                            data-price="<?php echo $item['price']; ?>"
                                                            data-stock="<?php echo $item['stock']; ?>"
                                                            data-unit="<?php echo $item['unit']; ?>">
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
                <div class="col-lg-3 col-md-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center" style="background-color: #000000; color: white;">
                            <span class="iconify me-1" data-icon="solar:cart-large-linear" data-width="24"></span>
                            <h5 class="card-title mb-0">Current Charge</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="mb-2">
                                    <label for="customer" class="form-label small">Select Customer</label>
                                    <div class="dropdown">
                                        <input type="text" class="form-control form-control-sm" id="customer" placeholder="Search Customer Name..." autocomplete="off" value="<?php echo htmlspecialchars($selectedCustomerName); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <input type="hidden" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($selectedCustomerId); ?>">
                                        <ul class="dropdown-menu w-100" id="customer-dropdown" style="max-height:200px; overflow-y:auto;">
                                            <?php foreach ($customers as $customer): ?>
                                                <li>
                                                    <a class="dropdown-item customer-option" href="#" 
                                                       data-id="<?php echo $customer['id']; ?>" 
                                                       data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                                       data-salesman="<?php echo htmlspecialchars($customer['salesman'] ?? ''); ?>">
                                                        <?php echo htmlspecialchars($customer['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Add P.O. number input -->
                                <div class="mb-2">
                                    <label for="po_number" class="form-label small">P.O. Number</label>
                                    <input type="text" class="form-control form-control-sm" id="po_number" placeholder="Enter P.O. Number" value="<?php echo htmlspecialchars($selectedPoNumber); ?>">
                                </div>

                                <!-- Add Salesman input -->
                                <div class="mb-2">
                                    <label for="salesman" class="form-label small">Salesman</label>
                                    <input type="text" class="form-control form-control-sm" id="salesman" placeholder="Enter Salesman Name" value="<?php echo htmlspecialchars($selectedSalesman); ?>">
                                </div>

                                <div class="d-flex justify-content-end mb-2">
                                    <button id="process-charge" class="btn btn-success btn-sm">Process Charge</button>
                                </div>
                            </div>

                            <div id="cart-items" class="mb-2">
                                <!-- Cart items will be added here dynamically -->
                            </div>

                            <div class="total-section">
                                <h5>Total: ₱<span id="total-amount">0.00</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Store customer data in session when values change
        $(document).ready(function() {
            // Function to save data to session
            function saveToSession() {
                $.ajax({
                    url: '../../controller/save_charge_session.php',
                    method: 'POST',
                    data: {
                        customer_name: $('#customer').val(),
                        customer_id: $('#customer_id').val(),
                        po_number: $('#po_number').val(),
                        salesman: $('#salesman').val()
                    },
                    dataType: 'json'
                });
            }
            
            // Save data when customer selection changes
            $(document).on('click', '.customer-option', function() {
                // Wait for the values to be updated then save
                setTimeout(saveToSession, 100);
            });
            
            // Save data when input fields change
            $('#customer, #po_number, #salesman').on('blur', saveToSession);
            
            // Clear session data when charge is processed successfully
            $(document).on('chargeProcessed', function() {
                $.ajax({
                    url: '../../controller/save_charge_session.php',
                    method: 'POST',
                    data: {
                        action: 'clear'
                    },
                    dataType: 'json'
                });
            });
        });
    </script>
    
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/dpdown.js"></script>
</body>

</html>