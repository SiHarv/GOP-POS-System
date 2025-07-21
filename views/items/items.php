<?php
session_start();
require_once __DIR__ . '/../../auth/check_auth.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/items_error.log');

try {
    require_once __DIR__ . '/../../controller/backend_items.php';
    $itemsController = new ItemsController();

    // Pagination parameters
    $itemsPerPage = 9;
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
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
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
                            <h5 class="fw-bold"><span class="text-danger fw-bolder">Product</span> Inventory</h5>
                            <div class="button-group">
                                <button id="printItemsBtn" class="add-btn btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#printItemsModal">
                                    <span class="iconify" data-icon="solar:printer-outline" data-width="24" data-height="24" style="margin-bottom: 2.5px;"></span>
                                    PRINT ITEMS
                                </button>
                                <button id="addItemBtn" class="add-btn btn btn-success">
                                    <span class="iconify" data-icon="solar:add-circle-outline" data-width="24" data-height="24" style="margin-bottom: 2.5px;"></span>
                                    ADD ITEM
                                </button>
                            </div>
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
                                            <label for="name-filter" class="form-label">Item Name</label>
                                            <input type="text" class="form-control form-control-sm" id="name-filter"
                                                placeholder="Filter by name">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="category-filter" class="form-label">Category</label>
                                            <input type="text" class="form-control form-control-sm" id="category-filter"
                                                placeholder="Filter by category">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sold-by-filter" class="form-label">Sold By</label>
                                            <input type="text" class="form-control form-control-sm" id="sold-by-filter"
                                                placeholder="Filter by unit">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="stock-min-filter" class="form-label">Min Stock</label>
                                            <input type="number" class="form-control form-control-sm" id="stock-min-filter"
                                                placeholder="Min stock">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="stock-max-filter" class="form-label">Max Stock</label>
                                            <input type="number" class="form-control form-control-sm" id="stock-max-filter"
                                                placeholder="Max stock">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">

                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">

                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button class="btn btn-danger btn-sm ms-auto" id="reset-filter">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Search Filter Section -->

                        <div class="table-wrapper">
                            <table class="items-table" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 8%; min-width: 30px;">Item code</th>
                                        <th style="width: 12%; min-width: 160px;">New stock date</th>
                                        <th>New stock</th>
                                        <th>In stock</th>
                                        <th style="width: 7%; min-width: 70px;">Sold by</th>
                                        <th style="width: 20%; min-width: 180px;">Name & Description</th>
                                        <th style="width: 14%; min-width: 120px;">Category</th>
                                        <th style="width: 7%; min-width: 60px;">Cost</th>
                                        <th style="width: 7%; min-width: 60px;">Price</th>
                                        <th style="width: 7%; min-width: 60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <?php
                                    if (empty($items)) {
                                        echo '<tr><td colspan="9" class="text-center">No items found</td></tr>';
                                    } else {
                                        foreach ($items as $item):
                                            $rowClass = '';
                                            if ($item['stock'] <= 5) {
                                                $rowClass = 'critical-stock-row';
                                            } elseif ($item['stock'] < 15) {
                                                $rowClass = 'low-stock-row';
                                            }
                                    ?>
                                            <tr class="<?php echo $rowClass; ?>">
                                                <td><?php echo $item['id']; ?></td>
                                                <td><?php echo isset($item['date_added']) ? date('m-d-Y', strtotime($item['date_added'])) : '-'; ?></td>
                                                <td><?php echo isset($item['quantity_added']) ? $item['quantity_added'] : '0'; ?></td>
                                                <td class="stock-value"><?php echo $item['stock']; ?></td>
                                                <td><?php echo htmlspecialchars($item['sold_by']); ?></td>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                                <td>₱<?php echo number_format($item['cost'], 2); ?></td>
                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-link edit-btn"
                                                        data-id="<?php echo $item['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                                        data-stock="<?php echo $item['stock']; ?>"
                                                        data-sold-by="<?php echo htmlspecialchars($item['sold_by']); ?>"
                                                        data-category="<?php echo htmlspecialchars($item['category']); ?>"
                                                        data-cost="<?php echo $item['cost']; ?>"
                                                        data-price="<?php echo $item['price']; ?>">
                                                        EDIT
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
                                            Showing <?php echo min($offset + 1, $totalItems); ?> to <?php echo min($offset + $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> items
                                        </small>
                                    </div>
                                </nav>
                            <?php endif; ?>
                        </div>
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
    <?php require_once 'itemPrintModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/lowstock.js"></script>
    <script src="../../js/print_item.js"></script>
</body>

</html>