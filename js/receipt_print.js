$(document).ready(function () {
    // Global variable to store current receipt ID and data
    let currentReceiptId = null;
    let currentReceiptData = null;

    // Handle print receipt button click
    $(document).on('click', '#print-receipt', function () {
        if (!currentReceiptId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No receipt selected for printing',
                confirmButtonColor: '#d33'
            });
            return;
        }

        // Check if receipt is already finalized
        if (currentReceiptData && currentReceiptData.finalized == 1) {
            // Receipt is already finalized, print directly without confirmation
            printReceipt();
        } else {
            // Receipt not finalized, show confirmation dialog
            Swal.fire({
                title: 'Confirm Print Receipt',
                text: 'This will finalize the receipt and subtract items from stock. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Print Receipt',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    finalizeAndPrintReceipt(currentReceiptId);
                }
            });
        }
    });

    // Function to finalize receipt and print
    function finalizeAndPrintReceipt(receiptId) {
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Finalizing receipt and updating stock...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send AJAX request to finalize receipt
        $.ajax({
            url: '../../controller/backend_receipt_print.php',
            method: 'POST',
            data: {
                action: 'finalize_receipt',
                id: receiptId
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // Close loading dialog
                    Swal.close();
                    
                    // Update current receipt data to reflect finalized status
                    if (currentReceiptData) {
                        currentReceiptData.finalized = 1;
                        currentReceiptData.finalized_date = new Date().toISOString();
                    }
                    
                    // Directly trigger the print without showing success message
                    printReceipt();
                    
                    // Optionally refresh the receipts table if it exists
                    if (typeof performSearch === 'function') {
                        performSearch();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to finalize receipt',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to communicate with server. Please try again.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }

    // Function to actually print the receipt
    function printReceipt() {
        // Get the printable content from the modal
        const printContents = document.getElementById("printable-area").innerHTML;
        const originalContents = document.body.innerHTML;

        // Replace the entire body content with just the printable area
        document.body.innerHTML = printContents;
        
        // Trigger the browser's print dialog
        window.print();
        
        // Restore the original page content
        document.body.innerHTML = originalContents;
        
        // Reload the page to restore all JavaScript functionality
        location.reload();
    }

    // Function to set current receipt ID and data (called from receipts.js when viewing a receipt)
    window.setCurrentReceiptId = function(receiptId) {
        currentReceiptId = receiptId;
        // Fetch receipt data to check finalization status
        fetchReceiptData(receiptId);
    };

    // Function to clear current receipt ID and data
    window.clearCurrentReceiptId = function() {
        currentReceiptId = null;
        currentReceiptData = null;
    };

    // Function to fetch receipt data including finalization status
    function fetchReceiptData(receiptId) {
        $.ajax({
            url: '../../controller/backend_receipt_print.php',
            method: 'POST',
            data: {
                action: 'get_receipt_details',
                id: receiptId
            },
            dataType: 'json',
            success: function (response) {
                currentReceiptData = response;
            },
            error: function (xhr, status, error) {
                console.error('Error fetching receipt data:', error);
                currentReceiptData = null;
            }
        });
    };
});
