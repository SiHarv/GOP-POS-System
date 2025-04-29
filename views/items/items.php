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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/items.js"></script>
</head>
<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="table-header">
                <div class="items">
                    <button id="addItemBtn" class="add-btn">
                        Add Item<span class="iconify" data-icon="gg:add-r" data-width="24" data-height="24"></span>
                    </button>
                    <h2>Items Inventory</h2>
                </div>
                <div class="table-wrapper">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>New Stock Date</th>
                                <th>New Stock</th>
                                <th>Current Stock</th>
                                <th>Sold By</th>
                                <th>Name/ Description</th>
                                <th>Category</th>
                                <th>Cost</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                            <td><?php echo isset($item['date_added']) ? date('Y-m-d H:i', strtotime($item['date_added'])) : '-'; ?></td>
                                <td><?php echo isset($item['quantity_added']) ? $item['quantity_added'] : '0'; ?></td>
                                <td><?php echo $item['stock']; ?></td>
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
                                        <span class="iconify" data-icon="mdi:pencil" data-width="16"></span>
                                        Edit
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
    <?php require_once 'itemsAddModal.php'; ?>
    <?php require_once 'itemsEditModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>
</html>