<?php
// Function to convert image to base64 for reliable PDF printing
function getImageAsBase64($imagePath)
{
    if (file_exists($imagePath)) {
        $imageData = file_get_contents($imagePath);
        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
        $base64 = base64_encode($imageData);
        return "data:image/{$imageType};base64,{$base64}";
    }
    return '';
}

// Get the logo as base64 for reliable printing
$logoPath = __DIR__ . '/../../icon/invoice-icon.png';
$logoBase64 = getImageAsBase64($logoPath);
?>

<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg " style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title" id="receiptModalLabel">Receipt Details</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printable-area" style="padding-bottom: 90px;">
                <div class="receipt-header text-center mt-5">
                    <div style="position: relative;">
                        <img src="<?php echo $logoBase64; ?>" alt="gop-icon" style="
                        position: absolute;
                        left: 0;
                        top: 0;
                        height: 75px; 
                        width: 90px;
                        ">
                        <div>
                            <div style="font-size: 20px; font-weight: bold;">GOP MARKETING</div>
                            <div style="font-size: 14px;">Wangag, Damulaan</div>
                            <div style="font-size: 14px; margin-bottom: 10px;">Albuera, Leyte</div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; position: relative;">
                        <div style="flex: 1;"></div>
                        <div style="flex: 1; text-align: center;"><b>Delivery Receipt</b></div>
                        <div style="flex: 1; text-align: right;"><strong>Receipt #:</strong> <span id="receipt-id" style="color: red;"></span></div>
                    </div>
                    <hr>
                </div>
                <div class="receipt-details mb-4">
                    <div class="d-flex">
                        <div style="font-size: 14px; display: flex; flex-direction: column; align-items: flex-start; flex: 1; gap: 2px;">
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>Customer:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-customer"></span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>Address:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-address"></span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>P.O  N.O:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-po-number">-</span>
                                </div>
                            </div>
                        </div>
                        <div style="font-size: 14px; display: flex; flex-direction: column; align-items: flex-start; margin-left: auto; gap: 2px;">
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>Date:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-date"></span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>Terms:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-terms"></span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 5px;">
                                <div style="min-width: 80px; text-align: left;">
                                    <strong>Salesman:</strong>
                                </div>
                                <div style="text-align: left;">
                                    <span id="receipt-salesman"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered" style="padding: none; margin:none;">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 12px; padding: 3px;">QTY</th>
                            <th class="text-center" style="font-size: 12px; padding: 3px;">UNIT</th>
                            <th class="text-center" style="text-align: center; width: 30%; font-size:12px; padding: 3px;">ITEM/DESCRIPTION</th>
                            <th class="text-center" style="font-size: 12px; padding: 3px;">BASE PRICE</th>
                            <th class="text-center" style="font-size: 12px; padding: 3px;">DISC.</th>
                            <th class="text-center" style="font-size: 12px; padding: 3px;">NET PRICE</th>
                            <th class="text-center" style="text-align: center; font-size:12px; padding: 3px;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody id="receipt-items">
                        <!-- Items will be inserted here dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end" style="font-size:12px; padding: 3px;"><strong>Total Amount ₱:</strong></td>
                            <td style="font-size:12px; padding: 3px; position: relative;"><strong><span style="position: absolute; left: 6px;"></span><span id="receipt-total" style="display: block; text-align: right;"></span></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="footer text-center mt-4">
                    <small style="font-style: italic; font-size: 14px">This is a computer-generated receipt</small>
                </div>
                
                <!-- Print page footer for page numbering -->
                <div class="print-page-footer" style="display: none;">
                    <span id="page-number-display">Receipt #<span id="receipt-number-footer"></span> | Page 1</span>
                </div>
            </div>
            <div class="modal-footer" style="position: sticky; bottom: 0; right: 0; background: #fff; border: none; z-index: 10; display: flex; gap: 10px; box-shadow: 0 -2px 8px rgba(0,0,0,0.05);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="save-receipt" style="display: none;">Save Changes</button>
                <button type="button" class="btn btn-secondary" id="cancel-edit" style="display: none;">Cancel Edit</button>
                <button type="button" class="btn btn-danger" id="edit-row-item">Edit Receipt</button>
                <button type="button" class="btn btn-primary" id="print-receipt">Print Receipt</button>
            </div>
        </div>
    </div>
