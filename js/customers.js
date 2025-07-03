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
  let customersRowsPerPage = 10;
  let customersCurrentPage = 1;

  function getFilteredRows(searchTerm) {
    if (!searchTerm) {
      return $("#customersTableBody tr");
    }
    return $("#customersTableBody tr").filter(function () {
      const row = $(this);
      const name = row.find("td:nth-child(2)").text().toLowerCase();
      const phone = row.find("td:nth-child(3)").text().toLowerCase();
      const address = row.find("td:nth-child(4)").text().toLowerCase();
      const salesman = row.find("td:nth-child(6)").text().toLowerCase();
      return (
        name.includes(searchTerm) ||
        phone.includes(searchTerm) ||
        address.includes(searchTerm) ||
        salesman.includes(searchTerm)
      );
    });
  }

  function renderFilteredTable(searchTerm, page = 1) {
    const filteredRows = getFilteredRows(searchTerm);
    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / customersRowsPerPage);
    customersCurrentPage = page;

    // Hide all rows first
    $("#customersTableBody tr").hide();

    // Show only the filtered rows for the current page
    const startIdx = (page - 1) * customersRowsPerPage;
    const endIdx = Math.min(startIdx + customersRowsPerPage, totalRows);

    for (let i = startIdx; i < endIdx; i++) {
      const row = $(filteredRows[i]);
      // Add or update the row index
      if (row.find(".row-index").length === 0) {
        row.prepend('<td class="row-index text-center"></td>');
      }
      row.find(".row-index").text(i + 1);
      row.show();
    }

    // Render pagination
    const pag = $("#customersTablePagination");
    pag.empty();
    if (totalPages > 1) {
      for (let i = 1; i <= totalPages; i++) {
        pag.append(
          `<li class="page-item${
            i === customersCurrentPage ? " active" : ""
          }"><a class="page-link" href="#">${i}</a></li>`
        );
      }
      pag.find("a").on("click", function (e) {
        e.preventDefault();
        const page = parseInt($(this).text());
        if (page !== customersCurrentPage) {
          renderFilteredTable(searchTerm, page);
        }
      });
    }

    // Show/hide "no results" message
    if (totalRows === 0 && searchTerm !== "") {
      if ($("#no-results-row").length === 0) {
        $("#customersTableBody").append(
          '<tr id="no-results-row"><td colspan="7" class="text-center text-muted">No customers found matching your search.</td></tr>'
        );
      }
    } else {
      $("#no-results-row").remove();
    }
  }

  // Search and clear search event handlers
  $("#customer-search").on("keyup", function () {
    const searchTerm = $(this).val().toLowerCase();
    renderFilteredTable(searchTerm, 1);
  });

  $("#clear-search").on("click", function () {
    $("#customer-search").val("");
    renderFilteredTable("", 1);
  });

  // Initial render
  renderFilteredTable("", 1);

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
});
