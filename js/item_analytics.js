$(document).ready(function() {

    // Pagination state
    let itemAnalyticsData = [];
    let itemCurrentPage = 1;
    const itemRowsPerPage = 6;

    function loadItemAnalytics(page = 1) {
        const period = $('input[name="itemPeriod"]:checked').val();
        const year = $('#yearSelect').val();
        const month = period === 'monthly' ? $('#itemMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#itemWeekSelect').val() : null;
        const date = period === 'daily' ? $('#itemDateSelect').val() : null;

        $('#itemsTableBody').html('<tr><td colspan="9">Loading...</td></tr>');

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
                    itemAnalyticsData = response.data;
                    itemCurrentPage = page;
                    updateItemsTable();
                } else {
                    itemAnalyticsData = [];
                    $('#itemsTableBody').html('<tr><td colspan="9">No data found.</td></tr>');
                    $('#itemsTablePagination').empty();
                }
            },
            error: function(xhr, status, error) {
                $('#itemsTableBody').html('<tr><td colspan="9">Error loading data.</td></tr>');
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
            const cost = parseFloat(item.cost || 0);
            const price = parseFloat(item.price || 0);
            const qtySold = parseInt(item.qty_sold || 0);
            const grossSales = parseFloat(item.gross_sales || 0);
            const profit = parseFloat(item.profit || 0);
            const profitPercent = parseFloat(item.profit_percent || 0);
            
            tbody.append(`
                <tr>
                    <td>${i + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${item.category}</td>
                    <td>${qtySold}</td>
                    <td>₱${cost.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td>₱${price.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td>₱${grossSales.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td>₱${profit.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td>${profitPercent.toFixed(2)}%</td>
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
        $('#dateSelectItem').toggle(period === 'daily');
        loadItemAnalytics(1);
    });
    $('#itemMonthSelect, #itemWeekSelect, #itemDateSelect, #yearSelect').on('change', function() { loadItemAnalytics(1); });

    // Initial load
    loadItemAnalytics(1);
});
