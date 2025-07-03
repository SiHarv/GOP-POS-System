$(document).ready(function() {

    // Pagination state
    let categoryAnalyticsData = [];
    let categoryCurrentPage = 1;
    const categoryRowsPerPage = 10;

    function loadCategoryAnalytics(page = 1) {
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
                    categoryAnalyticsData = response.data;
                    categoryCurrentPage = page;
                    updateCategoriesTable();
                    updateCategoryChart(response.data);
                } else {
                    categoryAnalyticsData = [];
                    $('#categoriesTableBody').html('<tr><td colspan="7">No data found.</td></tr>');
                    $('#categoriesTablePagination').empty();
                    updateCategoryChart([]);
                }
            },
            error: function(xhr, status, error) {
                $('#categoriesTableBody').html('<tr><td colspan="7">Error loading data.</td></tr>');
                $('#categoriesTablePagination').empty();
                updateCategoryChart([]);
            }
        });
    }

    function updateCategoriesTable() {
        const tbody = $('#categoriesTableBody');
        tbody.empty();
        const startIdx = (categoryCurrentPage - 1) * categoryRowsPerPage;
        const endIdx = Math.min(startIdx + categoryRowsPerPage, categoryAnalyticsData.length);
        for (let i = startIdx; i < endIdx; i++) {
            const cat = categoryAnalyticsData[i];
            tbody.append(`
                <tr>
                    <td>${i + 1}</td>
                    <td>${cat.category}</td>
                    <td>${cat.items}</td>
                    <td>${cat.qty_sold}</td>
                    <td>₱${parseFloat(cat.revenue).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>₱${parseFloat(cat.profit).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                    <td>${cat.profit_percent}%</td>
                </tr>
            `);
        }
        renderCategoriesPagination();
    }

    function renderCategoriesPagination() {
        const totalPages = Math.ceil(categoryAnalyticsData.length / categoryRowsPerPage);
        const pag = $('#categoriesTablePagination');
        pag.empty();
        if (totalPages <= 1) return;
        for (let i = 1; i <= totalPages; i++) {
            pag.append(`<li class="page-item${i === categoryCurrentPage ? ' active' : ''}"><a class="page-link" href="#">${i}</a></li>`);
        }
        // Click event
        pag.find('a').on('click', function(e) {
            e.preventDefault();
            const page = parseInt($(this).text());
            if (page !== categoryCurrentPage) {
                categoryCurrentPage = page;
                updateCategoriesTable();
            }
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
        loadCategoryAnalytics(1);
    });

    // Handle select changes
    $('#categoryMonthSelect, #categoryWeekSelect').on('change', function() { loadCategoryAnalytics(1); });
    // Handle year changes from the main year select
    $('#yearSelect').on('change', function() { loadCategoryAnalytics(1); });

    // Handle tab activation
    $('#categories-tab').on('shown.bs.tab', function() {
        initializeCategoryAnalytics();
    });

    // Initialize if we start on the categories tab
    if ($('#categories-tab').hasClass('active')) {
        initializeCategoryAnalytics();
    }
});
