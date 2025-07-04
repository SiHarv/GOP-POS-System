<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/items_error.log');

try {
    require_once __DIR__ . '/../../controller/backend_items.php';
    $itemsController = new ItemsController();

    // Pagination parameters
    $itemsPerPage = 10;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Get paginated items and total count
    $items = $itemsController->getAllItems($itemsPerPage, $offset);
    $totalItems = $itemsController->getTotalItemsCount();
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Get all low stock items for sidebar (not affected by pagination)
    $lowStockItems = $itemsController->getLowStockItems();
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
    <link rel="icon" type="image/x-icon" href="../../icon/temporary-icon.png">
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
                <div class="col-lg-1"></div>
                <div class="col-lg-8 col-md-10" style="margin-left: 3.5em;">
                    <div class="table-header">
                        <div class="items">
                            <button id="addItemBtn" class="add-btn btn btn-success">
                                <span class="iconify" data-icon="solar:add-circle-outline" data-width="24" data-height="24" style="margin-bottom: 2.5px;"></span>
                                ADD ITEM
                            </button>
                            <h5 class="fw-bold"><span class="text-danger fw-bolder">Product</span> Inventory</h5>
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

                        <div class="table-wrapper scrollable-table" style="max-height: 575px; overflow-y: auto;">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 3%; min-width: 30px;">#</th>
                                        <th style="width: 16%; min-width: 160px;">New stock date</th>
                                        <th>New stock</th>
                                        <th>In stock</th>
                                        <th style="width: 7%; min-width: 70px;">Sold by</th>
                                        <th style="width: 18%; min-width: 180px;">Name & Description</th>
                                        <th style="width: 14%; min-width: 120px;">Category</th>
                                        <th style="width: 7%; min-width: 60px;">Cost</th>
                                        <th style="width: 6%; min-width: 60px;">Price</th>
                                        <th style="width: 7%; min-width: 60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <?php foreach ($items as $item):
                                        $rowClass = '';
                                        if ($item['stock'] <= 5) {
                                            $rowClass = 'critical-stock-row';
                                        } elseif ($item['stock'] < 15) {
                                            $rowClass = 'low-stock-row';
                                        }
                                    ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td><?php echo isset($item['date_added']) ? date('m-d-Y', strtotime($item['date_added'])) : '-'; ?></td>
                                            <td><?php echo isset($item['quantity_added']) ? $item['quantity_added'] : '0'; ?></td>
                                            <td class="stock-value"><?php echo $item['stock']; ?></td>
                                            <td><?php echo $item['sold_by']; ?></td>
                                            <td><?php echo $item['name']; ?></td>
                                            <td><?php echo $item['category']; ?></td>
                                            <td>₱<?php echo number_format($item['cost'], 2); ?></td>
                                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-link edit-btn"
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
                            <nav>
                                <ul class="pagination justify-content-center" id="itemsTablePagination" style="padding-top: 1rem;"></ul>
                            </nav>
                            </table>
                        </div>

                        <!-- Bootstrap Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next</a>
                                    </li>
                                </ul>
                                <div class="text-center">
                                    <small class="text-muted">
                                        Showing <?php echo min($offset + 1, $totalItems); ?> to <?php echo min($offset + $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> items
                                    </small>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Low Quantity Items Panel (Right Side) -->
                <div class="col-lg-2 col-md-6 mt-3 mt-lg-0">
                    <?php
                    // Pass low stock items to the sidebar
                    $sidebarItems = $lowStockItems;
                    require_once 'lowQuantityItems.php';
                    ?>
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