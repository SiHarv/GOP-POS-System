$(document).ready(function() {
    console.log('Print items script loaded');
    
    // Load categories when modal is shown
    $('#printItemsModal').on('shown.bs.modal', function() {
        console.log('Print modal opened');
        loadCategories();
    });

    // Load items when button is clicked
    $('#loadItemsBtn').on('click', function() {
        console.log('Load items button clicked');
        loadItems();
    });

    // Handle print button click
    $('#printBtn').on('click', function() {
        console.log('Print button clicked');
        printItems();
    });

    // Reset modal when hidden
    $('#printItemsModal').on('hidden.bs.modal', function() {
        console.log('Print modal closed');
        resetModal();
    });

    function loadCategories() {
        console.log('Loading categories...');
        $.ajax({
            url: '../../controller/backend_itemsPrintModal.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getCategories'
            }),
            success: function(response) {
                console.log('Categories response:', response);
                if (response.success) {
                    populateCategoryDropdown(response.categories);
                } else {
                    showError('Failed to load categories: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Categories AJAX error:', xhr.responseText);
                showError('Error loading categories: ' + error);
            }
        });
    }

    function populateCategoryDropdown(categories) {
        console.log('Populating categories:', categories);
        const select = $('#categorySelect');
        select.empty();
        
        // Add "All Items" option first
        select.append('<option value="all">All Items</option>');
        
        // Add each category
        categories.forEach(function(category) {
            select.append(`<option value="${category}">${category}</option>`);
        });
    }

    function loadItems() {
        const selectedCategory = $('#categorySelect').val();
        console.log('Selected category:', selectedCategory);
        
        if (!selectedCategory) {
            showError('Please select a category first.');
            return;
        }

        // Show loading spinner
        $('#loadingSpinner').show();
        $('#itemsTableContainer').hide();
        $('#noItemsMessage').hide();
        $('#printBtn').hide();

        $.ajax({
            url: '../../controller/backend_itemsPrintModal.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getItems',
                category: selectedCategory
            }),
            success: function(response) {
                console.log('Items response:', response);
                $('#loadingSpinner').hide();
                
                if (response.success) {
                    if (response.items && response.items.length > 0) {
                        populateItemsTable(response.items);
                        $('#itemsTableContainer').show();
                        $('#printBtn').show();
                        $('#totalItemsCount').text(response.count);
                    } else {
                        $('#noItemsMessage').show();
                    }
                } else {
                    showError('Failed to load items: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Items AJAX error:', xhr.responseText);
                $('#loadingSpinner').hide();
                showError('Error loading items: ' + error);
            }
        });
    }

    function populateItemsTable(items) {
        console.log('Populating items table with:', items);
        const tbody = $('#printItemsTableBody');
        tbody.empty();

        items.forEach(function(item) {
            const row = `
                <tr>
                    <td>${item.item_id}</td>
                    <td>${item.item_name}</td>
                    <td>${item.category || 'N/A'}</td>
                    <td>${item.sold_by || 'N/A'}</td>
                    <td class="text-end">₱${parseFloat(item.cost || 0).toFixed(2)}</td>
                    <td class="text-end">₱${parseFloat(item.price || 0).toFixed(2)}</td>
                    <td class="text-end">${item.stock || 0}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function printItems() {
        console.log('Printing items...');
        const selectedCategory = $('#categorySelect').val();
        const categoryText = $('#categorySelect option:selected').text();
        const currentDate = new Date().toLocaleString();
        
        // Get table HTML
        const tableHtml = $('#printItemsTable')[0].outerHTML;
        const totalItems = $('#totalItemsCount').text();
        
        console.log('Print data:', {
            category: categoryText,
            totalItems: totalItems,
            tableExists: tableHtml.length > 0
        });
        
        // Create print content with preserved classes
        const printContent = `
            <div class="print-header">
                <h2>GOP Marketing - Items Report</h2>
                <h3>Category: ${categoryText}</h3>
                <p>Generated on: ${currentDate}</p>
            </div>
            
            <div class="print-body">
                ${tableHtml.replace(/table table-striped table-hover/g, 'print-table')
                          .replace(/class="fw-bold text-end"/g, 'class="fw-bold text-end print-header-right"')
                          .replace(/class="text-end"/g, 'class="text-end print-cell-right"')}
            </div>
            
            <div class="print-footer">
                <p>Total Items: ${totalItems}</p>
                <p>This report was generated automatically by GOP Marketing POS System</p>
            </div>
        `;
        
        // Update print area and trigger print
        $('#printArea').html(printContent);
        
        // Small delay to ensure content is rendered
        setTimeout(function() {
            console.log('Triggering print...');
            window.print();
        }, 100);
    }

    function resetModal() {
        console.log('Resetting modal...');
        $('#categorySelect').empty().append('<option value="">Loading categories...</option>');
        $('#itemsTableContainer').hide();
        $('#noItemsMessage').hide();
        $('#loadingSpinner').hide();
        $('#printBtn').hide();
        $('#printItemsTableBody').empty();
        $('#totalItemsCount').text('0');
    }

    function showError(message) {
        console.error('Error:', message);
        alert(message);
    }
});
