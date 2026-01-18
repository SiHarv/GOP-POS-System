$(document).ready(function() {
    const currentPath = window.location.pathname;
    console.log('Current path:', currentPath);

    $('.sidebar-icon').removeClass('active');

    const menuItems = {
        'receipts.php': 'receipts',
        'charge.php': 'charge',
        'customers.php': 'customers',
        'items.php': 'items',
        'sales.php': 'sales',
        'reports.php': 'reports'
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
            window.location.href = '../../auth/logout.php'; 
        }
    });

    // Mobile menu toggle functionality
    $('#mobileMenuToggle').on('click', function() {
        $('.sidebar').toggleClass('active');
        $('body').toggleClass('sidebar-open');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('#mobileMenuToggle').length) {
                $('.sidebar').removeClass('active');
                $('body').removeClass('sidebar-open');
            }
        }
    });

    // Close sidebar after clicking a menu item on mobile
    $('.sidebar-icon').on('click', function() {
        if (window.innerWidth <= 768) {
            setTimeout(function() {
                $('.sidebar').removeClass('active');
                $('body').removeClass('sidebar-open');
            }, 200);
        }
        localStorage.setItem('lastActiveSidebar', $(this).attr('href'));
    });

    // Handle window resize
    $(window).on('resize', function() {
        if (window.innerWidth > 768) {
            $('.sidebar').removeClass('active');
            $('body').removeClass('sidebar-open');
        }
    });

    // Touch support for mobile
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
});