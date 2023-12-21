jQuery(document).ready(function ($) {
    let opacity;
    let activeQuiz;
    let isQuizComplete = false;
    let isStepComplete = false;
    let bodySelector = $('body');
    const assessmentIdInstance = $('#assessment_id');
    const submissionId = $('#submission_id');
    const organisationIdInstance = $('#organisation_id');
    const messageWrap = $('.progress-message');

    const current = 1;
    const steps = $("quiz").length;

    const ajaxUrl = ajax_object.ajax_url;

    const continueBtnElement = $("<button>", {id: "continue-quiz-btn", class: "nextPrevBtn next", text: 'Save and continue'});
    const backBtnElement = $("<button>", {id: "go-back-quiz-btn", class: "nextPrevBtn next", text: 'Go back'});
    const submitBtnElement = $("<button>", { id: "submit-quiz-btn", class: "nextPrevBtn next", text: 'Submit' });

    initQuizDetail();
    updateCallToActions();
    styleActiveStep();
    markAsCompletedSection();
    activeFirstPendingSection();

    bodySelector.on('click', '#continue-quiz-btn', async function (e) {
        e.preventDefault();

        let quizCount = getQuizCount();
        let currentQuiz = $(`.quiz.active`);
        let current_quiz_id = currentQuiz.data('group')
        activeQuiz = current_quiz_id
        let nextQuiz = currentQuiz.next()
        let formController = $('.formController');
        let is_required_answer_all = $('#assessment-main-wrapper').data('required_answer_all')
        let is_required_document_all = $('#assessment-main-wrapper').data('required_document_all')

        if (is_required_answer_all == true) {
            let check_answered_quiz = getDataQuizAnswered(currentQuiz)
            if (check_answered_quiz == false) return
        }

        if (is_required_document_all == true) {
            let is_uploaded_doc_required = getUploadedDocumentRequired(currentQuiz)
            if (is_uploaded_doc_required == false) return;
        }
        
        if (activeQuiz >= quizCount) {

            $('#form_submit_quiz').addClass('loading')

			let checkAnswers = getCheckAnswers(currentQuiz);

            let isQuestionSaved = await saveQuestion(checkAnswers);
            if (!isQuestionSaved) return;
            
            await submitAssessment()

            $('#form_submit_quiz').removeClass('loading')
            
        } else {

            $('.formController button').css('opacity', '0.4')
            $('#saving-spinner').show()

            let checkAnswers = getCheckAnswers(currentQuiz);
            var active_question_wrapper = $('#main-quiz-form .quiz.active')
            var count_checked_choices = getCheckedChoices(active_question_wrapper)
            var all_sub_question_wrapper = $('#main-quiz-form .quiz.active .fieldsWrapper').length
            var incomplete_sub_question = (all_sub_question_wrapper - count_checked_choices)

            await submitAssessmentProgressByContinue();

            let isQuestionSaved = await saveQuestion(checkAnswers);

            if (isQuestionSaved) {
                let section_id = active_question_wrapper.data('group')
                let form_message = $('#main-quiz-form .form-message')

                form_message.addClass('_success')
                form_message.find('.message').text('Section '+ section_id +' has been saved.')
                form_message.show()
                setTimeout(function() {
                    form_message.hide()
                    form_message.removeClass('_success')
                }, 15000)
            }
            else {
                return
            }

            let current_step = $(`.step-${current_quiz_id}`);

            $('.formController button').css('opacity', '1')
            $('#saving-spinner').hide()
            moveToNextQuizStep(currentQuiz);
            current_step.removeClass('pending');
            current_step.addClass('completed');

            let current_step_id = current_step.attr('data-id');

            currentQuiz.removeClass('active')
            nextQuiz.addClass('active')

            $('.quizDetails .quiz-' + current_step_id).addClass('quiz-item-hide').removeClass('quiz-item-show');

            activeQuiz++;

            $('#saving-spinner').hide()

            styleActiveStep()

            $('html, body').animate({
                scrollTop: $('#form_submit_quiz').offset().top - 150
            }, 500);
        }

        if (activeQuiz == quizCount) {
            formController.prepend(backBtnElement)
            $(this).text('Submit')
        }
        else {
            formController.prepend(backBtnElement)
            formController.remove(submitBtnElement)
        }
    });

    function getDataQuizAnswered(currentSection) 
    {
        let count_empty_des = 0;
        let choice_answer_area = currentSection.find('.multiple-choice-area').length
        let count_checked_answers = currentSection.find('.form-check-input.checked').length
        let quiz_description = currentSection.find('.quiz-description')

        quiz_description.each(function(e) {
            if ($(this).val() == '') {
                $(this).addClass('required')
                count_empty_des++
            }
        })

        if (choice_answer_area > count_checked_answers) {
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

        if (choice_answer_area > count_checked_answers || count_empty_des > 0) {
            currentSection.find('.answer-notification').show()
            return false
        }
        else if (choice_answer_area = count_checked_answers) {
            return true
        }
    }

    function markAsCompletedSection() 
    {
        let is_required_all_document = $('#assessment-main-wrapper').data('required_document_all')
        let all_sections = $('#form_submit_quiz .quizDetails .quiz')
        let section_id = null;

        all_sections.each(function (e) {
            section_id = $(this).data('group')
            // get all answers choice
            if (is_required_all_document == true) {
                if (isAnswerChoicesChecked($(this)) == true 
                && isAnswerCmtsFilled($(this)) == true
                && getUploadedDocumentRequired($(this)) == true) {
                    $('.stepsWrap .step-'+ section_id).addClass('completed')
                }
            }
            else{
                if (isAnswerChoicesChecked($(this)) == true 
                && isAnswerCmtsFilled($(this)) == true) {
                    $('.stepsWrap .step-'+ section_id).addClass('completed')
                }
            }
        })
    }

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

    function getUploadedDocumentRequired(currentQuiz)
    {
        var upload_file_arr = [];
        let upload_doc_container = currentQuiz.find('.question-add-files-container')
        
        upload_doc_container.each(function(e) {

            let upload_file_items = $(this).find('.filesList .file-item').length
            let upload_message_error = $(this).find('.upload-message._error')
            
            if (upload_file_items == 0) {
                $('#saving-spinner').hide()
                $('.formController button').css('opacity', '1')
                upload_message_error.find('.message').text('Supporting documentation is required!')
                currentQuiz.find('.answer-notification').show()
                upload_message_error.css('display', 'flex')

                setTimeout(function() {
                    currentQuiz.find('.answer-notification').hide()
                }, 15000)
                
            }
            upload_file_arr.push(upload_file_items)
        })

        if (upload_file_arr.includes(0)) {
            return false
        }
        else {
            return true
        }
    }

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

    function activeFirstPendingSection()
    {
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
        // Add class active to first step pending
        $('#main-quiz-form .step.step-'+ step_ids[0]).addClass('active')

        // Add class active to first section pending
        all_section_wrapper.each(function (e) {
            section_wrapper_id = $(this).data('group')
            first_step_id = step_ids[0] ?? 1;
        
            if (section_wrapper_id == first_step_id) {
                $(this).removeClass('quiz-item-hide').addClass('quiz-item-show active')
            }
            else {
                $(this).removeClass('active')
                $(this).removeClass('quiz-item-show')
                if (!$(this).hasClass('quiz-item-hide')) {
                    $(this).addClass('quiz-item-hide')
                }
            }
        })
    }

    bodySelector.on('click', '.progressBtn', async function (e) {
        e.preventDefault();
        $(this).addClass('loading')
        activeQuiz = activeQuiz? activeQuiz : 1;
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
		let checkAnswers = getCheckAnswers(currentQuiz);

        await submitAssessmentProgress();

        let isQuestionSaved = await saveQuestion(checkAnswers);
        $(this).removeClass('loading')

        if (!isQuestionSaved) return;
    });

    bodySelector.on('click', '#go-back-quiz-btn', function (e) {
        e.preventDefault();

        let currentQuiz = $(`#quiz-item-${activeQuiz}`);

        let prevQuiz = $(`#quiz-item-${activeQuiz - 1}`);
        $('#continue-quiz-btn').text('Save and continue')

        if (activeQuiz <= 1) return;
        moveToNextQuizStep(currentQuiz, true);

        currentQuiz.removeClass('active')
        prevQuiz.addClass('active')

        activeQuiz--;

        $('html, body').animate({
            scrollTop: $('#form_submit_quiz').offset().top - 150
        }, 500);

        updateCallToActions();
        styleActiveStep()
    });

    bodySelector.on('change', '.assessment-file', async function (e) {
        e.preventDefault();
        // let attachmentPath = $(this).val()
        let that = $(this);
        let file = e.target.files[0];
        await uploadAssessmentAttachment(file, that)
    });
    bodySelector.on('click', '#submit-quiz-btn', async function (e) {
        e.preventDefault();
        await submitAssessment()
    });

    $(document).on('click focus','.step-item-container', async function (e) {
        e.preventDefault();
        $('.formWrapper .step-item-container').removeClass('active')
        $(this).addClass('active')
        // if ($(this).hasClass('completed') || $(this).hasClass('pending')) {
        if (true) {
            step_item_id = $(this).attr('data-id')
            $('.quizDetails .quiz').addClass('quiz-item-hide').removeClass('quiz-item-show').css('display', 'none').css('opacity', '0')
            $('.quizDetails #quiz-item-' + step_item_id).removeClass('quiz-item-hide').addClass('quiz-item-show').css('display', 'block').css('opacity', '1')
            // console.log(quiz_container);
        }
        let quizCount = getQuizCount();
        let thisQuiz = $(this).data('id');
        $('.formWrapper .quiz').removeClass('active')
        $(`#quiz-item-${thisQuiz}`).addClass('active')

        if (thisQuiz == quizCount) {
            $('#continue-quiz-btn').text('Submit')
        }
        else if (thisQuiz < quizCount) {
            $('#continue-quiz-btn').text('Save and continue')
        }
        else {
            $('#continue-quiz-btn').text('Save and continue')
        }
        let that = $(this);
        let targetQuizId = that.data('id');
        if (targetQuizId === activeQuiz) return;

        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let targetQuiz = $(`#quiz-item-${targetQuizId}`);

        updateCallToActions();

    })

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

    $(document).on('ready', function () {
        if ($('.quiz.active').data('id') == 1) {
            $('.formController').remove('#go-back-quiz-btn')
        }
    })

    $(document).on('click', 'report-toc a', function () {
        $('html, body').animate({
            scrollTop: $(body).offset(200).top
        }, 500);
        console.log('click');
    })

    function initQuizDetail() {
        let allQuizElement = $('.quiz');
        allQuizElement.each(function () {
            let element = $(this);
            if (element.hasClass('quiz-item-show')) {
                activeQuiz = element.data('quiz');
                return false;
            }
        })
    }

    // upload additional files on front fields
    $(document).on('change', ".additional-files", async function(e){

        uploadMutilpleAttachments(this.files, $(this))

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

                uploadMutilpleAttachments(main_files_input.files, $(this))
            }
        }
    
        $(this).removeClass("highlightDropArea");
        return false;
    });
    
    $(".dropFiles").on('dragover', function(e) {
        e.preventDefault();
    });

    function uploadMutilpleAttachments(filesInput, inputElement) {

        // const file_type_arr = [ 
        //         'ppt', 'pptx', 'pdf', 'doc', 
        //         'docx', 'xlsx', 'peg', 'png', 
        //         'jpg', 'jpeg', 'mp4', 'mpg', 
        //         'wmv', 'mov', 'msg', 'svg',
        //     ];

        let group_questions_id =  inputElement.closest('.group-question').data('group')
        let sub_question_id = inputElement.closest('.fieldsWrapper').data('sub')
        let add_files_container = inputElement.closest('.question-add-files-container')
        let upload_message_error = add_files_container.find('.upload-message._error')

        var file_error_list = [];
        var file_id_input = '';
        var file_item = '';
        var item_index = '';
        var fileName = '';
        var filesList = add_files_container.find(".filesList");

        upload_message_error.hide()

        for(var i = 0; i < filesInput.length; i++)
        {
            // Upload droppedFiles[i] to Media
            file_item = $('<span/>', {class: 'file-item', style: 'display:none;',})
            // fileName = $('<a/>', { class: 'name', href: '', text: filesInput.item(i).name,})
            fileName = $('<span/>', { class: 'name', text: filesInput.item(i).name,})
            fileName.prepend('<i class="fa-solid fa-paperclip"></i>')
            file_id_input  = '<input name="" type="hidden" class="input-file-hiden additional-files" value=""/>';

            file_item
                .append(fileName)
                .append(file_id_input)
                .append('<button class="file-delete" aria-label="Remove this uploaded file"><i class="fa-regular fa-trash-can"></i></button>')

            file_ext = filesInput.item(i).name.split('.').pop().toLowerCase();

            // if (jQuery.inArray(file_ext, file_type_arr) !== -1) {
                
                filesList.append(file_item);

                item_index = filesList.children().length

                file_item.addClass('file-item-' + item_index)
                file_item.find('input.input-file-hiden').attr('name', 'questions_'+group_questions_id+'_quiz_'+sub_question_id+'_attachmentIDs_'+ item_index )
                file_item.find('input.input-file-hiden').addClass('additional-file-id-'+ item_index)
                file_item.find('span.name').addClass('file-name-'+ item_index)

                frontUploadAdditionalFiles(filesInput[i], inputElement, item_index);

            // }
            // else {
            //     file_error_list.push({ name: filesInput.item(i).name })
            //     // console.log(file_ext);
            //     if (file_error_list.length > 0) {
            //         let error_text = 'The following file could not be uploaded.<br>'
            //         for (let i = 0; i < file_error_list.length; i++) {
            //             error_text += '<span class="file-name">'+ file_error_list[i].name + '</span><br>'
            //         }
            //             error_text += 'Please make sure your files are in one of the folowing formats: .ppt, .pdf, .docx, .xlsx, .png, .jpg, .mp4.'
            //         upload_message_error.css('display', 'flex')
            //         upload_message_error.find('.message').html(error_text)
            //     }
            // }
        }
    }

    // EventListener for delete file item
    $(document).on('click', '.file-delete', function(){
        let file_item = $(this).closest('.file-item')
        let input_file_hiden = file_item.find('.input-file-hiden')
        let attachmentId = input_file_hiden.val()
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let upload_file_container = $(this).closest('.question-add-files-container')
        let upload_message = upload_file_container.find('.upload-message._success')

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data:{
                'action' : 'delete_azure_attachments_ajax',
                'attachment_id' : attachmentId,
                'assessment_id' : assessmentId,
                'organisation_id' : organisationId,
            },
            beforeSend : function ( xhr ) {
                file_item.css('opacity', '0.5').attr('disable')
            },
            success:function(response){

                console.log(response);

                file_item.remove()

                upload_message.text('Delete file successfully.')
                
                setTimeout(function() {
                    upload_message.show()
                }, 100)
                
                setTimeout(function() {
                    upload_message.hide()
                }, 10000)
            }
        });
    });

    $(document).on('click', '.btn-open-upload-area', function (e) {
        let add_files_container = $(this).closest('.question-add-files-container')
        let upload_area = add_files_container.find('.drop-files-area')
        upload_area.slideToggle()
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

        let blobUrl = $(this).data('blob')

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

    function moveToNextQuizStep(instance, prev = false) {
        let prevQuiz = instance;
        let nextQuiz = prev ? instance.prev() : instance.next();

        nextQuiz.show();
        // prevQuiz.addClass('quiz-item-hide')
        prevQuiz.animate({opacity: 0}, {
            step: function (now) {
                opacity = 1 - now;
                prevQuiz.css({
                    'display': 'none', 'position': 'relative'
                });
                nextQuiz.css({'opacity': opacity});
            }, duration: 500
        });
    }

    function getQuizCount() {
        let quizElement = $('.quizDetails').children('.quiz');
        return quizElement.length;
    }

    function updateCallToActions() {
        let count = getQuizCount();
        let formController = $('.formController');
        let backBtnInstance = $('#go-back-quiz-btn');
        let submitBtnInstance = $('#submit-quiz-btn');

        activeQuiz = $('#main-quiz-form .quiz.active').data('group')

        if (activeQuiz <= 1 || activeQuiz >= count) {
            if (backBtnInstance.length !== 0) backBtnInstance.remove();
        } else {
            if (backBtnInstance.length === 0) {
                formController.prepend(backBtnElement)
            }
        }
        if (activeQuiz > 1) {
            formController.prepend(backBtnElement)
        }
        if (isQuizComplete) {
            formController.prepend(submitBtnElement)
        } else {
            submitBtnInstance.remove();
        }
        if (activeQuiz == 1) {
            formController.find(backBtnInstance).remove()
        }
    }

    function styleActiveStep() {
        let active_quiz_id = $('#main-quiz-form .quiz.active').data('group')
        let step_item = $('#main-quiz-form .step')
        let step_item_id = '';

        step_item.each(function( index, value ) {
            step_item_id = $(this).data('id')

            $(this).removeClass('active')

            if (active_quiz_id == step_item_id) {
                $(this).addClass('active')
            }
        });
    }

    function getDescriptionValue() {
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let input = currentQuiz.find('.quiz-description');

        return input.val();
    }

    function getAttachmentIdInput() {
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
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
            // let input_point = that.find('.quiz-input-point');
            let isChecked = input.is(':checked');

            if (isChecked) {
                choices.push({
                    id: input.data('id'), 
                    title: input.data('title'),
                    // point: input_point.val()
                })
            }
        })

        return choices;
    }

    async function saveQuestion(answers) {
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let submission_id = submissionId.val();
        let answerDescription = getDescriptionValue();
        let attachmentIdValue = getAttachmentIdInput().val();
        let multipleAttachmentIdValue = getMultipleAttachmentIdInput(currentQuiz);
        let data_form = $('#form_submit_quiz');
        let type_quiz = $('input[name="type_quiz"]').val();

        const data = {
            'action': 'save_question',
            'answers': answers,
            'quiz_id': activeQuiz,
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

        console.log(response);
        
        if (status == false) {
            alert(message)
        }
        return status;
    }

    async function submitAssessment() {
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();
        let quizCount = getQuizCount();
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let checkAnswers = getCheckAnswers(currentQuiz);
        let isQuestionSaved = await saveQuestion(checkAnswers);

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
        const {status, message, submission_url} = response;

        alert(message);

        if (status) {
            // redirect to single submission
            $(location).attr('href', submission_url);

            return true;
        }

        return status;
    }

    async function submitAssessmentProgress() {
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();

        const data = {
            'action': 'submit_assessment_progress',
            'assessment_id': assessmentId,
            'organisation_id' : organisationId,
        };

        let response = await $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data,
        });
        const {status, message, submission_id} = response;

        $('#submission_id').attr('value', submission_id);

        alert(message);

        return status;
    }

    async function submitAssessmentProgressByContinue() {
        let assessmentId = assessmentIdInstance.val();
        let organisationId = organisationIdInstance.val();

        const data = {
            'action': 'submit_assessment_progress',
            'assessment_id': assessmentId,
            'organisation_id' : organisationId,
        };

        let response = await $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data
        });
        const {status, message, submission_id} = response;

        console.log(response);

        return status;
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
        // formData.append("security", ajax_object.security)
        
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
        // formData.append("security", ajax_object.security)
    
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
                    console.log(response.message);
                } 
                else {
                    fileUploaderWrap.find('.spinner-upload').hide()
                    dropArea.removeClass('uploading')
                    upload_message_error.find('.message').text('There was aproblem attaching one of your files. Please try again.')
                    upload_message_error.css('display', 'flex')
                    console.log(response.message);
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
});
