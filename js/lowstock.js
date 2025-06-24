$(document).ready(function () {
  function matchHeights() {
    const windowHeight = $(window).height();
    const headerHeight = $("header").outerHeight() || 60;
    const itemsHeaderHeight = $(".items").outerHeight() || 60;
    const searchFilterHeight = $(".search-filter").outerHeight() || 0;
    const padding = 40;

    const availableHeight =
      windowHeight -
      headerHeight -
      itemsHeaderHeight -
      searchFilterHeight -
      padding;

    $(".scrollable-table").css("height", availableHeight + "px");
    $(".low-quantity-panel").css(
      "height",
      availableHeight + itemsHeaderHeight + searchFilterHeight + "px"
    );
  }

  $(".panel-header").on("click", function () {
    if ($(window).width() < 992) {
      $(".panel-body").slideToggle();
    }
  });

  matchHeights();
  $(window).on("resize", matchHeights);

  $("#itemSearchInput").on("keyup", function () {
    const searchValue = $(this).val().toLowerCase();
    filterTable(searchValue);
  });

  $("#clearSearchBtn").on("click", function () {
    $("#itemSearchInput").val("");
    filterTable("");
  });

  function filterTable(searchValue) {
    let visibleRows = 0;
    let totalRows = 0;

    searchValue = searchValue.trim().toLowerCase();

    $(".items-table tbody tr").each(function () {
      totalRows++;
      const row = $(this);

      const rowText = row.text().toLowerCase();

      if (rowText.includes(searchValue)) {
        row.show();
        visibleRows++;
      } else {
        const date = row.find("td:nth-child(1)").text().toLowerCase();
        const newStock = row.find("td:nth-child(2)").text().toLowerCase();
        const currentStock = row.find("td:nth-child(3)").text().toLowerCase();
        const soldBy = row.find("td:nth-child(4)").text().toLowerCase();
        const name = row.find("td:nth-child(5)").text().toLowerCase();
        const category = row.find("td:nth-child(6)").text().toLowerCase();
        const cost = row.find("td:nth-child(7)").text().toLowerCase();
        const price = row.find("td:nth-child(8)").text().toLowerCase();

        if (
          date.includes(searchValue) ||
          newStock.includes(searchValue) ||
          currentStock.includes(searchValue) ||
          soldBy.includes(searchValue) ||
          name.includes(searchValue) ||
          category.includes(searchValue) ||
          cost.includes(searchValue) ||
          price.includes(searchValue)
        ) {
          row.show();
          visibleRows++;
        } else {
          const words = searchValue.split(" ");
          let matchesAllWords = true;

          for (const word of words) {
            if (word.length > 1 && !(name.includes(word) || category.includes(word))) {
              matchesAllWords = false;
              break;
            }
          }

          if (matchesAllWords && words.length > 0 && words[0].length > 1) {
            row.show();
            visibleRows++;
          } else {
            row.hide();
          }
        }
      }
    });

    if (visibleRows === 0 && searchValue !== "") {
      if ($(".no-results-row").length === 0) {
        const colSpan = $(".items-table thead th").length;
        $(".items-table tbody").append(
          `<tr class="no-results-row">
                        <td colspan="${colSpan}" class="text-center py-4">
                            <div class="no-results-message">
                                <span class="iconify mb-2" data-icon="solar:magnifier-outline" data-width="32" style="color: #4e73df;"></span>
                                <p>No items match your search for: "${searchValue}"</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" id="clearSearchInTable">Clear Search</button>
                            </div>
                        </td>
                    </tr>`
        );
      } else {
        $(".no-results-row").show();
      }
    } else {
      $(".no-results-row").hide();
    }

    if (searchValue !== "") {
      updateSearchCount(visibleRows, totalRows);
    } else {
      $("#searchResultCount").remove();
    }
  }

  $(document).on("click", "#clearSearchInTable", function () {
    $("#itemSearchInput").val("");
    filterTable("");
  });

  function updateSearchCount(visible, total) {
    if ($("#searchResultCount").length === 0) {
      $(".search-filter").append(
        `<div id="searchResultCount" class="search-result-count mt-1">
                    Showing ${visible} of ${total} items
                </div>`
      );
    } else {
      $("#searchResultCount").text(`Showing ${visible} of ${total} items`);
    }
  }
});
