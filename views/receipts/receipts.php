<?php
session_start();
require_once __DIR__ . '/../../auth/check_auth.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../controller/backend_receipts.php';
    $receiptsController = new ReceiptsController();

    // Pagination parameters
    $receiptsPerPage = 9;
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
    <script src="../../js/libraries/sweetalert2.all.min.js"></script>
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
                <div class="col-lg-10">
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
                        <!-- End Filter Section -->

                        <div class="table-wrapper" style="max-height: 650px; overflow-y: auto;">
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
                                                    <button class="btn btn-link view-receipt"
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
                <!-- Right Sidebar Removed -->
            </div>
        </div>
    </main>
    <?php require_once 'receiptViewModal.php'; ?>
    <script src="../../js/sidebar.js"></script>

    <script>
        // Check if we need to open a specific receipt
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const receiptId = urlParams.get('open_receipt');

            if (receiptId) {
                // Trigger click on the receipt to open it
                setTimeout(function() {
                    // Find and click the view button for this receipt
                    $(`.view-receipt[data-id="${receiptId}"]`).click();

                    // Remove the parameter from URL without refreshing the page
                    const url = new URL(window.location);
                    url.searchParams.delete('open_receipt');
                    window.history.replaceState({}, document.title, url.toString());
                }, 500);
            }
        });
    </script>
</body>

</html>