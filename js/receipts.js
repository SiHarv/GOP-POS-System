$(document).ready(function () {
  const receiptModal = new bootstrap.Modal(
    document.getElementById("receiptModal")
  );

  // Toggle filter section
  $("#toggle-filters").click(function () {
    $("#filter-body").slideToggle();
  });

  // Function to perform search with AJAX
  function performSearch(page = 1) {
    const searchData = {
      action: "search_receipts",
      receipt_id: $("#receipt-id-filter").val(),
      customer_name: $("#customer-name-filter").val(),
      po_number: $("#po-number-filter").val(),
      date_from: $("#date-from-filter").val(),
      date_to: $("#date-to-filter").val(),
      page: page,
    };

    $.ajax({
      url: "../../controller/backend_receipts.php",
      method: "POST",
      data: searchData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Update table body
          $("#receipts-table tbody").html(response.tableHtml);

          // Update pagination container
          $("#pagination-container").html(response.paginationHtml);

          // Re-bind pagination click events
          bindPaginationEvents();

          // Re-bind view receipt events
          bindViewReceiptEvents();
        }
      },
      error: function () {
        alert("Error performing search");
      },
    });
  }

  // Function to bind pagination events
  function bindPaginationEvents() {
    $(document)
      .off("click", ".page-link")
      .on("click", ".page-link", function (e) {
        e.preventDefault();
        if (!$(this).parent().hasClass("disabled")) {
          const page = $(this).data("page");
          if (page && page > 0) {
            performSearch(page);
          }
        }
      });
  }

  // Function to bind view receipt events
  function bindViewReceiptEvents() {
    $(document)
      .off("click", ".view-receipt")
      .on("click", ".view-receipt", function () {
        const receiptId = $(this).data("id");

        $.ajax({
          url: "../../controller/backend_receipts.php",
          method: "POST",
          data: {
            action: "get_details",
            id: receiptId,
          },
          success: function (response) {
            // Fill modal with receipt details
            $("#receipt-id").text(response.id);
            $("#receipt-date").text(new Date(response.date).toLocaleDateString());
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
            let itemsBody = $("#receipt-items");
            itemsBody.empty();

            let total = 0;
            const ROWS_FIRST_PAGES = 35; // First and middle pages have 35 rows
            const ROWS_LAST_PAGE = 28;   // Last page has 28 rows
            const totalItems = response.items.length;
            
            // Calculate total pages based on new logic
            // We need to figure out how many pages are needed
            let tempItems = totalItems;
            let tempPages = 0;
            
            // Keep adding pages of 35 until we have less than or equal to 28 items left
            while (tempItems > ROWS_LAST_PAGE) {
              tempPages++;
              tempItems -= ROWS_FIRST_PAGES;
            }
            // Add one more page for the remaining items (last page with 28 rows)
            if (tempItems > 0 || totalItems === 0) {
              tempPages++;
            }
            
            const totalPages = Math.max(1, tempPages);
            
            // Get logo base64 from the existing header
            const logoSrc = $('.receipt-header img').attr('src') || '';

            // FIRST: Add all items to modal (without pagination) for viewing
            response.items.forEach((item, index) => {
                // Safely parse values with defaults
                const quantity = parseInt(item.quantity) || 0;
                const originalPrice = parseFloat(item.unit_price) || 0;
                const customPrice = item.custom_price !== null && item.custom_price !== undefined ? parseFloat(item.custom_price) : null;
                const discountPercentage = parseFloat(item.discount_percentage) || 0;
                const unit = item.unit || "PCS";
                const itemName = item.name || "-";

                // Use custom price if it exists, otherwise use original price
                const basePrice = customPrice !== null ? customPrice : originalPrice;

                // Calculate net price (discounted price)
                const discountAmount = basePrice * (discountPercentage / 100);
                const netPrice = basePrice - discountAmount;
                const amount = quantity * netPrice;

                total += amount;

                // Display discount percentage properly
                const discountText = discountPercentage.toFixed(1) + "%";
                let displayName = itemName;

                itemsBody.append(`
                <tr>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${quantity}</td>
                  <td style="text-align: start; font-size: 14px; padding: 3px;">${unit}</td>
                  <td style="font-size: 14px; padding: 3px;">${displayName}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${basePrice.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${discountText}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${netPrice.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${amount.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                </tr>
              `);
            });

            // Add total row to main table (always visible in modal)
            itemsBody.append(`
                <tr class="total-row">
                  <td colspan="6" class="text-end" style="font-size:12px; padding: 3px;"><strong>Total Amount ₱:</strong></td>
                  <td style="font-size:12px; padding: 3px; position: relative;"><strong><span id="receipt-total" style="display: block; text-align: right;">${total.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</span></strong></td>
                </tr>
              `);

            // SECOND: Build paginated print structure (hidden in modal, shown when printing)
            // Process items page by page for printing
            let processedItems = 0;
            for (let page = 0; page < totalPages; page++) {
              const isLastPage = page === totalPages - 1;
              const rowsForThisPage = isLastPage ? ROWS_LAST_PAGE : ROWS_FIRST_PAGES;
              
              const startIdx = processedItems;
              const endIdx = Math.min(startIdx + rowsForThisPage, totalItems);
              processedItems = endIdx;
              
              // Add page header for pages after the first (page 0 uses the original header)
              if (page > 0) {
                // Close previous table and add new header section with logo
                itemsBody.parent().after(`
                  <div class="page-header-section print-only" style="page-break-before: always;">
                    <div class="receipt-header text-center" style="margin-top: 20px; margin-bottom: 10px;">
                      <div style="position: relative;">
                        <img src="${logoSrc}" alt="gop-icon" style="
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
                        <div style="flex: 1; text-align: right;"><strong>Receipt #:</strong> <span style="color: red;">${response.id}</span></div>
                      </div>
                      <hr>
                    </div>
                    <div class="receipt-details" style="margin-bottom: 10px;">
                      <div class="d-flex">
                        <div style="font-size: 14px; display: flex; flex-direction: column; align-items: flex-start; flex: 1; gap: 2px;">
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>Customer:</strong></div>
                            <div style="text-align: left;">${response.customer_name || "-"}</div>
                          </div>
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>Address:</strong></div>
                            <div style="text-align: left;">${response.customer_address || "-"}</div>
                          </div>
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>P.O N.O:</strong></div>
                            <div style="text-align: left;">${response.po_number || "-"}</div>
                          </div>
                        </div>
                        <div style="font-size: 14px; display: flex; flex-direction: column; align-items: flex-start; margin-left: auto; gap: 2px;">
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>Date:</strong></div>
                            <div style="text-align: left;">${new Date(response.date).toLocaleDateString()}</div>
                          </div>
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>Terms:</strong></div>
                            <div style="text-align: left;">${response.customer_terms || "-"}</div>
                          </div>
                          <div style="display: flex; align-items: flex-start; gap: 5px;">
                            <div style="min-width: 80px; text-align: left;"><strong>Salesman:</strong></div>
                            <div style="text-align: left;">${response.customer_salesman || "-"}</div>
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
                      <tbody class="page-${page}-items print-only-tbody">
                      </tbody>
                    </table>
                  </div>
                `);
                // Get reference to the new page's tbody
                itemsBody = $(`.page-${page}-items`);
              } else {
                // For page 0, create a hidden print-only tbody that duplicates the items
                itemsBody.parent().append(`<tbody class="page-${page}-items print-only-tbody"></tbody>`);
                itemsBody = $(`.page-${page}-items`);
              }

              // Add items for this page (for print only)
              for (let i = startIdx; i < endIdx; i++) {
                const item = response.items[i];

                // Safely parse values with defaults
                const quantity = parseInt(item.quantity) || 0;
                const originalPrice = parseFloat(item.unit_price) || 0;
                const customPrice = item.custom_price !== null && item.custom_price !== undefined ? parseFloat(item.custom_price) : null;
                const discountPercentage = parseFloat(item.discount_percentage) || 0;
                const unit = item.unit || "PCS";
                const itemName = item.name || "-";

                // Use custom price if it exists, otherwise use original price
                const basePrice = customPrice !== null ? customPrice : originalPrice;

                // Calculate net price (discounted price)
                const discountAmount = basePrice * (discountPercentage / 100);
                const netPrice = basePrice - discountAmount;
                const amount = quantity * netPrice;

                // Display discount percentage properly
                const discountText = discountPercentage.toFixed(1) + "%";
                let displayName = itemName;

                itemsBody.append(`
                <tr>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${quantity}</td>
                  <td style="text-align: start; font-size: 14px; padding: 3px;">${unit}</td>
                  <td style="font-size: 14px; padding: 3px;">${displayName}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${basePrice.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${discountText}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${netPrice.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                  <td style="text-align: end; font-size: 14px; padding: 3px;">${amount.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</td>
                </tr>
              `);
              }

              // Calculate how many empty rows needed for this page
              const currentPageRows = endIdx - startIdx;
              let emptyRowsForPage;
              
              if (isLastPage) {
                // Last page: fill to make (items + empty + total) = 28
                emptyRowsForPage = ROWS_LAST_PAGE - currentPageRows - 1; // -1 for total row
              } else {
                // Not last page: fill to exactly 35 rows
                emptyRowsForPage = ROWS_FIRST_PAGES - currentPageRows;
              }

              // Add empty rows for this page (for print only)
              for (let i = 0; i < emptyRowsForPage; i++) {
                itemsBody.append(`
                <tr class="print-only-row">
                  <td style="text-align: end; font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="text-align: start; font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="text-align: end; font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="text-align: end; font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="text-align: end; font-size: 14px; padding: 8px;">&nbsp;</td>
                  <td style="text-align: end; font-size: 14px; padding: 8px;">&nbsp;</td>
                </tr>
              `);
              }

              // Only add total row on the last page (for print only)
              if (isLastPage) {
                itemsBody.append(`
                <tr>
                  <td colspan="6" class="text-end" style="font-size:12px; padding: 3px;"><strong>Total Amount ₱:</strong></td>
                  <td style="font-size:12px; padding: 3px; position: relative;"><strong><span style="display: block; text-align: right;">${total.toLocaleString(
                    "en-US",
                    {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    }
                  )}</span></strong></td>
                </tr>
              `);
              }
            }

            /* $("#receipt-total").text(
              total.toLocaleString("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              })
            ); */


            // Show modal
            receiptModal.show();

            // Set current receipt ID for printing functionality
            if (typeof setCurrentReceiptId === "function") {
              setCurrentReceiptId(receiptId);
            }

            // Append signature and note section to the printable area
            const bottomSection = `\n<!-- Additional receipt information container -->\n
          <div class=\"receipt-bottom-container mt-4\" style=\"border: none;\">\n    <div class=\"d-flex justify-content-between mb-4\">\n        
          <div style=\"font-size: 14px;\">\n            
          Note: Make all checks payable to <span style=\"color: red; font-weight: bold;\">GOP MARKETING</span>\n        
          </div>\n        <div style=\"font-size: 14px;\">\n            Received the above items in good order and condition.\n        </div>
          \n    </div>\n    <div class=\"d-flex justify-content-between\" style=\"margin-top: 40px;\">\n        
          <div style=\"font-size: 14px;\">\n            <!-- Left side blank for now -->\n        
          </div>\n        <div style=\"font-size: 14px;\">\n            
          By:_____________________________________<br>\n            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Printed Name & Signature/ Date\n        
          </div>\n    </div>\n</div>\n`;
            // Remove any previous bottom section to avoid duplicates
            $("#printable-area .receipt-bottom-container").remove();
            $("#printable-area").append(bottomSection);
          },
          error: function () {
            alert("Error fetching receipt details");
          },
        });
      });
  }

  // Auto-search with debounce for text inputs
  let searchTimeout;
  $("#receipt-id-filter, #customer-name-filter, #po-number-filter").on(
    "input",
    function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        performSearch(1);
      }, 300); // Wait 300ms after user stops typing
    }
  );

  // Auto-search immediately for date inputs
  $("#date-from-filter, #date-to-filter").on("change", function () {
    performSearch(1);
  });

  // Apply filter button (still available if needed)
  $("#apply-filter").click(function () {
    performSearch(1);
  });

  // Reset filter button
  $("#reset-filter").click(function () {
    $(
      "#receipt-id-filter, #customer-name-filter, #po-number-filter, #date-from-filter, #date-to-filter"
    ).val("");
    performSearch(1);
  });

  // Search on Enter key (for quick search)
  $("#receipt-id-filter, #customer-name-filter, #po-number-filter").keypress(
    function (e) {
      if (e.which == 13) {
        clearTimeout(searchTimeout); // Cancel debounced search
        performSearch(1); // Search immediately
      }
    }
  );

  // Initial binding
  bindPaginationEvents();
  bindViewReceiptEvents();

  // Clear receipt ID when modal is closed
  $("#receiptModal").on("hidden.bs.modal", function () {
    if (typeof clearCurrentReceiptId === "function") {
      clearCurrentReceiptId();
    }
  });

  // Remove the old print receipt handler - this will be handled by receipt_print.js
});