</div>



<!-- Include the receipt print functionality script -->
<script src="../../js/receipt_print.js"></script>
<script>
let isEditMode = false;
let originalReceiptData = [];

// Edit Receipt button functionality
document.getElementById('edit-row-item').addEventListener('click', function() {
    if (!isEditMode) {
        enterEditMode();
    }
    // Removed exit edit functionality - button will be disabled in edit mode
});

// Save changes button
document.getElementById('save-receipt').addEventListener('click', function() {
    saveAllChanges();
});

// Cancel edit button
document.getElementById('cancel-edit').addEventListener('click', function() {
    cancelEdit();
});

function enterEditMode() {
    isEditMode = true;
    
    // Store original data for cancellation
    storeOriginalData();
    
    // Add action column to table header
    const headerRow = document.querySelector('#receiptModal thead tr');
    const actionHeader = document.createElement('th');
    actionHeader.className = 'text-center';
    actionHeader.style.cssText = 'font-size: 12px; padding: 3px;';
    actionHeader.textContent = 'ACTIONS';
    headerRow.appendChild(actionHeader);
    
    // Add action buttons to each row
    const rows = document.querySelectorAll('#receipt-items tr');
    rows.forEach((row, index) => {
        addActionButtons(row, index);
    });
    
    // Update footer buttons - disable edit button instead of changing text
    document.getElementById('edit-row-item').disabled = true;
    document.getElementById('edit-row-item').style.opacity = '0.5';
    document.getElementById('save-receipt').style.display = 'inline-block';
    document.getElementById('cancel-edit').style.display = 'inline-block';
    document.getElementById('print-receipt').style.display = 'none';
    
    // Update total colspan
    const totalRow = document.querySelector('#receiptModal tfoot tr');
    totalRow.children[0].setAttribute('colspan', '7');
}

function exitEditMode() {
    isEditMode = false;
    
    // Remove action column from header
    const headerRow = document.querySelector('#receiptModal thead tr');
    const actionHeader = headerRow.lastElementChild;
    if (actionHeader && actionHeader.textContent === 'ACTIONS') {
        headerRow.removeChild(actionHeader);
    }
    
    // Remove action buttons from rows and exit any active editing
    const rows = document.querySelectorAll('#receipt-items tr');
    rows.forEach(row => {
        // Exit edit mode if row is being edited
        if (row.classList.contains('editing')) {
            cancelRowEdit(row);
        }
        
        // Remove action column
        const actionCell = row.lastElementChild;
        if (actionCell && actionCell.querySelector('.action-buttons')) {
            row.removeChild(actionCell);
        }
    });
    
    // Reset footer buttons - re-enable edit button
    document.getElementById('edit-row-item').disabled = false;
    document.getElementById('edit-row-item').style.opacity = '1';
    document.getElementById('save-receipt').style.display = 'none';
    document.getElementById('cancel-edit').style.display = 'none';
    document.getElementById('print-receipt').style.display = 'inline-block';
    
    // Reset total colspan
    const totalRow = document.querySelector('#receiptModal tfoot tr');
    totalRow.children[0].setAttribute('colspan', '6');
}

function addActionButtons(row, index) {
    const actionCell = document.createElement('td');
    actionCell.style.cssText = 'font-size: 12px; padding: 3px; text-align: center;';
    actionCell.innerHTML = `
        <div class="action-buttons">
            <button type="button" class="btn btn-sm btn-warning edit-btn" onclick="editRow(${index})">Edit</button>
            <button type="button" class="btn btn-sm btn-danger delete-btn" onclick="deleteRow(${index})">Delete</button>
        </div>
    `;
    row.appendChild(actionCell);
}

function editRow(index) {
    const rows = document.querySelectorAll('#receipt-items tr');
    const row = rows[index];
    
    if (row.classList.contains('editing')) {
        saveRowChanges(row, index);
    } else {
        enterRowEditMode(row, index);
    }
}

