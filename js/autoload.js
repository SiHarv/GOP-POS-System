$(document).ready(function () {
  // Load categories via AJAX on modal open
  $("#printItemsModal").on("shown.bs.modal", function () {
    $.ajax({
      url: "../../controller/backend_items.php",
      method: "GET",
      data: {
        action: "getCategories",
      },
      dataType: "json",
      success: function (data) {
        var options = '<option value="">Select a category</option>';
        // Use category name as value, not id
        data.forEach(function (category) {
          options +=
            '<option value="' +
            category.name +
            '">' +
            category.name +
            "</option>";
        });
        $("#categorySelect").html(options);
      },
      error: function () {
        $("#categorySelect").html(
          '<option value="">Error loading categories</option>'
        );
      },
    });
  });

  // Auto-load items when category changes
  $("#categorySelect").on("change", function () {
    loadItemsForPrint();
  });

  // Optionally, auto-load items for the first category when modal opens
  $("#printItemsModal").on("shown.bs.modal", function () {
    if ($("#categorySelect").val()) {
      loadItemsForPrint();
    }
  });

  // Hide the Load Items button if still present
  $("#loadItemsBtn").hide();

  function loadItemsForPrint() {
    var categoryName = $("#categorySelect").val();
    if (!categoryName) {
      $("#itemsTableContainer").hide();
      $("#noItemsMessage").show();
      return;
    }

    $("#loadingSpinner").show();
    $("#itemsTableContainer").hide();
    $("#noItemsMessage").hide();

    $.ajax({
      url: "../../controller/backend_items.php",
      method: "GET",
      data: {
        action: "getItemsByCategory",
        categoryId: categoryName, // send category name, not id
      },
      dataType: "json",
      success: function (items) {
        var rows = "";
        items.forEach(function (item) {
          rows +=
            "<tr>" +
            '<td class="text-center">' +
            item.id +
            "</td>" +
            "<td>" +
            item.name +
            "</td>" +
            '<td class="text-center">' +
            item.category +
            "</td>" +
            '<td class="text-center">' +
            item.sold_by +
            "</td>" +
            '<td class="text-end">' +
            parseFloat(item.cost).toFixed(2) +
            "</td>" +
            '<td class="text-end">' +
            parseFloat(item.price).toFixed(2) +
            "</td>" +
            '<td class="text-end">' +
            item.stock +
            "</td>" +
            "</tr>";
        });
        $("#printItemsTableBody").html(rows);
        $("#totalItemsCount").text(items.length);
        $("#itemsTableContainer").show();
        $("#noItemsMessage").toggle(items.length === 0);
      },
      error: function () {
        $("#itemsTableContainer").hide();
        $("#noItemsMessage").show().find("p").text("Error loading items.");
      },
      complete: function () {
        $("#loadingSpinner").hide();
      },
    });
  }
});
