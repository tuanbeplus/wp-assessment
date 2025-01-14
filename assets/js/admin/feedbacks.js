(function (w, $) {
  "use strict";

  $(document).ready(function () {
    $("a.and-add-feedback").on("click", async function (e) {
      e.preventDefault();
      await and_add_submission_feedback($(this));
    });

    $("body").on("click", ".ic-delete-feedback", function (e) {
      e.preventDefault();
      if (!confirm('Do you want to remove?')) {
        return;
      }
      and_remove_submission_feedback($(this));
    });

    $(".btn-showmore").on("click", function () {
      const fbRow = $(this).closest(".fd-row");
      const feedback = fbRow.find('.fb');
      if (feedback.hasClass('show_less')) {
        feedback.removeClass('show_less');
        $(this).text('Show less');
      }
      else {
        feedback.addClass('show_less');
        $(this).text('Show more');
      }
    });

  /**
   * Add a submission feedback
   */
  async function and_add_submission_feedback(instance) {
    try {
      // Extract data from the instance
      const quizId = instance.data("id");
      const parentQuizId = instance.data("group-id");
      const parent = $(`#main-container-${parentQuizId}_${quizId}`);
      const feedbackElement = parent.find("textarea.and-feedback-input");
      const editorId = feedbackElement.attr("id");
      const assessmentId = $("input#assessment_id").val();
      const submissionId = $("input#submission_id").val();
      const organisationId = $("input#organisation_id").val();
      const cardFeedback = instance.closest(".card.feedback");
      const feedbackMessage = cardFeedback.find(".fb-error-msg");
      const feedbackList = cardFeedback.find(".feedback-lst");
      const editorWrapper = parent.find(".wp-editor-wrap");

      // Get feedback value
      let feedbackValue = feedbackElement.val();
      if (editorWrapper.length > 0 && feedbackElement.hasClass("wp-editor-area")) {
        feedbackValue = tinymce.get(editorId) ? tinymce.get(editorId).getContent() : $(`#${editorId}`).val();
      }

      // Reset error state
      feedbackMessage.text("");
      cardFeedback.removeClass("error");
      feedbackElement.removeClass("error");

      // Validate feedback
      if (!feedbackValue) {
        const errorText = instance.hasClass("private-note") ? "Please enter your note in the textarea." : "Please enter your feedback in the editor.";
        feedbackMessage.text(errorText);
        feedbackElement.addClass("error");
        setTimeout(() => {
          feedbackElement.removeClass("error");
          feedbackMessage.text('');
        }, 10000);
        return false;
      }
      
      // Clean feedback content
      const notAllowTags = [
        "img", "iframe", "script", "style", "embed", "object", "video", "audio",
        "applet", "base", "link", "meta", "noscript", "svg", "canvas",
      ];
      let tempDiv = $('<div>').html(feedbackValue);
      tempDiv.find(notAllowTags.join(",")).remove();
      tempDiv.find('*').each(function () {
        $(this)
          .removeAttr('class')
          .removeAttr('id')
          .removeAttr('data-*');
      });
      feedbackValue = tempDiv.html();

      // Prepare AJAX request
      let response = await $.ajax({
        type: "POST",
        url: fb_object.ajax_url,
        data: {
          action: "and_add_a_submission_feedback",
          feedback: feedbackValue,
          assessment_id: assessmentId,
          submission_id: submissionId,
          organisation_id: organisationId,
          quiz_id: quizId,
          parent_quiz_id: parentQuizId,
        },
        beforeSend: function (xhr) {
          instance.addClass("loading");
          editorWrapper.addClass("loading");
          feedbackElement.addClass("loading");
        },
        success: function (response) {
          instance.removeClass("loading");
          editorWrapper.removeClass("loading");
          feedbackElement.removeClass("loading");
        },
      });
      // Handle response
      const { time, feedback_id, user_name, status, message } = response;

      if (status == true) {        
        feedbackElement.val(""); // Clear input
        // Generate feedback HTML
        const feedbackHtml = (`
          <div id="fb-${feedback_id}" class="fd-row">
            <div class="fb-content">
              <div class="fb-top">
                <div class="author">
                  <strong class="name">${user_name}</strong>
                  <span> - </span>
                  <span class="datetime">${time}</span>
                </div>
                <span class="ic-delete-feedback" data-fb-id="${feedback_id}">Remove</span>
              </div>
              <div class="fb">${feedbackValue}</div>
            </div>
          </div>
        `);
        // Append the feedback
        feedbackList.prepend(feedbackHtml);
        // Clear the TinyMCE editor content
        if (tinymce.get(editorId)) {
          tinymce.get(editorId).setContent('');
        } else {
          $('#' + editorId).val('');
        }
      } else {
        feedbackMessage.text(message);
      }
      return status;
    } 
    catch (error) {
      console.error("An error occurred:", error);
      alert("An error occurred while saving feedback.");
      return false;
    }
  }

    /**
     * Remove a submission feedback
     */
    function and_remove_submission_feedback(instance) {
      const feedbackId = instance.data("fb-id");
      const feedbackRow = instance.closest(".fd-row");
      const cardFeedback = instance.closest(".card.feedback");
      const addNoteBtn = cardFeedback.find('.button.private-note');
      const feedbackMessage = cardFeedback.find(".fb-error-msg");
      const msgText = (addNoteBtn.length > 0) ? "Note has been removed." : "Feedback has been removed.";
      if (!feedbackId) {
        return false;
      }
      $.ajax({
        type: "POST",
        url: fb_object.ajax_url,
        data: {
          action: "and_remove_a_submission_feedback",
          feedback_id: feedbackId,
        },
        beforeSend: function (xhr) {
          feedbackRow.addClass("loading");
        },
        success: function (response) {
          if (response.status) {
            feedbackRow.remove();
            feedbackMessage.addClass('success').text(msgText);
          } 
          else {
            alert(response.message);
          }
        },
      });
      setTimeout(() => feedbackMessage.removeClass('success').text(''), 10000);
    }
  });
})(window, jQuery);
