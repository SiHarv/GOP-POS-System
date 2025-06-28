$(document).ready(function() {
    const currentPath = window.location.pathname;
    console.log('Current path:', currentPath);

    $('.sidebar-icon').removeClass('active');

    const menuItems = {
        'receipts.php': 'receipts',
        'charge.php': 'charge',
        'customers.php': 'customers',
        'items.php': 'items',
        'sales.php': 'sales'
    };
    
    Object.keys(menuItems).forEach(key => {
        if (currentPath.includes(key)) {
            $(`a[href*="${key}"]`).addClass('active');
            console.log(`Activating ${menuItems[key]} link`);
        }
    });

    console.log('Found sidebar icons:', $('.sidebar-icon').length);

    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../auth/logout.php'; 
        }
    });

    if (window.innerWidth <= 768) {
        $('.sidebar').on('touchstart', function() {
            $(this).addClass('hover-effect');
        });
        
        $(document).on('touchstart', function(e) {
            if (!$(e.target).closest('.sidebar').length) {
                $('.sidebar').removeClass('hover-effect');
            }
        });
    }
    
    $('.sidebar-icon').on('click', function() {
        localStorage.setItem('lastActiveSidebar', $(this).attr('href'));
    });
});