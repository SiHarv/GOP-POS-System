$(document).ready(function () {
  preventDefault();
  initFilterPanel();

  $("#apply-filter").click(function () {
    filterTable();
  });

  $("#reset-filter").click(function () {
    $("#receipt-id-filter").val("");
    $("#customer-name-filter").val("");
    $("#po-number-filter").val("");
    $("#date-from-filter").val("");
    $("#date-to-filter").val("");
    filterTable();
  });

  $("#receipt-id-filter, #customer-name-filter, #po-number-filter").on(
    "input",
    function () {
      filterTable();
    }
  );

  function initFilterPanel() {
    const filterPanelVisible = localStorage.getItem("filterPanelVisible");

    if (filterPanelVisible === "true") {
      $("#filter-body").show();
    } else {
      $("#filter-body").hide();
    }

    $("#toggle-filters").click(function () {
      $("#filter-body").slideToggle(300, function () {
        const isVisible = $("#filter-body").is(":visible");
        localStorage.setItem("filterPanelVisible", isVisible);
      });
    });
  }

  function filterTable() {
    const receiptIdFilter = $("#receipt-id-filter").val().toLowerCase();
    const customerNameFilter = $("#customer-name-filter").val().toLowerCase();
    const poNumberFilter = $("#po-number-filter").val().toLowerCase();
    const dateFromFilter = $("#date-from-filter").val();
    const dateToFilter = $("#date-to-filter").val();

    $("#receipts-table tbody tr").show();

    const $emptyRows = $("#receipts-table tbody tr.empty-row");

    if (
      !receiptIdFilter &&
      !customerNameFilter &&
      !poNumberFilter &&
      !dateFromFilter &&
      !dateToFilter
    ) {
      updateFilterBadge(0);
      return;
    }

    let hiddenRowCount = 0;

    $("#receipts-table tbody tr").each(function () {
      const $row = $(this);

      if ($row.hasClass("empty-row")) {
        return;
      }

      const receiptId = $row.find("td:nth-child(1)").text().toLowerCase();
      const customerName = $row.find("td:nth-child(2)").text().toLowerCase();
      const poNumber = $row.find("td:nth-child(3)").text().toLowerCase();
      const dateText = $row.find("td:nth-child(5)").text();

      const dateParts = dateText.match(/(\w+) (\d+), (\d+) (\d+):(\d+) (\w+)/);
      let shouldShow = true;

      if (dateParts) {
        const month = {
          Jan: 0,
          Feb: 1,
          Mar: 2,
          Apr: 3,
          May: 4,
          Jun: 5,
          Jul: 6,
          Aug: 7,
          Sep: 8,
          Oct: 9,
          Nov: 10,
          Dec: 11,
        };

        const rowDate = new Date(
          parseInt(dateParts[3]),
          month[dateParts[1]],
          parseInt(dateParts[2]),
          dateParts[6] === "PM" && parseInt(dateParts[4]) < 12
            ? parseInt(dateParts[4]) + 12
            : parseInt(dateParts[4]),
          parseInt(dateParts[5])
        );

        if (receiptIdFilter && !receiptId.includes(receiptIdFilter)) {
          shouldShow = false;
        }

        if (
          shouldShow &&
          customerNameFilter &&
          !customerName.includes(customerNameFilter)
        ) {
          shouldShow = false;
        }

        if (
          shouldShow &&
          poNumberFilter &&
          !poNumber.includes(poNumberFilter)
        ) {
          shouldShow = false;
        }

        if (shouldShow && dateFromFilter) {
          const dateFrom = new Date(dateFromFilter);
          dateFrom.setHours(0, 0, 0, 0);
          if (rowDate < dateFrom) {
            shouldShow = false;
          }
        }

        if (shouldShow && dateToFilter) {
          const dateTo = new Date(dateToFilter);
          dateTo.setHours(23, 59, 59, 999);
          if (rowDate > dateTo) {
            shouldShow = false;
          }
        }

        if (shouldShow) {
          $row.show();
        } else {
          $row.hide();
          hiddenRowCount++;
        }
      }
    });

    $emptyRows.show();

    updateFilterBadge(hiddenRowCount);
  }

  function updateFilterBadge(hiddenRowCount) {
    const totalRows = $("#receipts-table tbody tr").length;
    const visibleRows = totalRows - hiddenRowCount;

    if (hiddenRowCount > 0) {
      const filterBadge = `<span class="badge bg-primary ms-1">${visibleRows}/${totalRows}</span>`;

      if ($("#toggle-filters .badge").length) {
        $("#toggle-filters .badge").remove();
      }

      $("#toggle-filters").append(filterBadge);
    } else {
      $("#toggle-filters .badge").remove();
    }
  }
});
