$(document).ready(function () {
  let cart = [];

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
                                        data-stock="${item.stock}">
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

  // Function to calculate item total with discount
  function calculateItemTotal(item) {
    // Ensure discount is a number
    if (item.discount === undefined || isNaN(parseInt(item.discount))) {
      item.discount = 0;
    }

    const subtotal = parseFloat(item.price) * parseInt(item.quantity);
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
        quantity: 1,
        discount: 0,
        maxStock: itemStock,
      };

      // Calculate initial total (no discount)
      newItem.total = newItem.price * newItem.quantity;

      cart.push(newItem);
    }

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
      updateCartDisplay();
    }
  });

  // Remove item from cart
  $(document).on("click", ".remove-item", function () {
    const itemId = $(this).closest(".cart-item").data("id");
    cart = cart.filter((item) => item.id !== itemId);
    updateCartDisplay();
  });

  // No longer disable the process-charge button on customer selection

  // Update process charge success callback
  $("#process-charge").on("click", function () {
    if (cart.length === 0 || !$("#customer").val()) {
      alert("Please select a customer and add items to cart");
      return;
    }

    // Get P.O. Number from input field
    const poNumber = $("#po_number").val();
    if (!poNumber || poNumber.trim() === "") {
      alert("Please enter a P.O. Number before processing the charge.");
      return;
    }

    $.ajax({
      url: "../../controller/backend_charge.php",
      method: "POST",
      data: {
        action: "process_charge",
        customer_id: $("#customer").val(),
        items: cart,
        po_number: poNumber, // Include P.O. number in the request
      },
      success: function (response) {
        if (response.status === "success") {
          alert("Charge processed successfully!");
          cart = [];
          updateCartDisplay();
          $("#customer").val("");
          $("#po_number").val(""); // Clear P.O. Number field
          // Refresh the items table
          refreshItemsTable();
        } else {
          alert("Error: " + (response.message || "Failed to process charge"));
        }
      },
      error: function () {
        alert("Error processing charge");
      },
    });
  });

  // Initial click handler setup
  $(".add-item").on("click", handleAddItem);

  // Add item to cart
  $(".add-item").on("click", function () {
    const itemId = $(this).data("id");
    const itemName = $(this).data("name");
    const itemPrice = parseFloat($(this).data("price"));
    const maxStock = parseInt($(this).data("stock"));

    // Check if item already in cart
    const existingItem = cart.find((item) => item.id === itemId);

    if (existingItem) {
      // Don't exceed available stock
      if (existingItem.quantity < maxStock) {
        existingItem.quantity += 1;
        // Recalculate total with discount
        calculateItemTotal(existingItem);
      } else {
        alert("Cannot add more of this item. Maximum stock reached.");
        return;
      }
    } else {
      const newItem = {
        id: itemId,
        name: itemName,
        price: itemPrice,
        quantity: 1,
        discount: 0, // Default 0% discount
        total: itemPrice,
      };
      cart.push(newItem);
    }

    updateCart();
  });

  // Remove item from cart
  $(document).on("click", ".remove-item", function () {
    const index = $(this).data("index");
    cart.splice(index, 1);
    updateCart();
  });

  // Update quantity
  $(document).on("change", ".item-quantity", function () {
    const index = $(this).data("index");
    const newQuantity = parseInt($(this).val());
    const maxStock = $(this).data("max-stock");

    if (newQuantity < 1) {
      $(this).val(1);
      cart[index].quantity = 1;
    } else if (newQuantity > maxStock) {
      $(this).val(maxStock);
      cart[index].quantity = maxStock;
    } else {
      cart[index].quantity = newQuantity;
    }

    // Recalculate total with discount
    calculateItemTotal(cart[index]);
    updateCart();
  });

  // Handle discount change
  $(document).on("change", ".item-discount", function () {
    const index = $(this).data("index");
    let discount = parseInt($(this).val());

    // Validate discount range (0-100%)
    if (isNaN(discount) || discount < 0) {
      discount = 0;
      $(this).val(0);
    } else if (discount > 100) {
      discount = 100;
      $(this).val(100);
    }

    if (cart[index]) {
      cart[index].discount = discount;
      // Recalculate total with discount
      calculateItemTotal(cart[index]);
      updateCart();
    }
  });

  

  // Update the cart display
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

      totalAmount += item.total;

      const subtotal = item.price * item.quantity;
      const savings = subtotal - item.total;

      const itemElement = `
        <div class="cart-item card mb-3">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 px-3">${item.name}</h6>
            <button class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
              <span class="iconify" data-icon="solar:trash-bin-minimalistic-outline" data-width="16"></span>
            </button>
          </div>
          <div class="card-body py-2">
            <div class="row mb-2">
              <div class="col-6">
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light">Qty</span>
                  <input type="number" class="form-control item-quantity" value="${
                    item.quantity
                  }" 
                    min="1" max="${$(`.add-item[data-id="${item.id}"]`).data(
                      "stock"
                    )}" 
                    data-index="${index}" data-max-stock="${$(
        `.add-item[data-id="${item.id}"]`
      ).data("stock")}">
                </div>
              </div>
              <div class="col-6">
                <div class="text-end">
                  <div class="text-muted small">Unit: ₱${item.price.toFixed(
                    2
                  )}</div>
                </div>
              </div>
            </div>
            
            <div class="row align-items-center mb-1">
              <div class="col-7">
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light">Discount</span>
                  <input type="number" class="form-control item-discount" value="${
                    item.discount
                  }" 
                    min="0" max="100" data-index="${index}">
                  <span class="input-group-text bg-light">%</span>
                </div>
              </div>
              <div class="col-5">
                <div class="text-end">
                  <strong>₱${item.total.toFixed(2)}</strong>
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
        const subtotal = item.price * item.quantity;
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

  // Initialize empty cart
  updateCart();

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
});
