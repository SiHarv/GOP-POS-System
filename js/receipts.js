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
        $("#receipt-customer").text(response.customer_name);
        $("#receipt-address").text(response.customer_address);

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
          total += parseFloat(item.subtotal);

          // Display discount percentage properly
          const discountText =
            parseFloat(item.discount_percentage) > 0
              ? item.discount_percentage + "%"
              : "-";

          itemsBody.append(`
                        <tr>
                            <td>${item.quantity}</td>
                            <td>${item.name}</td>
                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${discountText}</td>
                            <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `);
        });

        $("#receipt-total").text(total.toFixed(2));

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
