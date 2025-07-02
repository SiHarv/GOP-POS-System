<!-- Item Analytics Module -->
<link rel="stylesheet" href="../../styles/item_analytics.css">
<div class="item-analytics-container">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="itemPeriod" id="itemWeekly" value="weekly">
                <label class="btn btn-outline-primary" for="itemWeekly">Weekly</label>
                <input type="radio" class="btn-check" name="itemPeriod" id="itemMonthly" value="monthly" checked>
                <label class="btn btn-outline-primary" for="itemMonthly">Monthly</label>
                <input type="radio" class="btn-check" name="itemPeriod" id="itemYearly" value="yearly">
                <label class="btn btn-outline-primary" for="itemYearly">Yearly</label>
            </div>
        </div>
        <div class="col-md-3" id="monthSelectItem">
            <select class="form-select" id="itemMonthSelect">
                <option value="">All Months</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3" id="weekSelectItem" style="display: none;">
            <select class="form-select" id="itemWeekSelect">
                <?php for ($w = 1; $w <= 52; $w++): ?>
                    <option value="<?= $w ?>">Week <?= $w ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5>Item Performance Ranking</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                            <th>Profit</th>
                            <th>Profit %</th>
                            <th>Transactions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../../js/item_analytics.js"></script>
