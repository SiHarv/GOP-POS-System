<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../controller/backend_receipts.php';
    $receiptsController = new ReceiptsController();
    $receipts = $receiptsController->getAllReceipts();
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
    <link rel="icon" type="image/x-icon" href="../../icon/temporary-icon.png">
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
    <main class="main-content">
        <div class="container">
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
                                    <input type="text" class="form-control form-control-sm" id="receipt-id-filter" placeholder="Filter by ID">
                                </div>
                                <div class="col-md-3">
                                    <label for="customer-name-filter" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control form-control-sm" id="customer-name-filter" placeholder="Filter by name">
                                </div>
                                <!-- Add PO Number filter -->
                                <div class="col-md-3">
                                    <label for="po-number-filter" class="form-label">P.O. Number</label>
                                    <input type="text" class="form-control form-control-sm" id="po-number-filter" placeholder="Filter by PO#">
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

                <div class="table-wrapper">
                    <table class="receipts-table" id="receipts-table">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
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
                                $receiptCount = count($receipts);

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

                                if ($receiptCount < 10) {
                                    for ($i = 0; $i < (10 - $receiptCount); $i++) {
                                        echo '<tr class="empty-row"><td colspan="6">&nbsp;</td></tr>';
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'receiptViewModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>

</html>