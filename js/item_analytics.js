$(document).ready(function() {
    function loadItemAnalytics() {
        const period = $('input[name="itemPeriod"]:checked').val();
        const year = $('#yearSelect').val();
        const month = period === 'monthly' ? $('#itemMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#itemWeekSelect').val() : null;

        $('#itemsTableBody').html('<tr><td colspan="8">Loading...</td></tr>');

        $.ajax({
            url: '../../controller/backend_item_analytics.php',
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
                if (response.success && response.data.length > 0) {
                    updateItemsTable(response.data);
                } else {
                    $('#itemsTableBody').html('<tr><td colspan="8">No data found.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#itemsTableBody').html('<tr><td colspan="8">Error loading data.</td></tr>');
            }
        });
    }

    function updateItemsTable(items) {
        const tbody = $('#itemsTableBody');
        tbody.empty();
        items.forEach((item, idx) => {
            tbody.append(`
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${item.category}</td>
                    <td>${item.qty_sold}</td>
                    <td>₱${parseFloat(item.revenue).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>₱${parseFloat(item.profit).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>${item.profit_percent}%</td>
                    <td>${item.transactions}</td>
                </tr>
            `);
        });
    }

    // Event listeners
    $('input[name="itemPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectItem').toggle(period === 'monthly');
        $('#weekSelectItem').toggle(period === 'weekly');
        loadItemAnalytics();
    });
    $('#itemMonthSelect, #itemWeekSelect, #yearSelect').on('change', loadItemAnalytics);

    // Initial load
    loadItemAnalytics();
});
