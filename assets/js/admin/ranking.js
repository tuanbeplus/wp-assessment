(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("#and-ranking-assessment").select2({
      placeholder: "Select assessment",
      minimumResultsForSearch: 1,
    });

    $(".pr-info").on("click", function () {
      $(this).find(".btn-expland-fr").toggleClass("active");
      $(this).next(".cr-info").toggleClass("hide");

      let expand_text = $(this).find(".text");
      if (expand_text.text() == "Collapse") {
        expand_text.text("Expand");
      } else {
        expand_text.text("Collapse");
      }
    });
  });
})(window, jQuery);
