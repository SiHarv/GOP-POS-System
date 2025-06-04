$(document).ready(function() {
    // Get current page path
    const currentPath = window.location.pathname;
    console.log('Current path:', currentPath);

    // Remove active class from all icons
    $('.sidebar-icon').removeClass('active');

    // Add active class based on current page path
    const menuItems = {
        'customers.php': 'customers',
        'receipts.php': 'receipts',
        'items.php': 'items',
        'charge.php': 'charge',
        'sales.php': 'sales'
    };

    // Find which menu item matches the current path
    Object.keys(menuItems).forEach(key => {
        if (currentPath.includes(key)) {
            $(`a[href*="${key}"]`).addClass('active');
            console.log(`Activating ${menuItems[key]} link`);
        }
    });

    // Log how many sidebar icons were found
    console.log('Found sidebar icons:', $('.sidebar-icon').length);

    // Handle logout button click (you can add confirmation if needed)
    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../auth/logout.php';  // Adjust logout URL as needed
        }
    });

    // Add hover effect for better mobile experience
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
    
    // Update sidebar state in local storage for persistence
    $('.sidebar-icon').on('click', function() {
        localStorage.setItem('lastActiveSidebar', $(this).attr('href'));
    });
});