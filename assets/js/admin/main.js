jQuery(document).ready(function ($) {
    const ajaxUrl = ajax_object.ajax_url;
    const organisationIdInstance = $('#organisation_id');
    var groupCount = 0;
    if ($(".group-question-wrapper").length) {
        groupCount = $(".group-question-wrapper").length;
    }

    $(document).on("click", "#add-group-row", function () {

        let group_parent_repeater = $("#question-group-repeater")
        groupCount = groupCount + 1;

        let group_questions_html  = '<div class="group-question-wrapper question" id="question-group-row-' + groupCount + '" data-id="' + groupCount + '">';
            group_questions_html += '    <input class="is_locked_input" type="hidden" name="group_questions[' + groupCount + '][is_locked]" value="0">';
            group_questions_html += '    <div class="question-wrapper-top">';
            group_questions_html += '       <span class="button btn-remove-group">- Remove Group';
            group_questions_html += '           <div class="remove-message">Are you sure?';
            group_questions_html += '               <span class="btn-remove">Remove</span>';
            group_questions_html += '               <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>';
            group_questions_html += '           </div>';
            group_questions_html += '       </span>';
            group_questions_html += '       <span class="icon-toggle"></span>';
            group_questions_html += '    </div>';
            group_questions_html += '    <div class="row">';
            group_questions_html += '        <div class="col-12">';
            group_questions_html += '            <h5 class="admin-question-group-label">Question #' + groupCount + '</h5>';
            group_questions_html += '            <input class="form-field group-question-admin-title form-control" name="group_questions[' + groupCount + '][title]" placeholder="Group Question Title"/>';
            group_questions_html += '        </div>';
            group_questions_html += '        <!-- <div class="col-2 question-row-points-container">';
            group_questions_html += '            <label><strong>Question Point</strong></label>';
            group_questions_html += '            <input type="number" step="0.01" class="question-point-input" name="group_questions[' + groupCount + '][point]"/>';
            group_questions_html += '            <div class="question-points-actions-container">';
            group_questions_html += '                <div class="increment-question-point" aria-hidden="true">';
            group_questions_html += '                    <i class="fa fa-plus"></i>';
            group_questions_html += '                </div>';
            group_questions_html += '                <div class="decrement-question-point" aria-hidden="true">';
            group_questions_html += '                    <i class="fa fa-minus"></i>';
            group_questions_html += '                </div>';
            group_questions_html += '            </div>';
            group_questions_html += '        </div> -->';
            group_questions_html += '    </div>';
            group_questions_html += '    <div class="question-field-container"></div>';
            group_questions_html += '    <!-- Button add more sub questions -->';
            group_questions_html += '    <div class="add-row-field"><span class="add-row button button-primary">Add Sub Questions</span></div>';
            group_questions_html += '    <div class="question-wrapper-bottom">';
            group_questions_html += '       <a class="btn-lock-question" role="button">';
            group_questions_html += '           <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/lock.svg" width="36" height="36">';
            group_questions_html += '           <span class="lock-text">Lock</span>';
            group_questions_html += '       </a>';
            group_questions_html += '       <a class="btn-expland-wrapper" role="button">';
            group_questions_html += '           <span class="text">Expland Group</span>';
            group_questions_html += '           <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>';
            group_questions_html += '       </a>';
            group_questions_html += '    </div>';
            group_questions_html += '</div>';

        group_parent_repeater.append(group_questions_html)
  });

    $(document).on("click", ".add-row", function () {

        let group_questions_wrapper = $(this).closest(".group-question-wrapper")
        let group_id = group_questions_wrapper.attr("data-id")
        let sub_question_length = group_questions_wrapper.find(".question-row-container").length
        let rowCount = '';
        if (sub_question_length) {
        rowCount = sub_question_length + 1;
        }
        else {
        rowCount = 1;
        }
        let wp_editor = 'wp-editor-question-' + Date.now();
        let wp_advice_editor = 'question-advice-row-' + Date.now();

        let question_row_html  = '<div class="question-row-container" id="question-main-row-' + rowCount + '" data-id="'+rowCount+'">';
            question_row_html += '    <input type="hidden" name="question_repeater[]"/>';
            question_row_html += '    <div class="remove-question-block">';
            question_row_html += '        <span class="button btn-remove-question">- Remove Question';
            question_row_html += '            <div class="remove-message from-left">Are you sure?';
            question_row_html += '                <span class="btn-remove">Remove</span>';
            question_row_html += '                <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>';
            question_row_html += '            </div>';
            question_row_html += '        </span>';
            question_row_html += '    </div>';
            question_row_html += '    <div class="row question-input-area-container">';
            question_row_html += '        <div class="col-10">';
            question_row_html += '            <p class="admin-question-row-label">Sub Question ' + group_id + '.' + rowCount + '</p>';
            question_row_html += '            <input class="form-field question-admin-title form-control" name="group_questions[' + group_id + '][list][' + rowCount + '][sub_title]" placeholder="Sub Question Title"/>';
            question_row_html += '            <div class="admin-question-row-textarea">';
            question_row_html += '                <textarea id="'+wp_editor+'" name="group_questions[' + group_id + '][list][' + rowCount + '][description]" rows="10" class="sub-question-wpeditor"></textarea>';
            question_row_html += '                <div class="col-12">';
            question_row_html += '                    <div class="question-rule-checkbox-inner-container">';
            question_row_html += '                        <label>Requires a custom answer.. </label>';
            question_row_html += '                        <input type="checkbox" class="question-rule-description-checkbox"/>';
            question_row_html += '                        <input type="hidden" class="question-rule-description-checkbox-input" name="group_questions[' + group_id + '][list][' + rowCount + '][is_description]"/>';
            question_row_html += '                    </div>';
            question_row_html += '                </div>';
            question_row_html += '            </div>';
            question_row_html += '            <div class="question-advice-row-container">';
            question_row_html += '                <div class="btn-toggle-advice-area">Advice <span class="toggle-icon"></span></div>';
            question_row_html += '                <div class="visual-textarea-wrapper">';
            question_row_html += '                    <textarea id="'+ wp_advice_editor +'" class="form-control advice-area-wpeditor" name="group_questions[' + group_id + '][list][' + rowCount + '][advice]" rows="10"></textarea>';
            question_row_html += '                </div>';
            question_row_html += '            </div>';
            question_row_html += '        </div>';
            question_row_html += '        <div class="col-2 sub-point question-row-points-container">';
            question_row_html += '            <label><strong>Question Point</strong></label>';
            question_row_html += '            <input type="number" step="0.01" class="question-point-input" name="group_questions[' + group_id + '][list][' + rowCount + '][point]"/>';
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
            question_row_html += '                <input type="hidden" class="question-rule-checkbox-input" name="group_questions[' + group_id + '][list][' + rowCount + '][supporting_doc]"/>';
            question_row_html += '            </div>';
            question_row_html += '        </div>';
            question_row_html += '        <div class="col-10 multi-choice-btn-container">';
            question_row_html += '            <button class="button add-multi-choice-btn" type="button" data-group-id=' + group_id + ' data-id="' + rowCount + '">';
            question_row_html += '              <i class="fa-solid fa-plus"></i> Add Multiple Choice';
            question_row_html += '            </button>';
            question_row_html += '            <div class="multi-choice-btn-table-container">';
            question_row_html += '                <table class="multi-choice-table" id="multi-check-table-' + group_id + '_' + rowCount + '">';
            question_row_html += '                    <tbody>';
            question_row_html += '                    </tbody>';
            question_row_html += '                </table>';
            question_row_html += '            </div>';
            question_row_html += '        </div>';
            question_row_html += '    </div>';
            question_row_html += '    <div class="row question-add-files-container">'
            question_row_html += '        <div class="col-12">'
            question_row_html += '            <div class="btn-add-files-wrapper">'
            question_row_html += '                <label for="additional-files-' + group_id + '-' + rowCount + '">'
            question_row_html += '                    <span class="button" role="button" aria-disabled="false"><i class="fa-solid fa-file-arrow-up"></i> Add Additional Files</span>'
            question_row_html += '                </label>'
            question_row_html += '                <input id="additional-files-' + group_id + '-' + rowCount + '"'
            question_row_html += '                        class="additional-files"'
            question_row_html += '                        type="file" '
            question_row_html += '                        name="file[]" '
            question_row_html += '                        style="visibility: hidden; position: absolute;"/>'
            question_row_html += '                <div class="uploading-wrapper">'
            question_row_html += '                    <img src="../wp-content/plugins/wp-assessment/assets/images/front/Spinner-0.7s-200px.svg" alt="uploading">'
            question_row_html += '                </div>'
            question_row_html += '            </div>'
            question_row_html += '            <div class="filesList"></div>'
            question_row_html += '        </div>'
            question_row_html += '    </div>'
            question_row_html += '    <div class="key-areas">'
            question_row_html += '        <label class="col-12"><strong>Select Key Area</strong></label>'
            question_row_html += '        <select id="select-key-area-'+ group_id +'-'+ rowCount +'" class="select-key-area"'
            question_row_html += '                name="group_questions['+ group_id +'][list]['+ rowCount +'][key_area]">'
            question_row_html += '            <option value="">Choose Key Area</option>'
            question_row_html += '        </select>'
            question_row_html += '    </div>'
            question_row_html += '</div>';

        group_questions_wrapper.addClass('toggle')
        group_questions_wrapper.find('.question-wrapper-top').addClass('active')
        group_questions_wrapper.find('.btn-expland-wrapper').addClass('active')
        row_parent = group_questions_wrapper.find(".question-field-container")
        row_parent.append(question_row_html);

        let wpeditor_des_wrapper = $('#' + wp_editor)
        let wpeditor_advice_wrapper = $('#' + wp_advice_editor)

        // Render WP Editor
        append_wpeditor(wpeditor_des_wrapper, false)
        append_wpeditor(wpeditor_advice_wrapper, false)

        // Render Key Area
        render_key_area_question($('#select-key-area-'+ group_id +'-'+ rowCount ))

        return false;
    });

    function render_key_area_question(select_key_area) {
        let key_area_input = $('.key-areas-list .key-area-input')

        key_area_input.each(function(e) {
            let option_item = '<option value="'+ $(this).val() +'">'+ $(this).val() +'</option>';
            select_key_area.append(option_item)
        })
    }

    var groupCount_Simple = 0;
    if ($(".simple-question-container").length) {
        groupCount_Simple = $(".simple-question-container").length;
    }

    $(document).on("click", "#add-simple-row", function () {

        groupCount_Simple = groupCount_Simple + 1;
        let wp_editor = 'wp-editor-question-' + Date.now();
        let wp_advice_editor = 'wp-advice-editor-' + Date.now();

        let simple_question_html  = '<div class="simple-question-container question question-row-container" id="question-main-row-' + groupCount_Simple + '">';
            simple_question_html += '    <input class="is_locked_input" type="hidden" name="group_questions[' + groupCount_Simple + '][is_locked]" value="0">';
            simple_question_html += '    <div class="question-wrapper-top">';
            simple_question_html += '       <span class="button btn-remove-question">- Remove Question';
            simple_question_html += '           <div class="remove-message">Are you sure? ';
            simple_question_html += '               <span class="btn-remove">Remove</span>';
            simple_question_html += '               <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>';
            simple_question_html += '           </div>';
            simple_question_html += '       </span>';
            simple_question_html += '       <span class="icon-toggle"></span>';
            simple_question_html += '    </div>';
            simple_question_html += '    <input type="hidden" name="question_repeater[]"/>';
            simple_question_html += '    <div class="row question-input-area-container">';
            simple_question_html += '        <div class="col-12">';
            simple_question_html += '            <p class="admin-question-row-label">Question #' + groupCount_Simple + '</p>';
            simple_question_html += '            <input class="form-field question-admin-title form-control" name="group_questions[' + groupCount_Simple + '][title]" placeholder="Question Title"/>';
            simple_question_html += '            <div class="admin-question-row-textarea">';
            simple_question_html += '                <textarea id="'+wp_editor+'" name="group_questions[' + groupCount_Simple + '][description]" rows="10" class="question-wpeditor"></textarea>';
            simple_question_html += '                <div class="col-12">';
            simple_question_html += '                    <div class="question-rule-checkbox-inner-container">';
            simple_question_html += '                        <label>Requires a custom answer.. </label>';
            simple_question_html += '                        <input type="checkbox" class="question-rule-description-checkbox"/>';
            simple_question_html += '                        <input type="hidden" class="question-rule-description-checkbox-input" name="group_questions[' + groupCount_Simple + '][is_description]"/>';
            simple_question_html += '                    </div>';
            simple_question_html += '                </div>';
            simple_question_html += '            </div>';
            simple_question_html += '            <div class="question-advice-row-container">';
            simple_question_html += '                <label for="question-advice-row-0">Advice:</label>';
            simple_question_html += '                <textarea id="'+ wp_advice_editor +'" class="advice-area-wpeditor" name="group_questions[' + groupCount_Simple + '][advice]" rows="10"></textarea>';
            simple_question_html += '            </div>';
            simple_question_html += '        </div>';
            simple_question_html += '        <div class="col-2 question-row-points-container">';
            simple_question_html += '            <!-- <label for="question_point[]"><strong>Question Point</strong></label>';
            simple_question_html += '            <input type="number" class="question-point-input" name="group_questions[' + groupCount_Simple + '][point]"/>';
            simple_question_html += '            <div class="question-points-actions-container">';
            simple_question_html += '                <div class="increment-question-point" aria-hidden="true">';
            simple_question_html += '                    <i class="fa fa-plus"></i>';
            simple_question_html += '                </div>';
            simple_question_html += '                <div class="decrement-question-point" aria-hidden="true">';
            simple_question_html += '                    <i class="fa fa-minus"></i>';
            simple_question_html += '                </div>';
            simple_question_html += '            </div> -->';
            simple_question_html += '        </div>';
            simple_question_html += '    </div>';
            simple_question_html += '    <div class="row question-other-info-container">';
            simple_question_html += '        <div class="col-12">';
            simple_question_html += '            <strong class="checkbox-label-heading">Rules:</strong>';
            simple_question_html += '            <div class="question-rule-checkbox-inner-container" style="display:none;">';
            simple_question_html += '                <label>Supporting documentation required.. </label>';
            simple_question_html += '                <input type="checkbox" class="question-rule-checkbox"/>';
            simple_question_html += '                <input type="hidden" class="question-rule-checkbox-input" name="group_questions[' + groupCount_Simple + '][is_question_supporting]"/>';
            simple_question_html += '            </div>';
            simple_question_html += '        </div>';
            simple_question_html += '        <div class="col-10 multi-choice-btn-container">';
            simple_question_html += '            <button class="button add-multi-choice-simple" type="button" data-id="' + groupCount_Simple + '">Add multi choice button</button>';
            simple_question_html += '            <div class="multi-choice-btn-table-container">';
            simple_question_html += '                <table class="multi-choice-table" id="multi-check-table-' + groupCount_Simple + '">';
            simple_question_html += '                    <tbody>';
            simple_question_html += '                    </tbody>';
            simple_question_html += '                </table>';
            simple_question_html += '            </div>';
            simple_question_html += '        </div>';
            simple_question_html += '    </div>';
            simple_question_html += '    <div class="question-wrapper-bottom">';
            simple_question_html += '       <a class="btn-lock-question" role="button">';
            simple_question_html += '           <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/lock.svg" width="36" height="36">';
            simple_question_html += '           <span class="lock-text">Lock</span>';
            simple_question_html += '       </a>';
            simple_question_html += '       <a class="btn-expland-wrapper" role="button">';
            simple_question_html += '           <span class="text">Expland Question</span>';
            simple_question_html += '           <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>';
            simple_question_html += '       </a>';
            simple_question_html += '    </div>';
            simple_question_html += '</div>';

        let question_main_wrapper = $("#question-group-repeater")

        question_main_wrapper.append(simple_question_html)

        let wpeditor_wrapper = $('#' + wp_editor)
        let wpeditor_advice_wrapper = $('#' + wp_advice_editor)

        append_wpeditor(wpeditor_wrapper, false)
        append_wpeditor(wpeditor_advice_wrapper, false)

    })

    function append_wpeditor(wpeditor_wrapper, is_media_button) {

        $.each( $(wpeditor_wrapper), function( i, editor ) {

        // console.log(editor);
        var editor_id = $(editor).attr('id');

        wp.editor.initialize(
            editor_id,
            {
                tinymce: {
                    wpautop: true,
                    plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                    toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
                    toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
                },
                quicktags: true,
                mediaButtons: is_media_button,
            }
        );

        });
    }

    $(document).on("click", ".question-wrapper-top", function () {
        let wrapper_top = $(this)
        let question_wrapper = wrapper_top.closest('#question-template-wrapper .question')
        let btn_expland = question_wrapper.find('.btn-expland-wrapper')

        if (wrapper_top.hasClass('active')) {
            wrapper_top.removeClass('active')
            btn_expland.removeClass('active')

            if (question_wrapper.hasClass('group-question-wrapper')) {
                btn_expland.find('.text').text('Expland Group')
            }
            else {
                btn_expland.find('.text').text('Expland Question')
            }
        }
        else {
            btn_expland.addClass('active')
            wrapper_top.addClass('active')

            if (question_wrapper.hasClass('group-question-wrapper')) {
                btn_expland.find('.text').text('Collapse Group')
            }
            else {
                btn_expland.find('.text').text('Collapse Question')
            }
        }
        question_wrapper.toggleClass('toggle')
    })

    $(document).on("click", ".btn-expland-wrapper", function () {
        let btn = $(this)
        let question_wrapper = btn.closest('#question-template-wrapper .question')
        let wrapper_top = question_wrapper.find('.question-wrapper-top')

        if (btn.hasClass('active')) {
            btn.removeClass('active')
            wrapper_top.removeClass('active')

            if (question_wrapper.hasClass('group-question-wrapper')) {
                btn.find('.text').text('Expland Group')
            }
            else {
                btn.find('.text').text('Expland Question')
            }

            $('html, body').animate({
                scrollTop: question_wrapper.offset().top - 50
            }, 100);
        }
        else {
            btn.addClass('active')
            wrapper_top.addClass('active')

            if (question_wrapper.hasClass('group-question-wrapper')) {
                btn.find('.text').text('Collapse Group')
            }
            else {
                btn.find('.text').text('Collapse Question')
            }
        }
        question_wrapper.toggleClass('toggle')
    })

  $(document).on("click", "#simple_assessment_input", function () {
    let template_main_wrapper = $('#question-template-wrapper');
    groupCount_Simple = 0;
    let add_simple_block_count = $('#add-simple-row-block').length;
    let add_simple_template_block  = '<p id="add-simple-row-block">'
        add_simple_template_block += '  <span id="add-simple-row" class="button button-primary">Add Simple Question</span>'
        add_simple_template_block += '</p>'

    let group_template_block = $('#add-group-row-block')
    let group_questions_wrapper = $('.group-question-wrapper')

    if ($(this).is(":checked")) {

      if ((group_questions_wrapper.length) > 0) {
        if (confirm('If you change the assessment template, all questions in the current template will be removed.')) {
          template_main_wrapper.append(add_simple_template_block)
          group_template_block.remove()
          group_questions_wrapper.remove()
        }
        else {
          return false;
        }
      }
      else {
        if (add_simple_block_count == 0) {
          template_main_wrapper.append(add_simple_template_block)
        }
        group_template_block.remove()
      }
    }
  })

  $(document).on("click", "#comprehensive_assessment_input", function () {
    let template_main_wrapper = $('#question-template-wrapper');
    groupCount = 0;
    let add_group_block_count = $('#add-group-row-block').length;
    let add_group_template_block  = '<p id="add-group-row-block">'
        add_group_template_block += '  <span id="add-group-row" class="button button-primary">Add Group Question</span>'
        add_group_template_block += '</p>'

    let simple_template_block = $('#add-simple-row-block')
    let simple_question_wrapper = $('.simple-question-container')

    if ($(this).is(":checked")) {

      if ((simple_question_wrapper.length) > 0) {
        if (confirm('If you change the assessment template, all questions in the current template will be removed.')) {
          template_main_wrapper.append(add_group_template_block)
          simple_template_block.remove()
          simple_question_wrapper.remove()
        }
        else {
          return false;
        }
      }
      else {
        if (add_group_block_count == 0) {
          template_main_wrapper.append(add_group_template_block)
        }
        simple_template_block.remove()
      }
    }
  })

  // change or type point for Sub question
  $(document).on("change keyup keypress", ".input-answer-point", function () {
    let answer_point = $(this).val()
    let weighting_wrapper = $(this).closest('.weighting')
    let quiz_wrapper = $(this).closest('.submission-view-item-row')
    let section_wrapper = $(this).closest('.group-quiz-wrapper')
    let total_section_score = section_wrapper.find('.total-section-score-val')
    let quiz_weighting = weighting_wrapper.data('weighting')
    let sub_score = quiz_wrapper.find('.sub-total-score-val')
    let all_sub_score = section_wrapper.find('.sub-total-score-val')
    let all_sections_score = $('.total-section-score-val')
    let total_score_element = $('.total-submission-score')
    let input_total_score = $('input[name="total_submission_score"]')
    var sub_scores_arr = [];
    var section_scores_arr = [];
    var total_section_score_val = 0;
    var total_submission_score_val = 0;

    // Sub questions Scoring
    sub_score.text(parseFloat(answer_point * quiz_weighting).toFixed(1));

    all_sub_score.each(function(e) {
      let sub_score_fl = parseFloat($(this).text());
      sub_scores_arr.push(sub_score_fl);
    })
    sub_scores_arr.forEach( num => {
      total_section_score_val += num;
    })

    // Section Scoring
    total_section_score.text(total_section_score_val.toFixed(1));

    all_sections_score.each(function(e) {
        let section_score_fl = parseFloat($(this).text());
        section_scores_arr.push(section_score_fl);
    })
    section_scores_arr.forEach( num => {
        total_submission_score_val += num;
    })

    // Total Submission Scoring
    total_score_element.text(total_submission_score_val.toFixed(1));
    input_total_score.val(total_submission_score_val.toFixed(1))
  });

  // change or type point for Group question
  // $(document).on("change keyup keypress", ".input-group-weighting", function () {
  //   let group_quiz_wrapper = $(this).closest('.group-quiz-wrapper')
  //   let group_quiz_point = $(this).val()

  //   let count_weighting = group_quiz_wrapper.find('.input-weighting').length

  //   for (let index = 1; index <= count_weighting; index++) {
  //     let sub_weighting_input = group_quiz_wrapper.find(`#input-weighting-${index}`).val()
  //     let sub_total_score = group_quiz_point * sub_weighting_input
  //     group_quiz_wrapper.find(`#sub-total-score-val-${index}`).text(sub_total_score)
  //   }

  // });

  $(document).on("click", ".btn-remove-group", function (e) {
    e.stopPropagation();
    let remove_message = $(this).find('.remove-message')
    remove_message.toggleClass('active')
  });

  $(document).on("click", ".btn-remove-group .btn-remove", function (e) {
    e.stopPropagation();
    let group_remove = $(this).closest('.group-question-wrapper')
    group_remove.remove()
  });

  const updateQuestionsIndex = (group_question_wrapper) => {

    let get_sub_questions = group_question_wrapper.find('.question-row-container');
    let group_id = group_question_wrapper.data('id')

    $(get_sub_questions).each(function(index, item) {
        let _index = index + 1;
        let firstHeading = $(item).find('.admin-question-row-label').first();
        let value = `Sub Question 1.${_index}`;
        let item_id = 'question-main-row-'+ group_id +'_'+ _index;
        let input_question = $(this).find('input.question-admin-title')
        let input_point = $(this).find('input.question-point-input')
        let textarea_description = $(this).find('textarea.sub-question-wpeditor')
        let textarea_advice = $(this).find('textarea.advice-area-wpeditor')
        let btn_multi_choice = $(this).find('button.add-multi-choice-btn')
        let table_multi_choice = $(this).find('table.multi-choice-table')
        let input_supporting_doc = $(this).find('input.question-rule-checkbox-input')
        let add_files_wrapper = $(this).find('.btn-add-files-wrapper')
        let label_add_files = add_files_wrapper.find('label')
        let input_add_files = $(this).find('input.additional-files')

        $(this).attr('data-id', _index )
        $(this).attr('id', item_id )
        firstHeading.text(value);
        input_question.attr('name', 'group_questions['+ group_id +'][list]['+ _index +'][sub_title]')
        input_point.attr('name', 'group_questions['+ group_id +'][list]['+ _index +'][point]')
        textarea_description.attr('name', 'group_questions['+ group_id +'][list]['+ _index +'][description]')
        textarea_advice.attr('name', 'group_questions['+ group_id +'][list]['+ _index +'][advice]')
        btn_multi_choice.attr('data-id', _index)
        table_multi_choice.attr('id', 'multi-check-table-'+ group_id +'_'+ _index )
        input_supporting_doc.attr('name', 'group_questions['+ group_id +'][list]['+ _index +'][supporting_doc]')
        label_add_files.attr('for', 'additional-files-'+ group_id +'-'+ _index)
        input_add_files.attr('id', 'additional-files-'+ group_id +'-'+ _index)
    })
  }

    $(document).on("click", ".btn-remove-question", function (e) {
        e.stopPropagation()
        let remove_message = $(this).find('.remove-message')
        remove_message.toggleClass('active')
    });

    $(document).on("click", ".btn-remove-question .btn-remove", function (e) {
        e.stopPropagation()

        let group_question_wrapper = $(this).closest('.group-question-wrapper');
        let question_remove = $(this).closest('.question-row-container')

        question_remove.remove();

        updateQuestionsIndex(group_question_wrapper);

        let num_question_row = group_question_wrapper.find('.question-row-container').length

        if (num_question_row == 0) {
            if (group_question_wrapper.hasClass('toggle')) {
                group_question_wrapper.removeClass('toggle')
            }
            let btn_expland = group_question_wrapper.find('.btn-expland-wrapper.active')
            let wrapper_top = group_question_wrapper.find('.question-wrapper-top.active')

            wrapper_top.removeClass('active')
            btn_expland.removeClass('active')
            btn_expland.find('.text').text('Expland Group')
        }
    });

    $(document).on("click", ".icon-close", function (e) {
        e.stopPropagation()
        $(this).closest('.remove-message').removeClass('active')
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

        let table_row_html = '<tr class="multi-choice-list-item">';
            table_row_html += '   <td><label>Answer</label><input type="text" name="group_questions[' + group_index + '][list][' + currentIndex + '][choice][' + row_count + '][answer]"/></td>';
            table_row_html += '   <td><label>Point</label><input type="number" step="0.01" name="group_questions[' + group_index + '][list][' + currentIndex + '][choice][' + row_count + '][point]"/></td>';
            table_row_html += '   <td><label></label><input type="checkbox" name="group_questions[' + group_index + '][list][' + currentIndex + '][choice][' + row_count + '][is_correct]"/></td>';
            table_row_html += '   <td><label></label><span class="button btn-remove-choice">Remove</span></td>';
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

        let table_row_html = '<tr class="multi-choice-list-item">';
            table_row_html += '   <td><label>Answer</label><input type="text" class="choice-item-answer" name="group_questions[' + currentIndex + '][choice][' + row_count + '][answer]"/></td>';
            // table_row_html += '   <td><label>Point</label><input type="number" name="group_questions[' + currentIndex + '][choice][' + row_count + '][point]"/></td>';
            table_row_html += '   <td><label></label><input type="checkbox" name="group_questions[' + currentIndex + '][choice][' + row_count + '][is_correct]"/></td>';
            table_row_html += '   <td><label></label><span class="button btn-remove-choice">Remove</span></td>';
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
        getQuizsStatus($(this))
    });

    $(".reject-quiz-feedback").on("click", async function (e) {
        e.preventDefault();
        await markFeedbackSubmissionAnswers($(this));
        getQuizsStatus($(this))
    });

    function getQuizsStatus(btn) {
        let assessment_id = $('#assessment_id').val()
        let submission_id = $('#submission_id').val()
        let organisation_id = $('#organisation_id').val()
        let user_id = $('#user_id').val()
        let main_wrapper = btn.closest('#questions-repeater-field')
        $.ajax({
        type: 'POST',
        url: ajaxUrl,
        data:{
            'action' : 'get_quizs_status_submission',
            'assessment_id' : assessment_id,
            'submission_id' : submission_id,
            'organisation_id' : organisation_id,
            'user_id' : user_id,
        },
        beforeSend : function ( xhr ) {

        },
        success:function(response){
            if (response == true) {
            main_wrapper.find('.submission-admin-view-footer .final-accept').show()
            main_wrapper.find('.submission-admin-view-footer .final-reject').hide()
            }
            else {
            main_wrapper.find('.submission-admin-view-footer .final-accept').hide()
            main_wrapper.find('.submission-admin-view-footer .final-reject').show()
            }
        }
        });
    }

    $("#assigned_collaborator li.collaborator-item").on("click", async function (e) {
        e.preventDefault();

        $('#collaborator-selected-list ._placeholder').remove()

        let collaborator_id = $(this).data('id')
        let collaborator_name = $(this).text()

        let selected_collab = '<li class="selected-collab-item" data-id="'+ collaborator_id +'">'
            selected_collab +=    '<label for="input-hiden">'+ collaborator_name +'</label>'
            selected_collab +=    '<input id="input-hiden" type="hidden" name="assigned_collaborator[]" value="'+ collaborator_id +'">'
            selected_collab +=    '<span class="remove-collab"><i class="fa-solid fa-xmark"></i></span>'
            selected_collab += '</li>'

        if ($(this).hasClass('selected')) {
            // $(this).removeClass('selected')
        }
        else {
            $(this).addClass('selected')
            $('#collaborator-selected-list').append(selected_collab);
        }
    });

    $(document).on("click", "#collaborator-selected-list", async function (e) {
        $('#assigned_collaborator').show()
    });

    $(document).on("click", "#collaborator-selected-list .remove-collab", async function (e) {
        let selected_collab_item = $(this).closest('.selected-collab-item')
        let selected_collab_id = selected_collab_item.data('id')

        let collab_dropdown_item_selected = $('li#collaborator_' + selected_collab_id)

        collab_dropdown_item_selected.removeClass('selected')
        selected_collab_item.remove()
        let count_collad_item = $('#collaborator-selected-list').find('.selected-collab-item').length
        if (count_collad_item == 0) {
        $('#collaborator-selected-list').append('<label class="_placeholder">+ Add Collaborator</label>')
        }
    });

    $(document).click(function (e) {
        var collab_main_wrapper = $(".collaborator-box");
        var collab_dropdown_popup = $("#assigned_collaborator");
        if (!collab_main_wrapper.is(e.target) && collab_main_wrapper.has(e.target).length === 0) {
            collab_dropdown_popup.hide();
        }

        var field_select2 = $(".field-select2");
        var list_dropdown = $(".field-select2 .list-items-dropdown");
        if (!field_select2.is(e.target) && field_select2.has(e.target).length === 0) {
            list_dropdown.hide();
        }
    });

    $(document).on("click", "#btn-send-invite", async function (e) {
        let btn = $(this)
        let postId = $('input#post_ID').val()
        let collab_input = $('#collaborator-selected-list').find('.selected-collab-item')
        let collab_arr = [];

        collab_input.each(function () {
            let input = $(this).data('id');
            collab_arr.push({id: input})
        })

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'send_invite_to_collaborator',
                'post_id': postId,
                'user_id_arr' : collab_arr,
            },
            beforeSend : function ( xhr ) {
                btn.addClass('sending')
            },
            success:function(response){
                btn.removeClass('sending')
                alert(response.message)
                console.log(response);
            }
        });
    });

    $(document).on("click", "#btn-add-report-option", async function (e) {

        let report_sections ='<div id="intro-section" class="_section">'
            report_sections +='    <h3 class="_heading">Intro</h3>'
            report_sections +='    <textarea id="intro-section-wpeditor-'+ Date.now() +'" name="report_sections[intro]" rows="12" class="report-section-wpeditor"></textarea>'
            report_sections +='</div>'
            report_sections +='<div id="outro-section" class="_section">'
            report_sections +='    <h3 class="_heading">Outro</h3>'
            report_sections +='    <textarea id="outro-section-wpeditor'+ Date.now() +'" name="report_sections[outro]" rows="12" class="report-section-wpeditor"></textarea>'
            report_sections +='</div>'
            report_sections +='<div id="address-section" class="_section">'
            report_sections +='    <h3 class="_heading">Address</h3>'
            report_sections +='    <textarea id="address-section-wpeditor'+ Date.now() +'" name="report_sections[address]" rows="12" class="report-section-wpeditor"></textarea>'
            report_sections +='</div>'
            report_sections +='<div id="appendix-section" class="_section">'
            report_sections +='    <h3 class="_heading">Appendix</h3>'
            report_sections +='    <textarea id="appendix-section-wpeditor'+ Date.now() +'" name="report_sections[appendix]" rows="12" class="report-section-wpeditor"></textarea>'
            report_sections +='</div>'

        let report_section_wrapper = $('#report-section-container')
        let count_report_item = report_section_wrapper.find('._section').length

        if (count_report_item == 0) { // if has data on report section editor

            $(this).text('- Remove report section').addClass('remove')
            report_section_wrapper.append(report_sections);

            let report_wpeditor_textarea = $('#report-section-container').find('textarea.report-section-wpeditor')
            report_wpeditor_textarea.each(function () {
                let report_wpeditor_id = $(this).attr('id')
                report_wpeditor_id = '#' + report_wpeditor_id
                append_wpeditor(report_wpeditor_id, true)
            })
        }
        else { // if don't has data on report section editor
            if (confirm('Remove all content of report section')) {
                $('#report-section-container ._section').each(function () {
                $(this).remove()
                })
                $(this).text('+ Add report section')
            }
            else {
                return false;
            }
        }
    });

  $(document).ready(function (e){
    let count_report_item = $('#report-section-container').find('._section').length

    if (count_report_item > 0) {
      $('#btn-add-report-option').text('- Remove report section').addClass('remove')
    }
  });

    var key_recom_container = $('#report-key-areas-field .key-areas-list')
    var row_recom_index = 0;
    if ($(".row-key-area").length) {
        row_recom_index = $(".row-key-area").length;
    }

    $(document).on('click', '#report-key-areas-field .add-key-area', function (e){

        row_recom_index = row_recom_index + 1;
        let btn_position = $(this).data('position')

        let row_recom = '<li id="row-key-area-'+ row_recom_index +'" class="row-key-area">'
            row_recom += '    <div class="key-title">'
            row_recom += '          <input type="text" class="form-control"'
            row_recom += '                  placeholder="Add key Area name"'
            row_recom += '                  name="report_key_areas['+ row_recom_index +']"'
            row_recom += '                  value="">'
            row_recom += '    </div>'
            row_recom += '    <div class="key-action">'
            row_recom += '        <span class="remove-row"><i class="fa-regular fa-circle-xmark"></i></span>'
            row_recom += '    </div>'
            row_recom += '</li>'

        if (btn_position == 'top') {
            key_recom_container.prepend(row_recom)
        }
        else if (btn_position == 'bottom') {
            key_recom_container.append(row_recom)
        }
    });

    $(document).on('click', '.row-key-area .key-action', function (e){
        $(this).closest('#report-key-areas-field .row-key-area').remove()
    });

    $(document).on('click', '.btn-toggle-advice-area', function (e){
        let advice_container = $(this).closest('.question-advice-row-container')
        $(this).toggleClass('active')
        advice_container.find('.visual-textarea-wrapper').slideToggle()
    });

    $(document).on('click', '.field-select2 .sf-products-list .item', function (e){
        let item_dropdown = $(this)
        let sf_product_id = item_dropdown.data('id')
        let item_select_input = '<li class="item-selected products-selected" data-id="'+ sf_product_id +'">'
            item_select_input +=    item_dropdown.text()
            item_select_input +=    '<input type="hidden" name="related_sf_products[]" value="'+ sf_product_id +'">'
            item_select_input +=    '<span class="remove-item"><i class="fa-solid fa-xmark"></i></span>'
            item_select_input +='</li>'

        let list_selected_area = item_dropdown.closest('.field-select2').find('.list-items-selected-area')

        if (! item_dropdown.hasClass('selected')) {

            item_dropdown.addClass('selected')
            list_selected_area.append(item_select_input)
        }
    });

    $(document).on('click', '.field-select2 .remove-item', function (e){
        e.stopPropagation();
        let field_select2_wrapper = $(this).closest('.field-select2')
        let item_selected = $(this).closest('.item-selected')
        let item_selected_id = item_selected.data('id')
        let list_items_dropdown = field_select2_wrapper.find('.list-items-dropdown .item')

        list_items_dropdown.each( function (e) {
            if ($(this).data('id') == item_selected_id) {
                $(this).removeClass('selected')
            }
        })

        item_selected.remove()
    });

    $(document).on('click', '.field-select2 .list-items-selected-area', function (e){
        let field_select2_wrapper = $(this).closest('.field-select2')
        let list_dropdown = field_select2_wrapper.find('.list-items-dropdown')
        list_dropdown.show()
    });

    async function markFeedbackSubmissionAnswers(instance, type = "rejected") {

        let quizId = instance.data("id");
        let parent_quiz_id = instance.data("group-id");
        let parent = $(`#main-container-${parent_quiz_id}_${quizId}`);
        let feedback = parent.find(".feedback-input").val();
        let assessmentId = $(`[name="assessment_id"]`).val();
        let submissionId = $('#submission_id').val();
        let organisationId = $('#organisation_id').val();
        // let userId = parent.find(`[name="user_id"]`).val();
        let quizPoint = parent.find(`[name="quiz_point"]`).val();

        await updateQuizStatusSusmission(instance, type, {
            feedback,
            assessment_id: assessmentId,
            submission_id: submissionId,
            organisation_id: organisationId,
            // user_id: userId,
            quiz_id: quizId,
            parent_quiz_id: parent_quiz_id,
            quiz_point: quizPoint,
            type,
        });
    }

    async function updateQuizStatusSusmission(instance, type, data = {}) {

        let quiz_row = instance.closest('.submission-view-item-row')
        let row_status = quiz_row.find('.quiz-status')

        const payload = {
        action: "update_quiz_status_submission",
        ...data,
        };

        let response = await $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: payload,
            beforeSend : function ( xhr ) {
                quiz_row.addClass('loading')
            },
            success:function(response){
                quiz_row.removeClass('loading')
            }
        });
        const { quiz_id, parent_id, status, message } = response;
        console.log(response);

        if (status == true) {
            row_status.removeClass('accepted')
            row_status.removeClass('rejected')
            row_status.addClass(type)
            row_status.find('strong').text(type)
        }
        else {
            alert(response.message)
        }

        return status;
    }

    $(".btn-save-feedback").on("click", function (e) {
        e.preventDefault();
        let btn = $(this)
        let message = btn.find('.message')
        let feedbacks_area = btn.closest('.feedbacks-area')
        let quizId = btn.data("id");
        let parent_quiz_id = btn.data("group-id");
        let parent = $(`#main-container-${parent_quiz_id}_${quizId}`);
        let feedback = parent.find(".feedback-input").val();
        let assessmentId = $(`[name="assessment_id"]`).val();
        let submissionId = $('#submission_id').val();
        let organisationId = $('#organisation_id').val();

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'save_quiz_feedback_submission',
                'feedback' : feedback,
                'assessment_id': assessmentId,
                'submission_id': submissionId,
                'organisation_id': organisationId,
                'quiz_id': quizId,
                'parent_quiz_id': parent_quiz_id,
            },
            beforeSend : function ( xhr ) {
                feedbacks_area.addClass('loading')
            },
            success:function(response){
                console.log(response);
                feedbacks_area.removeClass('loading')
                message.html(response.message).show()
            }
        });
        setTimeout(function() {
            message.hide()
        }, 10000)
    });

    $(".reject-button").on("click", async function (e) {
        e.preventDefault();
        await submit_feedback_submission($(this), "rejected");
    });

    $(".accept-button").on("click", async function (e) {
        e.preventDefault();
        await submit_feedback_submission($(this), "accepted");
    });

    async function submit_feedback_submission(btn, feedbackType) {
        let assessment_id = $("#assessment_id").val();
        // let user_id = $("#user_id").val();
        let submission_id = $("#submission_id").val();
        let organisation_id = $("#organisation_id").val();
        let parent_wrapper = btn.closest('.submission-admin-view-footer')

        const payload = {
            action: "final_accept_reject_assessment",
            assessment_id: assessment_id,
            // user_id: user_id,
            submission_id: submission_id,
            organisation_id: organisation_id,
            type: feedbackType,
        };

        let response = await $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: payload,
            beforeSend : function ( xhr ) {
                parent_wrapper.find('.button').addClass('loading')
            },
            success:function(response){
                parent_wrapper.find('.button').removeClass('loading')
            }
        });
        const { quiz_point, status, message } = response;

        console.log(response);

        alert(message);

        if (status == true) {
            $('#submitpost input[name="save"]').click();
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

    // upload additional files on admin fields
    $(document).on('change', ".additional-files", async function(e){

        let that = $(this);
        let file = e.target.files[0];
        let group_questions_id =  $(this).closest('.group-question-wrapper').data('id')
        let sub_question_id = $(this).closest('.question-row-container').data('id')
        let add_files_container = $(this).closest('.question-add-files-container')
        let message = add_files_container.find('.__message')
        let count_file_item = Date.now();

        var file_id_input = '';
        var file_item = '';
        var fileName = '';
        var filesList = add_files_container.find(".filesList");
        var new_file_id = '';
        var new_file_url = '';

        // Hide the message
        message.hide();

        for (var i = 0; i < this.files.length; i++){
            file_item = $('<span/>', {class: 'file-item'})
            file_item.hide()
            fileName = $('<a/>', {
                class: 'name',
                text: this.files.item(i).name,
                target: '_blank'
            });

            file_id_input  = '<input name="group_questions['+group_questions_id+'][list]['+sub_question_id+'][additional_files]['+ count_file_item +']" ';
            file_id_input += 'type="hidden" class="input-file-hiden additional-file-id-'+ count_file_item +'" value="" />';

            file_item.append(fileName)
                .append('<span class="file-delete"><i class="fa-solid fa-xmark"></i></span>')
                .append(file_id_input)

            filesList.append(file_item);

            await admin_upload_additional_files(file, that, count_file_item);

            new_file_id = filesList.find('.additional-file-id-'+ count_file_item).val()

            wp.media.attachment(new_file_id).fetch().then(function (data) {
                // preloading finished
                // after this you can use your attachment normally
                new_file_url = wp.media.attachment(new_file_id).get('url');
                fileName.attr('href', new_file_url)
            });

            file_item.show()

            // Show the message
            message.text('File Uploaded');
            message.show();
            // Hide the message after 10 seconds
            setTimeout(function() {
                message.hide();
            }, 10000);
        };
    });

    // EventListener for delete file item
    $(document).on('click', '.file-delete', function(){
        let btn = $(this)
        let add_files_container = btn.closest('.question-add-files-container')
        let message = add_files_container.find('.__message')
        let input_file_hiden = btn.parent().find('.input-file-hiden')
        let file_ID = input_file_hiden.val()

        let confirm_result = confirm("Do you want to remove this file?");
        if (confirm_result) {
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data:{
                    'action' : 'delete_additional_file_assessment',
                    'file_id' : file_ID,
                },
                beforeSend : function ( xhr ) {
                    // Hide the message
                    message.hide();
                },
                success:function(response){
                    btn.parent().remove()

                    // Show the message
                    message.text('File Deleted');
                    message.show();
                    // Hide the message after 10 seconds
                    setTimeout(function() {
                        message.hide();
                    }, 10000);
                }
            });
        }
    });

    async function admin_upload_additional_files(file, inputInstance, index) {
        let formData = new FormData();
        let fileUploaderWrap = inputInstance.closest(".question-add-files-container")

        formData.append("file", file)
        formData.append("action", 'upload_assessment_attachment')
        formData.append("security", ajax_object.security)

        let response = await $.ajax({
            type: 'POST',
            url: ajaxUrl,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend : function ( xhr ) {
                fileUploaderWrap.find('.uploading-wrapper').show()
                fileUploaderWrap.find('.btn-add-files-wrapper').addClass('not-allowed')
            },
            success:function(response){
                fileUploaderWrap.find('.uploading-wrapper').hide()
                fileUploaderWrap.find('.btn-add-files-wrapper').removeClass('not-allowed')
            }
        });

        const { status, message } = response;
        // toggleMessageWrap(message)

        if (status) {
            fileUploaderWrap.find('.additional-file-id-' + index).val(response?.attachment_id)
        } else {

        }
    }

    $(document).on('click', '.sas-blob-cta', function (e){

        let blobUrl = $(this).data('blob')

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'create_sas_blob_url_azure_ajax',
                'blob_url' : blobUrl,
            },
            beforeSend : function ( xhr ) {
                $(this).focus()
            },
            success:function(response){
                if (response.status) {
                    window.open(
                        response.sas_blob_url,
                        '_blank',
                      );
                } else {
                    alert(response.message)
                }
                $(this).focusout()
            }
        });
    });

    $(document).on('click', '.btn-lock-question', function (e){

        let question_wrapper = $(this).closest('#question-group-repeater .question')
        let is_locked_input = question_wrapper.find('input.is_locked_input')
        let locked_icon = $(this).find('.locked-icon');
        let lock_text = $(this).find('.lock-text');
        let btn_collapse = question_wrapper.find('.btn-expland-wrapper.active')
        let group_title = question_wrapper.find('input.group-question-admin-title')
        let question_title = question_wrapper.find('input.question-admin-title')

        if (question_wrapper.hasClass('disabled')) {
            // Unlock
            question_wrapper.removeClass('disabled')
            locked_icon.hide()
            lock_text.text('Lock')
            is_locked_input.val('0')
            group_title.removeAttr('tabindex')
            question_title.removeAttr('tabindex')
        }
        else {
            // Collapse Question before lock
            btn_collapse.click();
            // Lock
            question_wrapper.addClass('disabled')
            locked_icon.show()
            lock_text.text('Unlock')
            is_locked_input.val('1')
            group_title.attr('tabindex', '-1')
            question_title.attr('tabindex', '-1')
        }
    });

    $(document).on('change', '#is_assessment_completed', function (e){
        let checkbox = $(this)
        let all_questions = $('#question-group-repeater .question')

        if (checkbox.is(":checked")) {
            // Lock all questions
            all_questions.each(function(e) {
                if (!$(this).hasClass('disabled')) {
                    $(this).find('.btn-lock-question').click().addClass('disabled');
                }
            })
        }
        else {
            // Unlock all questions
            all_questions.each(function(e) {
                if ($(this).hasClass('disabled')) {
                    $(this).find('.btn-lock-question').click().removeClass('disabled');
                }
            })
        }
    });

    $(document).on('keyup change', '.field-select2 input.search-item', function (e){
        let keyword = $(this).val().toLowerCase();
        let member_items = $('.list-items-dropdown .item.member')

        member_items.filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
        });
    });

    // Ajax create List member dropdown
    $(document).on('change', '#select-org', function (e){
        let OrgId = $(this).val();
        let PostId = $('input#post_ID').val();
        let list_members = $('.field-select2 #list-members-dropdown');
        let member_options_box = $('#access-control-panel .member-options');

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'get_members_from_org_ajax',
                'organisation_id' : OrgId,
                'post_id' : PostId,
            },
            beforeSend : function ( xhr ) {
                member_options_box.addClass('loading')
            },
            success:function(response){
                if (response.status == true) {
                    list_members.html(response.list)
                }
                else {
                    alert(response.message)
                }
                member_options_box.removeClass('loading')
            }
        });
    });

    // Add Items to Assigned members List
    var members_count = 0;
    if ($('#assigned-members-list .member-item').length) {
        members_count = $(".group-question-wrapper").length;
    }
    $(document).on('click', '.field-select2 #list-members-dropdown .item', function (e){
        members_count = members_count + 1;
        let member_id = $(this).data('id');
        let member_name = $(this).text().trim();
        let member_item = null;
        let org_name = $(this).data('org-name').trim();
        let assigned_members_list = $('.assigned-members #assigned-members-list');
        let assigned_member_items = assigned_members_list.find('.member-item');
        let assigned_members_arr = [];

        member_item  = '<li class="member-item" data-id="'+ member_id +'">'
        member_item += '   <span>'
        member_item += '       <i class="fa-solid fa-user"></i>'
        member_item += '       <span class="member-name">'+ member_name +' - '+ org_name +'</span>'
        member_item += '   </span>'
        member_item += '   <span class="icon-delete-member"><i class="fa-regular fa-circle-xmark"></i></span>'
        member_item += '   <input type="hidden" name="assigned_members['+ members_count +'][id]" value="'+ member_id +'">'
        member_item += '   <input type="hidden" name="assigned_members['+ members_count +'][name]" value="'+ member_name +'">'
        member_item += '   <input type="hidden" name="assigned_members['+ members_count +'][org]" value="'+ org_name +'">'
        member_item += '</li>'

        assigned_member_items.each(function (e) {
            assigned_members_arr.push($(this).data('id'));
        })

        if (jQuery.inArray(member_id, assigned_members_arr) == -1) {
            assigned_members_list.prepend(member_item);
            $(this).addClass('selected')
        }
    });

    // Remove Assigned member
    $(document).on('click', '.member-item .icon-delete-member', function (e){
        let assigned_member_item = $(this).closest('.member-item');
        let member_id = assigned_member_item.data('id');
        let members_dropdown = $('#list-members-dropdown .item.member');

        members_dropdown.each(function (e) {
            if ($(this).data('id') == member_id) {
                $(this).removeClass('selected');
            }
        })
        assigned_member_item.remove()
    });

    // Refresh Member Data
    $(document).on('click', '#btn-refresh-members', function (e){
        let btn = $(this)
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'save_option_members_data_ajax',
                'clicked' : true,
            },
            beforeSend : function ( xhr ) {
                btn.addClass('loading')
            },
            success:function(response){
                btn.removeClass('loading')
                alert(response.message)
            }
        });
    });

    // Report Front page add logo
    $(document).on('click', '#report-add-logo', function(e){
        e.preventDefault();
        let logo_uploader = wp.media({
            title: 'Logo Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        }).on('select', function() {
            let attachment = logo_uploader.state().get('selection').first().toJSON();
            $('#front-page-logo-url').val(attachment.url);
            $('img#report-front-logo-preview').attr('src', attachment.url).show()
        })
        .open();
        $('#report-front-page #btn-remove-logo').addClass('active')
    });

    // Remove current Logo
    $(document).on('click', '#btn-remove-logo', function(e){
        e.preventDefault();
        if (! confirm("Do you want to remove this image?")) {
            return;
        }
        $('#front-page-logo-url').val(null);
        $('img#report-front-logo-preview').attr('src', null).hide()
    });

    // Report Front page add Background Image
    $(document).on('click', '#report-add-bg-img', function(e){
        e.preventDefault();
        let logo_uploader = wp.media({
            title: 'Background Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        }).on('select', function() {
            let attachment = logo_uploader.state().get('selection').first().toJSON();
            $('#front-page-bg-img-url').val(attachment.url);
            $('img#report-front-bg-img-preview').attr('src', attachment.url).show()
        })
        .open();
        $('#report-front-page #btn-remove-bg-img').addClass('active')
    });

    // Remove current Background Image
    $(document).on('click', '#btn-remove-bg-img', function(e){
        e.preventDefault();
        if (! confirm("Do you want to remove this image?")) {
            return;
        }
        $('#front-page-bg-img-url').val(null);
        $('img#report-front-bg-img-preview').attr('src', null).hide()
    });

    // Add Generic page to List
    $(document).on('click', '.btn-add-generic-page', function(e){
        let btn_add_page = $(this);
        let generic_page_wrapper = $(this).closest('.generic-page-wrapper')
        let data_position = $(this).data('position')
        let data_insert = $(this).data('insert')
        let textarea_id = 'generic-page-textarea-' + Date.now();
        let page_count = Date.now();

        let generic_page_item  = '<li id="generic-page-'+ data_position +'-'+ page_count +'" class="_section generic-page">'
            generic_page_item +=     '<h3 class="_heading">Generic page '+ data_position +' #<span class="page-index"></span></h3>'
            generic_page_item +=     '<input type="text" name="report_template[generic_page_'+ data_position +']['+ page_count +'][title]" placeholder="Add title">'
            generic_page_item +=     '<textarea id="'+ textarea_id +'" class="generic-page-wpeditor" name="report_template[generic_page_'+ data_position +']['+ page_count +'][content]" rows="10"></textarea>'
            generic_page_item +=     '<div class="add-row-block">'
            generic_page_item +=         '<a class="btn-remove-generic-page button_remove">Remove this row</a>'
            generic_page_item +=         '<a class="btn-add-generic-page button button-primary" data-position='+ data_position +' data-insert="bottom">+ Add row</a>'
            generic_page_item +=     '</div>'
            generic_page_item += '</li>'

        if (data_insert == 'top') {
            generic_page_wrapper.find('.generic-pages-list').prepend(generic_page_item)
        }
        else {
            $(this).closest('.generic-pages-list .generic-page').after(generic_page_item)
        }
        // Append WP editor
        append_wpeditor('#'+ textarea_id, true);

        // Renew pages index
        setTimeout(function() {
            Renew_Index_Generic_Page_Report(btn_add_page);
        }, 300)
    });

    // Remove a Generic page row
    $(document).on('click', '.btn-remove-generic-page', function(e){
        e.preventDefault();
        let btn_remove_page = $(this);
        if (! confirm("Do you want to remove this page?")) {
            return;
        }
        let currnet_row = $(this).closest('.generic-pages-list .generic-page')
        currnet_row.addClass('removing')
        setTimeout(function() {
            currnet_row.remove();
        }, 300);
    });

    // Renew the Index of the Generic pages
    function Renew_Index_Generic_Page_Report(button) {
        let pages_wrapper = button.closest('.generic-page-wrapper')
        let all_pages_index = pages_wrapper.find('.page-index')
        let count_index = 1;
        all_pages_index.each(function(e) {
            $(this).text(count_index);            
            count_index++;
        })
    }

    // Show up Recommentdation WP editor
    $(document).on('click', '.btn-add-recommentdation', function(e){
        e.preventDefault();
        let recommentdation_wrapper = $(this).closest('.recommentdation')

        if ($(this).hasClass('active')) {
            $(this).removeClass('active')
            $(this).find('.text').text('Add Recommentdation')
            recommentdation_wrapper.find('._wpeditor').removeClass('active').slideUp()
        }
        else {
            $(this).addClass('active')
            $(this).find('.text').text('Hide Recommentdation')
            recommentdation_wrapper.find('._wpeditor').addClass('active').slideDown()
        }
    });

    // Click to Add all Chart Image to Report PDF file
    $(document).on('click', '#btn-add-charts-report', function(e){
        e.preventDefault();
        let btn = $(this)
        let all_canvas = $('.dashboard-charts-list .chart canvas')
        let data_img_arr = [];
        let reportId = $('input#post_ID').val()
        let existing_chart_imgs = report_chart_imgs_meta;

        if (existing_chart_imgs) {
            if ( existing_chart_imgs.Framework 
                || existing_chart_imgs.Implementation
                || existing_chart_imgs.Review
                || existing_chart_imgs.Overall ) {
                    let userConfirmed = confirm('Chart images already exist in the Report. Would you like to replace them?');
                    // user don't want to replace charts
                    if (!userConfirmed) {
                        return;
                    }
            }
        }

        all_canvas.each(function(e) {
            let img_data = $(this)[0].toDataURL("image/png", 1.0);
            data_img_arr.push({
                name: $(this).data('key'),
                data: img_data,
            });
        })
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'save_dashboard_charts_image_url',
                'data_imgs' : data_img_arr,
                'report_id' : reportId,
            },
            beforeSend : function ( xhr ) {
                btn.addClass('loading')
            },
            success:function(response){
                btn.removeClass('loading')
                // console.log(response);
                alert(response.message);
            }
        });
    });

    // Click to Download Framework Chart Image
    $(document).on('click', '.btn-download-chart', function(e){
        e.preventDefault();
        let canvas = $(this).closest('.chart').find('canvas');
        let key_area = canvas.data('key');
        image = canvas[0].toDataURL("image/jpg", 1.0);
        let link = document.createElement('a');
        link.download = key_area+'-dashboard-chart-'+ Date.now() +'.jpg';
        link.href = image;
        link.click();
    });

    // Click to Create Report
    $(document).on('click', '#btn-create-report', function(e){
        e.preventDefault();
        let btn = $(this)
        let submissionId = $("#submission_id").val();

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'create_comprehensive_report',
                'submission_id': submissionId,
            },
            beforeSend : function ( xhr ) {
                btn.addClass('loading')
            },
            success:function(response){
                btn.removeClass('loading')
                if (response.report_id) {
                    $('#btn-view-report')
                        .addClass('show')
                        .attr('href', '/wp-admin/post.php?post='+ response.report_id +'&action=edit')
                }
                if (response.status == false) {
                    alert(response.message)
                }
            }
        });
    });

    // Click to Add emails to Blacklist
    $(document).on('click', '#btn-add-emails-to-blacklist', function(e){
        e.preventDefault();
        let blacklist_wrapper = $('#assessment-blacklist')
        let emails = blacklist_wrapper.find('textarea#blacklist-emails-area').val() 
        let bl_message_error = blacklist_wrapper.find('.bl-message-error')
        let bl_message_success = blacklist_wrapper.find('.bl-message-success')
        let current_blacklist = blacklist_wrapper.find('ul#blacklist')
        let current_bl_items = blacklist_wrapper.find('ul#blacklist .blacklist-item .text')
        let current_bl_items_arr = []

        if (emails === '') {
            bl_message_error.html('<span>The emails is required.</span>')
            .show()
            return
        } 
        // Push current blacklist items to an array
        current_bl_items.each(function(e) {
            current_bl_items_arr.push($(this).text().replace(/\s/g, "").trim())
        })
        // Push emails to an array
        let emails_arr = emails.replace(/\s/g, "").split(',');
        let email_regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        // Check valid email
        for (let i = 0; i < emails_arr.length; i++) {
            let email = emails_arr[i];
            let position_at = email.indexOf("@"); // Using indexOf to find the first occurrence of "@"
            let position_last_at = email.lastIndexOf("@"); // Using lastIndexOf to find the last occurrence of "@"

            // Check if "@" appears more than once
            if (position_at !== position_last_at) {
                // If "@" appears more than once, it's invalid
                bl_message_error.html('<span>Please seperate each email by a comma.</span>').show();
                return; // Return to stop further processing
            }
            else {
                // If email are valid, hide the error message
                bl_message_error.hide();
            }
            // Check valid email
            if (email_regex.test(email)) {
                // If email are valid, hide the error message
                bl_message_error.hide();
            }
            else {
                bl_message_error.html('<span><strong>'+ email +'</strong> is an invalid email address.</span>').show();
                return; // Return to stop further processing
            }
            // Trim whitespace from the email address
            emails_arr[i] = email.trim();
        }
        
        let bl_item = '';
        bl_message_error.html('')
        bl_message_success.html('')
        // Add emails to list
        for (let i = 0; i < emails_arr.length; i++) {
            bl_item  = '<li class="blacklist-item" tabindex="0">' 
            bl_item +=      '<span class="text">'+ emails_arr[i] +'</span>'
            bl_item +=      '<span class="btn-remove-blacklist-item" tabindex="0" role="button">'
            bl_item +=          '<i class="fa-solid fa-xmark"></i>'
            bl_item +=      '</span>'
            bl_item +=      '<input type="hidden" name="blacklist_emails[]" value="'+ emails_arr[i] +'">'
            bl_item +=      '<div class="confirm-remove">'
            bl_item +=          'Do you want to remove this email?'
            bl_item +=          '<a class="btn-confirmed-remove" tabindex="0" role="button">Remove</a>'
            bl_item +=          '<a class="btn-not-remove" tabindex="0" role="button">No</a>'
            bl_item +=      '</div>'
            bl_item +=  '</li>'

            // Email existing message
            if (current_bl_items_arr.length > 0 && current_bl_items_arr.indexOf(emails_arr[i]) !== -1) {
                bl_message_error.append('<span><strong>'+ emails_arr[i] +'</strong> is existing in Blacklist.</span><br>')
                .show()
            }
            // Add email success message
            else {
                current_blacklist.prepend(bl_item)
                bl_message_success.append('<span>Add email <strong>'+ emails_arr[i] +'</strong> successfully.</span><br>')
                .show()
            }
        }
    });

    // Click to show confirm remove blacklist item
    $(document).on('click', '.btn-remove-blacklist-item', function(e){
        e.preventDefault();
        let bl_item = $(this).closest('.blacklist-item')
        bl_item.find('.confirm-remove').show()
    });
    // Click to remove blacklist item
    $(document).on('click', '.blacklist-item .btn-confirmed-remove', function(e){
        e.preventDefault();
        let bl_item = $(this).closest('.blacklist-item')
        bl_item.remove()
    });
    // Click to hide confirm remove message
    $(document).on('click', '.blacklist-item .btn-not-remove', function(e){
        e.preventDefault();
        let bl_item = $(this).closest('.blacklist-item')
        bl_item.find('.confirm-remove').hide()
    });

    // require assessment admin fields
    $('input.group-question-admin-title').prop('required',true);
    $('input.question-admin-title').prop('required',true);
    $('input.choice-item-answer').prop('required',true);

    //Share Reports
    if($('.post-type-reports').length > 0){
      $('.select-users-report').select2({placeholder: "Select users",});
      $('.btn-share-report').on('click',function(){
         var users = $('.select-users-report').val();
         var post_id = $('input[name="post_share"]').val();
         //ajax send report to Users
         sendReporttoUsers(this,users,post_id);
      });
      function sendReporttoUsers(ele,users,post_id){
          //if(users.length > 0){
            $.ajax({
              type: 'POST',
              url: ajaxUrl,
              data:{
                  'action' : 'send_report_to_users',
                  'users' : users,
                  'post_id' : post_id
              },
              beforeSend : function ( xhr ) {
                $(ele).addClass('disabled');
                $('.report-message').html();
              },
              success:function(res){
                console.log(res);
                if(res.status){
                  $('.report-message').html('<span class="success">'+res.message+'</span>');
                }else{
                  $('.report-message').html('<span class="error">'+res.message+'</span>');
                }
                $(ele).removeClass('disabled');
              }
            });
          }
      //}
    };
    
    // Initialize sorting order to descending
    var ascending = false;
    // Attach click event to the "Org Name" header
    $(document).on("click", "#sort-org-name", function () {
        $(this).toggleClass('active')
        // Get all table rows
        let rows = $(".saturn-invites-table tbody tr").get();
        // Sort the rows based on the text content of the "Org Name" column
        rows.sort(function (a, b) {
            var A = $(a).find('.org-name').text().toUpperCase();
            var B = $(b).find('.org-name').text().toUpperCase();

            if (A < B) {
                return ascending ? -1 : 1;
            }
            if (A > B) {
                return ascending ? 1 : -1;
            }
            return 0;
        });
        // Clear current rows in the table body
        $(".saturn-invites-table tbody").empty();

        // Append sorted rows with left to right animation
        $.each(rows, function (index, row) {
            let row_number = $(row).find('td.index')
            row_number.text(index + 1)
            $(row).css("opacity", "0.3")
                .appendTo(".saturn-invites-table tbody")
                .animate({ opacity: 1 }, 300);
        });
        // Toggle the sorting order for the next click
        ascending = !ascending;
    });

});
