$(document).ready(function() {
    let cart = [];
    
    // Function to refresh items table
    function refreshItemsTable() {
        $.ajax({
            url: '../../controller/backend_charge.php',
            method: 'POST',
            data: { action: 'get_items' },
            success: function(response) {
                const tbody = $('.table-hover tbody');
                tbody.empty();
                
                response.forEach(item => {
                    tbody.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.category}</td>
                            <td>${item.stock}</td>
                            <td>₱${parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td>
                                ${item.stock > 0 ? 
                                    `<button class="btn btn-sm btn-primary add-item" 
                                        data-id="${item.id}"
                                        data-name="${item.name}"
                                        data-price="${item.price}"
                                        data-stock="${item.stock}">
                                        Add
                                    </button>` : 
                                    `<button class="btn btn-sm btn-secondary" disabled>
                                        Out of Stock
                                    </button>`
                                }
                            </td>
                        </tr>
                    `);
                });
                
                // Reattach click handlers for new buttons
                $('.add-item').off('click').on('click', handleAddItem);
            }
        });
    }

    // Move add-item click handler to separate function
    function handleAddItem() {
        const button = $(this);
        const itemId = button.data('id');
        const itemName = button.data('name');
        const itemPrice = parseFloat(button.data('price'));
        const itemStock = parseInt(button.data('stock'));
        
        const existingItem = cart.find(item => item.id === itemId);
        
        if (existingItem) {
            // Check if adding one more would exceed stock
            if (existingItem.quantity >= itemStock) {
                alert('Not enough stock available!');
                return;
            }
            existingItem.quantity++;
        } else {
            if (itemStock <= 0) {
                alert('This item is out of stock!');
                return;
            }
            cart.push({
                id: itemId,
                name: itemName,
                price: itemPrice,
                quantity: 1,
                maxStock: itemStock
            });
        }
        updateCartDisplay();
    }

    // Update cart display
    function updateCartDisplay() {
        const cartContainer = $('#cart-items');
        cartContainer.empty();
        
        let total = 0;
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            cartContainer.append(`
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                    </div>
                    <div class="quantity-control">
                        <button class="btn btn-sm btn-outline-secondary decrease-qty">-</button>
                        <input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1">
                        <button class="btn btn-sm btn-outline-secondary increase-qty">+</button>
                        <i class="iconify remove-item ms-2" data-icon="mdi:trash-can-outline"></i>
                    </div>
                </div>
            `);
        });
        
        $('#total-amount').text(total.toFixed(2));
        
        // Fix: Only disable if cart is empty OR no customer selected
        const customerSelected = $('#customer').val() !== '';
        const hasItems = cart.length > 0;
        $('#process-charge').prop('disabled', !hasItems || !customerSelected);
    }
    
    // Handle quantity changes
    $(document).on('click', '.increase-qty', function() {
        const itemId = $(this).closest('.cart-item').data('id');
        const item = cart.find(item => item.id === itemId);
        if (item && item.quantity < item.maxStock) {
            item.quantity++;
            updateCartDisplay();
        } else {
            alert('Maximum stock limit reached!');
        }
    });
    
    $(document).on('click', '.decrease-qty', function() {
        const itemId = $(this).closest('.cart-item').data('id');
        const item = cart.find(item => item.id === itemId);
        if (item && item.quantity > 1) {
            item.quantity--;
            updateCartDisplay();
        }
    });
    
    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        const itemId = $(this).closest('.cart-item').data('id');
        cart = cart.filter(item => item.id !== itemId);
        updateCartDisplay();
    });
    
    // Handle customer selection
    $('#customer').change(function() {
        const hasItems = cart.length > 0;
        const customerSelected = $(this).val() !== '';
        $('#process-charge').prop('disabled', !hasItems || !customerSelected);
    });
    
    // Update process charge success callback
    $('#process-charge').on('click', function() {
        if (cart.length === 0 || !$('#customer').val()) {
            alert('Please select a customer and add items to cart');
            return;
        }

        $.ajax({
            url: '../../controller/backend_charge.php',
            method: 'POST',
            data: {
                action: 'process_charge',
                customer_id: $('#customer').val(),
                items: cart
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Charge processed successfully!');
                    cart = [];
                    updateCartDisplay();
                    $('#customer').val('');
                    // Refresh the items table
                    refreshItemsTable();
                } else {
                    alert('Error: ' + (response.message || 'Failed to process charge'));
                }
            },
            error: function() {
                alert('Error processing charge');
            }
        });
    });

    // Initial click handler setup
    $('.add-item').on('click', handleAddItem);
});