function enterRowEditMode(row, index) {
    row.classList.add('editing');
    
    const cells = row.querySelectorAll('td');
    // Edit all cells except the last one (actions)
    for (let i = 0; i < cells.length - 1; i++) {
        const cell = cells[i];
        let currentValue = cell.textContent.trim();
        
        // Special handling for discount field - remove % symbol for editing
        if (i === 4 && currentValue.includes('%')) {
            currentValue = currentValue.replace('%', '');
        }
        
        if (i === 2) { // Item description - use textarea
            cell.innerHTML = `<textarea class="form-control" style="font-size: 11px; padding: 2px; min-height: 40px;">${currentValue}</textarea>`;
        } else { // Other fields - use input
            const inputType = (i === 0 || i >= 3) ? 'number' : 'text'; // QTY and price fields as number
            const step = (i === 4) ? '0.1' : '0.01'; // Different step for discount
            const placeholder = (i === 4) ? 'Enter discount %' : '';
            
            // For quantity field, add stock validation attributes
            let additionalAttributes = '';
            if (i === 0) { // Quantity field
                additionalAttributes = 'min="0.1" data-original-qty="' + currentValue + '"';
            }
            
            cell.innerHTML = `<input type="${inputType}" class="form-control" style="font-size: 11px; padding: 2px;" value="${currentValue}" step="${step}" placeholder="${placeholder}" ${additionalAttributes}>`;
        }
    }
    
    // Get current stock for this item and add validation
    const itemDescription = cells[2].textContent.trim();
    getItemStock(itemDescription).then(stockData => {
        if (stockData.success) {
            const qtyInput = cells[0].querySelector('input');
            if (qtyInput) {
                const originalQty = parseFloat(qtyInput.getAttribute('data-original-qty')) || 0;
                const availableStock = stockData.stock + originalQty; // Add back the original quantity
                
                qtyInput.setAttribute('max', availableStock);
                qtyInput.setAttribute('title', `Maximum available: ${availableStock}`);
                
                // Add real-time validation
                qtyInput.addEventListener('input', function() {
                    const enteredQty = parseFloat(this.value) || 0;
                    if (enteredQty > availableStock) {
                        this.setCustomValidity(`Quantity cannot exceed available stock (${availableStock})`);
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.setCustomValidity('');
                        this.style.borderColor = '#ced4da';
                    }
                });
            }
        }
    });
    
    // Add real-time calculation for discount field
    const discountInput = cells[4].querySelector('input');
    const basePriceInput = cells[3].querySelector('input');
    const netPriceInput = cells[5].querySelector('input');
    
    if (discountInput && basePriceInput && netPriceInput) {
        const updateNetPrice = () => {
            const basePrice = parseFloat(basePriceInput.value) || 0;
            let discount = parseFloat(discountInput.value) || 0;
            
            // Handle percentage conversion
            if (discount > 1 && discount <= 100) {
                // discount is already percentage
            } else if (discount > 0 && discount <= 1) {
                discount = discount * 100;
            }
            
            if (discount > 100) discount = 100;
            if (discount < 0) discount = 0;
            
            const discountAmount = basePrice * (discount / 100);
            const netPrice = basePrice - discountAmount;
            
            netPriceInput.value = netPrice.toFixed(2);
            
            // Also update amount if quantity is available
            const qtyInput = cells[0].querySelector('input');
            if (qtyInput) {
                const qty = parseFloat(qtyInput.value) || 0;
                const amount = qty * netPrice;
                const amountCell = cells[6];
                if (amountCell) {
                    amountCell.textContent = amount.toFixed(2);
                }
            }
        };
        
        // Add event listeners for real-time updates
        discountInput.addEventListener('input', updateNetPrice);
        basePriceInput.addEventListener('input', updateNetPrice);
        
        // Also update amount when quantity changes
        const qtyInput = cells[0].querySelector('input');
        if (qtyInput) {
            qtyInput.addEventListener('input', () => {
                const qty = parseFloat(qtyInput.value) || 0;
                const netPrice = parseFloat(netPriceInput.value) || 0;
                const amount = qty * netPrice;
                cells[6].textContent = amount.toFixed(2);
            });
        }
    }
    
    // Update edit button
    const editBtn = row.querySelector('.edit-btn');
    editBtn.textContent = 'Save';
    editBtn.className = 'btn btn-sm btn-success edit-btn';
}

