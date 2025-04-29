$(document).ready(function() {
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    
    $('.view-receipt').on('click', function() {
        const receiptId = $(this).data('id');
        
        $.ajax({
            url: '../../controller/backend_receipts.php',
            method: 'POST',
            data: {
                action: 'get_details',
                id: receiptId
            },
            success: function(response) {
                // Fill modal with receipt details
                $('#receipt-id').text(response.id);
                $('#receipt-date').text(new Date(response.date).toLocaleString());
                $('#receipt-customer').text(response.customer_name);
                $('#receipt-address').text(response.customer_address);
                
                // Clear and populate items table
                const itemsBody = $('#receipt-items');
                itemsBody.empty();
                
                response.items.forEach(item => {
                    itemsBody.append(`
                        <tr>
                            <td>${item.quantity}</td>
                            <td>${item.name}</td>
                            <td>₱${parseFloat(item.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td>₱${parseFloat(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        </tr>
                    `);
                });
            
                $('#receipt-total').text(parseFloat(response.total_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                
                // Show modal
                receiptModal.show();
            },
            error: function() {
                alert('Error fetching receipt details');
            }
        });
    });

    // Add this print function
    $('#print-receipt').on('click', function() {
        const printContents = document.getElementById('printable-area').innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // Reload the page to restore functionality
    });
});