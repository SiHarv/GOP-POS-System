$(document).ready(function () {
  const receiptModal = new bootstrap.Modal(
    document.getElementById("receiptModal")
  );

  $(".view-receipt").on("click", function () {
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
                            <td style="text-align: end; font-size: 12px; padding: 3px;">${unitPrice.toLocaleString(
                              "en-US",
                              {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                              }
                            )}</td>
                            <td style="text-align: end; font-size: 12px; padding: 3px;">${discountText}</td>
                            <td style="text-align: end; font-size: 12px; padding: 3px;">${netPrice.toLocaleString(
                              "en-US",
                              {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                              }
                            )}</td>
                            <td style="text-align: end; font-size: 12px; padding: 3px;">${amount.toLocaleString(
                              "en-US",
                              {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                              }
                            )}</td>
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

  $("#print-receipt").on("click", function () {
    const printContents = document.getElementById("printable-area").innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  });
});
const printContents = document.getElementById("printable-area").innerHTML;
const originalContents = document.body.innerHTML;

document.body.innerHTML = printContents;
window.print();
document.body.innerHTML = originalContents;
location.reload();
