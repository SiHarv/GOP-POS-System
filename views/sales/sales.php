<?php
require_once __DIR__ . '/../../controller/backend_sales.php';
$salesController = new SalesController();
$currentYear = date('Y');
$currentMonth = date('n');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics</title>
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../styles/sales.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Load jQuery first -->
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <!-- Load Chart.js UMD version -->
    <script src="../../js/libraries/chart.umd.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
</head>
<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="sales-header">
                <h2>Sales Analytics</h2>
                <div class="d-flex gap-3">
                    <div class="month-selector">
                        <select id="monthSelect" class="form-select">
                            <?php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($num == $currentMonth) ? 'selected' : '';
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="year-selector">
                        <select id="yearSelect" class="form-select">
                            <?php
                            for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Sales Overview</h5>
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Monthly Summary</h5>
                            <div id="summaryStats">
                                <div class="stat-item">
                                    <span class="label">Monthly Sales:</span>
                                    <span class="value" id="totalSales">₱0.00</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Monthly Profit:</span>
                                    <span class="value" id="totalProfit">₱0.00</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Monthly Transactions:</span>
                                    <span class="value" id="totalTransactions">0</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Average Transaction Value:</span>
                                    <span class="value" id="avgTransaction">₱0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../../js/sales.js"></script>
    <!-- Add this before closing </body> tag -->
    <script>
        console.log('Year:', $('#yearSelect').val());
        console.log('Month:', $('#monthSelect').val());
        console.log('Chart object:', typeof Chart !== 'undefined' ? 'Loaded' : 'Not loaded');
    </script>
</body>
</html>