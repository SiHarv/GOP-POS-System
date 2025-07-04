<?php
$currentYear = date('Y');
$currentMonth = date('n');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../icon/icon.png">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../styles/sales.css">
    <link rel="stylesheet" href="../../bootstrap-5.3.6/css/bootstrap.min.css">
    <style>
        html, body {
            overflow: hidden !important;
            height: 100%;
        }
        body {
            position: relative;
            height: 100vh;
        }
        .main-content {
            height: 100vh;
            overflow: hidden;
        }
        /* Reduce spacing above h5 titles in tab panes */
        .tab-pane > .d-flex.mb-3 {
            margin-top: 0 !important;
            margin-bottom: 0.7rem !important;
        }
    </style>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../bootstrap-5.3.6/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/libraries/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php require_once '../renderParts/header.php'; ?>
    <?php require_once '../renderParts/sidebar.php'; ?>
    
    <main class="main-content" >
        <div class="container px-2" style="max-width: 1500px;">
            <!-- Page Title with reduced spacing for header -->
            <!-- <div style="height: 1.2rem;"></div> -->
            <div class="mb-4 mt-2 d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-0" style="letter-spacing: 1px;">Sales</h2>
                <div class="d-flex gap-2">
                    <select id="yearSelect" class="form-select" style="width: 100px;">
                        <?php 
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><span class="fw-bolder text-primary">Overview</span> Analytics</h5>
                    </div>
                    <?php require_once __DIR__ . '/overview_analytics.php'; ?>
                </div>


                <!-- Items Analytics Tab -->
                <div class="tab-pane fade" id="items" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><span class="fw-bolder text-success">Item</span> Analytics</h5>
                    </div>
                    <?php require_once __DIR__ . '/item_analytics.php'; ?>
                </div>


                <!-- Categories Analytics Tab -->
                <div class="tab-pane fade" id="categories" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><span class="fw-bolder text-danger">Category</span> Analytics</h5>
                    </div>
                    <?php require_once __DIR__ . '/category_analytics.php'; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../../js/sales.js"></script>
</body>
</html>