<?php
/**
 * Assessments template front Saturn
 * 
 * @since 2.0.0
 * @author Tuan
 */

get_header(); 

global $post;
global $wpdb;
$post_id = $post->ID;

if (isset($_COOKIE['userId'])) {
    $user_id = $_COOKIE['userId'];
} else if (is_user_logged_in()) {
    $user_id = get_user_meta(get_current_user_id(), '__salesforce_user_id', true);
} else {
    $user_id = null;
}

$main = new WP_Assessment();
$question_form = new Question_Form();
$azure = new WP_Azure_Storage();

$organisation_id = getUser($_COOKIE['userId'])->records[0]->AccountId;
$questions = get_post_meta($post_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);

$question_templates = get_post_meta($post_id, 'question_templates', true);
$quiz_title = get_the_title($post_id);

$i = 0;
$j = 0;

$submission_id = $main->get_submission_id($post_id, $organisation_id);
$submission_id_save_quiz = $main->is_check_save_progress_quiz($post_id, $organisation_id);

$quiz = $main->get_user_quiz_by_assessment_id($post_id, $organisation_id);

if($submission_id_save_quiz){
    $quiz = $main->get_user_quiz_by_assessment_id_and_submissions($post_id, $submission_id_save_quiz, $organisation_id);
}

$show_first_active_view = false;
$total_quiz = is_array($questions) ? count($questions) : 0;

$is_submission_exist = $question_form->is_submission_exist($user_id, $post_id);
$status = get_post_meta($submission_id, 'assessment_status', true);
$quiz_feedbacks = get_post_meta($submission_id, 'quiz_feedback', true);
$is_required_answer_all = get_post_meta($post_id, 'is_required_answer_all', true);
$is_required_document_all = get_post_meta($post_id, 'is_required_document_all', true);
$is_invite_colleagues = get_post_meta($post_id, 'is_invite_colleagues', true);

// check user access to asessment
$is_user_can_access = check_access_salesforce_members($user_id, $post_id);

$is_disabled = $status === 'pending';
$is_publish = $status === 'publish';
$is_accepted = $status === 'accepted';
?>

