<?php
// Overview Analytics Module
?>
<link rel="stylesheet" href="../../styles/overview_analytics.css">
<div class="overview-analytics-container">
    <div class="row mb-4">
        <div class="col-md-3">
            <select id="overviewMonthSelect" class="form-select">
                <option value="">All Months</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Total Sales</h6>
                    <h2 class="card-title" id="totalSales">₱0.00</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Gross Profit</h6>
                    <h2 class="card-title" id="grossProfit">₱0.00</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Total Transactions</h6>
                    <h2 class="card-title" id="totalTransactions">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Average Transaction</h6>
                    <h2 class="card-title" id="avgTransaction">₱0.00</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Sales Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Sales vs Cost</h5>
                </div>
                <div class="card-body">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../js/overview_analytics.js"></script>
