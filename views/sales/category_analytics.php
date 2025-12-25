<!-- Category Analytics Module -->
<link rel="stylesheet" href="../../styles/category_analytics.css">
<div class="category-analytics-container">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="categoryPeriod" id="categoryDaily" value="daily">
                <label class="btn btn-outline-primary" for="categoryDaily">Daily</label>
                <input type="radio" class="btn-check" name="categoryPeriod" id="categoryWeekly" value="weekly">
                <label class="btn btn-outline-primary" for="categoryWeekly">Weekly</label>
                <input type="radio" class="btn-check" name="categoryPeriod" id="categoryMonthly" value="monthly" checked>
                <label class="btn btn-outline-primary" for="categoryMonthly">Monthly</label>
                <input type="radio" class="btn-check" name="categoryPeriod" id="categoryYearly" value="yearly">
                <label class="btn btn-outline-primary" for="categoryYearly">Yearly</label>
            </div>
        </div>
        <div class="col-md-3" id="monthSelectCategory">
            <select class="form-select" id="categoryMonthSelect">
                <option value="">All Months</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3" id="weekSelectCategory" style="display: none;">
            <select class="form-select" id="categoryWeekSelect">
                <?php 
                $currentYear = date('Y');
                $currentWeek = date('W');
                for ($w = 1; $w <= 52; $w++): 
                    $dto = new DateTime();
                    $dto->setISODate($currentYear, $w);
                    $weekStart = $dto->format('M d');
                    $dto->modify('+6 days');
                    $weekEnd = $dto->format('M d');
                ?>
                    <option value="<?= $w ?>" <?= $w == $currentWeek ? 'selected' : '' ?>>
                        Week <?= $w ?> (<?= $weekStart ?> - <?= $weekEnd ?>)
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3" id="dateSelectCategory" style="display: none;">
            <input type="date" class="form-control" id="categoryDateSelect" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="analytics-table-container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Category Performance Ranking</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Category</th>
                                        <th>Items</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                        <th>Profit</th>
                                        <th>Profit %</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriesTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                            <nav>
                                <ul class="pagination justify-content-end" id="categoriesTablePagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Category Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../js/category_analytics.js"></script>
