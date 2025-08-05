$(document).ready(function () {
  const addCustomerModal = new bootstrap.Modal(
    document.getElementById("addCustomerModal")
  );
  const editCustomerModal = new bootstrap.Modal(
    document.getElementById("editCustomerModal")
  );

  // Track current page
  let currentPage = 1;

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
    // Update current page tracker
    currentPage = page;
    
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

  // Function to bind delete button events
  function bindDeleteButtonEvents() {
    $(document).off('click', '.delete-btn').on('click', '.delete-btn', function(e) {
      e.preventDefault();
      
      const customerId = $(this).data('id');
      const customerName = $(this).data('name');
      
      // Show SweetAlert confirmation dialog
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Are you sure?',
          text: `Do you want to delete "${customerName}"? This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            deleteCustomer(customerId, customerName);
          }
        });
      } else {
        // Fallback to native confirm if SweetAlert is not available
        if (confirm(`Are you sure you want to delete "${customerName}"? This action cannot be undone.`)) {
          deleteCustomer(customerId, customerName);
        }
      }
    });
  }

  // Function to delete customer via AJAX
  function deleteCustomer(customerId, customerName) {
    $.ajax({
      url: '../../controller/backend_customers.php',
      method: 'POST',
      data: {
        action: 'delete_customer',
        customer_id: customerId
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Customer has been deleted successfully.',
              confirmButtonColor: '#3085d6',
              timer: 2000
            });
          } else {
            alert('Customer deleted successfully!');
          }
          
          // Refresh the table by performing a new search, staying on current page
          performSearch(currentPage);
        } else {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Delete Failed',
              text: response.message || 'Failed to delete customer',
              confirmButtonColor: '#d33',
            });
          } else {
            alert('Failed to delete customer: ' + (response.message || 'Unknown error'));
          }
        }
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error occurred while deleting customer',
            confirmButtonColor: '#d33',
          });
        } else {
          alert('Error occurred while deleting customer');
        }
      }
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
  bindDeleteButtonEvents();

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
          // Refresh the table by performing a new search, staying on current page
          performSearch(currentPage);
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
          // For new customer, go to page 1 to see the newly added customer
          performSearch(1);
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
