$(document).ready(function() {
    const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
    let currentPage = 1;

    // Toggle filter section
    $("#toggle-filters").click(function() {
        $("#filter-body").slideToggle();
    });

    // Function to perform search with AJAX
    function performSearch(page = 1) {
        currentPage = page; // Update current page tracker
        const searchData = {
            action: 'search_items',
            name: $('#name-filter').val(),
            category: $('#category-filter').val(),
            sold_by: $('#sold-by-filter').val(),
            stock_min: $('#stock-min-filter').val(),
            stock_max: $('#stock-max-filter').val(),
            page: page
        };

        $.ajax({
            url: '../../controller/backend_items.php',
            method: 'POST',
            data: searchData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update table body
                    $('#itemsTableBody').html(response.tableHtml);
                    
                    // Update pagination container
                    $('#pagination-container').html(response.paginationHtml);
                    
                    // Re-bind pagination click events
                    bindPaginationEvents();
                    
                    // Re-bind edit button events
                    bindEditButtonEvents();
                    
                    // Re-bind delete button events
                    bindDeleteButtonEvents();
                }
            },
            error: function() {
                alert('Error performing search');
            }
        });
    }

    // Function to bind pagination events
    function bindPaginationEvents() {
        $(document).off('click', '.page-link').on('click', '.page-link', function(e) {
            e.preventDefault();
            if (!$(this).parent().hasClass('disabled')) {
                const page = $(this).data('page');
                if (page && page > 0) {
                    performSearch(page);
                }
            }
        });
    }

    // Function to bind edit button events
    function bindEditButtonEvents() {
        $(document).off('click', '.edit-btn').on('click', '.edit-btn', function() {
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
    }

    // Function to bind delete button events
    function bindDeleteButtonEvents() {
        $(document).off('click', '.delete-btn').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            const itemId = $(this).data('id');
            const itemName = $(this).data('name');
            
            // Show SweetAlert confirmation dialog
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete "${itemName}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteItem(itemId, itemName);
                    }
                });
            } else {
                // Fallback to native confirm if SweetAlert is not available
                if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
                    deleteItem(itemId, itemName);
                }
            }
        });
    }

    // Function to delete item via AJAX
    function deleteItem(itemId, itemName) {
        $.ajax({
            url: '../../controller/backend_items.php',
            method: 'POST',
            data: {
                action: 'delete_item',
                item_id: itemId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Item has been deleted successfully.',
                            confirmButtonColor: '#3085d6',
                            timer: 2000
                        });
                    } else {
                        alert('Item deleted successfully!');
                    }
                    
                    // Refresh the table by performing a new search
                    performSearch(currentPage);
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: response.message || 'Failed to delete item',
                            confirmButtonColor: '#d33',
                        });
                    } else {
                        alert('Failed to delete item: ' + (response.message || 'Unknown error'));
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error occurred while deleting item',
                        confirmButtonColor: '#d33',
                    });
                } else {
                    alert('Error occurred while deleting item');
                }
            }
        });
    }

    // Auto-search with debounce for text inputs
    let searchTimeout;
    $('#name-filter, #category-filter, #sold-by-filter').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch(1);
        }, 300); // Wait 300ms after user stops typing
    });

    // Auto-search immediately for number inputs
    $('#stock-min-filter, #stock-max-filter').on('change', function() {
        performSearch(1);
    });

    // Apply filter button (still available if needed)
    $('#apply-filter').click(function() {
        performSearch(1);
    });

    // Reset filter button
    $('#reset-filter').click(function() {
        $('#name-filter, #category-filter, #sold-by-filter, #stock-min-filter, #stock-max-filter').val('');
        performSearch(1);
    });

    // Search on Enter key (for quick search)
    $('#name-filter, #category-filter, #sold-by-filter').keypress(function(e) {
        if (e.which == 13) {
            clearTimeout(searchTimeout); // Cancel debounced search
            performSearch(1); // Search immediately
        }
    });

    // Initial binding
    bindPaginationEvents();
    bindEditButtonEvents();
    bindDeleteButtonEvents();

    // Handle edit form submission
    $('#editItemForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: $('#edit_item_id').val(),
            name: $('#edit_name').val(),
            stock: $('#edit_stock').val(),
            new_stock: $('#edit_new_stock').val(),
            sold_by: $('#edit_sold_by').val(),
            category: $('#edit_category').val(),
            cost: $('#edit_cost').val(),
            price: $('#edit_price').val()
        };

        $.ajax({
            url: '../../controller/update_item.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Clear all input fields
                    $('#edit_new_stock').val('');  // Clear new stock field
                    
                    // Close modal and refresh table
                    $('#editItemModal').modal('hide');
                    performSearch(currentPage);
                } else {
                    alert('Error updating item: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert('Error: ' + error);
            }
        });
    });

    // Add item functionality
    const addBtn = $("#addItemBtn");

    addBtn.on("click", function() {
        addItemModal.show();
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
                    $('#addItemForm')[0].reset();
                    addItemModal.hide();
                    // For new item, go to page 1 to see the newly added item
                    performSearch(1);
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