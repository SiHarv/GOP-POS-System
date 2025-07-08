$(document).ready(function () {
  const receiptModal = new bootstrap.Modal(document.getElementById("receiptModal"));

  // Toggle filter section
  $("#toggle-filters").click(function() {
    $("#filter-body").slideToggle();
  });

  // Function to perform search with AJAX
  function performSearch(page = 1) {
    const searchData = {
      action: 'search_receipts',
      receipt_id: $('#receipt-id-filter').val(),
      customer_name: $('#customer-name-filter').val(),
      po_number: $('#po-number-filter').val(),
      date_from: $('#date-from-filter').val(),
      date_to: $('#date-to-filter').val(),
      page: page
    };

    $.ajax({
      url: '../../controller/backend_receipts.php',
      method: 'POST',
      data: searchData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          // Update table body
          $('#receipts-table tbody').html(response.tableHtml);
          
          // Update pagination container
          $('#pagination-container').html(response.paginationHtml);
          
          // Re-bind pagination click events
          bindPaginationEvents();
          
          // Re-bind view receipt events
          bindViewReceiptEvents();
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

  // Function to bind view receipt events
  function bindViewReceiptEvents() {
    $(document).off('click', '.view-receipt').on('click', '.view-receipt', function() {
      const receiptId = $(this).data('id');
      
      $.ajax({
        url: '../../controller/backend_receipts.php',
        method: 'POST',
        data: {
          action: 'get_details',
          id: receiptId,
        },
        success: function (response) {
          // Fill modal with receipt details
          $("#receipt-id").text(response.id);
          $("#receipt-date").text(new Date(response.date).toLocaleString());
          $("#receipt-customer").text(response.customer_name || "-");
          $("#receipt-address").text(response.customer_address || "-");
          $("#receipt-terms").text(response.customer_terms || "-");
          $("#receipt-salesman").text(response.customer_salesman || "-");

          // Display PO number if available
          if (response.po_number) {
            $("#receipt-po-number").text(response.po_number);
          } else {
            $("#receipt-po-number").text("-");
          }

          // Clear and populate items table
          const itemsBody = $("#receipt-items");
          itemsBody.empty();

          let total = 0;
          response.items.forEach((item) => {
            // Safely parse values with defaults
            const quantity = parseInt(item.quantity) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;
            const discountPercentage = parseFloat(item.discount_percentage) || 0;
            const unit = item.unit || "PCS";
            const itemName = item.name || "-";

            // Calculate net price (discounted price)
            const discountAmount = unitPrice * (discountPercentage / 100);
            const netPrice = unitPrice - discountAmount;
            const amount = quantity * netPrice;

            total += amount;

            // Display discount percentage properly
            const discountText = discountPercentage.toFixed(1) + "%";

            itemsBody.append(`
              <tr>
                <td style="text-align: end; font-size: 12px; padding: 3px;">${quantity}</td>
                <td style="text-align: start; font-size: 12px; padding: 3px;">${unit}</td>
                <td style="font-size: 12px; padding: 3px;">${itemName}</td>
                <td style="text-align: end; font-size: 12px; padding: 3px;">${unitPrice.toLocaleString("en-US", {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2,
                })}</td>
                <td style="text-align: end; font-size: 12px; padding: 3px;">${discountText}</td>
                <td style="text-align: end; font-size: 12px; padding: 3px;">${netPrice.toLocaleString("en-US", {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2,
                })}</td>
                <td style="text-align: end; font-size: 12px; padding: 3px;">${amount.toLocaleString("en-US", {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2,
                })}</td>
              </tr>
            `);
          });

          $("#receipt-total").text(
            total.toLocaleString("en-US", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            })
          );

          // Show modal
          receiptModal.show();
        },
        error: function () {
          alert("Error fetching receipt details");
        },
      });
    });
  }

  // Auto-search with debounce for text inputs
  let searchTimeout;
  $('#receipt-id-filter, #customer-name-filter, #po-number-filter').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
      performSearch(1);
    }, 300); // Wait 300ms after user stops typing
  });

  // Auto-search immediately for date inputs
  $('#date-from-filter, #date-to-filter').on('change', function() {
    performSearch(1);
  });

  // Apply filter button (still available if needed)
  $('#apply-filter').click(function() {
    performSearch(1);
  });

  // Reset filter button
  $('#reset-filter').click(function() {
    $('#receipt-id-filter, #customer-name-filter, #po-number-filter, #date-from-filter, #date-to-filter').val('');
    performSearch(1);
  });

  // Search on Enter key (for quick search)
  $('#receipt-id-filter, #customer-name-filter, #po-number-filter').keypress(function(e) {
    if (e.which == 13) {
      clearTimeout(searchTimeout); // Cancel debounced search
      performSearch(1); // Search immediately
    }
  });

  // Initial binding
  bindPaginationEvents();
  bindViewReceiptEvents();

  $("#print-receipt").on("click", function () {
    const printContents = document.getElementById("printable-area").innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  });
});
