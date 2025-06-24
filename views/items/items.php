<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/items_error.log');

try {
    require_once __DIR__ . '/../../controller/backend_items.php';
    $itemsController = new ItemsController();
    $items = $itemsController->getAllItems();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Management</title>
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/items.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/items.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid px-4">
            <div class="row">
                <!-- Main Content Column (Items Table) -->
                <div class="col-lg-8 col-md-10" style="margin-left: 5em;">
                    <div class="table-header">
                        <div class="items">
                            <button id="addItemBtn" class="add-btn">
                                Add Item<span class="iconify" data-icon="solar:add-circle-outline" data-width="24" data-height="24"></span>
                            </button>
                            <h2 style="font-weight: bold;">PRODUCT INVENTORY</h2>
                        </div>

                        <!-- Search Filter -->
                        <div class="search-filter mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="iconify" data-icon="solar:magnifier-outline" data-width="20"></span>
                                </span>
                                <input type="text" id="itemSearchInput" class="form-control" placeholder="Search for items (name, category, stock level...)">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn">
                                    <span class="iconify" data-icon="solar:close-circle-outline" data-width="20"></span> Clear
                                </button>
                            </div>
                        </div>

                        <div class="table-wrapper scrollable-table">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>New stock date</th>
                                        <th>New stock</th>
                                        <th>Current stock</th>
                                        <th>Sold by</th>
                                        <th>Name & description</th>
                                        <th>Category</th>
                                        <th>Cost</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item):
                                        $rowClass = '';
                                        if ($item['stock'] <= 5) {
                                            $rowClass = 'critical-stock-row';
                                        } elseif ($item['stock'] < 15) {
                                            $rowClass = 'low-stock-row';
                                        }
                                    ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td><?php echo isset($item['date_added']) ? date('Y-m-d H:i', strtotime($item['date_added'])) : '-'; ?></td>
                                            <td><?php echo isset($item['quantity_added']) ? $item['quantity_added'] : '0', " (new)"; ?></td>
                                            <td class="stock-value"><?php echo $item['stock']; ?></td>
                                            <td><?php echo $item['sold_by']; ?></td>
                                            <td><?php echo $item['name']; ?></td>
                                            <td><?php echo $item['category']; ?></td>
                                            <td>₱<?php echo number_format($item['cost'], 2); ?></td>
                                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-btn"
                                                    data-id="<?php echo $item['id']; ?>"
                                                    data-name="<?php echo $item['name']; ?>"
                                                    data-stock="<?php echo $item['stock']; ?>"
                                                    data-sold-by="<?php echo $item['sold_by']; ?>"
                                                    data-category="<?php echo $item['category']; ?>"
                                                    data-cost="<?php echo $item['cost']; ?>"
                                                    data-price="<?php echo $item['price']; ?>">
                                                    EDIT
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Low Quantity Items Panel (Right Side) -->
                <div class="col-lg-3 col-md-5 mt-3 mt-lg-0">
                    <?php require_once 'lowQuantityItems.php'; ?>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'itemsAddModal.php'; ?>
    <?php require_once 'itemsEditModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/lowstock.js"></script>
</body>

</html>