function saveRowChanges(row, index) {
    const cells = row.querySelectorAll('td');
    const inputs = row.querySelectorAll('input, textarea');
    
    // Validate quantity before saving
    const qtyInput = inputs[0];
    const enteredQty = parseFloat(qtyInput.value) || 0;
    const maxStock = parseFloat(qtyInput.getAttribute('max')) || 0;
    
    if (enteredQty > maxStock) {
        alert(`Quantity (${enteredQty}) cannot exceed available stock (${maxStock})`);
        qtyInput.focus();
        return; // Don't save if validation fails
    }
    
    if (enteredQty <= 0) {
        alert('Quantity must be greater than 0');
        qtyInput.focus();
        return;
    }
    
    // Validate and update values
    const qty = enteredQty;
    const unit = inputs[1].value.trim();
    const description = inputs[2].value.trim();
    const basePrice = parseFloat(inputs[3].value) || 0;
    let discount = parseFloat(inputs[4].value) || 0;
    
    // Handle discount input - check if it's percentage or decimal
    if (inputs[4].value.includes('%')) {
        // Remove % sign and parse
        discount = parseFloat(inputs[4].value.replace('%', '')) || 0;
    } else if (discount > 1 && discount <= 100) {
        // If discount is between 1 and 100, treat as percentage
        // discount stays as is
    } else if (discount > 0 && discount <= 1) {
        // If discount is between 0 and 1, convert to percentage
        discount = discount * 100;
    }
    
    // Ensure discount doesn't exceed 100%
    if (discount > 100) discount = 100;
    if (discount < 0) discount = 0;
    
    // Calculate net price from base price and discount percentage
    const discountAmount = basePrice * (discount / 100);
    const netPrice = basePrice - discountAmount;
    
    // Calculate amount
    const amount = qty * netPrice;
    
    // Update cell contents with proper formatting
    cells[0].textContent = qty.toString();
    cells[1].textContent = unit;
    cells[2].textContent = description;
    cells[3].textContent = basePrice.toFixed(2);
    cells[4].textContent = discount.toFixed(1) + '%'; // Display as percentage with % symbol
    cells[5].textContent = netPrice.toFixed(2);
    cells[6].textContent = amount.toFixed(2);
    
    // Exit edit mode
    row.classList.remove('editing');
    
    // Update edit button
    const editBtn = row.querySelector('.edit-btn');
    editBtn.textContent = 'Edit';
    editBtn.className = 'btn btn-sm btn-warning edit-btn';
    
    // Recalculate total
    updateTotal();
}

function cancelRowEdit(row) {
    // This would restore original values if needed
    row.classList.remove('editing');
    const editBtn = row.querySelector('.edit-btn');
    if (editBtn) {
        editBtn.textContent = 'Edit';
        editBtn.className = 'btn btn-sm btn-warning edit-btn';
    }
}

function deleteRow(index) {
    if (confirm('Are you sure you want to delete this item?')) {
        const rows = document.querySelectorAll('#receipt-items tr');
        const row = rows[index];
        row.remove();
        
        // Renumber remaining rows
        const remainingRows = document.querySelectorAll('#receipt-items tr');
        remainingRows.forEach((row, newIndex) => {
            const editBtn = row.querySelector('.edit-btn');
            const deleteBtn = row.querySelector('.delete-btn');
            if (editBtn) editBtn.setAttribute('onclick', `editRow(${newIndex})`);
            if (deleteBtn) deleteBtn.setAttribute('onclick', `deleteRow(${newIndex})`);
        });
        
        updateTotal();
    }
}

