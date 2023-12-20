(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("a.read-more-link").on("click", function () {
      $(this).closest(".fb").addClass("show-full");
    });

    $("a.and-add-feedback").on("click", async function (e) {
      e.preventDefault();
      await and_add_submission_feedback($(this));
    });

    $("body").on("click", ".ic-delete-feedback", function (e) {
      e.preventDefault();
      and_remove_submission_feedback($(this));
    });

    /**
     * Create a submission feedback
     */
    async function and_add_submission_feedback(instance) {
      let quizId = instance.data("id");
      let parent_quiz_id = instance.data("group-id");
      let parent = $(`#main-container-${parent_quiz_id}_${quizId}`);
      let feedback_el = parent.find(".and-feedback-input");
      let feedback_vl = feedback_el.val();
      let assessmentId = $(`[name="assessment_id"]`).val();
      let submissionId = $("#submission_id").val();
      let organisationId = $("#organisation_id").val();

      let card_feedback = instance.closest(".feedback");
      let feedback_lst = card_feedback.find(".feedback-lst");

      feedback_el.next(".fb-error-msg").text("");
      feedback_el.removeClass("error");
      if (!feedback_vl) {
        if (instance.hasClass("private-note")) {
          feedback_el.next(".fb-error-msg").text("Must add private note above!!");
        } else {
          feedback_el.next(".fb-error-msg").text("Must add feedback above!!");
        }
        feedback_el.addClass("error");
        return false;
      }

      let response = await $.ajax({
        type: "POST",
        url: fb_object.ajax_url,
        data: {
          action: "and_add_a_submission_feedback",
          feedback: feedback_vl,
          assessment_id: assessmentId,
          submission_id: submissionId,
          organisation_id: organisationId,
          quiz_id: quizId,
          parent_quiz_id: parent_quiz_id,
        },
        beforeSend: function (xhr) {
          instance.addClass("disabled").html('<div class="and-spinner-loading"></div>');
        },
        success: function (response) {
          if (instance.hasClass("private-note")) {
            instance.removeClass("disabled").html("Add private note");
          } else {
            instance.removeClass("disabled").html("Add feedback");
          }
        },
      });
      const { time, feedback_id, user_name, status, message } = response;
      // console.log(status, feedback_id, message);

      if (status) {
        feedback_el.val("");

        let added_fb_html = '<div class="fd-row">';
        added_fb_html += ' <div class="fb-content">';
        added_fb_html +=
          '  <span class="ic-delete-feedback" data-fb-id="' + feedback_id + '" title="Remove this feedback">';
        added_fb_html += '    <i class="fa fa-trash-o"></i>';
        added_fb_html += "  </span>";
        added_fb_html += '  <div class="author"><strong>' + user_name + "</strong> - " + time + "</div>";
        added_fb_html += '  <div class="fb">' + feedback_vl + "</div>";
        added_fb_html += " </div>";
        added_fb_html += "</div>";
        feedback_lst.prepend(added_fb_html);
      } else {
        feedback_el.next(".fb-error-msg").text(message);
      }
      return status;
    }

    /**
     * Remove a submission feedback
     */
    function and_remove_submission_feedback(instance) {
      let fb_id = instance.data("fb-id");
      let parent_el = instance.closest(".fd-row");

      if (!fb_id) {
        return false;
      }

      $.ajax({
        type: "POST",
        url: fb_object.ajax_url,
        data: {
          action: "and_remove_a_submission_feedback",
          feedback_id: fb_id,
        },
        beforeSend: function (xhr) {
          parent_el.addClass("loading").append('<div class="and-spinner-loading"></div>');
        },
        success: function (response) {
          if (response.status) {
            parent_el.remove();
          } else {
          }
        },
      });
    }
  });
})(window, jQuery);
