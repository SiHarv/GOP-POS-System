$(document).ready(function() {
    let salesChart = null;
    let profitChart = null;
    let categoryChart = null;

    // Debug function to check data
    function debugData() {
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: { action: 'debug_data' },
            dataType: 'json',
            success: function(response) {
                console.log('Debug Data:', response.data);
                if (response.data.charges_count === 0) {
                    console.warn('No data found in charges table');
                }
                if (response.data.charge_items_count === 0) {
                    console.warn('No data found in charge_items table');
                }
                if (response.data.items_count === 0) {
                    console.warn('No data found in items table');
                }
            },
            error: function(xhr, status, error) {
                console.error('Debug AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Initialize charts
    function initCharts() {
        // Sales Overview Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Sales',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Profit Chart
        const profitCtx = document.getElementById('profitChart').getContext('2d');
        profitChart = new Chart(profitCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sales', 'Cost'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['#36A2EB', '#FF6384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        categoryChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Load Overview Data
    function loadOverviewData() {
        const year = $('#yearSelect').val();
        const month = $('#monthSelect').val();
        
        console.log('Loading overview data for year:', year, 'month:', month);
        
        // Log what data is actually in your database
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: { action: 'debug_data' },
            dataType: 'json',
            success: function(response) {
                console.log('Database data count:', response.data);
            }
        });
        
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: {
                action: 'get_monthly_sales',
                year: year,
                month: month
            },
            dataType: 'json',
            success: function(response) {
                console.log('Monthly sales response:', response);
                if (response.success) {
                    updateSummaryCards(response.data);
                    updateProfitChart(response.data);
                } else {
                    console.error('Error in response:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });

        // Load monthly overview
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: {
                action: 'get_sales_overview',
                year: year
            },
            dataType: 'json',
            success: function(response) {
                console.log('Sales overview response:', response);
                if (response.success) {
                    updateSalesChart(response.data);
                } else {
                    console.error('Error in response:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Load Item Analytics
    function loadItemAnalytics() {
        const period = $('input[name="itemPeriod"]:checked').val();
        const year = $('#yearSelect').val();
        const month = period === 'monthly' ? $('#itemMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#itemWeekSelect').val() : null;

        console.log('Loading item analytics:', { period, year, month, week });

        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: {
                action: 'get_item_analytics',
                period: period,
                year: year,
                month: month,
                week: week
            },
            dataType: 'json',
            success: function(response) {
                console.log('Item analytics response:', response);
                if (response.success) {
                    updateItemsTable(response.data);
                } else {
                    console.error('Error in response:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Load Category Analytics
    function loadCategoryAnalytics() {
        const period = $('input[name="categoryPeriod"]:checked').val();
        const year = $('#yearSelect').val();
        const month = period === 'monthly' ? $('#categoryMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#categoryWeekSelect').val() : null;

        console.log('Loading category analytics:', { period, year, month, week });

        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: {
                action: 'get_category_analytics',
                period: period,
                year: year,
                month: month,
                week: week
            },
            dataType: 'json',
            success: function(response) {
                console.log('Category analytics response:', response);
                if (response.success) {
                    updateCategoriesTable(response.data);
                    updateCategoryChart(response.data);
                } else {
                    console.error('Error in response:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Update Functions
    function updateSummaryCards(data) {
        console.log('Updating summary cards with:', data);
        $('#totalSales').text('₱' + parseFloat(data.total_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#grossProfit').text('₱' + parseFloat(data.gross_profit || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#totalTransactions').text(data.total_transactions || 0);
        $('#avgTransaction').text('₱' + parseFloat(data.avg_transaction || 0).toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    function updateSalesChart(data) {
        console.log('Updating sales chart with:', data);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const chartData = new Array(12).fill(0);
        
        data.forEach(item => {
            chartData[item.month - 1] = parseFloat(item.monthly_sales || 0);
        });

        salesChart.data.labels = months;
        salesChart.data.datasets[0].data = chartData;
        salesChart.update();
    }

    function updateProfitChart(data) {
        console.log('Updating profit chart with:', data);
        profitChart.data.datasets[0].data = [
            parseFloat(data.total_sales || 0),
            parseFloat(data.total_cost || 0)
        ];
        profitChart.update();
    }

    function updateItemsTable(items) {
        console.log('Updating items table with:', items);
        const tbody = $('#itemsTableBody');
        tbody.empty();

        if (items.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center">No data available</td></tr>');
            return;
        }

        items.forEach(item => {
            const row = `
                <tr>
                    <td><span class="badge bg-primary">#${item.rank}</span></td>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>${parseInt(item.total_quantity || 0).toLocaleString()}</td>
                    <td>₱${parseFloat(item.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>₱${parseFloat(item.profit || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>${parseFloat(item.profit_margin || 0).toFixed(1)}%</td>
                    <td>${item.transaction_count || 0}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function updateCategoriesTable(categories) {
        console.log('Updating categories table with:', categories);
        const tbody = $('#categoriesTableBody');
        tbody.empty();

        if (categories.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center">No data available</td></tr>');
            return;
        }

        categories.forEach(category => {
            const row = `
                <tr>
                    <td><span class="badge bg-success">#${category.rank}</span></td>
                    <td>${category.category}</td>
                    <td>${category.item_count || 0}</td>
                    <td>${parseInt(category.total_quantity || 0).toLocaleString()}</td>
                    <td>₱${parseFloat(category.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>₱${parseFloat(category.profit || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>${parseFloat(category.profit_margin || 0).toFixed(1)}%</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function updateCategoryChart(categories) {
        console.log('Updating category chart with:', categories);
        if (categories.length === 0) {
            categoryChart.data.labels = [];
            categoryChart.data.datasets[0].data = [];
        } else {
            const labels = categories.map(c => c.category);
            const data = categories.map(c => parseFloat(c.total_revenue || 0));
            
            categoryChart.data.labels = labels;
            categoryChart.data.datasets[0].data = data;
        }
        categoryChart.update();
    }

    // Add this function to your sales.js file
    function debugTableStructure() {
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: { action: 'debug_tables' },
            dataType: 'json',
            success: function(response) {
                console.log('Database Structure:', response.data);
                
                // Check the charges table columns
                console.log('Charges table columns:', response.data.charges_structure);
                
                // Check recent charge data
                console.log('Recent charges:', response.data.recent_charges);
                
                // Look for specific columns we need
                let hasChargeId = false;
                let hasChargeDate = false;
                let hasTotalPrice = false;
                
                response.data.charges_structure.forEach(column => {
                    if (column.Field === 'id') hasChargeId = true;
                    if (column.Field === 'charge_date') hasChargeDate = true;
                    if (column.Field === 'total_price') hasTotalPrice = true;
                });
                
                if (!hasChargeId || !hasChargeDate || !hasTotalPrice) {
                    console.error('Missing required columns in charges table!');
                    console.warn('Required: id, charge_date, total_price');
                    console.warn('Found:', response.data.charges_structure.map(c => c.Field).join(', '));
                }
                
                // Check if we have sample data in the correct year
                if (response.data.recent_charges.length > 0) {
                    const years = [...new Set(response.data.recent_charges.map(
                        c => new Date(c.charge_date).getFullYear()
                    ))];
                    console.log('Years with data:', years);
                    
                    // Force the year selector to match data
                    if (years.length > 0 && !years.includes(parseInt($('#yearSelect').val()))) {
                        console.log('Setting year selector to match available data:', years[0]);
                        $('#yearSelect').val(years[0]).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Debug Tables AJAX Error:', error);
            }
        });
    }

    // Event Listeners
    $('#yearSelect, #monthSelect').on('change', loadOverviewData);
    
    $('input[name="itemPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectItem').toggle(period === 'monthly');
        $('#weekSelectItem').toggle(period === 'weekly');
        loadItemAnalytics();
    });

    $('#itemMonthSelect, #itemWeekSelect').on('change', loadItemAnalytics);

    $('input[name="categoryPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectCategory').toggle(period === 'monthly');
        $('#weekSelectCategory').toggle(period === 'weekly');
        loadCategoryAnalytics();
    });

    $('#categoryMonthSelect, #categoryWeekSelect').on('change', loadCategoryAnalytics);

    // Tab change events
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('data-bs-target');
        
        if (target === '#items') {
            loadItemAnalytics();
        } else if (target === '#categories') {
            loadCategoryAnalytics();
        } else if (target === '#overview') {
            loadOverviewData();
        }
    });

    // Export function
    window.exportData = function() {
        alert('Export functionality would be implemented here');
    };

    // Initialize
    initCharts();
    debugData(); // Check if we have data
    debugTableStructure();
    loadOverviewData();
});