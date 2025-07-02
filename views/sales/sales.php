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
    <title>Sales Analytics Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../icon/temporary-icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../styles/sales.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid px-4">
            <!-- Header Section -->
            <div class="sales-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold">
                        <span class="iconify me-2" data-icon="solar:chart-2-outline" data-width="32"></span>
                        Sales Analytics Dashboard
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Update your year select to include 2025 explicitly -->
                        <select id="yearSelect" class="form-select" style="width: 100px;">
                            <?php 
                            // Make sure 2025 is included since your test data seems to be from 2025
                            $currentYear = date('Y');
                            $years = range(max(2025, $currentYear), $currentYear - 5);
                            foreach ($years as $y): 
                            ?>
                                <option value="<?= $y ?>" <?= $y == 2025 ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary" onclick="exportData()">
                            <span class="iconify me-1" data-icon="solar:download-outline"></span>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="salesTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                        <span class="iconify me-1" data-icon="solar:chart-outline"></span>
                        Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button">
                        <span class="iconify me-1" data-icon="solar:box-outline"></span>
                        Item Analytics
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button">
                        <span class="iconify me-1" data-icon="solar:list-outline"></span>
                        Category Analytics
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="salesTabContent">
                
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <?php require_once __DIR__ . '/../overview/overview_analytics.php'; ?>
                </div>

                <!-- Items Analytics Tab -->
                <div class="tab-pane fade" id="items" role="tabpanel">
                    <?php require_once __DIR__ . '/../items/item_analytics.php'; ?>
                </div>

                <!-- Categories Analytics Tab -->
                <div class="tab-pane fade" id="categories" role="tabpanel">
                    <?php require_once __DIR__ . '/../categories/category_analytics.php'; ?>
                </div>

            </div>
        </div>
    </main>

    <script src="../../js/sales.js"></script>
</body>
</html>