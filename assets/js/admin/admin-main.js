
jQuery(document).ready(function ($) {
    const ajaxUrl = ajax_object.ajax_url;
    const mainWrapper = $('#question-group-repeater');

    let groupCount = mainWrapper.find(".group-question-wrapper").length;
    // Add Comprehensive Group Question
    $(document).on("click", "#add-group-row", function () {
        const groupParentRepeater = $("#question-group-repeater");
        groupCount += 1;
        const groupQuestionsHtml = (`
            <div class="group-question-wrapper question" id="question-group-row-${groupCount}" data-id="${groupCount}">
                <input class="is_locked_input" type="hidden" name="group_questions[${groupCount}][is_locked]" value="0">
                <div class="question-wrapper-top">
                    <span class="button btn-remove-group">- Remove Group
                        <div class="remove-message">Are you sure?
                            <span class="btn-remove">Remove</span>
                            <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                        </div>
                    </span>
                    <span class="icon-toggle"></span>
                </div>
                <h3 class="admin-question-group-label">Question #${groupCount}</h3>
                <input type="text" class="group-question-admin-title form-control"
                    name="group_questions[${groupCount}][title]" 
                    placeholder="Group Question Title" required/>
                <div class="question-field-container"></div>
                <div class="add-row-field">
                    <span class="add-question-row button button-primary">Add Sub Question</span>
                </div>
                <div class="question-wrapper-bottom">
                    <a class="btn-lock-question" role="button">
                        <img class="locked-icon" 
                            src="/wp-content/plugins/wp-assessment/assets/images/img-lock.png">
                        <span class="lock-text">Lock</span>
                    </a>
                    <a class="btn-expand-wrapper" role="button">
                        <span class="text">Expand Group</span>
                        <span class="icon-chevron-down">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </a>
                </div>
            </div>
        `);
        groupParentRepeater.append(groupQuestionsHtml);
        renewAllQuestionsIndex(); // Renew Index
    });

    // Template for question title
    const getTitleRow = (groupId, rowCount) => (`
        <div class="question-input-area-container">
            <div class="question-title">
                <label>Title</label>
                <textarea rows="2" class="question-admin-title form-control"
                    name="group_questions[${groupId}][list][${rowCount}][sub_title]"
                    placeholder="Question Title"></textarea>
            </div>
            <div class="sub-point question-row-points-container">
                <label>Question Point</label>
                <input id="question-point-${groupId}-${rowCount}"
                    type="number" step="0.1" class="question-point-input"
                    name="group_questions[${groupId}][list][${rowCount}][point]" value=""/>
            </div>
        </div>
    `);

    // Template for description
    const getDescriptionRow = (editorId, groupId, rowCount) => (`
        <div class="question-row-desc question-row">
            <label>Description</label>
            <div class="visual-textarea-wrapper">
                <textarea id="${editorId}" name="group_questions[${groupId}][list][${rowCount}][description]"
                    rows="12" class="sub-question-wpeditor"></textarea>
            </div>
        </div>
    `);

    // Template for answer choices
    const getAnswerChoicesRow = (groupId, rowCount) => (`
        <div class="multi-choices-container question-row">
            <label>Answer Choices</label>
            <button class="button add-multi-choice-btn" type="button" data-group-id="${groupId}" data-id="${rowCount}">
                <span>Add Choice</span>
            </button>
            <div class="multi-choices-table-container">
                <table class="multi-choices-table" id="multi-choices-table-${groupId}-${rowCount}">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    `);

    // Template for client comment area checkbox
    const getClientCommentCheckbox = (groupId, rowCount) => (`
        <div class="checkbox-field question-row">
            <input id="desc-checkbox-${groupId}-${rowCount}" type="checkbox"
                name="group_questions[${groupId}][list][${rowCount}][is_description]" value="1"/>
            <label for="desc-checkbox-${groupId}-${rowCount}">Show client comment area</label>
        </div>
    `);

    // Template for supporting docs checkbox
    const getSupportingDocsCheckbox = (groupId, rowCount) => (`
        <div class="checkbox-field question-row">
            <input id="upload-docs-checkbox-${groupId}-${rowCount}" type="checkbox"
                name="group_questions[${groupId}][list][${rowCount}][supporting_doc]" value="1"/>
            <label for="upload-docs-checkbox-${groupId}-${rowCount}">Show upload supporting documentation area</label>
        </div>
    `);

    // Template for advice section
    const getAdviceRow = (editorId, groupId, rowCount) => (`
        <div class="question-advice-container question-row">
            <div class="btn-toggle-advice-area">
                <label>Advice (Tips and examples)</label>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </div>
            <div class="advice-editor-wrapper">
                <textarea id="${editorId}" class="form-control advice-area-wpeditor"
                    name="group_questions[${groupId}][list][${rowCount}][advice]" rows="10"></textarea>
            </div>
        </div>
    `);

    // Template for additional files section
    const getAdditionalFilesRow = (groupId, rowCount) => (`
        <div class="question-add-files-container question-row">
            <div class="btn-add-files-wrapper">
                <label>Additional Files</label>
                <label class="additional-files-label" for="additional-files-${groupId}-${rowCount}">
                    <span class="button" role="button" aria-disabled="false">
                        <span class="icon"><i class="fa-solid fa-file-arrow-up"></i></span>
                        <span>Upload Files</span>
                    </span>
                </label>
                <input id="additional-files-${groupId}-${rowCount}" class="additional-files" type="file" name="file[]" multiple/>
                <div class="uploading-wrapper">
                    <img src="../wp-content/plugins/wp-assessment/assets/images/front/Spinner-0.7s-200px.svg" alt="">
                </div>
                <div class="_message">File uploaded</div>
            </div>
            <div class="filesList"></div>
        </div>
    `);

    // Template for key areas
    const getKeyAreasRow = (groupId, rowCount) => (`
        <div class="question-key-areas">
            <label class="col-12"><strong>Select Key Area</strong></label>
            <select id="select-key-area-${groupId}-${rowCount}" class="select-key-area"
                name="group_questions[${groupId}][list][${rowCount}][key_area]">
                <option value="">Choose Key Area</option>
            </select>
        </div>
    `);
    
    let rowCount = mainWrapper.find(".group-question-wrapper .question-row-container").length;
    // Add the Sub Question Row
    $(document).on("click", ".add-question-row", function () {
        rowCount += 1;
        const groupQuestionsWrapper = $(this).closest(".group-question-wrapper");
        const groupId = groupQuestionsWrapper.attr("data-id");
        const wpDescEditorId = `wp-editor-question-${Date.now()}`;
        const wpAdviceEditorId = `question-advice-row-${Date.now()}`;
        const rowParent = groupQuestionsWrapper.find(".question-field-container");

        // Add classes to toggle visibility
        groupQuestionsWrapper.addClass('toggle');
        groupQuestionsWrapper.find('.question-wrapper-top').addClass('active');
        groupQuestionsWrapper.find('.btn-expand-wrapper').addClass('active');

        const questionRowHtml = (`
            <div class="question-row-container" id="question-main-row-${groupId}-${rowCount}" data-id="${rowCount}">
                <div class="question-name">
                    <h4 class="admin-question-row-label">Sub Question ${groupId}.${rowCount}</h4>
                    <span class="button btn-remove-question">- Remove Question
                        <div class="remove-message from-left">Are you sure? 
                            <span class="btn-remove">Remove</span>
                            <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                        </div>
                    </span>
                </div>
                ${getTitleRow(groupId, rowCount)}
                ${getDescriptionRow(wpDescEditorId, groupId, rowCount)}
                ${getAnswerChoicesRow(groupId, rowCount)}
                ${getClientCommentCheckbox(groupId, rowCount)}
                ${getSupportingDocsCheckbox(groupId, rowCount)}
                ${getAdviceRow(wpAdviceEditorId, groupId, rowCount)}
                ${getAdditionalFilesRow(groupId, rowCount)}
                ${getKeyAreasRow(groupId, rowCount)}
            </div>
        `);

        rowParent.append(questionRowHtml); // Append the new question to the container
        renderWpVisualEditor($(`#${wpDescEditorId}`), false); // Render description visual editor
        renderWpVisualEditor($(`#${wpAdviceEditorId}`), false); // Render advice visual editor
        renderQuestionKeyAreas($(`#select-key-area-${groupId}-${rowCount}`)); // Render Key Area
        renewAllQuestionsIndex(); // Renew Index
    });

    // Template for question title
    const getTitleRow_Simple = (index) => (`
        <h3 class="admin-question-group-label">Question #${index}</h3>
        <input type="text" class="question-admin-title form-control"
            name="group_questions[${index}][title]"
            value="" placeholder="Question Title" required/>
    `);

    // Template for description editor
    const getDescriptionRow_Simple = (editorId, index) => (`
        <div class="question-row-desc question-row">
            <label>Description</label>
            <div class="visual-textarea-wrapper">
                <textarea id="${editorId}" name="group_questions[${index}][description]"
                    rows="12" class="question-wpeditor"></textarea>
            </div>
        </div>
    `);

    // Template for answer choices
    const getChoicesRow_Simple = (index) => (`
        <div class="multi-choices-container question-row">
            <label>Answer Choices</label>
            <button class="button add-multi-choice-simple" type="button" data-id="${index}">Add Choice</button>
            <div class="multi-choices-table-container">
                <table class="multi-choices-table" id="multi-choice-table-${index}">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    `);

    // Template for comment area checkbox
    const getCommentCheckbox_Simple = (index) => (`
        <div class="checkbox-field question-row">
            <input id="desc-checkbox-${index}" type="checkbox"
                name="group_questions[${index}][is_description]" value="1" />
            <label for="desc-checkbox-${index}">Show client comment area</label>
        </div>
    `);

    // Template for advice editor
    const getAdviceRow_Simple = (editorId, index) => (`
        <div class="question-advice-container">
            <label>Advice (Tips and examples)</label>
            <div class="advice-editor-wrapper">
                <textarea id="${editorId}" class="advice-area-wpeditor"
                    name="group_questions[${index}][advice]" rows="10"></textarea>
            </div>
        </div>
    `);
    
    let grSimpleCount = mainWrapper.find(".simple-question-container").length;
    // Add Simple Question Row
    $(document).on("click", "#add-simple-row", function () {
        // Increment the count for the new question
        grSimpleCount += 1;
        const questionMainWrapper = $("#question-group-repeater");
        // Generate unique IDs for editors
        const wpDescEditorId = `wp-editor-question-${Date.now()}`;
        const wpAdviceEditorId = `wp-advice-editor-${Date.now()}`;
    
        // Generate the complete HTML for the new question
        const simpleQuestionHTML = (`
            <div id="question-main-row-${grSimpleCount}" class="simple-question-container question question-row-container toggle">
                <input class="is_locked_input" type="hidden" name="group_questions[${grSimpleCount}][is_locked]" value="0">
                <div class="question-wrapper-top active">
                    <span class="button btn-remove-question">- Remove Question
                        <div class="remove-message">Are you sure? 
                            <span class="btn-remove">Remove</span>
                            <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                        </div>
                    </span>
                    <span class="icon-toggle"></span>
                </div>
                ${getTitleRow_Simple(grSimpleCount)}
                ${getDescriptionRow_Simple(wpDescEditorId, grSimpleCount)}
                ${getChoicesRow_Simple(grSimpleCount)}
                ${getCommentCheckbox_Simple(grSimpleCount)}
                ${getAdviceRow_Simple(wpAdviceEditorId, grSimpleCount)}
                <div class="question-wrapper-bottom">
                    <a class="btn-lock-question" role="button">
                        <img class="locked-icon" 
                            src="/wp-content/plugins/wp-assessment/assets/images/img-lock.png">
                        <span class="lock-text">Lock</span>
                    </a> 
                    <a class="btn-expand-wrapper active" role="button">
                        <span class="text">Collapse Question</span>
                        <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                    </a>
                </div>
            </div>
        `);
    
        questionMainWrapper.append(simpleQuestionHTML); // Append the new question to the container
        renderWpVisualEditor($(`#${wpDescEditorId}`), false); // Render description visual editor
        renderWpVisualEditor($(`#${wpAdviceEditorId}`), false); // Render advice visual editor
        renewAllQuestionsIndex();
    });

    // Save all changes by trigger WP save button
    $(document).on("click", "#btn-save-changes", function (e) {
        e.preventDefault(); // Prevent default behavior
        const saveButton = $('#submitpost input[type=submit][name=save]');
        const inputsRequired = mainWrapper.find('input[required]');         
        let isValid = true; // Flag to track validation status

        // Validate required inputs
        inputsRequired.each(function () {
            const value = $(this).val().trim();
            if (!value || value === '') {
                $(this).focus(); // Highlight the invalid input
                isValid = false; // Set flag to invalid
                return false; // Break out of the .each() loop
            }
        });

        if (!isValid) {
            setTimeout(() => {
                alert("Please fill in all required fields.");
            }, 200);
            return; // Stop further execution if validation fails
        }
        if (saveButton.length > 0) {
            // Trigger the save action
            saveButton.click();
            // Disable the button to prevent multiple clicks
            $(this).addClass('loading').find('.text').text("Saving...");
        } 
        else {
            console.error("Save button not found.");
            alert("Unable to save changes. Please try again.");
        }
    });

    function renderQuestionKeyAreas(select_key_area) {
        let key_area_input = $('.key-areas-list .key-area-input');
        key_area_input.each(function(e) {
            let option_item = '<option value="'+ $(this).val() +'">'+ $(this).val() +'</option>';
            select_key_area.append(option_item);
        })
    }

    function renderWpVisualEditor(textarea, isMediaButton) {
        // Ensure the wrapper is valid
        const editors = $(textarea);
        if (!editors.length) {
            console.warn('No editors found for the given textarea:', textarea);
            return;
        }
        // Common TinyMCE configuration
        const tinymceConfig = {
            wpautop: true,
            plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
            toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright alignjustify | link unlink | fullscreen | wp_adv',
            toolbar2: 'formatselect forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
            fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
        };
        // Iterate and initialize editors
        editors.each(function () {
            const editorId = $(this).attr('id');
            // Initialize the editor
            wp.editor.initialize(editorId, {
                tinymce: tinymceConfig,
                quicktags: true,
                mediaButtons: isMediaButton,
            });
        });
    }

    function toggleQuestionState(wrapper, action) {
        const isGroup = wrapper.hasClass('group-question-wrapper');
        const btn = wrapper.find('.btn-expand-wrapper');
        const top = wrapper.find('.question-wrapper-top');
    
        if (action === 'collapse') {
            btn.removeClass('active');
            top.removeClass('active');
            btn.find('.text').text(isGroup ? 'Expand Group' : 'Expand Question');
        } else {
            btn.addClass('active');
            top.addClass('active');
            btn.find('.text').text(isGroup ? 'Collapse Group' : 'Collapse Question');
        }
        wrapper.toggleClass('toggle', action === 'expand');
    }

    $(document).on("click", ".question-wrapper-top", function () {
        const wrapper = $(this).closest('#question-template-wrapper .question');
        const action = $(this).hasClass('active') ? 'collapse' : 'expand';
        toggleQuestionState(wrapper, action);
    });
    
    $(document).on("click", ".btn-expand-wrapper", function () {
        const wrapper = $(this).closest('#question-template-wrapper .question');
        const action = $(this).hasClass('active') ? 'collapse' : 'expand';
        toggleQuestionState(wrapper, action);
    
        if (action === 'collapse') {
            $('html, body').animate({
                scrollTop: wrapper.offset().top - 50
            }, 100);
        }
    });

    // Variable to store the previously selected value
    let previousTemplate = $("input[type=radio][name=assessment_template]:checked").val();

    $(document).on("change", "input[type=radio][name=assessment_template]", function () {
        const existingQuestions = mainWrapper.find('.question');
        const actionsBlock = $('#actions-row-block');
        const selectedTemplate = $(this).val();
        // Handle existing questions
        if (existingQuestions.length > 0) {
            const userConfirmed = confirm(
                'If you change the assessment template, all questions in the current template will be removed.'
            );
            if (!userConfirmed) {
                // Restore the previously selected radio button
                $(`input[type=radio][name=assessment_template][value="${previousTemplate}"]`).prop("checked", true);
                return; // Exit if user cancels the action
            }
            // Remove existing questions if confirmed
            existingQuestions.remove();
        }
        // Update the previously selected template
        previousTemplate = selectedTemplate; 
        // Remove existing buttons
        actionsBlock.find("#add-simple-row, #add-group-row").remove(); 
        // Append appropriate button based on selected template
        if (selectedTemplate === 'Comprehensive Assessment') {
            actionsBlock.append(
                '<span id="add-group-row" class="button button-primary button-large">Add Group Question</span>'
            );
        } else if (selectedTemplate === 'Simple Assessment') {
            actionsBlock.append(
                '<span id="add-simple-row" class="button button-primary button-large">Add Simple Question</span>'
            );
        }
    });

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

    function renewAllQuestionsIndex() {
        let groupQuestions = mainWrapper.find('.question');
        $(groupQuestions).each(function(grIndex, groupRow) {            
            grIndex = grIndex + 1;
            $(groupRow).find('.admin-question-group-label').first().text(`Question #${grIndex}`);
            let subQuestions = $(groupRow).find('.question-row-container');
            $(subQuestions).each(function(subIndex, subRow) {
                subIndex = subIndex + 1;
                $(subRow).find('.admin-question-row-label').first().text(`Sub Question ${grIndex}.${subIndex}`);
            });
        });
    }

    $(document).on("click", ".btn-remove-group", function (e) {
        e.stopPropagation();
        let remove_message = $(this).find('.remove-message');
        remove_message.toggleClass('active');
    });

    $(document).on("click", ".btn-remove-group .btn-remove", function (e) {
        e.stopPropagation();
        let groupRow = $(this).closest('.group-question-wrapper');
        groupRow.addClass('removing');
        setTimeout(() => {
            groupRow.remove();
        }, 200);
        setTimeout(() => {
            renewAllQuestionsIndex();
        }, 600);
    });

    $(document).on("click", ".btn-remove-question", function (e) {
        e.stopPropagation()
        let remove_message = $(this).find('.remove-message')
        remove_message.toggleClass('active')
    });

    $(document).on("click", ".btn-remove-question .btn-remove", function (e) {
        e.stopPropagation();
        let group_question_wrapper = $(this).closest('.group-question-wrapper');
        let questionRow = $(this).closest('.question-row-container');

        questionRow.addClass('removing');
        setTimeout(() => {
            questionRow.remove();
            let num_question_row = group_question_wrapper.find('.question-row-container').length
            if (num_question_row == 0) {
                if (group_question_wrapper.hasClass('toggle')) {
                    group_question_wrapper.removeClass('toggle')
                }
                let btn_expand = group_question_wrapper.find('.btn-expand-wrapper.active')
                let wrapper_top = group_question_wrapper.find('.question-wrapper-top.active')

                wrapper_top.removeClass('active')
                btn_expand.removeClass('active')
                btn_expand.find('.text').text('Expand Group')
            }
        }, 200);
        setTimeout(() => {
            renewAllQuestionsIndex();
        }, 600);
    });

    $(document).on("click", ".icon-close", function (e) {
        e.stopPropagation()
        $(this).closest('.remove-message').removeClass('active');
    });

    $(document).on("click", ".btn-remove-choice", function () {
        $(this).parents("tr").remove();
    });

    $(document).on("click", ".add-multi-choice-btn", function () {
        let wrapper = $(this).closest('.multi-choices-container');
        let currentId = $(this).data("id");
        let groupId = $(this).data("group-id");
        let table = wrapper.find(`table#multi-choices-table-${groupId}-${currentId}`);
        let choiceIndex = Date.now();

        let table_row_html  = (`
            <tr class="multi-choice-list-item">
                <td><label>Answer</label><input type="text" name="group_questions[${groupId}][list][${currentId}][choice][${choiceIndex}][answer]"/></td>
                <td><label>Point</label><input type="number" step="0.1" name="group_questions[${groupId}][list][${currentId}][choice][${choiceIndex}][point]"/></td>
                <td><label>True</label><input type="checkbox" name="group_questions[${groupId}][list][${currentId}][choice][${choiceIndex}][is_correct]"/></td>
                <td><label></label><span class="button btn-remove-choice">Remove</span></td>
            </tr>
        `);
        table.find('tbody').append(table_row_html)
    });

    $(document).on("click", ".add-multi-choice-simple", function () {
        let wrapper = $(this).closest('.multi-choices-container');
        let currentId = $(this).data("id");
        let table = wrapper.find(`table#multi-choice-table-${currentId}`);
        let choiceIndex = Date.now();

        let table_row_html = (`
            <tr class="multi-choice-list-item">
                <td><label>Answer</label><input type="text" class="choice-item-answer" name="group_questions[${currentId}][choice][${choiceIndex}][answer]"/></td>
                <td><label>True</label><input type="checkbox" name="group_questions[${currentId}][choice][${choiceIndex}][is_correct]"/></td>
                <td><label></label><span class="button btn-remove-choice">Remove</span></td>
            </tr>
        `);
        table.find('tbody').append(table_row_html);
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
        let advice_container = $(this).closest('.question-advice-container')
        $(this).toggleClass('active')
        advice_container.find('.advice-editor-wrapper').slideToggle(200);
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

    $(document).on('keyup', '#search-dropdown-items', function() {
        let field_select2_wrapper = $(this).closest('.field-select2')
        let list_dropdown = field_select2_wrapper.find('.list-items-dropdown')
        let value = $(this).val().toLowerCase();
        
        // Filter the list items based on input
        list_dropdown.find('li.item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $(document).on('change', "select.select-quiz-status", async function (e) {
        e.preventDefault();
        const quizStatus = $(this).val();
        const quizId = $(this).data("quiz-id");
        const groupId = $(this).data("group-id");
        const wrapper = $(this).closest('.submission-view-item-row');
        const assessmentId = $('input#assessment_id').val();
        const submissionId = wrapper.data('submission');
        const organisationId = $('input#organisation_id').val();
        let allQuizzesStatus = [];

        if (!quizStatus || quizStatus === '' || quizStatus === null) return;

        $(".submission-view-item-row select.select-quiz-status").each(function() {
            let groupId = $(this).data("group-id");
            let quizId = $(this).data("quiz-id");
            let status = $(this).val();

            allQuizzesStatus.push({
                group_id: groupId,
                quiz_id: quizId,
                status: status
            });
        });

        let response = await $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: {
                action: "update_quiz_status_submission",
                assessment_id: assessmentId,
                submission_id: submissionId,
                organisation_id: organisationId,
                quiz_id: quizId,
                parent_id: groupId,
                quiz_status: quizStatus,
                all_quizzes_status: allQuizzesStatus,
            },
            beforeSend : function ( xhr ) {
                wrapper.addClass('loading');
            },
            success:function(response){
                wrapper.removeClass('loading');
            }
        });
        const { status, message, saved_status, status_class } = response;
        console.log(response);

        if (status == true) {
            wrapper.find('.quiz-status strong').removeClass().addClass(status_class).text(saved_status);
        }
        else {
            alert(message);
        }

    });

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

        setTimeout(() => {
            alert(message);
        }, 100);

        if (status == true) {
            $('#submitpost input[name="save"]').click();
            return true;
        }
    }

    $(document).on("click", ".btn-update-review-status", function (e) {
        const $thisBtn = $(this);
        const $assessmentId = $("input#assessment_id").val();
        const $submissionId = $("input#submission_id").val();
        const $reviewStatus = $thisBtn.data('status').trim();
        const $wrapper = $thisBtn.closest('.submission-admin-view-footer');
        const $currentStatusField = $wrapper.find('.current-status strong');
        const $subInfoWrapper = $('#submitted_info_view');
        
        if ($reviewStatus == $currentStatusField.text().trim()) {
            alert("The submission already has this status.");
            return;
        }

        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: {
                action: "update_submission_review_status_ajax",
                assessment_id: $assessmentId,
                submission_id: $submissionId,
                review_status: $reviewStatus,
            },
            beforeSend : function ( xhr ) {
                $wrapper.addClass('loading');
            },
            success:function(response){
                const {message, reviewed_status, status_class, status} = response;
                if (status === true) {
                    $wrapper.removeClass('loading');
                    $currentStatusField.removeClass().addClass(status_class).text(reviewed_status);
                    $subInfoWrapper.find('.status strong').removeClass().addClass(status_class).text(reviewed_status);
                }
                console.log(response);
                setTimeout(() => {
                    alert(message);
                }, 100);
            }
        });
    });

    $(".input-weighting").on("change", function () {
        let input_val = $(this).val()
        $(this).attr('value', input_val)
    });

    // Upload additional files on Admin Assessment
    $(document).on('change', ".additional-files", async function (e) {
        const files = Array.from(this.files);
        const groupQuestionsId = $(this).closest('.group-question-wrapper').data('id');
        const subQuestionId = $(this).closest('.question-row-container').data('id');
        const addFilesContainer = $(this).closest('.question-add-files-container');
        const message = addFilesContainer.find('._message');
        const filesList = addFilesContainer.find(".filesList");
        const spinner = addFilesContainer.find('.uploading-wrapper');
        // Hide the message
        message.removeClass('error').hide();
        if (!files.length) return;
        $(this).prop('disabled', true);
        spinner.show();
        try {
            // Upload all files in parallel
            const uploadPromises = files.map(async (file, index) => {
                const fileIndex = Date.now() + index; // Ensure unique index
                const fileItem = $('<span/>', { class: 'file-item', style: 'display: none;' });
                const fileName = $('<a/>', { class: 'name', text: file.name, target: '_blank' });
                const fileIcon = $('<span class="icon"><i class="fa-solid fa-paperclip"></i></span>');
                const deleteIcon = $('<span class="file-delete"><i class="fa-solid fa-xmark"></i></span>');
                const hiddenInput = $(`<input/>`, {
                    type: "hidden",
                    class: `input-file-hidden additional-file-id-${fileIndex}`,
                    name: `group_questions[${groupQuestionsId}][list][${subQuestionId}][additional_files][${fileIndex}]`,
                    value: "",
                });
    
                fileName.prepend(fileIcon);
                fileItem.append(fileName, deleteIcon, hiddenInput);
                filesList.append(fileItem);
    
                // Perform upload
                const fileId = await admin_upload_additional_file(file);
    
                // Update hidden input with the uploaded file ID
                hiddenInput.val(fileId);
    
                // Fetch the file URL using WordPress media API
                const attachment = wp.media.attachment(fileId);
                await attachment.fetch();
                const fileUrl = attachment.get("url");
    
                // Update file link and show the item
                fileName.attr("href", fileUrl);
                fileItem.fadeIn(200);
    
                return fileItem;
            });
    
            // Wait for all uploads to complete
            await Promise.all(uploadPromises);
            // Show success message
            message.text("Files uploaded successfully!").fadeIn(200);
            // Hide message after 10 seconds
            setTimeout(() => message.fadeOut(), 10000);
        } 
        catch (error) {
            console.error("Error uploading files:", error);
            message.addClass('error').text("An error occurred while uploading files.").fadeIn(200);
            // Hide error message after 10 seconds
            setTimeout(() => message.fadeOut(), 10000);
        }
        finally {
            // Re-enable input and remove spinner
            $(this).prop('disabled', false);
            spinner.hide();
        }
    });

    // EventListener for delete file item
    $(document).on('click', '.file-delete', function(){
        let btn = $(this);
        let add_files_container = btn.closest('.question-add-files-container');
        let this_file_item = btn.closest('.file-item');
        let message = add_files_container.find('._message')
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
                    this_file_item.css('opacity', '0.5')
                    // Hide the message
                    message.removeClass('error').hide();
                },
                success:function(response){
                    this_file_item.remove();
                    // Show the message
                    message.text('File deleted').show();
                    // Hide the message after 10 seconds
                    setTimeout(function() {
                        message.hide();
                    }, 10000);
                }
            });
        }
    });

    async function admin_upload_additional_file(file_upload) {
        let formData = new FormData();
        formData.append("file", file_upload)
        formData.append("action", 'upload_assessment_attachment')
        formData.append("security", ajax_object.security)

        let response = await $.ajax({
            type: 'POST',
            url: ajaxUrl,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend:function(xhr){},
            success:function(response){}
        });

        const { status, message } = response;

        if (status == true) {
            return response.attachment_id;
        } else {
            alert(message);
            return false;
        }
    }

    $(document).on('click', '.sas-blob-cta', function (e){
        const thisBtn = $(this);
        const blobUrl = thisBtn.data('blob');
        if (!blobUrl) return;
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'create_sas_blob_url_azure_ajax',
                'blob_url' : blobUrl,
            },
            beforeSend : function ( xhr ) {
                thisBtn.addClass('loading');
            },
            success:function(response){
                if (response.status) {
                    window.open(
                        response.sas_blob_url,
                        '_blank',
                      );
                } else {
                    alert(response.message);
                }
                thisBtn.removeClass('loading');
            }
        });
    });

    $(document).on('click', '.btn-lock-question', function () {
        let $this = $(this);  // Cache the button
        let questionWrapper = $this.closest('#question-group-repeater .question'); // Find the question wrapper
        let isLockedInput = questionWrapper.find('input.is_locked_input');
        let lockedIcon = $this.find('.locked-icon');
        let lockText = $this.find('.lock-text');
        let btnCollapse = questionWrapper.find('.btn-expand-wrapper.active');
        let groupTitle = questionWrapper.find('input.group-question-admin-title');
        let questionTitle = questionWrapper.find('input.question-admin-title');
        // Determine if the question is currently locked
        let isLocked = questionWrapper.hasClass('disabled');
        // If locked, unlock; if unlocked, lock
        if (isLocked) {
            unlockQuestion(questionWrapper, lockedIcon, lockText, isLockedInput, groupTitle, questionTitle);
        } else {
            // Collapse the question first before locking
            btnCollapse.click();
            lockQuestion(questionWrapper, lockedIcon, lockText, isLockedInput, groupTitle, questionTitle);
        }
    });
    
    // Unlock the question
    function unlockQuestion(questionWrapper, lockedIcon, lockText, isLockedInput, groupTitle, questionTitle) {
        questionWrapper.removeClass('disabled');
        lockedIcon.hide();
        lockText.text('Lock');
        isLockedInput.val('0'); // Set the lock state as unlocked
        // Make the input fields focusable again
        groupTitle.removeAttr('tabindex');
        questionTitle.removeAttr('tabindex');
        // ARIA attributes for accessibility
        groupTitle.attr('aria-disabled', 'false');
        questionTitle.attr('aria-disabled', 'false');
    }
    
    // Lock the question
    function lockQuestion(questionWrapper, lockedIcon, lockText, isLockedInput, groupTitle, questionTitle) {
        questionWrapper.addClass('disabled');
        lockedIcon.show();
        lockText.text('Unlock');
        isLockedInput.val('1'); // Set the lock state as locked
        // Disable focusability of the input fields
        groupTitle.attr('tabindex', '-1');
        questionTitle.attr('tabindex', '-1');
        // ARIA attributes for accessibility
        groupTitle.attr('aria-disabled', 'true');
        questionTitle.attr('aria-disabled', 'true');
    }

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
            generic_page_item +=         '<a class="btn-remove-generic-page button_remove">'
            generic_page_item +=            '<span><i class="fa-solid fa-xmark"></i></span>'
            generic_page_item +=            '<span>Remove this row</span>'
            generic_page_item +=         '</a>'
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
        renderWpVisualEditor('#'+ textarea_id, true);

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
        let submissionType = $("#post_type").val();
        
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action'          : 'create_comprehensive_report',
                'submission_id'   : submissionId,
                'submission_type' : submissionType,
            },
            beforeSend : function ( xhr ) {
                btn.addClass('loading')
            },
            success:function(response){
                btn.removeClass('loading')
                if (response.report_id) {
                    $('#btn-view-report')
                        .addClass('show')
                        .attr('href', '/wp-admin/post.php?post='+ response.report_id +'&action=edit');
                    setTimeout(function() {
                        alert(response.message);
                    }, 100)
                }
                if (response.status == false) {
                    setTimeout(function() {
                        alert(response.message);
                    }, 100)
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