<?php if (current_user_can('administrator') || ($_COOKIE['userId'] && is_user_logged_in())): ?>

    <?php if (current_user_can('administrator') || $is_user_can_access == true): ?>

        <?php if (isset($_COOKIE['userId'])): ?>
            <input type="hidden" id="sf_user_id" value="<?php echo $_COOKIE['userId']; ?>" />
        <?php endif; ?>

        <?php if (isset($_COOKIE['sf_name'])): ?>
            <input type="hidden" id="sf_user_name" value="<?php echo $_COOKIE['sf_name']; ?>" />
        <?php endif; ?>
        
        <input type="hidden" id="assessment_id" value="<?php echo $post_id; ?>" />
        <input type="hidden" id="organisation_id" value="<?php echo $organisation_id; ?>"/>

        <section id="assessment-main-wrapper" class="formWrapper" 
                data-required_answer_all="<?php echo $is_required_answer_all ?>"
                data-required_document_all="<?php echo $is_required_document_all ?>">
            <div class="container">
                <div class="topBar">
                    <h1><?php echo $quiz_title; ?></h1>
                    <?php if(!$is_publish && !$is_accepted): ?>
                    <div class="topbar-action">
                        <?php if ($is_invite_colleagues == true): ?>
                            <button id="toggle-invite-colleagues"><span class="material-icons">arrow_forward</span>Invite Colleagues</button>
                        <?php endif; ?>
                        <button <?php echo $is_disabled ? 'disabled' : '' ?> class="progressBtn">
                            Save Progress
                            <img class="progress-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                        </button>
                        <span class="notify">Your progress has been saved</span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($is_invite_colleagues == true): ?>
                    <?php echo $question_form->get_invite_colleagues_form(); ?>
                <?php endif; ?>

                <!-- Notification Box -->
                <?php if ($status == 'rejected' && $questions && !$is_disabled) : ?>
                    <div class="notificationBar">
                        <div class="bgRed"><h3>ATTENTION</h3></div>
                        <div class="messageBox">
                            <p class="result">Your submission has been rejected by the moderator for the following reason.</p>
                            <ul class="testDetails">
                                <?php foreach ($questions as $quiz_id => $field) :

                                    $submission_data = $main->is_quiz_exist_in_object($quiz_id, $quiz, $organisation_id);
                                    $question_title = $field['question_title'] ?? '';
                                    $__status = $submission_data['status'] ?? '';
                                ?>

                                <?php if ($question_templates == 'Comprehensive Assessment') : ?>
                                    <?php
                                    $sub_question = $field['list'];
                                    foreach ($sub_question as $sub => $field) :
                                    $submission_data_sub = $main->is_quiz_exist_in_object_sub($quiz_id,$sub, $quiz, $organisation_id);
                                    $sub_title = $field['sub_title'] ?? '';
                                    $sub_title = htmlentities(stripslashes(utf8_decode($sub_title)));
                                    $__status_sub = $submission_data_sub['status'];
                                    ?>
                                        <?php if ($__status_sub) : ?>
                                            <li>
                                                <?php if ($__status_sub === 'rejected') : ?>
                                                    <span class="crossIcon">
                                                        <i class="fa-solid fa-circle-xmark"></i>
                                                    </span>
                                                <?php elseif ($__status_sub === 'accepted') : ?>
                                                    <span class="checkedIcon">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="stepNumber"><?php echo $sub_title; ?></span>:
                                                <span class="remarks"><strong><?php echo $__status_sub; ?></strong></span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                <?php endif; ?>

                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <p class="revisionRemarks">Please resubmit the assessment for review after completing the revision.</p>
                    </div>
                <?php endif; ?>

                <?php if($is_disabled): ?>
                    <div class="notificationBar">
                        <h3 style="text-align:center;">Your assessment is under pending review!</h3>
                    </div>
                <?php endif; ?>

                <?php if($is_accepted): ?>
                    <div class="notificationBar">
                        <h3 style="text-align:center;">Your assessment is accepted!</h3>
                    </div>
                <?php endif; ?>
                <!-- Notification Box -->

                <?php if ($question_templates == 'Simple Assessment' && $questions && !$is_disabled && !$is_accepted) : ?>
                    <!-- Begin Simple Assessment -->
                    <div class="stepperFormWrap" id="main-quiz-form">
                        <form onsubmit="return false" id="form_submit_quiz">
                            <div class="loading-overlay">
                                <img class="form-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="Spinner"> 
                            </div>
                            
                            <div class="stepsWrap">
                                <?php foreach ($questions as $quiz_id => $field) :

                                    $is_step_completed = $main->is_quiz_exist_in_object($quiz_id, $quiz, $organisation_id);
                                    $step_completed_class = $is_step_completed ? 'completed' : '';
                                    $question_title = $field['title'] ?? '';
                                ?>
                                    <button class="step step-item-container <?php echo $step_completed_class; ?> step-<?php echo $quiz_id; ?>" data-id="<?php echo $quiz_id; ?>">
                                        <span class="editImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/edit.svg" alt="edit">
                                        </span>
                                        <span class="completedImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/completed.svg" alt="completed">
                                        </span>
                                        <span class="pendingImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/pending-png.png" alt="pending">
                                        </span>
                                        <p class="count">
                                            <span class="title">Question <?php echo $quiz_id; ?></span>
                                        </p>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="quizDetails">
                                <?php foreach ($questions as $field) : $j++; ?>
                                    <?php
                                    $multiple_choice = $field['choice'] ?? array();
                                    $question_description = $field['description'] ?? '';
                                    $question_advice = $field['advice'] ?? '';
                                    $choices_index = 0;

                                    $is_attachment = isset($field['is_question_supporting']) ? 1 : '';
                                    $is_question_description = isset($field['is_description']) ? 1 : '';
                                    $question_title = $field['title'] ?? '';

                                    $item_class = $j === 1 ? 'quiz-item-show' : 'quiz-item-hide';
                                    $answers = null;
                                    $description = null;
                                    $attachment_id = null;
                                    $feedback = null;

                                    $current_quiz = $main->is_quiz_exist_in_object($j, $quiz, $organisation_id);

                                    // remove all attr of html tags before print
                                    $question_description = stripslashes($question_description);
                                    $question_description = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $question_description);
                                    $question_advice = stripslashes($question_advice);
                                    $question_advice = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $question_advice);

                                    if ($current_quiz) {
                                        $item_class = $total_quiz === $j ? 'quiz-item-show active' : 'quiz-item-hide';

                                        if (array_key_exists('answers', $current_quiz)) {
                                            $answers = $current_quiz['answers'];
                                        }

                                        if (array_key_exists('description', $current_quiz)) {
                                            $description = $current_quiz['description'];
                                        }

                                        if (array_key_exists('attachment_id', $current_quiz)) {
                                            $attachment_id = $current_quiz['attachment_id'];
                                        }

                                        if (array_key_exists('feedback', $current_quiz)) {
                                            $feedback = $current_quiz['feedback'];
                                        }
                                    } else {
                                        if (!$show_first_active_view) {
                                            $show_first_active_view = true;
                                            $item_class = 'quiz-item-show active';
                                        }
                                    }
                                    ?>
                                    <div class="quiz <?php echo $item_class; ?> quiz-<?php echo $j; ?>" id="quiz-item-<?php echo $j ?>" data-group="<?php echo $j ?>" data-quiz="<?php echo $j ?>">
                                        <div class="quizTitle"><?php echo $question_title; ?></div>
                                        <div class="fieldsWrapper">
                                            <div class="fieldDetails">
                                                <div class="question-description"><?php echo $question_description; ?></div>
                                            </div>
                                            <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                                <div class="multiple-choice-area <?php if (!empty($answers)) echo 'checked'; ?>">
                                                    <?php foreach ($multiple_choice as $item) :
                                                        $choices_index++;
                                                        $is_checked = $main->is_answer_exist($choices_index, $answers) ? 'checked' : null;
                                                        $answer = isset($item['answer']) ? $item['answer'] : null;
                                                        $point = isset($item['point']) ? $item['point'] : null;
                                                    ?>
                                                        <div class="checkBox">
                                                            <input class="form-check-input <?php echo $is_checked; ?>" type="radio"
                                                                    value="" <?php echo $is_disabled ? 'disabled' : '' ?>
                                                                    id="checkbox-<?php echo $j ?>-<?php echo $choices_index; ?>"
                                                                    name="quiz_<?php echo $j; ?>_choice"
                                                                    data-title="<?php echo $answer; ?>" 
                                                                    data-point="<?php echo $point; ?>" 
                                                                    data-id="<?php echo $choices_index; ?>" <?php echo $is_checked; ?>>
                                                            <label class="form-check-label" for="checkbox-<?php echo $j ?>-<?php echo $choices_index; ?>">
                                                                <?php echo $item['answer']; ?>
                                                                <?php if ($is_checked): ?>
                                                                    <span class="answer-tooltip">This answer has been selected</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($is_question_description) : ?>
                                                <div class="textAreaWrap">
                                                    <label for="quiz-description-<?php echo $j; ?>">
                                                        Your comments 
                                                        <?php if ($is_required_answer_all == true) echo '(Required)'; ?>
                                                    </label>
                                                    <textarea name="description" <?php echo $is_disabled ? 'disabled' : '' ?> 
                                                            id="quiz-description-<?php echo $j; ?>"
                                                            class="quiz-description textarea medium" 
                                                            placeholder="Enter comments" 
                                                            rows="10"><?php if (isset($current_quiz['description'])) echo $description; ?></textarea>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($is_attachment) : ?>
                                                <div class="fileUploaderWrap">
                                                    <input type="file" class="assessment-file" <?php echo $is_disabled ? 'disabled' : '' ?> />
                                                    <div class="uploading-wrapper">
                                                        <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                                    </div>
                                                    <input name="attachment_id" type="hidden" class="assessment-attachment-id" <?php echo $is_disabled ? 'disabled' : '' ?> value="<?php echo $attachment_id; ?>" />
                                                    <p class=" fileInstruct">Maximum file size: <?php echo size_format(wp_max_upload_size()); ?> <br> File types
                                                        allowed:
                                                        .ppt, .pdf, .docx</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($question_advice): ?>
                                            <div class="quizAdvice">
                                                <div class="advice-area">
                                                    <?php echo $question_advice; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="answer-notification">
                                            <p>Please make sure you have answered all questions and provided evidence.</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="formController" <?php if($is_disabled) echo 'disabled'; ?>>
                                    <button <?php echo $is_disabled ? 'disabled' : '' ?> id="continue-quiz-btn" class="nextPrevBtn next">Save and continue</button>
                                    <div id="saving-spinner" style="display:none;"> 
                                        <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- End Simple Assessment -->
                <?php endif; ?>

                <?php if ($question_templates == 'Comprehensive Assessment' && $questions && !$is_disabled && !$is_accepted) : ?>
                    <!-- Begin Comprehensive Assessment -->
                    <div class="stepperFormWrap" id="main-quiz-form">
                        <!-- form message -->
                        <div class="form-message">
                            <span class="icon-checked"><i class="fa-solid fa-circle-check"></i></span>
                            <span class="message">Section has been saved.</span>
                        </div>
                        <!-- /form message -->
                        <form onsubmit="return false" id="form_submit_quiz">
                            <div class="loading-overlay">
                                <img class="form-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="Spinner"> 
                            </div>
                            <div class="stepsWrap">
                                <?php foreach ($questions as $group_id => $field):
                                    $args = '';
                                    foreach ($field['list'] as $sub):
                                        $args_choice = $sub['choice'] ?? null;
                                        $args_is_description = $sub['is_description'] ?? null;
                                        $args_supporting_doc = $sub['supporting_doc'] ?? null;
                                        
                                        $args = array(
                                            'is_required_answer_all' => $is_required_answer_all,
                                            'choice' => $args_choice,
                                            'is_description' => $args_is_description,
                                            'supporting_doc' => $args_supporting_doc,
                                        );
                                        $is_step_completed = $main->is_group_quiz_exist_in_object($group_id, $quiz, $organisation_id, $args);
                                    endforeach;

                                    $step_completed_class = $is_step_completed ? 'completed' : '';
                                ?>
                                    <button class="step step-item-container <?php echo $step_completed_class; ?> step-<?php echo $group_id; ?>" data-id="<?php echo $group_id; ?>">
                                        <span class="editImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/edit.svg" alt="edit"> 
                                        </span>
                                        <span class="completedImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/completed.svg" alt="completed">
                                        </span>
                                        <span class="pendingImg">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/pending-png.png" alt="pending">
                                        </span>
                                        <p class="count">
                                            <span class="title">Section <?php echo $group_id; ?></span>
                                        </p>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="quizDetails">
                                <?php foreach ($questions as  $field): 
                                    $j++; 
                                    $group_question_title = $field['title'] ?? '';
                                    $group_question_title = htmlentities(stripslashes(utf8_decode($group_question_title)));
                                    $sub_question = $field['list'];
                                    $current_quiz = $main->is_quiz_exist_in_object($j, $quiz, $organisation_id);
                                    $item_class = $j === 1 ? 'quiz-item-show' : 'quiz-item-hide';
                                    $answers = null;
                                    $description = null;
                                    $attachment_id = null;
                                    $feedback = null;

                                    if ($current_quiz) {
                                        $item_class = $total_quiz === $j ? 'quiz-item-show active' : 'quiz-item-hide';

                                        if (array_key_exists('answers', $current_quiz)) {
                                            $answers = $current_quiz['answers'];
                                        }

                                        if (array_key_exists('description', $current_quiz)) {
                                            $description = $current_quiz['description'];
                                        }

                                        if (array_key_exists('attachment_id', $current_quiz)) {
                                            $attachment_id = $current_quiz['attachment_id'];
                                        }

                                        if (array_key_exists('feedback', $current_quiz)) {
                                            $feedback = $current_quiz['feedback'];
                                        }
                                    } 
                                    else {
                                        if (!$show_first_active_view) {
                                            $show_first_active_view = true;
                                            $item_class = 'quiz-item-show active';
                                        }
                                    }
                                    ?>
                                    <div class="group-question quiz <?php echo $item_class; ?>" id="quiz-item-<?php echo $j; ?>" data-group="<?php echo $j; ?>">
                                        <div class="quizTitle"><?php echo $group_question_title; ?></div>
                                        <?php foreach ($sub_question as $sub_id => $field): ?>
                                            <?php
                                                $multiple_choice = $field['choice'] ?? null;
                                                $sub_title = $field['sub_title'] ?? '';
                                                $question_description = $field['description'];
                                                $question_advice = $field['advice'] ?? '';
                                                $choices_index = 0;
                                                $additional_files = $field['additional_files'] ?? null;
                                                $is_attachment = $field['supporting_doc'] ?? null;
                                                $is_question_description = $field['is_description'] ?? null;
                                                $arr_attachmentID = '';

                                                // remove Slashes of Quotes character(\", \')
                                                $sub_title = htmlentities(stripslashes(utf8_decode($sub_title)));

                                                // remove all attr of html tags before print
                                                $question_description = stripslashes($question_description);
                                                $question_description = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $question_description);
                                                $question_advice = stripslashes($question_advice);
                                                $question_advice = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $question_advice);

                                                $current_quiz_sub = $main->is_quiz_exist_in_object_sub($j, $sub_id, $quiz, $organisation_id);

                                                if ($current_quiz_sub) {
                                                    //$item_class = $total_quiz === $j ? 'quiz-item-show' : 'quiz-item-hide';
                                                    if (array_key_exists('answers', $current_quiz_sub)) {
                                                        $answers = $current_quiz_sub['answers'];
                                                    }
                                                    if (array_key_exists('answers', $current_quiz_sub)) {
                                                        $answers = $current_quiz_sub['answers'];
                                                    }

                                                    if (array_key_exists('description', $current_quiz_sub)) {
                                                        $description = $current_quiz_sub['description'];
                                                    }

                                                    if (array_key_exists('attachment_id', $current_quiz_sub)) {
                                                        $attachment_id = $current_quiz_sub['attachment_id'];
                                                    }

                                                    if (array_key_exists('attachment_ids', $current_quiz_sub)) {
                                                        $arr_attachmentID = $current_quiz_sub['attachment_ids'];
                                                        $arr_attachmentID = json_decode($arr_attachmentID, true);
                                                    }

                                                    if (array_key_exists('feedback', $current_quiz_sub)) {
                                                        $feedback = $current_quiz_sub['feedback'];
                                                        $feedback = htmlentities(stripslashes(utf8_decode($feedback)));
                                                    }
                                                }
                                            ?>
                                            <div class="fieldsWrapper sub-quiz-<?php echo $sub_id; ?>" data-sub="<?php echo $sub_id; ?>">
                                                <div class="fieldDetails">
                                                    <h3 class="sub-quiz-title"><?php echo $j.'.'.$sub_id.' '.$sub_title; ?></h3>
                                                    <div class="question-description"><?php echo $question_description; ?></div>
                                                </div>
                                                <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                                    <div class="multiple-choice-area <?php if (!empty($answers)) echo 'checked'; ?>">
                                                        <?php foreach ($multiple_choice as $item) :
                                                            $choices_index++;
                                                            $is_checked = $main->is_answer_exist_title($item['answer'], $answers) ? 'checked' : '';
                                                        ?>
                                                            <div class="checkBox">
                                                                <input class="form-check-input <?php echo $is_checked; ?>" 
                                                                        type="radio" value="<?php echo $item['answer']; ?>" <?php echo $is_disabled ? 'disabled' : '' ?>
                                                                        id="checkbox-<?php echo $j ?>-<?php echo $sub_id; ?>-<?php echo $choices_index; ?>"
                                                                        data-title="<?php echo $item['answer']; ?>"
                                                                        data-point="<?php echo $item['point']; ?>"
                                                                        name="questions_<?php echo $j; ?>_quiz_<?php echo $sub_id; ?>_choice"
                                                                        data-id="<?php echo $choices_index; ?>" <?php echo $is_checked; ?>>
                                                                <label class="form-check-label" for="checkbox-<?php echo $j ?>-<?php echo $sub_id; ?>-<?php echo $choices_index; ?>">
                                                                    <?php echo $item['answer']; ?>
                                                                    <?php if ($is_checked): ?>
                                                                        <span class="answer-tooltip">This answer has been selected</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($is_question_description) : ?>
                                                    <div class="textAreaWrap">
                                                        <label for="quiz-description-<?php echo $j; ?>-<?php echo $sub_id; ?>">
                                                            Your comments 
                                                            <?php if ($is_required_answer_all == true) echo '(Required)'; ?>
                                                        </label>
                                                        <textarea name="questions_<?php echo $j; ?>_quiz_<?php echo $sub_id; ?>_description" 
                                                                id="quiz-description-<?php echo $j; ?>-<?php echo $sub_id; ?>"
                                                                <?php echo $is_disabled ? 'disabled' : '' ?> 
                                                                class="quiz-description textarea medium" 
                                                                placeholder="Enter comments"
                                                                rows="10"><?php if (isset($current_quiz_sub['description'])) echo $description; ?></textarea>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- For NULL description if assessment don't required -->
                                                    <div class="textAreaWrap" style="display:none;">
                                                        <textarea class="quiz-description textarea">description</textarea>
                                                    </div>
                                                    <!-- / -->
                                                <?php endif; ?>

                                                <?php if ($is_attachment) : ?>
                                                    <div class="question-add-files-container">
                                                        <div class="upload-files-top">
                                                            <button <?php echo $is_disabled ? 'disabled' : ''; ?> class="btn-open-upload-area">
                                                                Upload documents
                                                            </button>
                                                            <p style="margin-top:4px;">
                                                                <?php if ($is_required_document_all == true) echo '(Required)'; ?>
                                                            </p>
                                                            <div class="upload-files-message">
                                                                <div class="upload-message _success">
                                                                    Upload successfully: x file
                                                                </div>
                                                                <div class="upload-message _error">
                                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                                    <p class="message"></p>
                                                                    <span class="remove-message">
                                                                        <i class="fa-solid fa-circle-xmark"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Drag & drop file -->
                                                        <div class="drop-files-area">
                                                            <label class="_label">Upload documents 
                                                                <?php if ($is_required_document_all == true) echo '(Required)'; ?>
                                                            </label>
                                                            <p>
                                                                Your supporting documentation required (Maximum file size: <?php echo size_format(wp_max_upload_size()); ?>, 
                                                                File types allowed: .ppt, .pdf, .docx, .xlsx, .jpg, .png, .mp4,...)
                                                            </p>
                                                            <div id="dropFiles" class="dropFiles" name="file[]">
                                                                <img class="spinner-upload" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-upload-file-2.svg" alt="spinner-upload">
                                                                <div class="icon-upload">
                                                                    <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/icons8-upload-64.png" alt="icon-upload">
                                                                </div>
                                                                <p class="helper-text" style="display:inline;">Drop files to attach, or </p>
                                                                <div class="btn-add-files-wrapper">
                                                                    <label for="additional-files-<?php echo $j.'-'.$sub_id; ?>"
                                                                        <?php if($is_disabled) echo 'style="opacity: 0.5; cursor:default;"'; ?>>
                                                                        <span aria-disabled="false">Browse.</span>
                                                                    </label>
                                                                    <input  <?php if($is_disabled) echo 'disabled'; ?>
                                                                            id="additional-files-<?php echo $j.'-'.$sub_id; ?>"
                                                                            class="additional-files"
                                                                            type="file"
                                                                            name="file[]"
                                                                            style="visibility: hidden; position: absolute;"
                                                                            multiple />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- /Drag & drop file -->

                                                        <div class="filesList">
                                                        <?php 
                                                            $azure_attachments_uploaded = $azure->get_azure_attachments_uploaded($j, $sub_id, $post_id, $organisation_id);
                                                        ?>
                                                        <!-- WP media attachment file -->
                                                        <?php if ($arr_attachmentID): ?>
                                                            <?php foreach($arr_attachmentID as $key => $field): ?>
                                                                <?php
                                                                    $file_id = $field['value'];
                                                                    $file_url = wp_get_attachment_url($file_id);
                                                                    $file_name = get_the_title($file_id);
                                                                    $file_index = $key + 1;
                                                                ?>
                                                                <?php if ($file_url): ?>
                                                                <span class="file-item file-item-<?php echo $file_index; ?>">
                                                                    <a class="name" href="<?php echo $file_url; ?>" target="_blank">
                                                                            <i class="fa-solid fa-paperclip"></i>
                                                                            <?php echo $file_name; ?>
                                                                    </a>
                                                                    <input name="questions_<?php echo $j; ?>_quiz_<?php echo $sub_id; ?>_attachmentIDs_<?php echo $file_index; ?>"
                                                                            type="hidden"
                                                                            class="input-file-hiden additional-files additional-file-id-<?php echo $file_index; ?>"
                                                                            value="<?php echo $file_id; ?>">
                                                                    <?php if($is_disabled): ?>
                                                                        <span class="icon-checked"><i class="fa-solid fa-circle-check"></i></span>
                                                                    <?php else: ?>
                                                                        <button class="file-delete" aria-label="Remove this uploaded file">
                                                                            <i class="fa-regular fa-trash-can"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>

                                                        <!-- Azure Storage attachment file -->
                                                        <?php if ($azure_attachments_uploaded): ?>
                                                            <?php foreach($azure_attachments_uploaded as $key => $field): ?>
                                                                <?php
                                                                    $file_id = $field->attachment_id;
                                                                    $file_name = $field->attachment_name;
                                                                    $file_url = $field->attachment_path;
                                                                    $file_index = $key + 1;
                                                                ?>
                                                                <?php if ($file_url): ?>
                                                                <span class="file-item file-item-<?php echo $file_index; ?>">
                                                                    <button class="name sas-blob-cta" data-blob="<?php echo $file_url; ?>">
                                                                            <i class="fa-solid fa-paperclip"></i>
                                                                            <?php echo $file_name; ?>
                                                                    </button>
                                                                    <input name="questions_<?php echo $j; ?>_quiz_<?php echo $sub_id; ?>_attachmentIDs_<?php echo $file_index; ?>"
                                                                            type="hidden"
                                                                            class="input-file-hiden additional-files additional-file-id-<?php echo $file_index; ?>"
                                                                            value="<?php echo $file_id; ?>">
                                                                    <?php if($is_disabled): ?>
                                                                        <span class="icon-checked"><i class="fa-solid fa-circle-check"></i></span>
                                                                    <?php else: ?>
                                                                        <button class="file-delete" aria-label="Remove this uploaded file">
                                                                            <i class="fa-regular fa-trash-can"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($additional_files) : ?>
                                                    <div class="additional-files-area">
                                                        <p class="__label">
                                                            Additional resources:
                                                        </p>
                                                        <div class="files-list">
                                                            <?php foreach ($additional_files as $file) : ?>
                                                                <?php 
                                                                    $file_url = wp_get_attachment_url($file);
                                                                    $file_name = get_the_title($file)
                                                                ?>
                                                                <?php if ($file_url): ?>
                                                                    <li class="file-item">
                                                                        <a href="<?php echo $file_url; ?>" target="_blank">
                                                                            <span><i class="fa-solid fa-link"></i></span>
                                                                            <?php echo $file_name; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($question_advice) : ?>
                                                    <div class="quizAdvice">
                                                        <p>Tips and examples</p>
                                                        <div class="advice-area"><?php echo $question_advice; ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($status == 'rejected') : ?>
                                                    <div class="quizAdvice feedback-area">
                                                        <p>Feedback</p>
                                                        <div><?php echo $quiz_feedbacks[$j][$sub_id]; ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="answer-notification">
                                            <p>Please make sure you have answered all questions and provided evidence.</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="formController">
                                    <!-- <p class="helper-text">Click continue to save this Section</p> -->
                                    <input type="hidden" name="type_quiz" value="<?php echo $question_templates ?>">
                                    <button <?php echo $is_disabled ? 'disabled' : '' ?> id="continue-quiz-btn" class="nextPrevBtn next">
                                        Save and continue
                                    </button>
                                    <div id="saving-spinner" style="display:none;"> 
                                        <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                    </div>
                                </div>
                                
                            </div>
                        </form>
                    </div>
                    <!-- End Comprehensive Assessment -->
                <?php endif; ?>
            </div>
        </section>
        
    <?php else: ?>
        <!-- User is logged in but not allowed to access asessment -->
        <section class="formWrapper">
            <div class="container">
                <h3 style="text-align:center;">Oops! You can't access this assessment.</h3>
            </div>
        </section>
    <?php endif; ?>

<?php else: ?>
    
    <!-- User is not logged in -->
    <?php 
        $quick_10_register_url = get_field('quick_10_register_url', 'option');
    ?>
    <section id="assessment-login-wrapper" class="formWrapper">
        <div class="container">
            <div class="require-login">
                <h3>Please login or register to access this assessment.</h3>
                <a href="/login" class="btn">Login</a>
                <?php if ($quick_10_register_url): ?>
                    <a href="<?php echo $quick_10_register_url ?>" class="btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
  
<?php get_footer(); ?>
