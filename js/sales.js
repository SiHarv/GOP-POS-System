$(document).ready(function() {
    console.log("Sales dashboard initialized");
    function debugData() {
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: { action: 'debug_data' },
            dataType: 'json',
            success: function(response) {
                console.log('Debug Data:', response.data);
            },
            error: function(xhr, status, error) {
                console.error('Debug AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Export function
    window.exportData = function() {
        const activeTab = $('.nav-link.active').attr('id');
        const year = $('#yearSelect').val();
        
        if (activeTab === 'overview-tab') {
            exportOverviewData(year);
        } else if (activeTab === 'items-tab') {
            exportItemAnalytics(year);
        } else if (activeTab === 'categories-tab') {
            exportCategoryAnalytics(year);
        }
    };

    function exportItemAnalytics(year) {
        const period = $('input[name="itemPeriod"]:checked').val();
        const month = period === 'monthly' ? $('#itemMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#itemWeekSelect').val() : null;
        const date = period === 'daily' ? $('#itemDateSelect').val() : null;

        $.ajax({
            url: '../../controller/backend_item_analytics.php',
            method: 'POST',
            data: {
                action: 'get_item_analytics',
                period: period,
                year: year,
                month: month,
                week: week,
                date: date
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    exportToCSV(response.data, 'item-analytics', [
                        'item_name', 'category', 'qty_sold', 'cost', 'price', 
                        'gross_sales', 'profit', 'profit_percent'
                    ], [
                        'Item Name', 'Category', 'Qty Sold', 'Cost', 'Price',
                        'Gross Sales', 'Profit', 'Profit %'
                    ]);
                } else {
                    alert('No data to export');
                }
            },
            error: function() {
                alert('Error exporting data');
            }
        });
    }

    function exportCategoryAnalytics(year) {
        const period = $('input[name="categoryPeriod"]:checked').val();
        const month = period === 'monthly' ? $('#categoryMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#categoryWeekSelect').val() : null;
        const date = period === 'daily' ? $('#categoryDateSelect').val() : null;

        $.ajax({
            url: '../../controller/backend_category_analytics.php',
            method: 'POST',
            data: {
                action: 'get_category_analytics',
                period: period,
                year: year,
                month: month,
                week: week,
                date: date
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    exportToCSV(response.data, 'category-analytics', [
                        'category', 'items', 'qty_sold', 'revenue', 'profit', 'profit_percent'
                    ], [
                        'Category', 'Items', 'Qty Sold', 'Revenue', 'Profit', 'Profit %'
                    ]);
                } else {
                    alert('No data to export');
                }
            },
            error: function() {
                alert('Error exporting data');
            }
        });
    }

    function exportOverviewData(year) {
        const month = $('#overviewMonthSelect').val();
        
        $.ajax({
            url: '../../controller/backend_overview_analytics.php',
            method: 'POST',
            data: {
                action: 'get_sales_overview',
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    // Transform data to add profit and month name
                    const transformedData = response.data.map(function(row) {
                        const sales = parseFloat(row.monthly_sales || 0);
                        const cost = parseFloat(row.monthly_cost || 0);
                        const profit = sales - cost;
                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                          'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        
                        return {
                            month_name: monthNames[row.month - 1],
                            monthly_sales: sales.toFixed(2),
                            monthly_cost: cost.toFixed(2),
                            profit: profit.toFixed(2),
                            transactions: row.monthly_transactions
                        };
                    });
                    
                    exportToCSV(transformedData, 'overview-analytics', [
                        'month_name', 'monthly_sales', 'monthly_cost', 'profit', 'transactions'
                    ], [
                        'Month', 'Total Sales', 'Total Cost', 'Profit', 'Transactions'
                    ]);
                } else {
                    alert('No data to export');
                }
            },
            error: function() {
                alert('Error exporting data');
            }
        });
    }

    function exportToCSV(data, filename, fields, headers) {
        // Create CSV content
        let csv = headers.join(',') + '\n';
        
        data.forEach(function(row) {
            let values = fields.map(function(field) {
                let value = row[field] || '';
                // Escape quotes and wrap in quotes if contains comma
                if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
                    value = '"' + value.replace(/"/g, '""') + '"';
                }
                return value;
            });
            csv += values.join(',') + '\n';
        });

        // Create download link
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const timestamp = new Date().toISOString().slice(0, 10);
        link.setAttribute('href', url);
        link.setAttribute('download', filename + '_' + timestamp + '.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Initialize
    try {
        debugData();
        console.log("Dashboard initialization complete");
    } catch (e) {
        console.error("Error during dashboard initialization:", e);
    }
});