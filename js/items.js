$(document).ready(function() {
    const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));

    // Pagination variables
    const itemsRowsPerPage = 10;
    let currentSearchTerm = '';
    let currentPage = 1;

    function getAllRows() {
        return $("#itemsTableBody tr");
    }

    function getFilteredRows(searchTerm) {
        if (!searchTerm) {
            return getAllRows();
        }
        return getAllRows().filter(function () {
            const row = $(this);
            const name = row.find("td:nth-child(6)").text().toLowerCase();
            const category = row.find("td:nth-child(7)").text().toLowerCase();
            const stock = row.find("td:nth-child(4)").text().toLowerCase();
            return (
                name.includes(searchTerm) ||
                category.includes(searchTerm) ||
                stock.includes(searchTerm)
            );
        });
    }

    function renderTable(searchTerm = currentSearchTerm, page = 1) {
        currentSearchTerm = searchTerm;
        currentPage = page;

        const filteredRows = getFilteredRows(searchTerm);
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / itemsRowsPerPage);

        // Hide all rows first
        getAllRows().hide();

        // Show only the filtered rows for the current page
        const startIdx = (page - 1) * itemsRowsPerPage;
        const endIdx = Math.min(startIdx + itemsRowsPerPage, totalRows);
        
        // Handle no results
        if (totalRows === 0 && searchTerm !== '') {
            if ($("#no-results-row").length === 0) {
                $("#itemsTableBody").append(
                    '<tr id="no-results-row"><td colspan="10" class="text-center text-muted">No items found matching your search.</td></tr>'
                );
            }
        } else {
            $("#no-results-row").remove();
            
            // Show and update rows for current page
            for (let i = startIdx; i < endIdx; i++) {
                const row = $(filteredRows[i]);
                row.find(".row-index").text(i + 1).addClass('text-center');
                row.show();
            }
        }

        // Update pagination
        const pag = $("#itemsTablePagination");
        pag.empty();
        
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                pag.append(`<li class="page-item${i === currentPage ? ' active' : ''}"><a class="page-link" href="#">${i}</a></li>`);
            }
        }

        // Bind pagination click events
        pag.find("a").on("click", function (e) {
            e.preventDefault();
            const newPage = parseInt($(this).text());
            if (newPage !== currentPage) {
                renderTable(currentSearchTerm, newPage);
            }
        });
    }

    // Search and clear search event handlers
    $("#itemSearchInput").on("keyup", function () {
        renderTable($(this).val().toLowerCase(), 1);
    });

    $("#clearSearchBtn").on("click", function () {
        $("#itemSearchInput").val("");
        renderTable("", 1);
    });

    // Initial render
    renderTable("", 1);

    // Add click handler for edit buttons (delegated for dynamic rows)
    $(document).on('click', '.edit-btn', function() {
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
                    $('#editItemModal').modal('hide');
                    location.reload();
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
                    $('#addItemForm')[0].reset();
                    modal.hide();
                    location.reload();
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