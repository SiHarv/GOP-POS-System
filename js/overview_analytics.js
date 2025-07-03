$(document).ready(function() {
    let salesChart = null;
    let profitChart = null;

    function loadOverviewData() {
        const year = $('#yearSelect').val();
        const month = $('#overviewMonthSelect').val();

        // Load monthly sales data
        $.ajax({
            url: '../../controller/backend_overview_analytics.php',
            method: 'POST',
            data: {
                action: 'get_monthly_sales',
                year: year,
                month: month
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateSummaryCards(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading monthly sales:', error);
            }
        });

        // Load sales overview for charts
        $.ajax({
            url: '../../controller/backend_overview_analytics.php',
            method: 'POST',
            data: {
                action: 'get_sales_overview',
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCharts(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading sales overview:', error);
            }
        });
    }

    function updateSummaryCards(data) {
        $('#totalSales').text('₱' + parseFloat(data.total_sales).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#grossProfit').text('₱' + parseFloat(data.gross_profit).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalTransactions').text(data.total_transactions);
        $('#avgTransaction').text('₱' + parseFloat(data.avg_transaction).toLocaleString('en-PH', {minimumFractionDigits: 2}));
    }

    function updateCharts(monthlyData) {
        updateSalesChart(monthlyData);
        updateProfitChart(monthlyData);
    }

    function updateSalesChart(monthlyData) {
        const canvas = document.getElementById('salesChart');
        // Completely remove and recreate the canvas to prevent any reuse issues
        if (salesChart) {
            salesChart.destroy();
            const parent = canvas.parentNode;
            parent.removeChild(canvas);
            const newCanvas = document.createElement('canvas');
            newCanvas.id = 'salesChart';
            parent.appendChild(newCanvas);
        }
        // Get the new context after recreating the canvas
        const ctx = document.getElementById('salesChart').getContext('2d');
        const labels = monthlyData.map(d => {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return monthNames[d.month - 1];
        });
        const data = monthlyData.map(d => d.monthly_sales);
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Monthly Sales',
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + value.toLocaleString('en-PH')
                        }
                    }
                }
            }
        });
    }

    function updateProfitChart(monthlyData) {
        const canvas = document.getElementById('profitChart');
        // Completely remove and recreate the canvas to prevent any reuse issues
        if (profitChart) {
            profitChart.destroy();
            const parent = canvas.parentNode;
            parent.removeChild(canvas);
            const newCanvas = document.createElement('canvas');
            newCanvas.id = 'profitChart';
            parent.appendChild(newCanvas);
        }
        // Get the new context after recreating the canvas
        const ctx = document.getElementById('profitChart').getContext('2d');
        const totalSales = monthlyData.reduce((sum, d) => sum + parseFloat(d.monthly_sales), 0);
        const totalCost = monthlyData.reduce((sum, d) => sum + parseFloat(d.monthly_cost), 0);
        const profit = totalSales - totalCost;
        profitChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sales', 'Cost', 'Profit'],
                datasets: [{
                    data: [totalSales, totalCost, profit],
                    backgroundColor: [
                        '#3b82f6',  // blue
                        '#ef4444',  // red
                        '#22c55e'   // green
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Event listeners
    $('#overviewMonthSelect').on('change', loadOverviewData);
    $('#yearSelect').on('change', loadOverviewData);

    // Handle tab activation
    $('#overview-tab').on('shown.bs.tab', function() {
        loadOverviewData();
    });

    // Initial load if we start on the overview tab
    if ($('#overview-tab').hasClass('active')) {
        loadOverviewData();
    }
});
