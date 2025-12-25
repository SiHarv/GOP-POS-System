<!-- Comparative Analytics Module -->
<link rel="stylesheet" href="../../styles/comparative_analytics.css">
<div class="comparative-analytics-container">
    <!-- Comparison Type Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="comparisonType" id="monthlyComparison" value="monthly" checked>
                <label class="btn btn-outline-primary" for="monthlyComparison">Monthly Comparison</label>
                <input type="radio" class="btn-check" name="comparisonType" id="yearlyComparison" value="yearly">
                <label class="btn btn-outline-primary" for="yearlyComparison">Yearly Comparison</label>
                <input type="radio" class="btn-check" name="comparisonType" id="customComparison" value="custom">
                <label class="btn btn-outline-primary" for="customComparison">Custom Period</label>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison Controls -->
    <div id="monthlyControls" class="row mb-4">
        <div class="col-md-3">
            <label class="form-label fw-bold">Year</label>
            <select class="form-select" id="monthlyYear">
                <?php 
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2020; $y--): 
                ?>
                    <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <small class="text-muted" style="visibility: hidden;">Placeholder</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Select Month (Period 1)</label>
            <select class="form-select" id="month1Select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <small class="text-muted">The period you want to analyze</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Compare To (Period 2)</label>
            <select class="form-select" id="month2Select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == (date('n') - 1) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <small class="text-muted">The baseline for comparison</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold" style="visibility: hidden;">Action</label>
            <button class="btn btn-primary w-100" onclick="loadComparison()">
                <span class="iconify me-1" data-icon="solar:refresh-outline"></span>
                Compare
            </button>
            <small class="text-muted" style="visibility: hidden;">Placeholder</small>
        </div>
    </div>

    <!-- Yearly Comparison Controls -->
    <div id="yearlyControls" class="row mb-4" style="display: none;">
        <div class="col-md-3">
            <label class="form-label fw-bold">Select Year (Period 1)</label>
            <select class="form-select" id="year1Select">
                <?php 
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2020; $y--): 
                ?>
                    <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <small class="text-muted">The year you want to analyze</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Compare To (Period 2)</label>
            <select class="form-select" id="year2Select">
                <?php 
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2020; $y--): 
                ?>
                    <option value="<?= $y ?>" <?= $y == ($currentYear - 1) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <small class="text-muted">The baseline for comparison</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold" style="visibility: hidden;">Action</label>
            <button class="btn btn-primary w-100" onclick="loadComparison()">
                <span class="iconify me-1" data-icon="solar:refresh-outline"></span>
                Compare
            </button>
            <small class="text-muted" style="visibility: hidden;">Placeholder</small>
        </div>
    </div>

    <!-- Custom Period Controls -->
    <div id="customControls" class="row mb-4" style="display: none;">
        <div class="col-md-3">
            <label class="form-label fw-bold">Period 1 Start</label>
            <input type="date" class="form-control" id="period1Start" value="<?= date('Y-m-01') ?>">
            <small class="text-muted">Current period start</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Period 1 End</label>
            <input type="date" class="form-control" id="period1End" value="<?= date('Y-m-d') ?>">
            <small class="text-muted">Current period end</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Period 2 Start</label>
            <input type="date" class="form-control" id="period2Start" value="<?= date('Y-m-01', strtotime('-1 month')) ?>">
            <small class="text-muted">Baseline period start</small>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Period 2 End</label>
            <input type="date" class="form-control" id="period2End" value="<?= date('Y-m-t', strtotime('-1 month')) ?>">
            <small class="text-muted">Baseline period end</small>
        </div>
    </div>

    <!-- Comparison Summary Cards -->
    <div class="row mb-4" id="comparisonSummary">
        <div class="col-md-3">
            <div class="card comparison-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Current Period Sales</h6>
                    <h3 class="card-title mb-2" id="period1Sales">₱0.00</h3>
                    <small class="text-muted" id="period1Label">Period 1</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card comparison-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Baseline Period Sales</h6>
                    <h3 class="card-title mb-2" id="period2Sales">₱0.00</h3>
                    <small class="text-muted" id="period2Label">Period 2</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card comparison-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Sales Growth</h6>
                    <h3 class="card-title mb-2" id="salesDifference">₱0.00</h3>
                    <small class="growth-indicator" id="growthIndicator">
                        <span class="iconify" data-icon="solar:arrow-up-outline"></span>
                        0% vs baseline
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card comparison-card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Profit Growth</h6>
                    <h3 class="card-title mb-2" id="profitGrowth">₱0.00</h3>
                    <small class="growth-indicator" id="profitGrowthIndicator">
                        <span class="iconify" data-icon="solar:arrow-up-outline"></span>
                        0% vs baseline
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Sales Comparison</h5>
                </div>
                <div class="card-body">
                    <canvas id="comparisonBarChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <canvas id="metricsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Detailed Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="comparisonTable">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Current (Period 1)</th>
                                    <th>Baseline (Period 2)</th>
                                    <th>Difference</th>
                                    <th>Growth %</th>
                                </tr>
                            </thead>
                            <tbody id="comparisonTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Click "Compare" to load data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../js/comparative_analytics.js"></script>
