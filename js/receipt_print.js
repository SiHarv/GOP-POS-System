$(document).ready(function () {
  // Global variable to store current receipt ID and data
  let currentReceiptId = null;
  let currentReceiptData = null;

  // Handle print receipt button click
  $(document).on("click", "#print-receipt", function () {
    if (!currentReceiptId) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No receipt selected for printing",
        confirmButtonColor: "#d33",
      });
      return;
    }

    // Check if receipt is already finalized
    if (currentReceiptData && currentReceiptData.finalized == 1) {
      // Receipt is already finalized, print directly without confirmation
      printReceipt();
    } else {
      // Receipt not finalized, show confirmation dialog
      Swal.fire({
        title: "Confirm Print Receipt",
        text: "This will finalize the receipt and subtract items from stock. This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Print Receipt",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          finalizeAndPrintReceipt(currentReceiptId);
        }
      });
    }
  });

  // Function to finalize receipt and print
  function finalizeAndPrintReceipt(receiptId) {
    // Show loading
    Swal.fire({
      title: "Processing...",
      text: "Finalizing receipt and updating stock...",
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Send AJAX request to finalize receipt
    $.ajax({
      url: "../../controller/backend_receipt_print.php",
      method: "POST",
      data: {
        action: "finalize_receipt",
        id: receiptId,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          // Close loading dialog
          Swal.close();

          // Update current receipt data to reflect finalized status
          if (currentReceiptData) {
            currentReceiptData.finalized = 1;
            currentReceiptData.finalized_date = new Date().toISOString();
          }

          // Directly trigger the print without showing success message
          printReceipt();

          // Optionally refresh the receipts table if it exists
          if (typeof performSearch === "function") {
            performSearch();
          }
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message || "Failed to finalize receipt",
            confirmButtonColor: "#d33",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Failed to communicate with server. Please try again.",
          confirmButtonColor: "#d33",
        });
      },
    });
  }

  // Function to actually print the receipt
  function printReceipt() {
    // Inject print-specific CSS styles
    const printStyles = `
        <style>
            @media print {
                @page {
                  margin: 0.75in !important;
                  size: A4 !important;
                }
                
                /* Hide browser default headers and footers */
                @page {
                  margin: 0.5in !important;
                  size: A4 !important;
                  @top-left { content: ""; }
                  @top-center { content: ""; }
                  @top-right { content: ""; }
                  @bottom-left { content: ""; }
                  @bottom-center { content: ""; }
                  @bottom-right { content: ""; }
                }
                
                /* Hide default browser print elements */
                body {
                  -webkit-print-color-adjust: exact !important;
                  color-adjust: exact !important;
                }
                
                /* Remove default print styles */
                * {
                  -webkit-print-color-adjust: exact !important;
                  print-color-adjust: exact !important;
                }
                
                body, html {
                  margin: 0 !important;
                  padding: 0 !important;
                  width: 100% !important;
                  height: 100% !important;
                  font-family: Arial, sans-serif !important;
                }
                
                body {
                  box-sizing: border-box !important;
                  border: none !important;
                  min-height: calc(100vh - 1.5in) !important;
                  display: flex !important;
                  flex-direction: column !important;
                }
                
                .receipt-header {
                  text-align: center !important;
                  margin-bottom: 5px !important;
                  position: relative !important;
                  padding-top: 0px !important;
                  flex-shrink: 0 !important;
                }
                
                .receipt-header .text-start {
                  position: absolute !important;
                  left: 0 !important;
                  top: 0 !important;
                  margin: 0 !important;
                }
                
                .receipt-header .text-start img {
                  height: 90px !important;
                  width: 82px !important;
                  object-fit: contain !important;
                  padding-right: 24px;
                }
                !
                .receipt-header .d-flex {
                  display: flex !important;
                  justify-content: space-between !important;
                  align-items: center !important;
                  font-size: 14px !important;
                  margin-top: 0px !important;
                }
                
                .receipt-header .d-flex > div:first-child {
                    flex: 1 !important;
                }
                
                .receipt-header .d-flex > div:nth-child(2) {
                  flex: 1 !important;
                  text-align: center !important;
                }
                
                .receipt-header .d-flex > div:last-child {
                  flex: 1 !important;
                  text-align: right !important;
                }
                
                .receipt-details {
                  margin-bottom: 32px !important; /* Much more visible space below upper details */
                  font-size: 14px !important;
                  flex-shrink: 0 !important;
                }
                
                .receipt-details .d-flex {
                  display: flex !important;
                  justify-content: space-between !important;
                  margin-bottom: 3px !important;
                }
                
                .table {
                  width: 100% !important;
                  border-collapse: collapse !important;
                  border: 1px solid #000000 !important;
                  table-layout: fixed !important;
                  /* Remove flex-grow for print consistency */
                  margin-top: 40px !important; /* Force spacing from upper details */
                  margin-bottom: 20px !important; /* Force spacing below table */
                }
                
                /* Additional spacing selectors to ensure separation */
                .table-container {
                  margin-top: 20px !important;
                }
                
                .receipt-details + * {
                  margin-top: 20px !important;
                }
                
                .table th, .table td {
                  border: 1px solid #000000 !important;
                  font-size: 12px !important;
                  vertical-align: middle !important;
                  padding: 2px 2px !important;
                  overflow: hidden !important;
                  word-break: break-word !important;
                }
                
                .table th {
                  background-color: #f8f9fa !important;
                  text-align: center !important;
                  font-weight: bold !important;
                  padding: 2px 8px !important;
                }
                
                .table tfoot td {
                  border-top: 1px solid #000000 !important;
                  font-weight: bold !important;
                  padding: 2px 2px !important;
                }
               
                .footer {
                  margin-top: 20px !important; /* Increased spacing above footer */
                  text-align: center !important;
                  font-size: 11px !important;
                  flex-shrink: 0 !important;
                }
                
                .receipt-bottom-container {
                  margin-top: 20px !important; /* Increased spacing above bottom container */
                  font-size: 11px !important;
                  flex-shrink: 0 !important;
                }
              
                .receipt-bottom-container .d-flex {
                  display: flex !important;
                  justify-content: space-between !important;
                  margin-bottom: 0px !important;
                }      

                .receipt-bottom-container span {
                  color: red !important;
                  font-weight: bold !important;
                }
                
                
                .text-center { text-align: center !important; }
                .text-start { text-align: left !important; }
                .text-end { text-align: right !important; }
                .mt-3 { margin-top: 0px !important; }
                .mt-4 { margin-top: 10px !important; }
                .mt-5 { margin-top: 10px !important; }
                .mb-4 { margin-bottom: 0px !important; }
                
                /* Ensure table fills available space */
                .table-container {
                  flex-grow: 1 !important;
                  display: flex !important;
                  flex-direction: column !important;
                }
                
                /* Column width adjustments for better space utilization (fixed px for print consistency) */
                .table th:nth-child(1), .table td:nth-child(1) { width: 35px !important; }
                .table th:nth-child(2), .table td:nth-child(2) { width: 30px !important; }
                .table th:nth-child(3), .table td:nth-child(3) { width: 260px !important; }
                .table th:nth-child(4), .table td:nth-child(4) { width: 50px !important; }
                .table th:nth-child(5), .table td:nth-child(5) { width: 35px !important; }
                .table th:nth-child(6), .table td:nth-child(6) { width: 50px !important; }
                .table th:nth-child(7), .table td:nth-child(7) { width: 70px !important; }
            }
        </style>
        `;

    // Get the printable content from the modal
    const printContents = document.getElementById("printable-area").innerHTML;
    const originalContents = document.body.innerHTML;

    // Create a complete HTML document with styles for printing
    const printDocument = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt Print</title>
            ${printStyles}
        </head>
        <body>
            ${printContents}
        </body>
        </html>
        `;

    // Replace the entire document content
    document.open();
    document.write(printDocument);
    document.close();

    // Trigger the browser's print dialog
    window.print();

    // Restore the original page content
    document.body.innerHTML = originalContents;

    // Reload the page to restore all JavaScript functionality
    location.reload();
  }

  // Function to set current receipt ID and data (called from receipts.js when viewing a receipt)
  window.setCurrentReceiptId = function (receiptId) {
    currentReceiptId = receiptId;
    // Fetch receipt data to check finalization status
    fetchReceiptData(receiptId);
  };

  // Function to clear current receipt ID and data
  window.clearCurrentReceiptId = function () {
    currentReceiptId = null;
    currentReceiptData = null;
  };

  // Function to fetch receipt data including finalization status
  function fetchReceiptData(receiptId) {
    $.ajax({
      url: "../../controller/backend_receipt_print.php",
      method: "POST",
      data: {
        action: "get_receipt_details",
        id: receiptId,
      },
      dataType: "json",
      success: function (response) {
        currentReceiptData = response;
      },
      error: function (xhr, status, error) {
        console.error("Error fetching receipt data:", error);
        currentReceiptData = null;
      },
    });
  }
});
