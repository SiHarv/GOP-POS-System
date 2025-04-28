$(document).ready(function() {
    function initChart() {
        try {
            const ctx = document.getElementById('salesChart').getContext('2d');
            salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sales', 'Cost', 'Profit'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            // Add suggestedMax for dynamic scaling
                            suggestedMax: function(context) {
                                const values = context.chart.data.datasets[0].data;
                                const maxValue = Math.max(...values);
                                return maxValue * 1.1; // Add 10% padding
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing chart:', error);
        }
    }

    function updateChart(data) {
        if (!salesChart) {
            console.error('Chart not initialized');
            return;
        }
        try {
            const sales = parseFloat(data.total_sales) || 0;
            const cost = parseFloat(data.total_cost) || 0;
            const profit = parseFloat(data.gross_profit) || 0;

            salesChart.data.datasets[0].data = [sales, cost, profit];
            salesChart.update();
        } catch (error) {
            console.error('Error updating chart:', error);
        }
    }

    function updateSummary(data) {
        $('#totalSales').text('₱' + (parseFloat(data.total_sales) || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#totalProfit').text('₱' + (parseFloat(data.gross_profit) || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#totalTransactions').text((parseInt(data.transactions) || 0).toLocaleString());
        $('#avgTransaction').text('₱' + (parseFloat(data.avg_transaction) || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    function loadSalesData(year, month) {
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
                console.log('Received data:', response); // Debug log
                if (response) {
                    updateChart(response);
                    updateSummary(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Server response:', xhr.responseText);
            }
        });
    }

    // Initialize chart
    initChart();

    // Load initial data
    loadSalesData($('#yearSelect').val(), $('#monthSelect').val());

    // Handle year and month changes
    $('#yearSelect, #monthSelect').on('change', function() {
        loadSalesData($('#yearSelect').val(), $('#monthSelect').val());
    });
});