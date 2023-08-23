jQuery(document).ready(function ($) {
    const ajaxUrl = ajax_object.ajax_url;

    $(document).on("click", "#add-group-row", function () {

      let group_parent_repeater = $("#question-group-repeater")
      let groupCount = ($(".group-question-wrapper").length) + 1;

        let group_question_html = '    <div class="group-question-wrapper" id="question-group-row-'+groupCount+'" data-id="'+groupCount+'">';
            group_question_html += '        <p style="text-align: right;" ><span class="button btn-remove-group" data-id="'+groupCount+'">Remove Group</span></p>';
            group_question_html += '        <div class="row">';
            group_question_html += '            <div class="col-10">';
            group_question_html += '                <h5 class="admin-question-group-label">Question #'+groupCount+'</h5>';
            group_question_html += '                <input class="form-field group-question-admin-title form-control" name="group_question_title['+groupCount+']" placeholder="Group Question Title"/>';
            group_question_html += '            </div>';
            group_question_html += '            <div class="col-2 question-row-points-container">';
            group_question_html += '                <label for="group_question_point[]"><strong>Question Point</strong></label>';
            group_question_html += '                <input type="number" class="question-point-input" name="group_question_point['+groupCount+']"/>';
            group_question_html += '                <div class="question-points-actions-container">';
            group_question_html += '                    <div class="increment-question-point" aria-hidden="true">';
            group_question_html += '                        <i class="fa fa-plus"></i>';
            group_question_html += '                    </div>';
            group_question_html += '                    <div class="decrement-question-point" aria-hidden="true">';
            group_question_html += '                        <i class="fa fa-minus"></i>';
            group_question_html += '                    </div>';
            group_question_html += '                </div>';
            group_question_html += '            </div>';
            group_question_html += '        </div>';
            group_question_html += '        <div class="question-field-container"></div>';
            group_question_html += '        <!-- Button add more sub questions -->';
            group_question_html += '        <p><span class="add-row button button-primary">Add Sub Questions</span></p>';
            group_question_html += '    </div>';

      group_parent_repeater.append(group_question_html)

      // const group_row = group_parent_repeater.children().last()
      // group_row.attr("id", `question-group-row-${groupCount}`);
      // group_row.attr("data-id", groupCount);
      // group_row.find(".admin-question-group-label").html(`Question #${groupCount}`);
      // group_row.find(".group-question-admin-title").attr('name', `group_question_title[${groupCount}]`);
      // group_row.find(".question-point-input").attr('name', `group_question_point[${groupCount}]`);

    });

    $(document).on("click", ".add-row", function(){

      let group_question_wrapper = $(this).closest(".group-question-wrapper")
      let group_id = group_question_wrapper.attr("data-id")
      let rowCount = (group_question_wrapper.find(".question-row-container").length) + 1;

      let question_row_html = '<div class="question-row-container" id="question-main-row-'+rowCount+'">';
          question_row_html += '    <input type="hidden" name="question_repeater[]"/>';
          question_row_html += '    <div class="row question-input-area-container">';
          question_row_html += '        <div class="col-10">';
          question_row_html += '            <p class="admin-question-row-label">Sub Question '+group_id+'.'+rowCount+'</p>';
          question_row_html += '            <input class="form-field question-admin-title form-control" name="question_title['+group_id+']['+rowCount+'][sub_title]" placeholder="Sub Question Title"/>';
          question_row_html += '            <div class="admin-question-row-textarea">';
          question_row_html += '                <textarea class="form-control description_area" name="question_description['+group_id+']['+rowCount+'][description]" placeholder="Sub Question Description"></textarea>';
          question_row_html += '                <div class="col-12">';
          question_row_html += '                    <div class="question-rule-checkbox-inner-container">';
          question_row_html += '                        <label>Requires a custom answer.. </label>';
          question_row_html += '                        <input type="checkbox" class="question-rule-description-checkbox"/>';
          question_row_html += '                        <input type="hidden" class="question-rule-description-checkbox-input" name="is_question_description['+group_id+']['+rowCount+'][is_description]"/>';
          question_row_html += '                    </div>';
          question_row_html += '                </div>';
          question_row_html += '            </div>';
          question_row_html += '            <div class="question-advice-row-container">';
          question_row_html += '                <label for="question-advice-row-0">Advice</label>';
          question_row_html += '                <textarea id="question-advice-row-0" class="form-control" name="question_advice['+group_id+']['+rowCount+'][advice]"></textarea>';
          question_row_html += '            </div>';
          question_row_html += '        </div>';
          question_row_html += '        <div class="col-2 question-row-points-container">';
          question_row_html += '            <p style="text-align: right;" ><span class="button btn-remove-question">Remove Question</span></p>';
          question_row_html += '            <label for="question_point['+group_id+']['+rowCount+'][point]"><strong>Question Point</strong></label>';
          question_row_html += '            <input type="number" class="question-point-input" name="question_point['+group_id+']['+rowCount+'][point]"/>';
          question_row_html += '            <div class="question-points-actions-container">';
          question_row_html += '                <div class="increment-question-point" aria-hidden="true">';
          question_row_html += '                    <i class="fa fa-plus"></i>';
          question_row_html += '                </div>';
          question_row_html += '                <div class="decrement-question-point" aria-hidden="true">';
          question_row_html += '                    <i class="fa fa-minus"></i>';
          question_row_html += '                </div>';
          question_row_html += '            </div>';
          question_row_html += '        </div>';
          question_row_html += '    </div>';
          question_row_html += '    <div class="row question-other-info-container">';
          question_row_html += '        <div class="col-12">';
          question_row_html += '            <strong class="checkbox-label-heading">Rules:</strong>';
          question_row_html += '            <div class="question-rule-checkbox-inner-container">';
          question_row_html += '                <label>Supporting documentation required.. </label>';
          question_row_html += '                <input type="checkbox" class="question-rule-checkbox"/>';
          question_row_html += '                <input type="hidden" class="question-rule-checkbox-input" name="is_question_supporting['+group_id+']['+rowCount+'][supporting_doc]"/>';
          question_row_html += '            </div>';
          question_row_html += '        </div>';
          question_row_html += '        <div class="col-12 multi-choice-btn-container">';
          question_row_html += '            <button class="button add-multi-choice-btn" type="button" data-group-id='+group_id+' data-id="'+rowCount+'">Add multi choice button</button>';
          question_row_html += '            <div class="multi-choice-btn-table-container">';
          question_row_html += '                <table id="multi-check-table-'+group_id+'_'+rowCount+'">';
          question_row_html += '                    <tbody>';
          question_row_html += '                    </tbody>';
          question_row_html += '                </table>';
          question_row_html += '            </div>';
          question_row_html += '        </div>';
          question_row_html += '    </div>';
          question_row_html += '</div>';

      row_parent = group_question_wrapper.find(".question-field-container")
      row_parent.append(question_row_html);


      // const question_row = group_question_wrapper.find(".question-field-container").children().last()
      // question_row.attr("id", `question-main-row-${rowCount}`);
      // question_row.find(".admin-question-row-label").html(`Sub Question ${group_id}.${rowCount}`);
      // question_row.find("table").attr("id", `multi-check-table-${rowCount}`);
      // question_row.find(".add-multi-choice-btn").attr("data-id", `${rowCount}`);
      // question_row.find(".question-admin-title").attr("name", `question_title[${group_id}][${rowCount}][sub_title]`)

      return false;
    });

    $(document).on("click", "#add-simple-row", function(){

      let rowCount = ($('.question-row-container').length) + 1;

      let simple_question_html =  '<div class="simple-question-container question-row-container" id="question-main-row-'+rowCount+'">';
          simple_question_html += '    <input type="hidden" name="question_repeater[]"/>';
          simple_question_html += '    <div class="row question-input-area-container">';
          simple_question_html += '        <div class="col-10">';
          simple_question_html += '            <p class="admin-question-row-label">Question #'+rowCount+'</p>';
          simple_question_html += '            <input class="form-field question-admin-title form-control" name="question_title['+rowCount+']" placeholder="Question Title"/>';
          simple_question_html += '            <div class="admin-question-row-textarea">';
          simple_question_html += '                <textarea class="form-control description_area" name="question_description['+rowCount+']" placeholder="Question Description"></textarea>';
          simple_question_html += '                <div class="col-12">';
          simple_question_html += '                    <div class="question-rule-checkbox-inner-container">';
          simple_question_html += '                        <label>Requires a custom answer.. </label>';
          simple_question_html += '                        <input type="checkbox" class="question-rule-description-checkbox"/>';
          simple_question_html += '                        <input type="hidden" class="question-rule-description-checkbox-input" name="is_question_description['+rowCount+']"/>';
          simple_question_html += '                    </div>';
          simple_question_html += '                </div>';
          simple_question_html += '            </div>';
          simple_question_html += '            <div class="question-advice-row-container">';
          simple_question_html += '                <label for="question-advice-row-0">Advice</label>';
          simple_question_html += '                <textarea id="question-advice-row-0" class="form-control" name="question_advice['+rowCount+']"></textarea>';
          simple_question_html += '            </div>';
          simple_question_html += '        </div>';
          simple_question_html += '        <div class="col-2 question-row-points-container">';
          simple_question_html += '            <p style="text-align: right;" ><span class="button btn-remove-question">Remove Question</span></p>';
          simple_question_html += '            <label for="question_point[]"><strong>Question Point</strong></label>';
          simple_question_html += '            <input type="number" class="question-point-input" name="question_point['+rowCount+']"/>';
          simple_question_html += '            <div class="question-points-actions-container">';
          simple_question_html += '                <div class="increment-question-point" aria-hidden="true">';
          simple_question_html += '                    <i class="fa fa-plus"></i>';
          simple_question_html += '                </div>';
          simple_question_html += '                <div class="decrement-question-point" aria-hidden="true">';
          simple_question_html += '                    <i class="fa fa-minus"></i>';
          simple_question_html += '                </div>';
          simple_question_html += '            </div>';
          simple_question_html += '        </div>';
          simple_question_html += '    </div>';
          simple_question_html += '    <div class="row question-other-info-container">';
          simple_question_html += '        <div class="col-12">';
          simple_question_html += '            <strong class="checkbox-label-heading">Rules:</strong>';
          simple_question_html += '            <div class="question-rule-checkbox-inner-container">';
          simple_question_html += '                <label>Supporting documentation required.. </label>';
          simple_question_html += '                <input type="checkbox" class="question-rule-checkbox"/>';
          simple_question_html += '                <input type="hidden" class="question-rule-checkbox-input" name="is_question_supporting['+rowCount+']"/>';
          simple_question_html += '            </div>';
          simple_question_html += '        </div>';
          simple_question_html += '        <div class="col-12 multi-choice-btn-container">';
          simple_question_html += '            <button class="button add-multi-choice-simple" type="button" data-id="'+rowCount+'">Add multi choice button</button>';
          simple_question_html += '            <div class="multi-choice-btn-table-container">';
          simple_question_html += '                <table id="multi-check-table-'+rowCount+'">';
          simple_question_html += '                    <tbody>';
          simple_question_html += '                    </tbody>';
          simple_question_html += '                </table>';
          simple_question_html += '            </div>';
          simple_question_html += '        </div>';
          simple_question_html += '    </div>';
          simple_question_html += '</div>';

          let  question_main_wrapper = $("#question-group-repeater")

          question_main_wrapper.append(simple_question_html)
    })

    $(document).on("change", "#simple_assessment_input", function () {
      let template_main_wrapper = $('#question-template-wrapper');
      let add_simple_template_block  = '<p id="add-simple-row-block">'
          add_simple_template_block += '  <span id="add-simple-row" class="button button-primary">Add Simple Question</span>'
          add_simple_template_block += '</p>'

      let group_template_block = $('#add-group-row-block')
      let group_question_wrapper = $('.group-question-wrapper')

      if ($(this).is(":checked")) {
        template_main_wrapper.append(add_simple_template_block)
        group_template_block.remove()
        group_question_wrapper.remove()
      }
    })

    $(document).on("change", "#comprehensive_assessment_input", function () {
      let template_main_wrapper = $('#question-template-wrapper');
      let add_group_template_block  = '<p id="add-group-row-block">'
          add_group_template_block += '  <span id="add-group-row" class="button button-primary">Add Group Question</span>'
          add_group_template_block += '</p>'

      let simple_template_block = $('#add-simple-row-block')
      let simple_question_wrapper = $('.simple-question-container')

      if ($(this).is(":checked")) {
        template_main_wrapper.append(add_group_template_block)
        simple_template_block.remove()
        simple_question_wrapper.remove()
      }
    })

    $(document).on("click", ".btn-remove-group", function () {
      let group_remove = $(this).closest('.group-question-wrapper')
      group_remove.remove()
    });

    $(document).on("click", ".btn-remove-question", function () {
      let question_remove = $(this).closest('.question-row-container')
      question_remove.remove()
    });

    $(document).on("click", ".btn-remove-choice", function () {
      $(this).parents("tr").remove();
      return false;
    });

    $(document).on("click", ".increment-question-point", function () {
      const that = $(this);
      const parent = that.parent(".question-points-actions-container");
      const input = parent.siblings(".question-point-input");

      let value = input.val();
      value = value !== "" ? value : "0";
      input.val(parseInt(value) + 1);
    });

    $(document).on("click", ".decrement-question-point", function () {
      const that = $(this);
      const parent = that.parent(".question-points-actions-container");
      const input = parent.siblings(".question-point-input");

      let value = input.val();
      value = value !== "" ? value : "0";
      value = parseInt(value);
      if (value <= 0) return;

      input.val(value - 1);
    });

    $(document).on("change", ".question-rule-checkbox", function () {
      const that = $(this);
      const input = that.siblings(".question-rule-checkbox-input");
      let val = 0;

      if (that.is(":checked")) {
        val = 1;
      }
      input.val(val);
    });

    $(document).on("change", ".question-rule-description-checkbox", function () {
      const that = $(this);
      const input = that.siblings(".question-rule-description-checkbox-input");
      let val = 0;

      if (that.is(":checked")) {
        val = 1;
      }
      input.val(val);
    });

    $(document).on("click", ".add-multi-choice-btn", function () {
      let index = $(this).data("id");
      let group_index = $(this).data("group-id");
      let currentIndex = index;
      let table = $(`#multi-check-table-${group_index}_${index}`);
      let row = table.find(".multi-choice-list-item");
      let row_count = (row.length) + 1;

      let table_row_html =  '<tr class="multi-choice-list-item">';
          table_row_html += '   <td><input type="text" name="multi_choice_value['+group_index+']['+currentIndex+']['+row_count+']"/></td>';
          table_row_html += '   <td><input type="checkbox" name="multi_choice_check['+group_index+']['+currentIndex+']['+row_count+']"/></td>';
          table_row_html += '   <td><span class="button btn-remove-choice">Remove</span></td>';
          table_row_html += '</tr>';

      let parent_row = $(`#multi-check-table-${group_index}_${index} tbody`)
      parent_row.append(table_row_html)

      return false;
    });

    $(document).on("click", ".add-multi-choice-simple", function () {
      let index = $(this).data("id");
      let currentIndex = index;
      let table = $(`#multi-check-table-${index}`);
      let row = table.find(".multi-choice-list-item");
      let row_count = (row.length) + 1;

      let table_row_html =  '<tr class="multi-choice-list-item">';
          table_row_html += '   <td><input type="text" name="multi_choice_value['+currentIndex+']['+row_count+']"/></td>';
          table_row_html += '   <td><input type="checkbox" name="multi_choice_check['+currentIndex+']['+row_count+']"/></td>';
          table_row_html += '   <td><span class="button btn-remove-choice">Remove</span></td>';
          table_row_html += '</tr>';

      let parent_row = $(`#multi-check-table-${index} tbody`)
      parent_row.append(table_row_html)

      return false;
    });

    $(document).on("change", ".multi-choice-check-input", function () {
      const that = $(this);
      const input = that.siblings("input[type=hidden]");
      let val = 0;

      if (that.is(":checked")) {
        val = 1;
      }
      input.val(val);
    });

    $(".accept-quiz-feedback").on("click", async function (e) {
      e.preventDefault();
      await markFeedbackSubmissionAnswers($(this), "accepted");
    });

    $(".reject-quiz-feedback").on("click", async function (e) {
      e.preventDefault();
      await markFeedbackSubmissionAnswers($(this));
    });

    async function markFeedbackSubmissionAnswers(instance, type = "rejected") {
      let quizId = instance.data("id");

      let parent = $(`#${quizId}-main-container`);
      let feedback = parent.find(".feedback-input").val();
      let assessmentId = parent.find(`[name="assessment_id"]`).val();
      let userId = parent.find(`[name="user_id"]`).val();
      let quizPoint = parent.find(`[name="quiz_point"]`).val();

      await rejectSubmissionWithFeedback({
        feedback,
        assessment_id: assessmentId,
        user_id: userId,
        quiz_id: quizId,
        quiz_point: quizPoint,
        type,
      });
    }

    async function rejectSubmissionWithFeedback(data = {}) {
      let submission_id = $('#submission_id').val()

      const payload = {
        action: "reject_submission_feedback",
        ...data,
        'submission_id': submission_id,
      };

      let response = await $.ajax({ type: "POST", url: ajaxUrl, data: payload });
      const {post, status, message } = response;
      // console.log(response);

      alert(message);

      if (status) {
        // toggleBtnDisable();

        // setTimeout(function () {
        //     location.reload();
        // }, 1000);
        // return true;
      }

      return status;
    }

    $(".reject-button").on("click", async function (e) {
      e.preventDefault();
      submit_feedback_submission("rejected");
    });

    $(".accept-button").on("click", async function (e) {
      e.preventDefault();
      await submit_feedback_submission("accepted");
    });

    async function submit_feedback_submission(feedbackType) {
      let assessment_id = $("#assessment_id").val();
      let user_id = $("#user_id").val();
      let submission_id = $("#submission_id").val();

      const payload = {
        action: "final_accept_reject_assessment",
        assessment_id,
        user_id,
        submission_id,
        type: feedbackType,
      };

      let response = await $.ajax({ type: "POST", url: ajaxUrl, data: payload });
      const { quiz_point, status, message } = response;
      console.log(response);

      alert(message);

      if (status) {
        setTimeout(function () {
          $('#publish').click()
        }, 100);
        return true;
      }
    }

    function toggleBtnDisable() {
      let acceptBtn = $(`.accept-button`);
      let rejectBtn = $(`.reject-button`);

      acceptBtn.attr("disabled", true);
      rejectBtn.attr("disabled", false);
    }

    $(".input-weighting").on("change", function () {
      let input_val = $(this).val()
      $(this).attr('value', input_val)
    });

  });
  
