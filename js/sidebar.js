$(document).ready(function() {
    // Get current page path
    const currentPath = window.location.pathname;
    console.log('Current path:', currentPath);

    // Remove active class from all icons
    $('.sidebar-icon').removeClass('active');

    // Add active class based on current page
    if (currentPath.includes('customers.php')) {
        $('[href*="customers.php"]').addClass('active');
        console.log('Activating customers link');
    } else if (currentPath.includes('receipts.php')) {
        $('[href*="receipts.php"]').addClass('active');
        console.log('Activating receipts link');
    } else if (currentPath.includes('items.php')) {
        $('[href*="items.php"]').addClass('active');
        console.log('Activating items link');
    } else if (currentPath.includes('charge.php')) {
        $('[href*="charge.php"]').addClass('active');
        console.log('Activating charge link');
    } else if (currentPath.includes('sales.php')) {
        $('[href*="sales.php"]').addClass('active');
        console.log('Activating sales link');
    }

    // Log how many sidebar icons were found
    console.log('Found sidebar icons:', $('.sidebar-icon').length);
});