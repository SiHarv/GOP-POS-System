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
        const canvas = document.getElementById('categoryChart');
        // Completely remove and recreate the canvas to prevent any reuse issues
        if (categoryChart) {
            categoryChart.destroy();
            const parent = canvas.parentNode;
            parent.removeChild(canvas);
            const newCanvas = document.createElement('canvas');
            newCanvas.id = 'categoryChart';
            parent.appendChild(newCanvas);
        }
        const ctx = document.getElementById('categoryChart').getContext('2d');
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
    function initializeCategoryAnalytics() {
        // Set default period if none selected
        if (!$('input[name="categoryPeriod"]:checked').length) {
            $('#categoryMonthly').prop('checked', true);
        }
        
        const period = $('input[name="categoryPeriod"]:checked').val();
        $('#monthSelectCategory').toggle(period === 'monthly');
        $('#weekSelectCategory').toggle(period === 'weekly');
        
        loadCategoryAnalytics();
    }

    // Handle period changes
    $('input[name="categoryPeriod"]').on('change', function() {
        const period = $(this).val();
        $('#monthSelectCategory').toggle(period === 'monthly');
        $('#weekSelectCategory').toggle(period === 'weekly');
        loadCategoryAnalytics();
    });

    // Handle select changes
    $('#categoryMonthSelect, #categoryWeekSelect').on('change', loadCategoryAnalytics);
    
    // Handle year changes from the main year select
    $('#yearSelect').on('change', loadCategoryAnalytics);

    // Handle tab activation
    $('#categories-tab').on('shown.bs.tab', function() {
        initializeCategoryAnalytics();
    });

    // Initialize if we start on the categories tab
    if ($('#categories-tab').hasClass('active')) {
        initializeCategoryAnalytics();
    }
});