function updateTotal() {
    const rows = document.querySelectorAll('#receipt-items tr');
    let total = 0;
    
    rows.forEach(row => {
        const amountCell = row.cells[6]; // Amount column
        if (amountCell) {
            const amount = parseFloat(amountCell.textContent) || 0;
            total += amount;
        }
    });
    
    document.getElementById('receipt-total').textContent = total.toFixed(2);
}

function storeOriginalData() {
    const rows = document.querySelectorAll('#receipt-items tr');
    originalReceiptData = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [];
        
        // Only store rows that have actual content (not empty padding rows)
        if (cells.length > 0 && cells[0].textContent.trim() !== '' && cells[0].textContent.trim() !== '\u00a0') {
            cells.forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            originalReceiptData.push(rowData);
        }
    });
}

function saveAllChanges() {
    // Collect all current receipt data
    const rows = document.querySelectorAll('#receipt-items tr');
    const items = [];
    
    rows.forEach(row => {
        const cells = row.cells;
        // Only include rows with actual data (not empty rows)
        if (cells.length >= 7 && cells[0].textContent.trim() !== '' && cells[0].textContent.trim() !== '\u00a0') {
            items.push({
                qty: cells[0].textContent.trim(),
                unit: cells[1].textContent.trim(),
                description: cells[2].textContent.trim(),
                basePrice: cells[3].textContent.trim(),
                discount: cells[4].textContent.trim(),
                netPrice: cells[5].textContent.trim(),
                amount: cells[6].textContent.trim()
            });
        }
    });
    
    if (items.length === 0) {
        alert('No items to save');
        return;
    }
    
    // Get current receipt ID
    const receiptId = document.getElementById('receipt-id').textContent;
    
    if (!receiptId) {
        alert('Receipt ID not found');
        return;
    }
    
    // Show loading state
    const saveBtn = document.getElementById('save-receipt');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    // Send updated data to backend
    $.ajax({
        url: '../../controller/backend_receipts.php',
        method: 'POST',
        data: {
            action: 'update_receipt',
            receipt_id: receiptId,
            items: JSON.stringify(items)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Receipt changes saved successfully!\n\nNote: Stock will be deducted when the receipt is printed/finalized.');
                
                // Update the receipt total if returned
                if (response.total_price) {
                    document.getElementById('receipt-total').textContent = parseFloat(response.total_price).toFixed(2);
                }
                
                // Exit edit mode
                exitEditMode();
                
                // Store the new data as original for future cancellations
                storeOriginalData();
                
            } else {
                alert('Error saving receipt: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error saving receipt. Please check the console for details.');
        },
        complete: function() {
            // Restore button state
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    });
}

function cancelEdit() {
    if (confirm('Are you sure you want to cancel all changes?')) {
        // Restore original data
        restoreOriginalData();
        exitEditMode();
    }
}

function restoreOriginalData() {
    const tbody = document.getElementById('receipt-items');
    tbody.innerHTML = '';
    
    originalReceiptData.forEach(rowData => {
        const row = document.createElement('tr');
        rowData.forEach((cellData, index) => {
            const cell = document.createElement('td');
            
            // Apply the same styling as the original rendering from receipts.js
            switch(index) {
                case 0: // QTY
                    cell.style.cssText = 'text-align: end; font-size: 14px; padding: 3px;';
                    break;
                case 1: // UNIT
                    cell.style.cssText = 'text-align: start; font-size: 14px; padding: 3px;';
                    break;
                case 2: // ITEM/DESCRIPTION
                    cell.style.cssText = 'font-size: 14px; padding: 3px;';
                    break;
                case 3: // BASE PRICE
                case 4: // DISC.
                case 5: // NET PRICE
                case 6: // AMOUNT
                    cell.style.cssText = 'text-align: end; font-size: 14px; padding: 3px;';
                    break;
                default:
                    cell.style.cssText = 'font-size: 12px; padding: 3px;';
            }
            
            cell.textContent = cellData;
            row.appendChild(cell);
        });
        tbody.appendChild(row);
    });
    
    // Add empty rows to maintain the same structure as receipts.js
    const minRows = 28;
    const currentRows = originalReceiptData.length;
    for (let i = currentRows; i < minRows; i++) {
        const emptyRow = document.createElement('tr');
        
        // Create 7 empty cells with proper styling
        for (let j = 0; j < 7; j++) {
            const cell = document.createElement('td');
            switch(j) {
                case 0: // QTY
                    cell.style.cssText = 'text-align: end; font-size: 14px; padding: 8px;';
                    break;
                case 1: // UNIT
                    cell.style.cssText = 'text-align: start; font-size: 14px; padding: 8px;';
                    break;
                case 2: // ITEM/DESCRIPTION
                    cell.style.cssText = 'font-size: 14px; padding: 8px;';
                    break;
                case 3: // BASE PRICE
                case 4: // DISC.
                case 5: // NET PRICE
                case 6: // AMOUNT
                    cell.style.cssText = 'text-align: end; font-size: 14px; padding: 8px;';
                    break;
            }
            cell.innerHTML = '&nbsp;';
            emptyRow.appendChild(cell);
        }
        tbody.appendChild(emptyRow);
    }
    
    updateTotal();
}

// Function to get current stock for an item
async function getItemStock(itemName) {
    try {
        const response = await $.ajax({
            url: '../../controller/backend_receipts.php',
            method: 'POST',
            data: {
                action: 'get_item_stock',
                item_name: itemName
            },
            dataType: 'json'
        });
        return response;
    } catch (error) {
        console.error('Error getting item stock:', error);
        return { success: false, stock: 0 };
    }
}

// Prevent modal from closing when in edit mode
document.getElementById('receiptModal').addEventListener('hide.bs.modal', function (e) {
    if (isEditMode) {
        e.preventDefault();
        alert('Please save or cancel your changes before closing.');
    }
});

// Update the receipt number in footer when receipt is loaded
function updateReceiptFooter() {
    const receiptNumber = document.getElementById('receipt-id').textContent || 'N/A';
    const footerElement = document.getElementById('receipt-number-footer');
    if (footerElement) {
        footerElement.textContent = receiptNumber;
    }
}

// Call this function whenever receipt data is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Observer to watch for changes in receipt-id
    const receiptIdElement = document.getElementById('receipt-id');
    if (receiptIdElement) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    updateReceiptFooter();
                }
            });
        });
        
        observer.observe(receiptIdElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }
});
</script>

