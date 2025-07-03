$(document).ready(function() {
    console.log("Sales dashboard initialized");
    function debugData() {
        $.ajax({
            url: '../../controller/backend_sales.php',
            method: 'POST',
            data: { action: 'debug_data' },
            dataType: 'json',
            success: function(response) {
                console.log('Debug Data:', response.data);
            },
            error: function(xhr, status, error) {
                console.error('Debug AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Export function
    window.exportData = function() {
        alert('Export functionality would be implemented here');
    };

    // Initialize
    try {
        debugData();
        console.log("Dashboard initialization complete");
    } catch (e) {
        console.error("Error during dashboard initialization:", e);
    }
});