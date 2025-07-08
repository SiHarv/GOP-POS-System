<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../controller/backend_receipts.php';
    $receiptsController = new ReceiptsController();

    // Pagination parameters
    $receiptsPerPage = 8;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $receiptsPerPage;

    // Get paginated receipts and total count
    $receipts = $receiptsController->getAllReceipts($receiptsPerPage, $offset);
    $totalReceipts = $receiptsController->getTotalReceiptsCount();
    $totalPages = ceil($totalReceipts / $receiptsPerPage);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Management</title>
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/receipts.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/receipts.js"></script>
    <script src="../../js/filter.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content" style="margin-left: 4.5em; margin-top: 4.5em;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-8">
                    <div class="table-header">
                        <div class="receipts d-flex align-items-center">
                            <!-- <span class="iconify me-1 ms-3" data-icon="solar:chat-square-2-broken" data-width="24"></span> -->
                            <h5 class="fw-bold"><span class="fw-bolder text-danger">Charge</span> History</h5>
                        </div>

                        <!-- Filter Section -->
                        <div class="filter-container mb-3">
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
                                            <label for="receipt-id-filter" class="form-label">Receipt ID</label>
                                            <input type="text" class="form-control form-control-sm" id="receipt-id-filter"
                                                placeholder="Filter by ID">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="customer-name-filter" class="form-label">Customer Name</label>
                                            <input type="text" class="form-control form-control-sm" id="customer-name-filter"
                                                placeholder="Filter by name">
                                        </div>
                                        <!-- Add PO Number filter -->
                                        <div class="col-md-3">
                                            <label for="po-number-filter" class="form-label">P.O. Number</label>
                                            <input type="text" class="form-control form-control-sm" id="po-number-filter"
                                                placeholder="Filter by PO#">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date-from-filter" class="form-label">Date From</label>
                                            <input type="date" class="form-control form-control-sm" id="date-from-filter">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date-to-filter" class="form-label">Date To</label>
                                            <input type="date" class="form-control form-control-sm" id="date-to-filter">
                                        </div>
                                        <div class="col-12 text-end">
                                            <button class="btn btn-primary btn-sm" id="apply-filter">Apply Filter</button>
                                            <button class="btn btn-secondary btn-sm" id="reset-filter">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Filter Section -->

                        <div class="table-wrapper" style="max-height: 615px; overflow-y: auto;">
                            <table class="receipts-table" id="receipts-table">
                                <thead>
                                    <tr>
                                        <th>Receipt NO</th>
                                        <th>Customer Name</th>
                                        <th>P.O. Number</th>
                                        <th>Total Amount</th>
                                        <th>Date</th>
                                        <th>View Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($receipts)) {
                                        echo '<tr><td colspan="6" class="text-center">No receipts found</td></tr>';
                                    } else {
                                        foreach ($receipts as $receipt):
                                    ?>
                                            <tr>
                                                <td><?php echo $receipt['id']; ?></td>
                                                <td><?php echo $receipt['customer_name']; ?></td>
                                                <td><?php echo !empty($receipt['po_number']) ? $receipt['po_number'] : '-'; ?></td>
                                                <td>₱<?php echo number_format($receipt['total_price'], 2); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($receipt['charge_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-link view-receipt"
                                                        data-id="<?php echo $receipt['id']; ?>">
                                                        View
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
                                            Showing <?php echo min($offset + 1, $totalReceipts); ?> to <?php echo min($offset + $receiptsPerPage, $totalReceipts); ?> of <?php echo $totalReceipts; ?> receipts
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
                                    Recent Activity
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="recent-receipts">
                                    <?php
                                    $recentReceipts = array_slice($receipts, 0, 8);
                                    foreach ($recentReceipts as $receipt):
                                    ?>
                                        <div class="recent-item mb-2 p-2 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <small class="fw-bold text-primary">#<?php echo $receipt['id']; ?></small>
                                                <small class="text-success fw-bold">₱<?php echo number_format($receipt['total_price'], 2); ?></small>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                <?php echo substr($receipt['customer_name'], 0, 25); ?><?php echo strlen($receipt['customer_name']) > 25 ? '...' : ''; ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo date('M d, h:i A', strtotime($receipt['charge_date'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (empty($receipts)): ?>
                                        <div class="text-center text-muted py-3">
                                            <span class="iconify" data-icon="solar:inbox-linear" data-width="32"></span>
                                            <div class="mt-2">No recent activity</div>
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
    <?php require_once 'receiptViewModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>

</html>