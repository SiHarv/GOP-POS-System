$(document).ready(function() {
    let comparisonBarChart = null;
    let metricsChart = null;

    // Handle comparison type changes
    $('input[name="comparisonType"]').on('change', function() {
        const type = $(this).val();
        $('#monthlyControls').toggle(type === 'monthly');
        $('#yearlyControls').toggle(type === 'yearly');
        $('#customControls').toggle(type === 'custom');
    });

    // Global function for compare button
    window.loadComparison = function() {
        const type = $('input[name="comparisonType"]:checked').val();
        
        if (type === 'monthly') {
            compareMonthly();
        } else if (type === 'yearly') {
            compareYearly();
        } else if (type === 'custom') {
            compareCustom();
        }
    };

    function compareMonthly() {
        const year = $('#monthlyYear').val();
        const month1 = $('#month1Select').val();
        const month2 = $('#month2Select').val();

        $.ajax({
            url: '../../controller/backend_comparative_analytics.php',
            method: 'POST',
            data: {
                action: 'compare_monthly',
                year: year,
                month1: month1,
                month2: month2
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const label1 = monthNames[month1 - 1] + ' ' + year;
                    const label2 = monthNames[month2 - 1] + ' ' + year;
                    updateComparisonDisplay(response.data, label1, label2);
                }
            },
            error: function() {
                alert('Error loading comparison data');
            }
        });
    }

    function compareYearly() {
        const year1 = $('#year1Select').val();
        const year2 = $('#year2Select').val();

        $.ajax({
            url: '../../controller/backend_comparative_analytics.php',
            method: 'POST',
            data: {
                action: 'compare_yearly',
                year1: year1,
                year2: year2
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateComparisonDisplay(response.data, 'Year ' + year1, 'Year ' + year2);
                }
            },
            error: function() {
                alert('Error loading comparison data');
            }
        });
    }

    function compareCustom() {
        const start1 = $('#period1Start').val();
        const end1 = $('#period1End').val();
        const start2 = $('#period2Start').val();
        const end2 = $('#period2End').val();

        $.ajax({
            url: '../../controller/backend_comparative_analytics.php',
            method: 'POST',
            data: {
                action: 'compare_custom',
                start1: start1,
                end1: end1,
                start2: start2,
                end2: end2
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const label1 = start1 + ' to ' + end1;
                    const label2 = start2 + ' to ' + end2;
                    updateComparisonDisplay(response.data, label1, label2);
                }
            },
            error: function() {
                alert('Error loading comparison data');
            }
        });
    }

    function updateComparisonDisplay(data, label1, label2) {
        const p1 = data.period1;
        const p2 = data.period2;
        const comp = data.comparison;

        // Update summary cards
        $('#period1Sales').text('₱' + parseFloat(p1.total_sales).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#period2Sales').text('₱' + parseFloat(p2.total_sales).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#period1Label').text(label1);
        $('#period2Label').text(label2);

        const salesDiff = parseFloat(p1.total_sales) - parseFloat(p2.total_sales);
        const salesGrowth = comp.total_sales.growth_percent;
        $('#salesDifference').text('₱' + Math.abs(salesDiff).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        
        const growthIcon = salesGrowth >= 0 ? 'solar:arrow-up-outline' : 'solar:arrow-down-outline';
        const growthClass = salesGrowth >= 0 ? 'text-success' : 'text-danger';
        $('#growthIndicator').html(`<span class="iconify" data-icon="${growthIcon}"></span> ${Math.abs(salesGrowth).toFixed(2)}% vs baseline`);
        $('#growthIndicator').removeClass('text-success text-danger').addClass(growthClass);

        const profitDiff = parseFloat(p1.total_profit) - parseFloat(p2.total_profit);
        const profitGrowth = comp.total_profit.growth_percent;
        $('#profitGrowth').text('₱' + Math.abs(profitDiff).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        
        const profitIcon = profitGrowth >= 0 ? 'solar:arrow-up-outline' : 'solar:arrow-down-outline';
        const profitClass = profitGrowth >= 0 ? 'text-success' : 'text-danger';
        $('#profitGrowthIndicator').html(`<span class="iconify" data-icon="${profitIcon}"></span> ${Math.abs(profitGrowth).toFixed(2)}% vs baseline`);
        $('#profitGrowthIndicator').removeClass('text-success text-danger').addClass(profitClass);

        // Update table
        updateComparisonTable(p1, p2, comp, label1, label2);

        // Update charts
        updateComparisonCharts(p1, p2, label1, label2);
    }

    function updateComparisonTable(p1, p2, comp, label1, label2) {
        const tbody = $('#comparisonTableBody');
        tbody.empty();

        const metrics = [
            {key: 'total_sales', label: 'Total Sales', prefix: '₱'},
            {key: 'total_cost', label: 'Total Cost', prefix: '₱'},
            {key: 'total_profit', label: 'Total Profit', prefix: '₱'},
            {key: 'total_transactions', label: 'Transactions', prefix: ''},
            {key: 'total_items_sold', label: 'Items Sold', prefix: ''},
            {key: 'unique_items_sold', label: 'Unique Items', prefix: ''},
            {key: 'avg_transaction', label: 'Avg Transaction', prefix: '₱'}
        ];

        metrics.forEach(function(metric) {
            const val1 = parseFloat(p1[metric.key]);
            const val2 = parseFloat(p2[metric.key]);
            const diff = comp[metric.key].difference;
            const growth = comp[metric.key].growth_percent;
            
            const growthClass = growth >= 0 ? 'text-success' : 'text-danger';
            const growthIcon = growth >= 0 ? '▲' : '▼';
            
            tbody.append(`
                <tr>
                    <td class="fw-bold">${metric.label}</td>
                    <td>${metric.prefix}${val1.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td>${metric.prefix}${val2.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td class="${growthClass}">${metric.prefix}${Math.abs(diff).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td class="${growthClass}">${growthIcon} ${Math.abs(growth).toFixed(2)}%</td>
                </tr>
            `);
        });
    }

    function updateComparisonCharts(p1, p2, label1, label2) {
        // Bar Chart
        const barCtx = document.getElementById('comparisonBarChart').getContext('2d');
        if (comparisonBarChart) {
            comparisonBarChart.destroy();
        }

        comparisonBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Sales', 'Cost', 'Profit', 'Transactions'],
                datasets: [
                    {
                        label: label1,
                        data: [
                            parseFloat(p1.total_sales),
                            parseFloat(p1.total_cost),
                            parseFloat(p1.total_profit),
                            parseFloat(p1.total_transactions)
                        ],
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: label2,
                        data: [
                            parseFloat(p2.total_sales),
                            parseFloat(p2.total_cost),
                            parseFloat(p2.total_profit),
                            parseFloat(p2.total_transactions)
                        ],
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Metrics Chart (Radar)
        const metricsCtx = document.getElementById('metricsChart').getContext('2d');
        if (metricsChart) {
            metricsChart.destroy();
        }

        metricsChart = new Chart(metricsCtx, {
            type: 'radar',
            data: {
                labels: ['Sales', 'Profit', 'Transactions', 'Items Sold', 'Avg Transaction'],
                datasets: [
                    {
                        label: label1,
                        data: [
                            parseFloat(p1.total_sales) / 1000,
                            parseFloat(p1.total_profit) / 1000,
                            parseFloat(p1.total_transactions),
                            parseFloat(p1.total_items_sold),
                            parseFloat(p1.avg_transaction)
                        ],
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgb(59, 130, 246)',
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff'
                    },
                    {
                        label: label2,
                        data: [
                            parseFloat(p2.total_sales) / 1000,
                            parseFloat(p2.total_profit) / 1000,
                            parseFloat(p2.total_transactions),
                            parseFloat(p2.total_items_sold),
                            parseFloat(p2.avg_transaction)
                        ],
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgb(239, 68, 68)',
                        pointBackgroundColor: 'rgb(239, 68, 68)',
                        pointBorderColor: '#fff'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Initialize with default comparison
    loadComparison();
});
