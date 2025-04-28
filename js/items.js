$(document).ready(function() {
    const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));

    // Add click handler for edit buttons
    $('.edit-btn').on('click', function() {
        // Get data from button attributes
        const id = $(this).data('id');
        const name = $(this).data('name');
        const stock = $(this).data('stock');
        const soldBy = $(this).data('sold-by');
        const category = $(this).data('category');
        const cost = $(this).data('cost');
        const price = $(this).data('price');

        // Populate form fields
        $('#edit_item_id').val(id);
        $('#edit_name').val(name);
        $('#edit_stock').val(stock);
        $('#edit_sold_by').val(soldBy);
        $('#edit_category').val(category);
        $('#edit_cost').val(cost);
        $('#edit_price').val(price);

        // Show modal
        editItemModal.show();
    });

    // Handle edit form submission
    $('#editItemForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit_item');

        $.ajax({
            url: '../../controller/backend_items.php',
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    editItemModal.hide();
                    window.location.reload();
                } else {
                    alert('Error updating item: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                alert('Failed to update item. Check console for details.');
            }
        });
    });

    // Add item functionality
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    const addBtn = $("#addItemBtn");

    addBtn.on("click", function() {
        modal.show();
    });

    $("#addItemForm").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "../../controller/backend_items.php",
            data: $(this).serialize() + "&action=add",
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    // Create new row and prepend it to the table
                    const newRow = `
                        <tr>
                            <td>${response.item.stock}</td>
                            <td>${response.item.sold_by}</td>
                            <td>${response.item.name}</td>
                            <td>${response.item.category}</td>
                            <td>₱${parseFloat(response.item.cost).toFixed(2)}</td>
                            <td>₱${parseFloat(response.item.price).toFixed(2)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" 
                                    data-id="${response.item.id}"
                                    data-name="${response.item.name}"
                                    data-stock="${response.item.stock}"
                                    data-sold-by="${response.item.sold_by}"
                                    data-category="${response.item.category}"
                                    data-cost="${response.item.cost}"
                                    data-price="${response.item.price}">
                                    <span class="iconify" data-icon="mdi:pencil" data-width="16"></span>
                                    Edit
                                </button>
                            </td>
                        </tr>
                    `;
                    $('.items-table tbody').prepend(newRow);
                    
                    // Reset form and close modal
                    $('#addItemForm')[0].reset();
                    modal.hide();
                    
                    // Show success message
                    alert('Item added successfully!');
                } else {
                    alert("Error adding item: " + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Error occurred while adding item. Check console for details.");
            }
        });
    });
});