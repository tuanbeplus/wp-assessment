(function ($) {
	"use strict";

    const assessmentIdInstance = $('#assessment_id');
    const submissionId = $('#submission_id');
    const organisationIdInstance = $('#organisation_id');
    const orgNameInstance = $('#org_name');
    const messageWrap = $('.progress-message');
    const ajaxUrl = ajax_object.ajax_url;
    const assessmentWrapper = $('#assessment-main-wrapper');
    const btn_Prev = assessmentWrapper.find('#go-back-quiz-btn');
    const btn_Next = assessmentWrapper.find('#go-next-quiz-btn');
    const isRequiredAnswerAll = $('input#required_answer_all').val();
    const isRequiredDocumentAll = $('input#required_document_all').val();
    
    /**
     * Validate required quiz answers and documents before saving.
     * 
     * @returns {boolean} True if validation passes, false otherwise.
     */
    function validateQuizAnswersRequired(quizId) {
        let currentQuiz = $('#quiz-item-' + quizId);
        if (!currentQuiz.length) return false;

        if (isRequiredAnswerAll == true) {
            let hasAnsweredAll = getDataQuizAnswered(currentQuiz);
            if (!hasAnsweredAll) return false;
        }
        if (isRequiredDocumentAll == true) {
            let hasUploadedRequiredDocs = getUploadedDocumentRequired(currentQuiz);
            if (!hasUploadedRequiredDocs) return false;
        }
        return true;
    }

    /**
     * Show/Hide loading Spinner in the button
     */
    function buttonLoadingStatus(btn, status) {
        if (!status) return false;

        if (status == 'show') {
            btn.addClass('loading');
            btn.attr('disabled', true); 
        }
        else if (status == 'hide') {
            btn.removeClass('loading');
            btn.removeAttr('disabled'); 
        }
        else {
            return false;
        }
    }

    /**
     * Validate Quiz choices, description answered
     */
    function getDataQuizAnswered(currentSection) {
        let count_empty_des = 0;
        // Get count of choice areas and checked answers
        let choice_answer_area = currentSection.find('.multiple-choice-area').length;
        let count_checked_answers = currentSection.find('.form-check-input.checked').length;

        // Handle description validation only if exists
        let quiz_description = currentSection.find('.quiz-description');
        if (quiz_description.length > 0) {
            quiz_description.each(function(e) {
                if ($(this).val() == '') {
                    $(this).addClass('required')
                    count_empty_des++
                }
            })
        }
        // Handle multiple choice validation only if exists
        if (choice_answer_area > 0 && choice_answer_area > count_checked_answers) {
            let multiple_choice_area = $('.quiz.active .multiple-choice-area')

            multiple_choice_area.each(function(e) {
                if ($(this).hasClass('checked')) {
                    $(this).removeClass('required')
                }
                else {
                    $(this).addClass('required')
                }
            })
            $('.multiple-choice-area.required .form-check-label').addClass('required')
        }
        // Show notification if any errors
        if ((choice_answer_area > count_checked_answers) || count_empty_des > 0) {
            currentSection.find('.answer-notification').fadeIn();
            setTimeout(() => {
                currentSection.find('.answer-notification').fadeOut()
            }, 10000); 
            return false
        }
        else if (choice_answer_area == count_checked_answers) {
            return true
        }
    }

    /**
     * Check the section and mark it as completed if criteria are met
     */
    function markAsCompletedSection(sectionId) {
        const $isRequiredAllDocuments = $('input#required_document_all').val();
        const $thisStep = $(`.stepsWrap .step-${sectionId}`);
        const $thisQuiz = $(`#form_submit_quiz #quiz-item-${sectionId}`);
        // Check conditions
        const hasCheckedAnswers = isAnswerChoicesChecked($thisQuiz);
        const hasFilledComments = isAnswerCmtsFilled($thisQuiz);
        const hasUploadedDocuments = ($isRequiredAllDocuments == true) ? getUploadedDocumentRequired($thisQuiz) : true;

        // Mark section as completed if all conditions are met
        if (hasCheckedAnswers == true && hasFilledComments == true && hasUploadedDocuments == true) {
            $thisStep.addClass('completed');
        } else {
            $thisStep.removeClass('completed');
        }
    }

    /**
     * Validate the Choies checked
     */
    function isAnswerChoicesChecked(section_wrapper)
    {
        let answer_choices = section_wrapper.find('.multiple-choice-area').length;
        let answer_choices_checked = section_wrapper.find('.multiple-choice-area.checked').length;

        if (answer_choices == answer_choices_checked) {
            return true
        }
        else {
            return false
        }
    }

    /**
     * Validate the Cmt fields content
     */
    function isAnswerCmtsFilled(section_wrapper)
    {
        let answer_cmts = section_wrapper.find('textarea.quiz-description')
        let count_empty_des = 0;

        answer_cmts.each(function(e) {
            if ($(this).val() == '') {
                count_empty_des++
            }
        })

        if (count_empty_des > 0) {
            return false
        }
        else {
            return true
        }
    }

    /**
     * Validate the Documents required fields
     */
    function getUploadedDocumentRequired(currentQuiz) {
        var upload_file_arr = [];
        let upload_doc_container = currentQuiz.find('.question-add-files-container')
        upload_doc_container.each(function(e) {
            let upload_file_items = $(this).find('.filesList .file-item').length
            let upload_message_error = $(this).find('.upload-message._error');
            if (upload_file_items == 0) {
                $('#saving-spinner').hide()
                $('.formController button').css('opacity', '1')
                upload_message_error.find('.message').text('Supporting documentation is required!')
                currentQuiz.find('.answer-notification').show()
                upload_message_error.css('display', 'flex')
                setTimeout(function() {
                    currentQuiz.find('.answer-notification').hide()
                }, 15000);
            }
            upload_file_arr.push(upload_file_items)
        });
        if (upload_file_arr.includes(0)) {
            return false
        }
        else {
            return true
        }
    }

    /**
     * Get all Choices have checked
     */
    function getCheckedChoices(question_main_wrapper) {
        let checkboxes = question_main_wrapper.find('.checkBox');

        let choices = [];
        checkboxes.each(function () {
            let that = $(this);
            let input = that.find('.form-check-input');
            let isChecked = input.is(':checked');

            if (isChecked) {
                choices.push({id: input.data('id'), title: input.data('title')})
            }
        })

        let count_checked_choices = choices.length;
        return count_checked_choices;
    }

    /**
     * Active & Focus to first pending section after load page
     */
    function activeFirstPendingSection() {
        let all_steps = $('#main-quiz-form .step')
        let all_section_wrapper = $('#main-quiz-form .quizDetails .quiz')
        let step_ids = [];
        let first_step_id = null;
        let section_wrapper_id = null;

        all_steps.each(function (e) {
            // Remove all class active of step
            $(this).removeClass('active')

            // Add steps id peding to Array
            if (!$(this).hasClass('completed')) {
                step_ids.push($(this).data('id'))
            }
        })

        if (step_ids.length > 0) {
            // Add class active to first step pending
            $('#main-quiz-form .step.step-'+ step_ids[0]).addClass('active')
        }
        else {
            // Add class active to first step if all is completed
            $('#main-quiz-form .step.step-1').addClass('active')
            $('#go-back-quiz-btn').hide()
        }

        // Add class active to first section pending
        all_section_wrapper.each(function (e) {
            section_wrapper_id = $(this).data('group')
            first_step_id = step_ids[0] ?? 1;
        
            if (section_wrapper_id == first_step_id) {
                $(this).addClass('active')
            }
            else {
                $(this).removeClass('active')
            }
        })
    }

    /**
     * Get total number of quizzes
     * @returns {number} Total number of quizzes
     */
    function getQuizzesCount() {
        let quizzesElement = $('#main-quiz-form .quizDetails').children('.quiz');
        return quizzesElement.length || 0;
    }

    /**
     * Show/Hide form controller buttons
     */
    function updateFormController() {
        let countQuizzes = getQuizzesCount();
        let activeQuizId = getActiveQuizId();        

        if (activeQuizId == 1) {
            btn_Prev.removeClass('show');
            btn_Next.addClass('show');
        }
        else if (activeQuizId >= countQuizzes) {
            btn_Prev.addClass('show');
            btn_Next.removeClass('show');
        }
        else {
            btn_Prev.addClass('show');
            btn_Next.addClass('show');
        }
    }

    /**
     * Get current active quiz ID
     */
    function getActiveQuizId() {
        let mainWrapper = $('#assessment-main-wrapper #main-quiz-form');
        let activeQuizId = mainWrapper.find('.quiz.active').data('group');
        let activeStepId = mainWrapper.find('.step.active').data('id');

        return ( activeQuizId || activeStepId ||  1 );
    }

    /**
     * Move to the Quiz by ID
     * @param {number} quizId - The ID of the quiz to move to
     * @returns {boolean} True if the quiz was successfully activated, otherwise false
     */
    function moveToTheQuiz(quizId) {
        const mainWrapper = $('#main-quiz-form');
        if (!mainWrapper.length) return false; // Ensure the main wrapper exists
    
        const countQuizzes = getQuizzesCount();
        if (!quizId || !countQuizzes) return false;
    
        const stepTarget = mainWrapper.find(`#step-${quizId}`);
        const quizTarget = mainWrapper.find(`#quiz-item-${quizId}`);
    
        if (stepTarget.length && quizTarget.length) {
            // Activate the step
            mainWrapper.find('.step').removeClass('active');
            stepTarget.addClass('active');
    
            // Activate the quiz
            mainWrapper.find('.quiz').removeClass('active');
            quizTarget.addClass('active');
    
            return true; // Successfully activated
        }
        else {
            return false; // Targets not found
        }
    }

    /**
     * Upload multiple attachment files.
     * 
     * @param {FileList} filesInput - List of files to be uploaded.
     * @param {jQuery} inputElement - The input element that triggered the upload.
     */
    function uploadMultipleAttachments(filesInput, inputElement) {
        // Fetch relevant DOM elements and data attributes
        const groupQuestionsId = inputElement.closest('.group-question').data('group');
        const subQuestionId = inputElement.closest('.fieldsWrapper').data('sub');
        const addFilesContainer = inputElement.closest('.question-add-files-container');
        const uploadMessageError = addFilesContainer.find('.upload-message._error');
        const filesList = addFilesContainer.find(".filesList");
        // Hide any existing error messages
        uploadMessageError.hide();
        // Iterate through the files and process each one
        Array.from(filesInput).forEach((file, index) => {
            const fileIndex = filesList.children().length + 1; // Calculate the file index
            // Template for creating a file item
            const fileItemHTML = (`
                <span class="file-item file-item-${fileIndex}" style="display:none;">
                    <span class="name file-name-${fileIndex}">
                        <i class="fa-solid fa-paperclip"></i> ${file.name}
                    </span>
                    <input 
                        type="hidden" 
                        class="input-file-hidden additional-files additional-file-id-${fileIndex}" 
                        name="questions_${groupQuestionsId}_quiz_${subQuestionId}_attachmentIDs_${fileIndex}"
                    />
                    <button class="file-delete" aria-label="Remove this uploaded file">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </span>
            `);
            // Append the generated file item to the files list
            filesList.append(fileItemHTML);
            // Handle file upload
            frontUploadAdditionalFiles(file, inputElement, fileIndex);
        });
    }

    function getDescriptionValue() {
        let currentQuiz = $('#quiz-item-' + getActiveQuizId());
        let input = currentQuiz.find('.quiz-description');

        return input.val();
    }

    function getAttachmentIdInput() {
        let currentQuiz = $('#quiz-item-' + getActiveQuizId());
        let input = currentQuiz.find('.fileUploaderWrap .assessment-attachment-id');
        return input;
    }

    function getMultipleAttachmentIdInput(currentQuizInstance) {
        let input_file = currentQuizInstance.find('.file-item');
        let files_arr = [];

        if (input_file.length === 0) return true;

        input_file.each(function () {
            let that = $(this);
            let input = that.find('.input-file-hiden');
            let isHasValue = input.val();

            if (isHasValue) {
                files_arr.push({
                    id: input.val(),
                })
            }
        })
        return files_arr;
    }

    function getCheckAnswers(currentQuizInstance) {
        let checkboxes = currentQuizInstance.find('.checkBox');
        let choices = [];

        if (checkboxes.length === 0) return true;

        checkboxes.each(function () {
            let that = $(this);
            let input = that.find('.form-check-input');
            let isChecked = input.is(':checked');

            if (isChecked) {
                choices.push({
                    id: input.data('id'), 
                    title: input.data('title'),
                })
            }
        })
        return choices;
    }

    async function saveQuizAssessment(answers) {
        let activeQuizId = getActiveQuizId();
        let currentQuiz = $('#quiz-item-' + activeQuizId);
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let submission_id = submissionId.val();
        let answerDescription = getDescriptionValue();
        let attachmentIdValue = getAttachmentIdInput().val();
        let multipleAttachmentIdValue = getMultipleAttachmentIdInput(currentQuiz);
        let data_form = $('#form_submit_quiz');
        let type_quiz = $('input[name="type_quiz"]').val();

        const data = {
            'action': 'save_answers_assessment',
            'answers': answers,
            'quiz_id': activeQuizId,
            'organisation_id' : organisationId,
            'assessment_id': assessmentId,
            'submission_id': submission_id,
            'description': answerDescription,
            'attachment_ids': multipleAttachmentIdValue,
            'attachment_id': attachmentIdValue,
            'data_quiz' : data_form.serializeArray(),
            'type_quiz' : type_quiz,
        };
        // console.log(data_form.serializeArray());

        let response = await $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data,
        });
        const {status, message, result, list_quiz} = response;
        
        if (status == false) {
            alert(message)
        }
        return status;
    }

    async function submitPublishSubmission() {
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();

        const data = {
            'action': 'create_assessment_submission',
            'assessment_id': assessmentId,
            'organisation_id' : organisationId,
        };

        let response = await $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data
        });

        return response;
    }

    async function saveDraftSubmission() {
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let orgName = orgNameInstance.val();

        const data = {
            'action'          : 'submit_assessment_progress',
            'assessment_id'   : assessmentId,
            'organisation_id' : organisationId,
            'org_name'        : orgName,
        };

        let response = await $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data,
        });
        const {status, message, submission_id} = response;
        // console.log(response);

        $('#submission_id').attr('value', submission_id);

        return submission_id;
    }

    async function uploadAssessmentAttachment(file, inputInstance) {

        let formData = new FormData();
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let userId = $('#sf_user_id').val();
        let userName = $('#sf_user_name').val();
        let fileUploaderWrap = inputInstance.closest(".fileUploaderWrap")

        formData.append("file", file)
        formData.append("action", 'azure_upload_assessment_attachment')
        formData.append("sf_user_id", userId)
        formData.append("sf_user_name", userName)
        formData.append("assessment_id", assessmentId)
        formData.append("organisation_id", organisationId)
        
        let response = await $.ajax({
            type: 'POST',
            url: ajaxUrl,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend : function ( xhr ) {
                fileUploaderWrap.find('.uploading-wrapper').show()
                $('.formController').addClass('not-allowed')
            },
            success:function(response){
                fileUploaderWrap.find('.uploading-wrapper').hide()
                $('.formController').removeClass('not-allowed')
            }
        });

        const { status, message } = response;
        toggleMessageWrap(message)

        if (status) {
            inputInstance.siblings('.assessment-attachment-id').val(response?.attachment_id)
        } else {
            $('html, body').animate({
                scrollTop: $(".formWrapper").offset().top
            }, 500);
        }
    }

    async function frontUploadAdditionalFiles(file, inputInstance, index) {
        let formData = new FormData();
        let userName = $('#sf_user_name').val();
        let groupId =  inputInstance.closest('.group-question').data('group')
        let quizId = inputInstance.closest('.fieldsWrapper').data('sub')
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let fileUploaderWrap = inputInstance.closest(".question-add-files-container")
        let dropArea = fileUploaderWrap.find('.dropFiles')
        let upload_message_success = fileUploaderWrap.find('.upload-message._success')
        let upload_message_error = fileUploaderWrap.find('.upload-message._error')
    
        formData.append("file", file)
        formData.append("action", 'save_attachments_azure_storage_ajax')
        formData.append("user_name", userName)
        formData.append("parent_id", groupId)
        formData.append("quiz_id", quizId)
        formData.append("assessment_id", assessmentId)
        formData.append("organisation_id", organisationId)
    
        let response = await $.ajax({
            type: 'POST',
            url: ajaxUrl,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend : function ( xhr ) {
                fileUploaderWrap.find('.spinner-upload').show()
                dropArea.addClass('uploading')
                fileUploaderWrap.find('.btn-add-files-wrapper').addClass('not-allowed')
            },
            success:function(response){
                if (response.status == true) {
                    let attachment_id = response.attachment_id
                    upload_message_success.text('Uploaded file successfully.').show()
                    setTimeout(function() {
                        upload_message_success.hide()
                    }, 10000)
                    fileUploaderWrap.find('.btn-add-files-wrapper').removeClass('not-allowed')
                    fileUploaderWrap.find('.additional-file-id-' + index).val(attachment_id)
                    fileUploaderWrap.find('.file-item-' + index).removeAttr('style')
                    console.log(response);
                } 
                else {
                    fileUploaderWrap.find('.spinner-upload').hide()
                    dropArea.removeClass('uploading')
                    upload_message_error.find('.message').text('There was aproblem attaching one of your files. Please try again.')
                    upload_message_error.css('display', 'flex')
                    console.log(response);
                }
                fileUploaderWrap.find('.spinner-upload').hide()
                dropArea.removeClass('uploading')
            }
        }); 
    }

    function toggleMessageWrap(message) {
        messageWrap.show();
        messageWrap.html(message);

        setTimeout(() => {
            messageWrap.hide();
            messageWrap.html('');
        }, 8000)
    }

    jQuery(document).ready(async function ($) {
        activeFirstPendingSection();
        updateFormController();
    });

    // Click to Save a Quiz Assessment
    $(document).on('click', '#continue-quiz-btn', async function (e) {
        e.preventDefault();
        const $button = $(this); // Cache the button for reuse
        const $activeQuizId = getActiveQuizId(); // Get the currently active quiz ID
        const $currentQuiz = $(`#quiz-item-${$activeQuizId}`); // Select the current quiz element
        const $formMessage = $('#main-quiz-form .form-message'); // Cache form message element
        // Validate quiz answers
        if (!validateQuizAnswersRequired($activeQuizId)) {
            return; // Exit if validation fails
        }
        buttonLoadingStatus($button, 'show'); // Show loading indicator
        try {
            // Fetch and save answers
            let answers = getCheckAnswers($currentQuiz);
            await saveDraftSubmission(); // Save the draft submission
            let isQuizSaved = await saveQuizAssessment(answers);

            if (isQuizSaved) {
                // Show success message
                let sectionId = $currentQuiz.data('group');
                $formMessage
                    .addClass('_success')
                    .find('.message')
                    .text(`Section ${sectionId} has been saved.`);
                $formMessage.show();

                // Hide the message after 10 seconds
                setTimeout(() => {
                    $formMessage.hide().removeClass('_success');
                }, 10000);
            } else {
                throw new Error('Failed to save the quiz assessment.');
            }
            // Mark section as completed
            markAsCompletedSection($activeQuizId);
            // Scroll to top of the assessment wrapper
            $('html, body').animate({ scrollTop: assessmentWrapper.offset().top - 32 }, 500);
            // Move to the next quiz
            moveToTheQuiz($activeQuizId + 1);

        } catch (error) {
            console.error(error); // Log error for debugging
            alert('An error occurred while saving the quiz. Please try again.');
        } finally {
            buttonLoadingStatus($button, 'hide'); // Hide loading indicator
        }
    });

    // Click to Save Quiz progress
    $(document).on('click', '#save-progress-btn', async function (e) {
        e.preventDefault();
        let this_Btn = $(this);
        let currentQuiz = $('#quiz-item-' + getActiveQuizId());
		let checkAnswers = getCheckAnswers(currentQuiz);

        buttonLoadingStatus(this_Btn, 'show'); // Begin loading

        let isDraftSaved = await saveDraftSubmission();

        let isQuizSaved = await saveQuizAssessment(checkAnswers);
        
        buttonLoadingStatus(this_Btn, 'hide'); // Stop loading

        // Delay the alert
        if (isDraftSaved && isQuizSaved) {
            setTimeout(() => {
                alert('Submission progress has been saved');
            }, 100); // Add a small delay
        }
    });

    // Click to Submit Submission
    $(document).on('click', '#submit-quiz-btn', async function (e) {
        e.preventDefault();
        let thisBtn = $(this);

        // Show loading status
        buttonLoadingStatus(thisBtn, 'show');

        // Validate All Quizzes
        let allValid = true; // Flag to track validation status
        $('#main-quiz-form .quiz').each(function () {
            let quizId = $(this).data('group');
            let isValid = validateQuizAnswersRequired(quizId);
            if (!isValid) {
                allValid = false; // Mark as invalid
                return false; // Break out of the `.each()` loop
            }
        });
        // Stop if validation fails
        if (!allValid) {
            buttonLoadingStatus(thisBtn, 'hide'); // Hide loading
            setTimeout(() => {
                alert('Please make sure you have completed all sections.');
            }, 100);
            return; // Exit click handler
        }
        // Proceed to submission if validation passes
        try {
            let currentQuiz = $('#quiz-item-' + getActiveQuizId());
            let checkAnswers = getCheckAnswers(currentQuiz);

            let isQuizSaved = await saveQuizAssessment(checkAnswers);     
                    
            let response = await submitPublishSubmission();

            // Hide loading after submission
            buttonLoadingStatus(thisBtn, 'hide');

            const { status, message, submission_url } = response;

            if (message) {
                setTimeout(() => {
                    alert(message);
                }, 100); // Small delay for better UX
            }
            if (status) {
                // Redirect to single submission
                window.location.href = submission_url;
            }
        } catch (error) {
            // Handle submission error
            buttonLoadingStatus(thisBtn, 'hide');
            alert('An error occurred during submission. Please try again.');
            console.error(error); // Log error for debugging
        }
    });

    // Move to the Section by Step
    $(document).on('click focus','.stepsWrap .step', function (e) {
        e.preventDefault();
        let currentStepId = $(this).data('id');

        let movedQuiz = moveToTheQuiz(currentStepId);
        if (movedQuiz) {
            updateFormController();
        }
    });

    // Click to go to Prev Quiz
    $(document).on('click', '#go-back-quiz-btn', function (e) {
        e.preventDefault();
        let currentQuizId = getActiveQuizId();
        let prevQuizId = currentQuizId - 1;

        if (currentQuizId <= 1) return;

        let movedPrev = moveToTheQuiz(prevQuizId);
        if (movedPrev) {
            $('html, body').animate({ scrollTop: assessmentWrapper.offset().top - 32 }, 500);
            updateFormController();
        }
    });

    // Click to go to Next Quiz
    $(document).on('click', '#go-next-quiz-btn', function (e) {
        e.preventDefault();
        let countQuizzes = getQuizzesCount();
        let currentQuizId = getActiveQuizId();
        let nextQuizId = currentQuizId + 1;
        
        if (currentQuizId >= countQuizzes) return;

        let movedNext = moveToTheQuiz(nextQuizId);
        if (movedNext) {
            $('html, body').animate({ scrollTop: assessmentWrapper.offset().top - 32 }, 500);
            updateFormController();
        }
    });

    $(document).on('change', '.assessment-file', async function (e) {
        e.preventDefault();
        let that = $(this);
        let file = e.target.files[0];
        await uploadAssessmentAttachment(file, that)
    });

    $(document).on('click','.fieldsWrapper .form-check-input', function (e) {
        let check_input = $(this)
        let answer_point = check_input.data('point')
        let checkBox = check_input.closest('.checkBox')
        let check_input_wrapper = check_input.closest('.fieldsWrapper')
        let all_check_input = check_input_wrapper.find('.form-check-input')
        let all_input_point = check_input_wrapper.find('.quiz-input-point')
        let group_id = check_input.closest('.group-question').data('group')
        let quiz_id = check_input.closest('.fieldsWrapper').data('sub')
        let multiple_choice_area = $(this).closest('.multiple-choice-area')

        let input_quiz_point =  '<input class="quiz-input-point" type="hidden"';
            input_quiz_point += 'name="questions_'+ group_id +'_quiz_'+ quiz_id +'_point"';
            input_quiz_point += 'value="'+ answer_point +'">';
        
        multiple_choice_area.removeClass('required')
        multiple_choice_area.find('.form-check-label').removeClass('required')
        
        if (check_input.hasClass('checked')) {
            check_input.removeClass('checked')
            multiple_choice_area.removeClass('checked')
            all_input_point.remove()
            check_input.prop('checked', false)
        }
        else {
            all_check_input.removeClass('checked')
            all_input_point.remove()
            check_input.addClass('checked')
            multiple_choice_area.addClass('checked')
            check_input.prop('checked', true)
            checkBox.append(input_quiz_point)
        }
    })

    $(document).on('click', 'report-toc a', function () {
        $('html, body').animate({
            scrollTop: $(body).offset(200).top
        }, 500);
        console.log('click');
    })

    // upload additional files on front fields
    $(document).on('change', ".additional-files", async function(e){
        uploadMultipleAttachments(this.files, $(this));
    });

    $(".dropFiles").on('dragenter', function(ev) {
        // Entering drop area. Highlight area
        $(this).addClass("highlightDropArea");
    });
    
    $(".dropFiles").on('dragleave',async function(ev) {
        // Going out of drop area. Remove Highlight
        $(this).removeClass("highlightDropArea");
    });
    
    $(".dropFiles").on('drop', async function(e) {
        // Dropping files
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("uploading");
       
        let add_files_container = $(this).closest('.question-add-files-container')
        var main_files_input = add_files_container.find('input.additional-files')

        if(e.originalEvent.dataTransfer){
            if(e.originalEvent.dataTransfer.files.length) {

                var droppedFiles = e.originalEvent.dataTransfer.files;
                var data_transfer = new DataTransfer();

                for(let i=0; i<droppedFiles.length; i++) {
                    let file = droppedFiles[i];
                    data_transfer.items.add(
                    new File(
                        [file.slice(0, file.size, file.type)],
                        file.name
                    ));
                }
                main_files_input.files = data_transfer.files;

                uploadMultipleAttachments(main_files_input.files, $(this))
            }
        }
    
        $(this).removeClass("highlightDropArea");
        return false;
    });
    
    $(".dropFiles").on('dragover', function(e) {
        e.preventDefault();
    });

    // Event Listener for deleting a file item
    $(document).on('click', '.file-delete', function () {
        // Confirm before deletion
        if (!confirm('Do you want to delete this file?')) {
            return;
        }
        // Fetch relevant elements and data
        const fileItem = $(this).closest('.file-item');
        const attachmentId = fileItem.find('input.input-file-hidden').val();
        const assessmentId = assessmentIdInstance.val();
        const organisationId = organisationIdInstance.val();
        const uploadFileContainer = $(this).closest('.question-add-files-container');
        const uploadMessage = uploadFileContainer.find('.upload-message._success');

        if (!attachmentId || !assessmentId || !organisationId) {
            alert('Missing required data for deletion.');
            return;
        }
        // AJAX request for deleting the file
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: {
                action: 'delete_azure_attachments_ajax',
                attachment_id: attachmentId,
                assessment_id: assessmentId,
                organisation_id: organisationId,
            },
            beforeSend: function () {
                fileItem.css('opacity', '0.5').prop('disabled', true);
            },
            success: function (response) {
                console.log(response);
                if (response.status) {
                    fileItem.remove();
                    uploadMessage.text('File deleted successfully.').show();
                } else {
                    uploadMessage.text('Failed to delete the file.').show();
                    alert(response.message || 'An unknown error occurred.');
                    fileItem.css('opacity', '1').prop('disabled', false);
                }
            },
            error: function (xhr, status, error) {
                alert(`Error: ${error}`);
                fileItem.css('opacity', '1').prop('disabled', false);
            },
            complete: function () {
                setTimeout(() => {
                    uploadMessage.hide();
                }, 10000); // Hide the message after 10 seconds
            },
        });
    });

    $(document).on('click', '.btn-open-upload-area', function (e) {
        let add_files_container = $(this).closest('.question-add-files-container')
        let upload_area = add_files_container.find('.drop-files-area')
        upload_area.slideToggle(300);
    })

    $(document).on('click', '.remove-message', function() {
        $(this).closest('.upload-message').hide()
    })

    $(document).on('click focus','.quiz-description', async function (e) {
        $(this).removeClass('required')
    })

    $(document).on('click','#toggle-invite-colleagues', async function (e) {
        $(this).toggleClass('active')
        let invite_colleagues_wrapper = $('.invite-colleagues-wrapper')
        invite_colleagues_wrapper.slideToggle()
    })

    $(document).on('click','#btn-close-invite', async function (e) {
        $(this).closest('.invite-colleagues-wrapper').slideUp()
        $('#toggle-invite-colleagues').removeClass('active')
    })

    $(document).on('click','#btn-send-invite-colleagues', async function (e) {

        let send_message = $('#form-invite-colleagues').find('.send-message')
        let input_emails = $('#emails-area').val()
        let emails_arr = input_emails.split(',') // remove comma and push to array
        let assessmentId = $('#assessment_id').val()
        
        for (let i = 0; i < emails_arr.length; i++) {
            let position_at = emails_arr[i].search("@")

            if (position_at == -1) {
                send_message.text('Please ensure that a valid email address has been entered.').show()
                return
            }
            else {
                send_message.hide()
            }
        }

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data:{
                'action' : 'send_invite_to_colleagues',
                'emails' : emails_arr,
                'assessment_id' : assessmentId,
            },
            beforeSend : function ( xhr ) {
                $('#btn-send-invite-colleagues').addClass('sending')
            },
            success:function(response){
                $('#btn-send-invite-colleagues').removeClass('sending')
                console.log(response);
                if (response.status == true) {
                    send_message.text('Your invitation has been sent.').show()
                }
                else {
                    send_message.html('Unable to send invitation, ensure that emails are seperated by a comma.').show()
                }        
                
                if (response.updated_meta == false) {
                    console.log("Post meta invited_members has not been updated.");
                }
                else {
                    console.log("Updated post meta invited_members Successful.");
                }
            }
        });
    })

    $(document).on('click', '.sas-blob-cta', function (e){
        let blobUrl = $(this).data('blob');
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'create_sas_blob_url_azure_ajax',
                'blob_url' : blobUrl,
            },
            beforeSend : function ( xhr ) {
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
            }
        });
    });

    $(document).on('click', '.btn-showmore-cmt', function (e){
        let cmt = $(this).closest('.comment')

        if ($(this).hasClass('active')) {
            cmt.addClass('show_less');
            $(this).removeClass('active');
            $(this).text('Show more');
        }
        else {
            cmt.removeClass('show_less');
            $(this).addClass('active');
            $(this).text('Show less');
        }
    });

    $(document).on('click', function(event) {
        // Check if the click is outside the dropdown and the button that opens it
        if (!$(event.target).closest('.submission-vers').length) {
            $('.sub-vers-list').slideUp(200); // Close the dropdown
        }
    });

    $(document).on('click', '.submission-vers', function (e){
        e.stopPropagation();
    });

    $(document).on('click', '#btn-show-submission-vers', function (e){
        e.preventDefault();
        let wrapper = $(this).closest('.submission-vers');
        let sub_vers_list = wrapper.find('.sub-vers-list');
        sub_vers_list.slideToggle(200);
    });

    $(document).on('click', '.btn-toggle-feedback', function (e){
        e.preventDefault();
        const questionWrapper = $(this).closest('.fieldsWrapper');
        const feedbacksArea = questionWrapper.find('.feedback-area');
        if (feedbacksArea.hasClass('expand')) {
            feedbacksArea.removeClass('expand');
            $(this).text('Expand feedbacks');
            $('html, body').animate({ scrollTop: feedbacksArea.offset().top - 30 }, 200);
        }
        else {
            feedbacksArea.addClass('expand');
            $(this).text('Collapse feedbacks');
        }
    });

})(jQuery);
