<?php
session_start();
require_once __DIR__ . '/../../auth/check_auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - GOP Marketing</title>
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../styles/reports.css">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>

    <main class="main-content">
        <div class="container px-2" style="max-width: 1500px;">
            <!-- Page Title -->
            <div class="mb-4 mt-2 d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-0" style="letter-spacing: 1px;">Reports</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger" id="exportPdfBtn">
                        <span class="iconify me-1" data-icon="solar:document-download-outline"></span>
                        PDF
                    </button>
                    <button class="btn btn-outline-success" id="exportExcelBtn">
                        <span class="iconify me-1" data-icon="solar:file-download-outline"></span>
                        CSV
                    </button>
                </div>
            </div>

            <!-- Report Type Selection -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                <span class="iconify me-1" data-icon="solar:clipboard-list-outline"></span>
                                Report Type
                            </label>
                            <select class="form-select" id="reportType">
                                <option value="sales_summary">Sales Summary</option>
                                <option value="inventory">Inventory Status</option>
                                <option value="profit_loss">Profit & Loss</option>
                                <option value="product_performance">Product Performance</option>
                                <option value="transaction">Transactions</option>
                                <option value="customer_sales">Customer Sales</option>
                                <option value="low_stock">Low Stock Alert</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                <span class="iconify me-1" data-icon="solar:calendar-outline"></span>
                                Start Date
                            </label>
                            <input type="date" class="form-control" id="startDate" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                <span class="iconify me-1" data-icon="solar:calendar-outline"></span>
                                End Date
                            </label>
                            <input type="date" class="form-control" id="endDate" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="generateReport">
                                <span class="iconify me-1" data-icon="solar:refresh-outline"></span>
                                Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Summary Cards -->
            <div class="row g-3 mb-4" id="reportSummary">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Cost</p>
                                    <h3 class="mb-0 fw-bold" style="color: #dc2626;" id="totalCost">₱0.00</h3>
                                </div>
                                <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <span class="iconify" data-icon="solar:bag-outline" data-width="28" style="color: white;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Revenue</p>
                                    <h3 class="mb-0 fw-bold" style="color: #1e3a8a;" id="totalRevenue">₱0.00</h3>
                                </div>
                                <div class="stat-icon-modern" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <span class="iconify" data-icon="solar:wallet-money-bold" data-width="28" style="color: white;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Profit</p>
                                    <h3 class="mb-0 fw-bold" style="color: #15803d;" id="totalProfit">₱0.00</h3>
                                </div>
                                <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <span class="iconify" data-icon="solar:chart-square-bold" data-width="28" style="color: white;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Profit Margin</p>
                                    <h3 class="mb-0 fw-bold" style="color: #c2410c;" id="profitMargin">0%</h3>
                                </div>
                                <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <span class="iconify" data-icon="solar:chart-2-bold" data-width="28" style="color: white;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0 fw-bold" id="reportTitle">
                            <span class="iconify me-2" data-icon="solar:document-text-bold" style="color: #3b82f6;"></span>
                            <span class="fw-bolder" style="color: #3b82f6;">Report</span> Preview
                        </h5>
                        <small class="text-muted fw-semibold" id="reportDateRange">
                            <span class="iconify me-1" data-icon="solar:calendar-mark-outline"></span>
                        </small>
                    </div>
                    <div class="position-relative" id="searchBarContainer" style="display: none;">
                        <span class="iconify position-absolute" data-icon="solar:magnifer-outline" style="left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></span>
                        <input type="text" class="form-control ps-5" id="tableSearchInput" placeholder="Search in table..." style="font-size: 14px;">
                        <span class="position-absolute text-muted small" id="searchResultCount" style="right: 12px; top: 50%; transform: translateY(-50%);"></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="reportContent">
                        <div class="text-center py-5">
                            <span class="iconify mb-3 d-block" data-icon="solar:document-text-outline" data-width="80" style="color: #cbd5e1;"></span>
                            <p class="text-muted mb-1 fw-semibold">Select a report type and click "Generate" to view the report</p>
                            <small class="text-muted">Choose from 7 comprehensive report types above</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../js/reports.js"></script>
    <script src="../../js/sidebar.js"></script>
</body>
</html>
