(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("#and-ranking-assessment").select2({
      placeholder: "Select assessment",
      minimumResultsForSearch: 1,
    });
  });
})(window, jQuery);
