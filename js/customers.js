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


  // Pagination variables
  let customersRows = $("#customersTableBody tr");
  let customersRowsPerPage = 10;
  let customersCurrentPage = 1;

  function renderCustomersTable(page = 1) {
    customersRows = $("#customersTableBody tr");
    const totalRows = customersRows.length;
    const totalPages = Math.ceil(totalRows / customersRowsPerPage);
    customersCurrentPage = page;

    // Hide all rows
    customersRows.hide();
    // Show only the rows for the current page
    const startIdx = (page - 1) * customersRowsPerPage;
    const endIdx = Math.min(startIdx + customersRowsPerPage, totalRows);
    for (let i = startIdx; i < endIdx; i++) {
      // Add index number as first cell
      const row = $(customersRows[i]);
      if (row.find(".row-index").length === 0) {
        row.prepend(`<td class="row-index"></td>`);
      }
      row.find(".row-index").text(i + 1);
      row.show();
    }

    // Render pagination
    const pag = $("#customersTablePagination");
    pag.empty();
    if (totalPages > 1) {
      for (let i = 1; i <= totalPages; i++) {
        pag.append(`<li class="page-item${i === customersCurrentPage ? ' active' : ''}"><a class="page-link" href="#">${i}</a></li>`);
      }
      pag.find("a").on("click", function (e) {
        e.preventDefault();
        const page = parseInt($(this).text());
        if (page !== customersCurrentPage) {
          renderCustomersTable(page);
        }
      });
    }
  }

  // Initial render
  renderCustomersTable(1);

  // Add click handler for edit buttons (delegated for dynamic rows)
  $(document).on("click", ".edit-btn", function () {
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

  // Search filter functionality
  $("#customer-search").on("keyup", function () {
    const searchTerm = $(this).val().toLowerCase();
    filterCustomers(searchTerm);
  });

  $("#clear-search").on("click", function () {
    $("#customer-search").val("");
    filterCustomers("");
  });

  function filterCustomers(searchTerm) {
    $(".customers-table tbody tr").each(function () {
      const row = $(this);
      const name = row.find("td:nth-child(1)").text().toLowerCase();
      const phone = row.find("td:nth-child(2)").text().toLowerCase();
      const address = row.find("td:nth-child(3)").text().toLowerCase();
      const salesman = row.find("td:nth-child(5)").text().toLowerCase();

      const matches =
        name.includes(searchTerm) ||
        phone.includes(searchTerm) ||
        address.includes(searchTerm) ||
        salesman.includes(searchTerm);

      if (matches || searchTerm === "") {
        row.show();
      } else {
        row.hide();
      }
    });

    // Show/hide "no results" message
    const visibleRows = $(".customers-table tbody tr:visible").length;
    if (visibleRows === 0 && searchTerm !== "") {
      if ($("#no-results-row").length === 0) {
        $(".customers-table tbody").append(
          '<tr id="no-results-row"><td colspan="6" class="text-center text-muted">No customers found matching your search.</td></tr>'
        );
      }
    } else {
      $("#no-results-row").remove();
    }
  }
});
