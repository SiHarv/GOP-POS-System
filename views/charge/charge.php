<?php
require_once __DIR__ . '/../../controller/backend_charge.php';
$chargeController = new ChargeController();
$customers = $chargeController->getAllCustomers();
$items = $chargeController->getAllItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Management</title>
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/charge.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/charge.js"></script>
</head>
<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="row">
                <!-- Left side - Item Selection -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <span class="iconify me-1" data-icon="solar:box-linear" data-width="24"></span>
                            <h5 class="card-title mb-0">Available Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                        <div class="card-header d-flex align-items-center">
                            <span class="iconify me-1" data-icon="solar:cart-large-linear" data-width="24"></span>
                            <h5 class="card-title mb-0">Current Charge</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-end mb-3">
                                    <div class="flex-grow-1 me-3">
                                        <label for="customer" class="form-label">Select Customer</label>
                                        <select class="form-select" id="customer" required>
                                            <option value="">Choose customer...</option>
                                            <?php foreach ($customers as $customer): ?>
                                            <option value="<?php echo $customer['id']; ?>"><?php echo $customer['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
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
</body>
</html>