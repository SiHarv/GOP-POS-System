$(document).ready(function () {
  let cart = [];

  // Load cart from localStorage on page load
  function loadCartFromStorage() {
    const savedCart = localStorage.getItem('charge_cart');
    if (savedCart) {
      try {
        cart = JSON.parse(savedCart);
        updateCart();
      } catch (e) {
        console.error('Error loading cart from localStorage:', e);
        cart = [];
      }
    }
  }

  // Save cart to localStorage
  function saveCartToStorage() {
    try {
      localStorage.setItem('charge_cart', JSON.stringify(cart));
    } catch (e) {
      console.error('Error saving cart to localStorage:', e);
    }
  }

  // Clear cart from localStorage
  function clearCartFromStorage() {
    localStorage.removeItem('charge_cart');
  }

  // Initialize cart from localStorage when page loads
  loadCartFromStorage();

  // Function to refresh items table
  function refreshItemsTable() {
    $.ajax({
      url: "../../controller/backend_charge.php",
      method: "POST",
      data: { action: "get_items" },
      success: function (response) {
        const tbody = $(".table-hover tbody");
        tbody.empty();

        response.forEach((item) => {
          tbody.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.category}</td>
                            <td>${item.stock}</td>
                            <td>₱${parseFloat(item.price).toLocaleString(
                              "en-US",
                              {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                              }
                            )}</td>
                            <td>
                                ${
                                  item.stock > 0
                                    ? `<button class="btn btn-sm btn-primary add-item" 
                                        data-id="${item.id}"
                                        data-name="${item.name}"
                                        data-price="${item.price}"
                                        data-stock="${item.stock}"
                                        data-unit="${item.unit}">
                                        Add
                                    </button>`
                                    : `<button class="btn btn-sm btn-secondary" disabled>
                                        Out of Stock
                                    </button>`
                                }
                            </td>
                        </tr>
                    `);
        });

        // Reattach click handlers for new buttons
        $(".add-item").off("click").on("click", handleAddItem);
      },
    });
  }

  // Update cart display
  function updateCart() {
    const cartContainer = $("#cart-items");
    cartContainer.empty();

    if (cart.length === 0) {
      cartContainer.html('<p class="text-muted">Your cart is empty</p>');
      $("#total-amount").text("0.00");
      return;
    }

    let totalAmount = 0;

    cart.forEach((item, index) => {
      // Safety check: ensure item has total calculated
      if (item.total === undefined || isNaN(item.total)) {
        calculateItemTotal(item);
      }

      // Initialize customPrice if not exists
      if (item.customPrice === undefined) {
        item.customPrice = item.price;
        item.isPriceEditable = false;
      }

      // Initialize unit if not exists
      if (item.unit === undefined || item.unit === null || item.unit === "") {
        item.unit = "PCS"; // Default unit only if no unit is set
      }

      totalAmount += item.total;

      const subtotal = item.customPrice * item.quantity;
      const savings = subtotal - item.total;

      const itemElement = `
        <div class="cart-item card mb-3">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 px-2">${item.name}</h6>
            <div class="d-flex align-items-center">
              <button class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                <span class="iconify" data-icon="solar:trash-bin-minimalistic-outline" data-width="16"></span>
              </button>
            </div>
          </div>
          <div class="card-body py-2">
            <div class="row mb-2">
              <div class="col-12 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="form-check form-switch">
                    <input class="form-check-input price-toggle" type="checkbox" data-index="${index}" ${item.isPriceEditable ? 'checked' : ''}>
                    <label class="form-check-label small text-muted">Edit Price</label>
                  </div>
                </div>
              </div>
              <div class="col-6 mb-2">
                <label class="form-label small mb-1">Unit</label>
                <input type="text" class="form-control form-control-sm item-unit" 
                  value="${item.unit}" 
                  data-index="${index}" 
                  data-item-id="${item.id}"
                  placeholder="e.g., PCS, KG, LTR">
              </div>
              <div class="col-6 mb-2">
                <div class="text-end">
                  ${item.isPriceEditable ? `
                    <label class="form-label small mb-1">Unit Price</label>
                    <input type="number" class="form-control form-control-sm item-custom-price" 
                      value="${item.customPrice.toFixed(2)}" 
                      min="0" step="0.01" data-index="${index}"
                      placeholder="0.00">
                    ${item.customPrice !== item.price ? 
                      `<small class="text-muted">Original: ₱${item.price.toFixed(2)}</small>` : 
                      ''
                    }
                  ` : `
                    <label class="form-label small mb-1">Unit Price</label>
                    <div class="text-muted small border rounded px-2 py-1">₱${item.customPrice.toFixed(2)}</div>
                    ${item.customPrice !== item.price ? 
                      `<small class="text-warning">Custom Price (Original: ₱${item.price.toFixed(2)})</small>` : 
                      ''
                    }
                  `}
                </div>
              </div>
              <div class="col-6 pe-1">
                <label class="form-label small mb-1">Quantity</label>
                <input type="number" class="form-control form-control-sm item-quantity" value="${
                  item.quantity
                }" 
                  min="1" max="${$(`.add-item[data-id="${item.id}"]`).data(
                    "stock"
                  )}" 
                  data-index="${index}" data-max-stock="${$(
        `.add-item[data-id="${item.id}"]`
      ).data("stock")}">
              </div>
              <div class="col-6 ps-1">
                <label class="form-label small mb-1">Discount %</label>
                <input type="number" class="form-control form-control-sm item-discount" value="${
                  item.discount
                }" 
                  min="0" max="100" data-index="${index}">
              </div>
            </div>
            
            <div class="row align-items-center mb-1">
              <div class="col-12">
                <div class="text-end">
                  <strong class="h6">Total: ₱${item.total.toFixed(2)}</strong>
                </div>
              </div>
            </div>
            
            ${
              item.discount > 0
                ? `
              <div class="discount-info alert alert-success py-1 px-2 mt-1 mb-0 d-flex justify-content-between align-items-center">
                <small>You save:</small>
                <strong>₱${savings.toFixed(2)}</strong>
              </div>
            `
                : ""
            }
          </div>
        </div>
      `;

      cartContainer.append(itemElement);
    });

    const discountedItems = cart.filter((item) => item.discount > 0);
    if (discountedItems.length > 0) {
      const totalSavings = discountedItems.reduce((sum, item) => {
        const subtotal = item.customPrice * item.quantity;
        return sum + (subtotal - item.total);
      }, 0);

      cartContainer.append(`
        <div class="savings-summary alert alert-success mb-3">
          <div class="d-flex justify-content-between">
            <span>Total Savings:</span>
            <strong>₱${totalSavings.toFixed(2)}</strong>
          </div>
        </div>
      `);
    }

    $("#total-amount").text(totalAmount.toFixed(2));
  }

  // Handle price toggle
  $(document).on("change", ".price-toggle", function () {
    const index = $(this).data("index");
    cart[index].isPriceEditable = $(this).is(":checked");
    
    // If toggling off, reset to original price
    if (!cart[index].isPriceEditable) {
      cart[index].customPrice = cart[index].price;
      calculateItemTotal(cart[index]);
    }
    
    // Save cart to localStorage after price toggle
    saveCartToStorage();
    updateCart();
  });

  // Handle custom price change
  $(document).on("input", ".item-custom-price", function () {
    const $input = $(this);
    const index = $input.data("index");
    const newPrice = parseFloat($input.val());

    if (!isNaN(newPrice) && newPrice >= 0) {
      cart[index].customPrice = newPrice;
      
      // Recalculate total with new price
      calculateItemTotal(cart[index]);

      // Update only the total display immediately
      const $cartItem = $input.closest('.cart-item');
      $cartItem.find('.h6').text(`Total: ₱${cart[index].total.toFixed(2)}`);
      
      // Update savings display if discount exists
      if (cart[index].discount > 0) {
        const subtotal = cart[index].customPrice * cart[index].quantity;
        const savings = subtotal - cart[index].total;
        $cartItem.find('.discount-info strong').text(`₱${savings.toFixed(2)}`);
      }

      // Save cart to localStorage after price change
      saveCartToStorage();
      // Update grand total immediately
      updateGrandTotal();
    }
  });

  // Update the calculateItemTotal function to use customPrice
  function calculateItemTotal(item) {
    // Ensure discount is a number
    if (item.discount === undefined || isNaN(parseInt(item.discount))) {
      item.discount = 0;
    }

    // Use customPrice if available, otherwise use original price
    const priceToUse = item.customPrice !== undefined ? item.customPrice : item.price;
    const subtotal = parseFloat(priceToUse) * parseInt(item.quantity);
    const discountAmount = subtotal * (parseFloat(item.discount) / 100);
    item.total = subtotal - discountAmount;

    // Check if total is NaN and fix it
    if (isNaN(item.total)) {
      console.warn("Total was NaN, fixing:", item);
      item.total = subtotal;
    }

    return item.total;
  }

  // Move add-item click handler to separate function
  function handleAddItem() {
    const button = $(this);
    const itemId = button.data("id");
    const itemName = button.data("name");
    const itemPrice = parseFloat(button.data("price"));
    const itemStock = parseInt(button.data("stock"));
    const itemUnit = button.data("unit"); // Remove the fallback, trust the backend

    const existingItem = cart.find((item) => item.id === itemId);

    if (existingItem) {
      // Check if adding one more would exceed stock
      if (existingItem.quantity >= itemStock) {
        alert("Not enough stock available!");
        return;
      }
      existingItem.quantity++;

      // Ensure discount is initialized and total is calculated
      if (existingItem.discount === undefined) {
        existingItem.discount = 0;
      }
      calculateItemTotal(existingItem);
      
      // Move existing item to top of cart (beginning of array)
      const itemIndex = cart.findIndex((item) => item.id === itemId);
      const [movedItem] = cart.splice(itemIndex, 1);
      cart.unshift(movedItem);
    } else {
      if (itemStock <= 0) {
        alert("This item is out of stock!");
        return;
      }
      // Create new item with proper initialization of all fields
      const newItem = {
        id: itemId,
        name: itemName,
        price: itemPrice,
        customPrice: itemPrice, // Initialize custom price same as original
        isPriceEditable: false, // Default to non-editable
        quantity: 1, // Set quantity to 1 instead of 0
        discount: 0,
        unit: itemUnit, // Use the unit from database
        maxStock: itemStock,
      };

      // Calculate initial total (with quantity 1)
      newItem.total = newItem.customPrice * newItem.quantity;

      // Add new item to the beginning of cart array (top position)
      cart.unshift(newItem);
    }

    // Save cart to localStorage after adding item
    saveCartToStorage();
    updateCart();
  }

  // Update cart display
  function updateCartDisplay() {
    const cartContainer = $("#cart-items");
    cartContainer.empty();

    let total = 0;

    cart.forEach((item) => {
      const itemTotal = item.price * item.quantity;
      total += itemTotal;

      cartContainer.append(`
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₱${item.price.toFixed(
                          2
                        )}</div>
                    </div>
                    <div class="quantity-control">
                        <button class="btn btn-sm btn-outline-secondary decrease-qty">-</button>
                        <input type="number" class="form-control form-control-sm qty-input" value="${
                          item.quantity
                        }" min="1">
                        <button class="btn btn-sm btn-outline-secondary increase-qty">+</button>
                        <i class="iconify remove-item ms-2" data-icon="mdi:trash-can-outline"></i>
                    </div>
                </div>
            `);
    });

    $("#total-amount").text(total.toFixed(2));

    // No longer disable the process-charge button
  }

  // Handle quantity changes
  $(document).on("click", ".increase-qty", function () {
    const itemId = $(this).closest(".cart-item").data("id");
    const item = cart.find((item) => item.id === itemId);
    if (item && item.quantity < item.maxStock) {
      item.quantity++;
      // Save cart to localStorage after quantity change
      saveCartToStorage();
      updateCartDisplay();
    } else {
      alert("Maximum stock limit reached!");
    }
  });

  $(document).on("click", ".decrease-qty", function () {
    const itemId = $(this).closest(".cart-item").data("id");
    const item = cart.find((item) => item.id === itemId);
    if (item && item.quantity > 1) {
      item.quantity--;
      // Save cart to localStorage after quantity change
      saveCartToStorage();
      updateCartDisplay();
    }
  });

  // Remove item from cart
  $(document).on("click", ".remove-item", function () {
    const index = $(this).data("index");
    cart.splice(index, 1);
    // Save cart to localStorage after removing item
    saveCartToStorage();
    updateCart();
  });

  // No longer disable the process-charge button on customer selection

  // Update process charge success callback
  $("#process-charge").on("click", function () {
    if (cart.length === 0 || !$("#customer").val()) {
      Swal.fire({
        icon: "warning",
        title: "Missing Information",
        text: "Please select a customer and add items to cart",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    // Check for items with quantity 0
    const zeroQuantityItems = cart.filter(item => item.quantity === 0);
    if (zeroQuantityItems.length > 0) {
      const itemNames = zeroQuantityItems.map(item => item.name).join(", ");
      Swal.fire({
        icon: "error",
        title: "Invalid Quantity",
        text: `The following items have quantity 0: ${itemNames}. Please update quantities before processing.`,
        confirmButtonColor: "#d33",
      });
      return;
    }

    // Get P.O. Number from input field
    const poNumber = $("#po_number").val();
    if (!poNumber || poNumber.trim() === "") {
      Swal.fire({
        icon: "warning",
        title: "Missing P.O. Number",
        text: "Please enter a P.O. Number before processing the charge.",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    $.ajax({
      url: "../../controller/backend_charge.php",
      method: "POST",
      data: {
        action: "process_charge",
        customer_id: $("#customer_id").val(), // <-- Use the hidden input for customer ID
        items: cart,
        po_number: poNumber, // Include P.O. number in the request
      },
      success: function (response) {
        if (response.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Receipt Created Successfully!",
            text: "Receipt has been created. Stock will be subtracted when you print the receipt.",
            showCancelButton: true,
            confirmButtonText: "View Receipt",
            cancelButtonText: "Create Another",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#6c757d",
          }).then((result) => {
            if (result.isConfirmed && response.charge_id) {
              // Redirect to receipts page and open the specific receipt
              window.location.href = `../receipts/receipts.php?open_receipt=${response.charge_id}`;
            }
          });

          // Clear the form and localStorage
          cart = [];
          clearCartFromStorage(); // Clear localStorage when charge is processed
          updateCartDisplay();
          $("#customer").val("");
          $("#po_number").val(""); // Clear P.O. Number field
          $("#salesman").val(""); // Clear Salesman field
          $("#customer_id").val(""); // Clear customer ID
          // Refresh the items table
          refreshItemsTable();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message || "Failed to process charge",
            confirmButtonColor: "#d33",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error processing charge",
          confirmButtonColor: "#d33",
        });
      },
    });
  });

  // Initial click handler setup
  $(".add-item").on("click", handleAddItem);

  // Update quantity - immediate update without debouncing
  $(document).on("input", ".item-quantity", function () {
    const $input = $(this);
    const index = $input.data("index");
    const newQuantity = parseInt($input.val());
    const maxStock = $input.data("max-stock");

    // Update cart data immediately for real-time validation
    if (newQuantity < 1) {
      $input.val(1);
      cart[index].quantity = 1;
    } else if (newQuantity > maxStock) {
      $input.val(maxStock);
      cart[index].quantity = maxStock;
    } else {
      cart[index].quantity = newQuantity;
    }

    // Recalculate total immediately
    calculateItemTotal(cart[index]);

    // Update only the total display immediately without rebuilding entire cart
    const $cartItem = $input.closest('.cart-item');
    $cartItem.find('.h6').text(`Total: ₱${cart[index].total.toFixed(2)}`);
    
    // Update savings display if discount exists
    if (cart[index].discount > 0) {
      const subtotal = cart[index].price * cart[index].quantity;
      const savings = subtotal - cart[index].total;
      $cartItem.find('.discount-info strong').text(`₱${savings.toFixed(2)}`);
    }

    // Save cart to localStorage after quantity change
    saveCartToStorage();
    // Update grand total immediately
    updateGrandTotal();
  });

  // Clear default "0" when user focuses on discount input
  $(document).on("focus", ".item-discount", function () {
    const $input = $(this);
    if ($input.val() === "0") {
      $input.val("");
    }
  });

  // Restore "0" if user leaves discount input empty
  $(document).on("blur", ".item-discount", function () {
    const $input = $(this);
    if ($input.val() === "" || $input.val() === null) {
      $input.val("0");
      // Trigger input event to update calculations
      $input.trigger("input");
    }
  });

  // Handle discount change - immediate update without debouncing
  $(document).on("input", ".item-discount", function () {
    const $input = $(this);
    const index = $input.data("index");
    let discount = parseInt($input.val());

    // Allow empty input while typing
    if ($input.val() === "") {
      discount = 0;
    } else {
      // Validate discount range (0-100%)
      if (isNaN(discount) || discount < 0) {
        discount = 0;
        $input.val(0);
      } else if (discount > 100) {
        discount = 100;
        $input.val(100);
      }
    }

    if (cart[index]) {
      cart[index].discount = discount;
      // Recalculate total with discount
      calculateItemTotal(cart[index]);

      // Update only the total display immediately
      const $cartItem = $input.closest('.cart-item');
      $cartItem.find('.h6').text(`Total: ₱${cart[index].total.toFixed(2)}`);
      
      // Update or show/hide savings display
      const subtotal = cart[index].price * cart[index].quantity;
      const savings = subtotal - cart[index].total;
      
      if (discount > 0) {
        let $discountInfo = $cartItem.find('.discount-info');
        if ($discountInfo.length === 0) {
          $cartItem.find('.card-body').append(`
            <div class="discount-info alert alert-success py-1 px-2 mt-1 mb-0 d-flex justify-content-between align-items-center">
              <small>You save:</small>
              <strong>₱${savings.toFixed(2)}</strong>
            </div>
          `);
        } else {
          $discountInfo.find('strong').text(`₱${savings.toFixed(2)}`);
        }
      } else {
        $cartItem.find('.discount-info').remove();
      }

      // Save cart to localStorage after discount change
      saveCartToStorage();
      // Update grand total immediately
      updateGrandTotal();
    }
  });

  // Function to update only the grand total
  function updateGrandTotal() {
    let totalAmount = 0;
    cart.forEach(item => {
      if (item.total !== undefined && !isNaN(item.total)) {
        totalAmount += item.total;
      }
    });
    $("#total-amount").text(totalAmount.toFixed(2));

    // Update total savings
    const discountedItems = cart.filter((item) => item.discount > 0);
    if (discountedItems.length > 0) {
      const totalSavings = discountedItems.reduce((sum, item) => {
        const subtotal = item.price * item.quantity;
        return sum + (subtotal - item.total);
      }, 0);
      
      $('.savings-summary strong').text(`₱${totalSavings.toFixed(2)}`);
    }
  }

  // Item search functionality
  $("#item-search").on("keyup", function () {
    const searchTerm = $(this).val().toLowerCase();
    filterItems(searchTerm);
  });

  $("#clear-item-search").on("click", function () {
    $("#item-search").val("");
    filterItems("");
  });

  function filterItems(searchTerm) {
    $("#items-table tbody tr").each(function () {
      const row = $(this);
      const name = row.find("td:nth-child(1)").text().toLowerCase();
      const category = row.find("td:nth-child(2)").text().toLowerCase();

      const matches =
        name.includes(searchTerm) || category.includes(searchTerm);

      if (matches || searchTerm === "") {
        row.show();
      } else {
        row.hide();
      }
    });

    // Show/hide "no results" message
    const visibleRows = $("#items-table tbody tr:visible").length;
    if (visibleRows === 0 && searchTerm !== "") {
      if ($("#no-items-found").length === 0) {
        $("#items-table tbody").append(
          '<tr id="no-items-found"><td colspan="5" class="text-center text-muted">No items found matching your search.</td></tr>'
        );
      }
    } else {
      $("#no-items-found").remove();
    }
  }

  // Handle unit change and update database
  $(document).on("blur", ".item-unit", function () {
    const $input = $(this);
    const index = $input.data("index");
    const itemId = $input.data("item-id");
    const newUnit = $input.val().trim();

    if (newUnit === "") {
      $input.val("PCS"); // Default to PCS if empty
      cart[index].unit = "PCS";
      // Save cart to localStorage after unit change
      saveCartToStorage();
      return;
    }

    // Update cart item
    cart[index].unit = newUnit;
    
    // Save cart to localStorage after unit change
    saveCartToStorage();

    // Update database
    $.ajax({
      url: "../../controller/backend_charge.php",
      method: "POST",
      data: {
        action: "update_item_unit",
        item_id: itemId,
        unit: newUnit
      },
      success: function (response) {
        if (response.status === "success") {
          // Show success indicator briefly
          $input.removeClass("is-invalid").addClass("is-valid");
          setTimeout(() => {
            $input.removeClass("is-valid");
          }, 2000);
          
          // Refresh items table to show updated unit
          refreshItemsTable();
        } else {
          // Show error indicator
          $input.addClass("is-invalid");
          alert("Failed to update unit: " + (response.message || "Unknown error"));
        }
      },
      error: function () {
        $input.addClass("is-invalid");
        alert("Error updating unit in database");
      }
    });
  });

  // Handle unit input validation on typing
  $(document).on("input", ".item-unit", function () {
    const $input = $(this);
    const index = $input.data("index");
    const newUnit = $input.val().trim();

    // Remove validation classes while typing
    $input.removeClass("is-valid is-invalid");

    // Update cart item immediately for UI consistency
    if (cart[index]) {
      cart[index].unit = newUnit || "PCS";
    }
  });

  // Handle customer selection and populate salesman field
  $(document).on("click", ".customer-option", function (e) {
    e.preventDefault();
    const customerId = $(this).data("id");
    const customerName = $(this).data("name");
    const customerSalesman = $(this).data("salesman");

    // Set customer name and ID
    $("#customer").val(customerName);
    $("#customer_id").val(customerId);

    // Populate salesman field
    $("#salesman").val(customerSalesman || "");

    // Close dropdown
    $("#customer-dropdown").removeClass("show");
  });

  // Handle salesman field update when user changes it
  $(document).on("blur", "#salesman", function () {
    const $input = $(this);
    const customerId = $("#customer_id").val();
    const newSalesman = $input.val().trim();

    // Only update if customer is selected
    if (!customerId) {
      return;
    }

    // Update database
    $.ajax({
      url: "../../controller/backend_charge.php",
      method: "POST",
      data: {
        action: "update_customer_salesman",
        customer_id: customerId,
        salesman: newSalesman
      },
      success: function (response) {
        if (response.status === "success") {
          // Show success indicator briefly
          $input.removeClass("is-invalid").addClass("is-valid");
          setTimeout(() => {
            $input.removeClass("is-valid");
          }, 2000);
          
          // Update the dropdown data for future selections
          $(`.customer-option[data-id="${customerId}"]`).attr("data-salesman", newSalesman);
        } else {
          // Show error indicator
          $input.addClass("is-invalid");
          Swal.fire({
            icon: "error",
            title: "Update Failed",
            text: "Failed to update salesman: " + (response.message || "Unknown error"),
            confirmButtonColor: "#d33",
          });
        }
      },
      error: function () {
        $input.addClass("is-invalid");
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error updating salesman in database",
          confirmButtonColor: "#d33",
        });
      }
    });
  });

  // Handle salesman input validation on typing
  $(document).on("input", "#salesman", function () {
    const $input = $(this);
    // Remove validation classes while typing
    $input.removeClass("is-valid is-invalid");
  });
});