<style>
.editing {
    background-color: #fff3cd !important;
}

.btn-sm {
    font-size: 10px;
    padding: 2px 6px;
    margin: 1px;
}

#receipt-items td {
    vertical-align: middle;
}

#receipt-items .form-control {
    border: 1px solid #ced4da;
    border-radius: 3px;
    width: 100%;
}

.action-buttons {
    display: flex;
    gap: 3px;
    justify-content: center;
    flex-wrap: wrap;
}

@media print {
    .action-buttons,
    #edit-row-item,
    #save-receipt,
    #cancel-edit {
        display: none !important;
    }
    
    /* Print header that repeats on every page */
    .print-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: white;
        z-index: 999;
        border-bottom: 1px solid #000;
        padding: 10px;
        display: block !important;
    }
    
    /* Hide the original header on print */
    .receipt-header {
        display: none !important;
    }
    
    /* Add top margin to content to account for fixed header */
    .modal-body {
        margin-top: 130px !important;
        padding-top: 0 !important;
    }
    
    /* Ensure page breaks work properly */
    .receipt-details {
        page-break-inside: avoid;
    }
    
    table {
        page-break-inside: auto;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    /* Show page footer only on print */
    .print-page-footer {
        display: block !important;
    }
    
    /* Hide screen footer on print if needed */
    .footer {
        margin-top: 20px !important;
        text-align: center !important;
        font-size: 11px !important;
        flex-shrink: 0 !important;
        margin-bottom: 0.5in !important;
    }
}

/* Hidden print header - only visible when printing */
.print-header {
    display: none;
}

/* Hidden print page footer - only visible when printing */
.print-page-footer {
    display: none;
}
</style>