$(document).ready(function () {
  const addCustomerModal = new bootstrap.Modal(
    document.getElementById("addCustomerModal")
  );
  const editCustomerModal = new bootstrap.Modal(
    document.getElementById("editCustomerModal")
  );

  // Add click handler for "Add Customer" button
  $("#addCustomerBtn").on("click", function () {
    addCustomerModal.show();
  });

  // Toggle filter section
  $("#toggle-filters").click(function() {
    $("#filter-body").slideToggle();
  });

  // Function to perform search with AJAX
  function performSearch(page = 1) {
    const searchData = {
      action: 'search_customers',
      name: $('#name-filter').val(),
      phone: $('#phone-filter').val(),
      address: $('#address-filter').val(),
      salesman: $('#salesman-filter').val(),
      page: page
    };

    $.ajax({
      url: '../../controller/backend_customers.php',
      method: 'POST',
      data: searchData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          // Update table body
          $('#customersTableBody').html(response.tableHtml);
          
          // Update pagination container
          $('#pagination-container').html(response.paginationHtml);
          
          // Re-bind pagination click events
          bindPaginationEvents();
          
          // Re-bind edit button events
          bindEditButtonEvents();
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
    $(document).off('click', '.edit-btn').on('click', '.edit-btn', function () {
      const id = $(this).data("id");
      const name = $(this).data("name");
      const phone = $(this).data("phone");
      const address = $(this).data("address");
      const terms = $(this).data("terms");
      const salesman = $(this).data("salesman");

      // Populate form fields
      $("#edit_customer_id").val(id);
      $("#edit_name").val(name);
      $("#edit_phone_number").val(phone);
      $("#edit_address").val(address);
      $("#edit_terms").val(terms);
      $("#edit_salesman").val(salesman);

      // Show modal
      editCustomerModal.show();
    });
  }

  // Auto-search with debounce for text inputs
  let searchTimeout;
  $('#name-filter, #phone-filter, #address-filter, #salesman-filter').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
      performSearch(1);
    }, 300); // Wait 300ms after user stops typing
  });

  // Apply filter button (still available if needed)
  $('#apply-filter').click(function() {
    performSearch(1);
  });

  // Reset filter button
  $('#reset-filter').click(function() {
    $('#name-filter, #phone-filter, #address-filter, #salesman-filter').val('');
    performSearch(1);
  });

  // Search on Enter key (for quick search)
  $('#name-filter, #phone-filter, #address-filter, #salesman-filter').keypress(function(e) {
    if (e.which == 13) {
      clearTimeout(searchTimeout); // Cancel debounced search
      performSearch(1); // Search immediately
    }
  });

  // Initial binding
  bindPaginationEvents();
  bindEditButtonEvents();

  // Handle edit form submission
  $("#editCustomerForm").submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append("action", "edit_customer");

    $.ajax({
      url: "../../controller/backend_customers.php",
      method: "POST",
      data: Object.fromEntries(formData),
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          editCustomerModal.hide();
          window.location.reload();
        } else {
          alert(
            "Error updating customer: " + (response.message || "Unknown error")
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", { xhr, status, error });
        alert("Failed to update customer. Check console for details.");
      },
    });
  });

  // Handle add customer form submission
  $("#addCustomerForm").submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
      url: "../../controller/backend_customers.php",
      method: "POST",
      data: Object.fromEntries(formData),
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          // Reset form and close modal
          $("#addCustomerForm")[0].reset();
          addCustomerModal.hide();
          window.location.reload();
        } else {
          alert(
            "Error adding customer: " + (response.message || "Unknown error")
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", { xhr, status, error });
        alert("Failed to add customer. Check console for details.");
      },
    });
  });
});
