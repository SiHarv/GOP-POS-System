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
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }
        .main-content {
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 2rem;
        }
    </style>
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
    <main class="main-content">
        <div class="container px-2" style="max-width: 1500px;">
            <!-- Page Title -->
            <div class="mb-4 mt-2 d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-0" style="letter-spacing: 1px;"><span class="fw-bolder text-danger">Charge</span> History</h2>
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
                                <?php
                                    // Calculate pagination range (show max 10 pages at a time)
                                    $maxPagesToShow = 10;
                                    $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                                    $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                                    
                                    // Adjust start page if we're near the end
                                    if ($endPage - $startPage < $maxPagesToShow - 1) {
                                        $startPage = max(1, $endPage - $maxPagesToShow + 1);
                                    }
                                ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- Previous Button -->
                                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="#" data-page="<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span> Previous
                                            </a>
                                        </li>

                                        <!-- First page if not in range -->
                                        <?php if ($startPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="#" data-page="1">1</a>
                                            </li>
                                            <?php if ($startPage > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Page numbers -->
                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                                <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Last page if not in range -->
                                        <?php if ($endPage < $totalPages): ?>
                                            <?php if ($endPage < $totalPages - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="#" data-page="<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Next Button -->
                                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="#" data-page="<?php echo $currentPage + 1; ?>" aria-label="Next">
                                                Next <span aria-hidden="true">&raquo;</span>
                                            </a>
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