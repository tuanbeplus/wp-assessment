(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("a.and-add-feedback").on("click", async function (e) {
      e.preventDefault();
      await and_add_submission_feedback($(this));
    });

    async function and_add_submission_feedback(btn) {
      let assessment_id = $("#assessment_id").val();
      let user_id = $("#user_id").val();
      let submission_id = $("#submission_id").val();
      let organisation_id = $("#organisation_id").val();

      console.log(assessment_id, user_id, submission_id, organisation_id);

      // const payload = {
      //   action: "final_accept_reject_assessment",
      //   assessment_id: assessment_id,
      //   // user_id: user_id,
      //   submission_id: submission_id,
      //   organisation_id: organisation_id,
      //   type: feedbackType,
      // };

      // let response = await $.ajax({
      //   type: "POST",
      //   url: fb_object.ajax_url,
      //   data: payload,
      //   beforeSend: function (xhr) {},
      //   success: function (response) {},
      // });
      // const { quiz_point, status, message } = response;
      // //console.log(response);
      // //alert(message);

      // if (status) {
      //   // location.reload()

      //   return true;
      // }
    }
  });
})(window, jQuery);
