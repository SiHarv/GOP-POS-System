$(document).ready(function() {

    // Pagination state
    let itemAnalyticsData = [];
    let itemCurrentPage = 1;
    const itemRowsPerPage = 7;

    function loadItemAnalytics(page = 1) {
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
                    itemAnalyticsData = response.data;
                    itemCurrentPage = page;
                    updateItemsTable();
                } else {
                    itemAnalyticsData = [];
                    $('#itemsTableBody').html('<tr><td colspan="8">No data found.</td></tr>');
                    $('#itemsTablePagination').empty();
                }
            },
            error: function(xhr, status, error) {
                $('#itemsTableBody').html('<tr><td colspan="8">Error loading data.</td></tr>');
                $('#itemsTablePagination').empty();
            }
        });
    }

    function updateItemsTable() {
        const tbody = $('#itemsTableBody');
        tbody.empty();
        const startIdx = (itemCurrentPage - 1) * itemRowsPerPage;
        const endIdx = Math.min(startIdx + itemRowsPerPage, itemAnalyticsData.length);
        for (let i = startIdx; i < endIdx; i++) {
            const item = itemAnalyticsData[i];
            tbody.append(`
                <tr>
                    <td>${i + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${item.category}</td>
                    <td>${item.qty_sold}</td>
                    <td>₱${parseFloat(item.revenue).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>₱${parseFloat(item.profit).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>${item.profit_percent}%</td>
                    <td>${item.transactions}</td>
                </tr>
            `);
        }
        renderItemsPagination();
    }

    function renderItemsPagination() {
        const totalPages = Math.ceil(itemAnalyticsData.length / itemRowsPerPage);
        const pag = $('#itemsTablePagination');
        pag.empty();
        if (totalPages <= 1) return;
        for (let i = 1; i <= totalPages; i++) {
            pag.append(`<li class="page-item${i === itemCurrentPage ? ' active' : ''}"><a class="page-link" href="#">${i}</a></li>`);
        }
        // Click event
        pag.find('a').on('click', function(e) {
            e.preventDefault();
            const page = parseInt($(this).text());
            if (page !== itemCurrentPage) {
                itemCurrentPage = page;
                updateItemsTable();
            }
        });
    }

    // Event listeners
    $('input[name="itemPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectItem').toggle(period === 'monthly');
        $('#weekSelectItem').toggle(period === 'weekly');
        loadItemAnalytics(1);
    });
    $('#itemMonthSelect, #itemWeekSelect, #yearSelect').on('change', function() { loadItemAnalytics(1); });

    // Initial load
    loadItemAnalytics(1);
});
