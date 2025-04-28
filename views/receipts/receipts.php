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
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/receipts.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="../../js/receipts.js"></script>
</head>
<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="table-header">
                <div class="receipts">
                    <h2>Charge History</h2>
                </div>
                <div class="table-wrapper">
                    <table class="receipts-table">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Customer Name</th>
                                <th>Total Amount</th>
                                <th>Date</th>
                                <th>View Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td><?php echo $receipt['id']; ?></td>
                                <td><?php echo $receipt['customer_name']; ?></td>
                                <td>₱<?php echo number_format($receipt['total_price'], 2); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($receipt['charge_date'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-receipt" 
                                            data-id="<?php echo $receipt['id']; ?>">
                                        <span class="iconify" data-icon="mdi:receipt" data-width="16"></span>
                                        View
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
    <?php require_once 'receiptViewModal.php'; ?>
    <script src="../../js/sidebar.js"></script>
</body>
</html>