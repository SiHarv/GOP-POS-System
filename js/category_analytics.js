$(document).ready(function() {
    function loadCategoryAnalytics() {
        const period = $('input[name="categoryPeriod"]:checked').val();
        const year = $('#yearSelect').val();
        const month = period === 'monthly' ? $('#categoryMonthSelect').val() : null;
        const week = period === 'weekly' ? $('#categoryWeekSelect').val() : null;

        $('#categoriesTableBody').html('<tr><td colspan="7">Loading...</td></tr>');

        $.ajax({
            url: '../../controller/backend_category_analytics.php',
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
                if (response.success && response.data.length > 0) {
                    updateCategoriesTable(response.data);
                    updateCategoryChart(response.data);
                } else {
                    $('#categoriesTableBody').html('<tr><td colspan="7">No data found.</td></tr>');
                    updateCategoryChart([]);
                }
            },
            error: function(xhr, status, error) {
                $('#categoriesTableBody').html('<tr><td colspan="7">Error loading data.</td></tr>');
                updateCategoryChart([]);
            }
        });
    }

    function updateCategoriesTable(categories) {
        const tbody = $('#categoriesTableBody');
        tbody.empty();
        categories.forEach((cat, idx) => {
            tbody.append(`
                <tr>
                    <td>${idx + 1}</td>
                    <td>${cat.category}</td>
                    <td>${cat.items}</td>
                    <td>${cat.qty_sold}</td>
                    <td>₱${parseFloat(cat.revenue).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>₱${parseFloat(cat.profit).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>${cat.profit_percent}%</td>
                </tr>
            `);
        });
    }
    
    let categoryChart = null;
    function updateCategoryChart(categories) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        if (categoryChart) categoryChart.destroy();
        const labels = categories.map(c => c.category);
        const data = categories.map(c => parseFloat(c.revenue));
        categoryChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#C9CBCF', '#3b82f6', '#eab308', '#22d3ee'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Event listeners
    $('input[name="categoryPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectCategory').toggle(period === 'monthly');
        $('#weekSelectCategory').toggle(period === 'weekly');
        loadCategoryAnalytics();
    });
    $('#categoryMonthSelect, #categoryWeekSelect, #yearSelect').on('change', loadCategoryAnalytics);

    // Initial load
    loadCategoryAnalytics();
});
