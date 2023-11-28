(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("#and-ranking-assessment").select2({
      placeholder: "Select assessment",
      minimumResultsForSearch: 1,
    });

    $(".pr-info").on("click", function () {
      $(this).find(".btn-expland-fr").toggleClass("active");
      $(this).next(".pr-ranking-lst").toggleClass("hide");

      let expand_text = $(this).find(".text");
      if (expand_text.text() == "Collapse") {
        expand_text.text("Expand");
      } else {
        expand_text.text("Collapse");
      }
    });

    $(".btn-expand-wrapper").on("click", function () {
      $(this).find(".icon-chevron-down").toggleClass("up");
      $(this).next(".cr-info").toggleClass("hide");

      let expand_text = $(this).find(".text");
      if (expand_text.text() == "Collapse Group") {
        expand_text.text("Expand Group");
      } else {
        expand_text.text("Collapse Group");
      }
    });
  });
})(window, jQuery);
