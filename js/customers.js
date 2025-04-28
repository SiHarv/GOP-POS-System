$(document).ready(function() {
    const addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    const editCustomerModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));

    // Add click handler for "Add Customer" button
    $('#addCustomerBtn').on('click', function() {
        addCustomerModal.show();
    });

    // Add click handler for edit buttons
    $('.edit-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const phone = $(this).data('phone');
        const address = $(this).data('address');

        // Populate form fields
        $('#edit_customer_id').val(id);
        $('#edit_name').val(name);
        $('#edit_phone_number').val(phone);
        $('#edit_address').val(address);

        // Show modal
        editCustomerModal.show();
    });

    // Handle edit form submission
    $('#editCustomerForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit_customer');

        $.ajax({
            url: '../../controller/backend_customers.php',
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    editCustomerModal.hide();
                    window.location.reload();
                } else {
                    alert('Error updating customer: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                alert('Failed to update customer. Check console for details.');
            }
        });
    });

    // Handle add customer form submission
    $('#addCustomerForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        $.ajax({
            url: '../../controller/backend_customers.php',
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Reset form and close modal
                    $('#addCustomerForm')[0].reset();
                    addCustomerModal.hide();
                    window.location.reload();
                } else {
                    alert('Error adding customer: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                alert('Failed to add customer. Check console for details.');
            }
        });
    });
});