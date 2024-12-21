<?php
/**
 * Template Comprehensive Assessments Front - Saturn
 * 
 * @author Tuan
 */

get_header(); 

global $post;
$post_id = $post->ID;

if (isset($_COOKIE['userId']) && !empty($_COOKIE['userId'])) {
    $user_id = $_COOKIE['userId'];
} else if (is_user_logged_in()) {
    $user_id = get_user_meta(get_current_user_id(), '__salesforce_user_id', true);
} else {
    $user_id = null;
}

$main = new WP_Assessment();
$question_form = new WPA_Question_Form();
$azure = new WP_Azure_Storage();
$feedback_cl = new AndSubmissionFeedbacks();
$organisation_id = getUser($user_id)->records[0]->AccountId ?? '';
$org_name = '';
$org_data = sf_get_object_metadata('Account', $organisation_id);
if (!empty($org_data)) {
    $org_name = $org_data->Name ?? '';
}
$questions = get_post_meta($post_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$question_templates = get_post_meta($post_id, 'question_templates', true);
$quiz_title = get_the_title($post_id);
$terms = get_assessment_terms($post_id);
$submission_id = $main->get_latest_submission_id($post_id, $organisation_id);
$all_submission_vers = $main->get_all_dcr_submission_vers($post_id, $organisation_id);
$quizzes = $main->get_quizzes_by_assessment_and_submissions($post_id, $submission_id, $organisation_id);

$show_first_active_view = false;
$total_quiz = is_array($questions) ? count($questions) : 0;

$is_submission_exist = $question_form->is_submission_exist($user_id, $post_id);
$assessment_status = get_post_meta($submission_id, 'assessment_status', true);
$is_required_answer_all = get_post_meta($post_id, 'is_required_answer_all', true);
$is_required_document_all = get_post_meta($post_id, 'is_required_document_all', true);
$is_invite_colleagues = get_post_meta($post_id, 'is_invite_colleagues', true);
$dcr_feedbacks = $feedback_cl->format_feedbacks_by_question($post_id, $organisation_id);

// Check user access to asessment
$is_all_users_can_access = get_post_meta($post_id, 'is_all_users_can_access', true);

// Get Status of the Saturn Invite
$saturn_invite_status = get_saturn_invite_status($user_id, $post_id);

// Get all answers desciption of all submissions
$all_quiz_pre_cmts = $main->get_dcr_quiz_answers_pre_submissions($post_id, $submission_id, $organisation_id);

$is_disabled = $assessment_status === 'pending';
$is_publish = $assessment_status === 'publish';
$is_accepted = $assessment_status === 'accepted';

if (($terms[0] == 'dcr')) {
    if (isset($_GET['submission_id'])) {
        $is_disabled = true;
    }
    else {
        $is_disabled = false;
    }
}

// Get the Exception Organisations ID
$exception_orgs_id = get_exception_orgs_id();
?>

<?php if (current_user_can('administrator') || ($_COOKIE['userId'] && is_user_logged_in())): ?>

    <?php if (current_user_can('administrator') || $is_all_users_can_access == true || $saturn_invite_status == 'Active'): ?>

        <?php if (isset($_COOKIE['userId'])): ?>
            <input type="hidden" id="sf_user_id" value="<?php echo $_COOKIE['userId']; ?>" />
        <?php endif; ?>

        <?php if (isset($_COOKIE['sf_name'])): ?>
            <input type="hidden" id="sf_user_name" value="<?php echo $_COOKIE['sf_name']; ?>" />
        <?php endif; ?>
        
        <input type="hidden" id="assessment_id" value="<?php echo $post_id; ?>" />
        <input type="hidden" id="organisation_id" value="<?php echo $organisation_id; ?>"/>
        <input type="hidden" id="org_name" value="<?php echo $org_name; ?>"/>

        <section id="assessment-main-wrapper" class="formWrapper" 
                data-required_answer_all="<?php echo $is_required_answer_all ?>"
                data-required_document_all="<?php echo $is_required_document_all ?>">

                <?php if ($assessment_status == 'rejected' && $questions && !$is_disabled) : ?>
                    <!-- Notification Box -->
                    <div class="notificationBar rejected">
                        <div class="bgRed"><h2>ATTENTION</h2></div>
                        <div class="messageBox">
                            <p class="result">Your submission has been rejected by the moderator, please see the results below.</p>
                            <div class="notifiDetails">
                            <?php foreach ($questions as $group_id => $gr_field) :
                                $submission_data = $main->is_quiz_exist_in_object($group_id, $quizzes, $organisation_id);
                                $section_title = $gr_field['title'] ?? '';
                                $sub_questions = $gr_field['list'] ?? array();
                                ?>
                                <h3><?php echo $group_id .'. '. esc_html($section_title); ?></h3>
                                <ul>
                                <?php
                                foreach ($sub_questions as $sub_id => $field) :
                                    $submission_data_sub = $main->get_quiz_object_sub_question($group_id, $sub_id, $quizzes, $organisation_id);
                                    $sub_title = $field['sub_title'] ?? '';
                                    $all_status = $submission_data_sub['status'] ?? array();
                                    if (!empty($all_status)):
                                        $latest_status = '';
                                        foreach ($all_status as $status):
                                            if ($status == 'accepted'):
                                                $latest_status = 'accepted';
                                                break;
                                            endif;
                                            $latest_status = $status;
                                        endforeach;
                                        echo '<li>'.$group_id.'.'.$sub_id.' - <strong class="remarks '.$latest_status.'">'.$latest_status.'</strong></li>';
                                    endif;
                                    ?>
                                <?php endforeach; ?>
                                </ul>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="revisionRemarks">Please resubmit the assessment for review after completing the revision.</p>
                    </div><!-- .Notification Box -->
                    
                <?php endif; ?>

            <div class="container">
                <div class="topBar <?php if (!in_array('dcr', $terms) || empty($all_submission_vers)) echo 'flex'; ?>">
                    <h1><?php echo $quiz_title; ?></h1>
                    <?php if( (!$is_disabled && !$is_accepted) || in_array($organisation_id, $exception_orgs_id) || $terms[0] == 'dcr' ): ?>
                    <div class="topbar-action">
                        <?php if ($terms[0] == 'dcr' && !empty($all_submission_vers) && is_array($all_submission_vers)): 
                            $count_vers = 0;
                            foreach ($all_submission_vers as $submission) {
                                if ($submission->post_status == 'publish') {
                                    $count_vers++;
                                }
                            }
                            $subm_current_name = '';
                            $next_ver = $count_vers ? $count_vers + 1 : 1;
                            if (isset($_GET['submission_id']) && !empty($_GET['submission_id'])) {
                                $current_sub_id = $_GET['submission_id'];
                                $subm_current_name = get_submission_version_name($current_sub_id);
                            }
                            else {
                                $subm_current_name = 'New Submission ( #'. $next_ver .' )';
                            }
                            ?>
                            <div class="submission-vers">
                                <p>Choose Submission Version</p>
                                <button id="btn-show-submission-vers" class="submission-ver-current" aria-label="Choose Submission Version">
                                    <span><?php echo $subm_current_name; ?></span>
                                    <span class="icon"><i class="fa-solid fa-chevron-down"></i></span>
                                </button>
                                <ul class="sub-vers-list">
                                    <li class="sub-ver-item">
                                        <a href="<?php echo get_the_permalink() ?>">New Submission ( #<?php echo $next_ver ?> )</a>
                                    </li>
                                <?php foreach ($all_submission_vers as $submission): 
                                    if ($submission->post_status == 'publish'):
                                        $sub_ver_name = get_submission_version_name($submission->ID);
                                        $is_current = ($_GET['submission_id'] == $submission->ID) ? 'current' : '';
                                        ?>
                                        <li class="sub-ver-item <?php echo $is_current ?>">
                                            <a href="<?php echo get_the_permalink() .'?submission_id='. $submission->ID ?>">
                                                <?php echo $sub_ver_name; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if (!$is_disabled && !$is_accepted): ?>
                            <button id="save-progress-btn" class="progressBtn" <?php echo $is_disabled ? 'disabled' : '' ?>>
                                <span class="text">Save Progress</span>
                                <div class="spinner-wrapper"><div class="wpa-spinner"></div></div>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if( $is_disabled ): ?>
                    <!-- Notification Box -->
                    <div class="notificationBar pending">
                        <h3>Your submission is under pending review!</h3>
                        <p>Not enable to edit.</p>
                    </div><!-- .Notification Box -->
                <?php endif; ?>

                <?php if($is_accepted && !in_array($organisation_id, $exception_orgs_id)): ?>
                    <!-- Notification Box -->
                    <div class="notificationBar accepted">
                        <h3>Your submission is accepted.</h3>
                    </div><!-- .Notification Box -->
                <?php endif; ?>

                <?php if ( ($question_templates == 'Comprehensive Assessment' && $questions && !$is_accepted) 
                            || ($terms[0] == 'dcr' && in_array($organisation_id, $exception_orgs_id)) ): ?>
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
                                    $questions_list = $field['list'] ?? array();
                                    $is_step_completed = $main->is_group_quiz_completed($questions_list, $group_id, $quizzes);
                                    $step_completed_class = $is_step_completed ? 'completed' : '';
                                ?>
                                    <button id="step-<?php echo $group_id; ?>" class="step step-item-container <?php echo $step_completed_class; ?> step-<?php echo $group_id; ?>" data-id="<?php echo $group_id; ?>">
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
                                <?php foreach ($questions as $group_id => $field): 

                                    $group_question_title = $field['title'] ?? '';
                                    $sub_questions = $field['list'] ?? array();
                                    $item_class = $group_id === 1 ? 'active' : '';
                                    ?>
                                    <div class="group-question quiz <?php echo $item_class; ?>" id="quiz-item-<?php echo $group_id; ?>" data-group="<?php echo $group_id; ?>">
                                        <div class="quizTitle"><?php echo esc_html($group_question_title); ?></div>
                                        <?php foreach ($sub_questions as $sub_id => $field): ?>
                                            <?php
                                                $multiple_choice = $field['choice'] ?? null;
                                                $sub_title = $field['sub_title'] ?? '';
                                                $question_description = wpa_clean_html_string($field['description']) ?? '';
                                                $question_advice = wpa_clean_html_string($field['advice']) ?? '';
                                                $choices_index = 0;
                                                $additional_files = $field['additional_files'] ?? null;
                                                $is_attachment = $field['supporting_doc'] ?? null;
                                                $is_question_description = $field['is_description'] ?? null;
                                                $arr_attachmentID = '';
                                                $answers = '';
                                                $description = '';
                                                $feedback = '';

                                                $current_quiz_sub = $main->get_quiz_object_sub_question($group_id, $sub_id, $quizzes, $organisation_id);

                                                if ($current_quiz_sub) {
                                                    if (array_key_exists('answers', $current_quiz_sub)) {
                                                        $answers = $current_quiz_sub['answers'] ?? '';
                                                    }
                                                    if (array_key_exists('description', $current_quiz_sub)) {
                                                        $description = $current_quiz_sub['description'] ?? '';
                                                    }
                                                    if (array_key_exists('attachment_ids', $current_quiz_sub)) {
                                                        $arr_attachmentID = $current_quiz_sub['attachment_ids'] ?? '';
                                                        $arr_attachmentID = json_decode($arr_attachmentID, true);
                                                    }
                                                    if (array_key_exists('feedback', $current_quiz_sub)) {
                                                        $feedback = $current_quiz_sub['feedback'] ?? '';
                                                    }
                                                }
                                            ?>
                                            <div class="fieldsWrapper sub-quiz-<?php echo $sub_id; ?>" data-sub="<?php echo $sub_id; ?>">
                                                <div class="fieldDetails">
                                                    <h3 class="sub-quiz-title"><?php echo $group_id.'.'.$sub_id.' '. esc_html($sub_title); ?></h3>
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
                                                                        id="checkbox-<?php echo $group_id ?>-<?php echo $sub_id; ?>-<?php echo $choices_index; ?>"
                                                                        data-title="<?php echo $item['answer']; ?>"
                                                                        data-point="<?php echo $item['point']; ?>"
                                                                        name="questions_<?php echo $group_id; ?>_quiz_<?php echo $sub_id; ?>_choice"
                                                                        data-id="<?php echo $choices_index; ?>" <?php echo $is_checked; ?>>
                                                                <label class="form-check-label" for="checkbox-<?php echo $group_id ?>-<?php echo $sub_id; ?>-<?php echo $choices_index; ?>">
                                                                    <?php echo $item['answer']; ?>
                                                                    <?php if ($is_checked): ?>
                                                                        <span class="answer-tooltip">This answer has been selected</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($is_question_description == true) : ?>
                                                    <div class="textAreaWrap">
                                                        <label for="quiz-description-<?php echo $group_id; ?>-<?php echo $sub_id; ?>">
                                                            Your comments 
                                                            <?php if ($is_required_answer_all == true) echo '(Required)'; ?>
                                                        </label>
                                                        <textarea name="questions_<?php echo $group_id; ?>_quiz_<?php echo $sub_id; ?>_description" 
                                                                id="quiz-description-<?php echo $group_id; ?>-<?php echo $sub_id; ?>"
                                                                <?php echo $is_disabled ? 'disabled' : '' ?> 
                                                                class="quiz-description textarea medium" 
                                                                placeholder="Enter comments"
                                                                rows="10"><?php echo wp_unslash($description); ?></textarea>
                                                    </div>
                                                    <?php if (!empty($all_quiz_pre_cmts) && $terms[0] == 'dcr'): ?>
                                                        <div class="pre-comments">
                                                            <p>Previous comments:</p>
                                                            <ul class="pre-comments-list">
                                                            <?php foreach ($all_quiz_pre_cmts as $row): 
                                                                $submission_status = get_post_meta($row->submission_id, 'assessment_status', true);
                                                                if (isset($row->parent_id) && isset($row->quiz_id)):
                                                                    if ($row->parent_id == $group_id && $row->quiz_id == $sub_id && $submission_status != 'draft'):
                                                                        $cmt_time = date("M d Y H:i a", strtotime($row->time));
                                                                        $cmt_desc = !empty($row->description) ? wp_unslash($row->description) : '';
                                                                        $cmt_class = (strlen($cmt_desc) > 400) ? 'show_less' : '';
                                                                        ?>
                                                                        <?php if ($cmt_desc != null): ?>
                                                                            <li class="comment <?php echo $cmt_class; ?>">
                                                                                <span class="_datetime"><?php echo $cmt_time; ?></span>
                                                                                <div class="_content">
                                                                                <?php 
                                                                                    if (strlen($cmt_desc) > 400) {
                                                                                        echo '<div class="show_less">'.substr($cmt_desc, 0, 400).'...</div>';
                                                                                        echo '<div class="show_full">'.$cmt_desc.'</div>';
                                                                                    }
                                                                                    else {
                                                                                        echo $cmt_desc;
                                                                                    }
                                                                                ?>
                                                                                </div>
                                                                                <?php if (strlen($cmt_desc) > 400) echo '<a class="btn-showmore-cmt">Show more</a>';?>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <!-- For NULL description if assessment don't required -->
                                                    <div class="textAreaWrap" style="display:none;">
                                                        <textarea class="quiz-description textarea">description</textarea>
                                                    </div>
                                                    <!-- / -->
                                                <?php endif; ?>

                                                <?php if ($is_attachment == true) : ?>
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
                                                                    <label for="additional-files-<?php echo $group_id.'-'.$sub_id; ?>"
                                                                        <?php if($is_disabled) echo 'style="opacity: 0.5; cursor:default;"'; ?>>
                                                                        <span aria-disabled="false">Browse.</span>
                                                                    </label>
                                                                    <input  <?php if($is_disabled) echo 'disabled'; ?>
                                                                            id="additional-files-<?php echo $group_id.'-'.$sub_id; ?>"
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
                                                            $azure_attachments_uploaded = $azure->get_azure_attachments_uploaded($group_id, $sub_id, $post_id, $organisation_id);
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
                                                                    <input name="questions_<?php echo $group_id; ?>_quiz_<?php echo $sub_id; ?>_attachmentIDs_<?php echo $file_index; ?>"
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
                                                                    <input name="questions_<?php echo $group_id; ?>_quiz_<?php echo $sub_id; ?>_attachmentIDs_<?php echo $file_index; ?>"
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
                                                        <span class="icon-info"><i class="fa-solid fa-circle-info"></i></span>
                                                        <p>Tips and examples</p>
                                                        <div class="advice-area"><?php echo $question_advice; ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($terms[0] == 'dcr'): ?>
                                                    <?php if (!empty($dcr_feedbacks[$group_id][$sub_id])): ?>
                                                        <div class="quizAdvice feedback-area">
                                                            <span class="icon-info"><i class="fa-solid fa-circle-info"></i></span>
                                                            <p>Feedbacks</p>
                                                            <ul class="feedback-list">
                                                                <?php $quiz_feedbacks = array_reverse($dcr_feedbacks[$group_id][$sub_id]); ?>
                                                                <?php foreach ($quiz_feedbacks as $feedback): ?>
                                                                    <?php if (!empty($feedback['feedback'])): ?>
                                                                        <li class="feedback-item">
                                                                            <div class="_info">
                                                                                <strong class="author"><?php echo $feedback['user_name'] ?></strong> - 
                                                                                <span class="datetime"><?php echo date("M d Y H:i a", strtotime($feedback['time'])); ?></span>
                                                                            </div>
                                                                            <div class="_content"><?php echo $feedback['feedback']; ?></div>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if (!empty($feedback)) : ?>
                                                        <div class="quizAdvice feedback-area">
                                                            <span class="icon-info"><i class="fa-solid fa-circle-info"></i></span>
                                                            <p>Feedbacks</p>
                                                            <div><?php echo $feedback; ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="answer-notification">
                                            <p>Please make sure you have answered all questions and provided evidence.</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="formController <?php echo $is_disabled ? 'disabled' : ''; ?>">
                                    <input type="hidden" name="type_quiz" value="<?php echo $question_templates ?>">
                                    <button id="go-back-quiz-btn" class="nextPrevBtn prev show">
                                        <span class="icon"><i class="fa-solid fa-arrow-left"></i></span>
                                        <span>Go back</span>
                                    </button>
                                    <div class="__center">
                                        <button <?php echo $is_disabled ? 'disabled' : '' ?> id="continue-quiz-btn" class="primaryBtn">
                                            <span class="text">Save and continue</span>
                                            <div class="spinner-wrapper"><div class="wpa-spinner"></div></div>
                                        </button>
                                        <button <?php echo $is_disabled ? 'disabled' : '' ?> id="submit-quiz-btn" class="primaryBtn">
                                            <span class="text">Submit</span>
                                            <div class="spinner-wrapper"><div class="wpa-spinner"></div></div>
                                        </button>
                                    </div>
                                    <button id="go-next-quiz-btn" class="nextPrevBtn next show">
                                        <span>Go next</span>
                                        <span class="icon"><i class="fa-solid fa-arrow-right"></i></span>
                                    </button>
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
                <?php 
                    if ($saturn_invite_status == 'Expired') {
                        echo '<p class="access-exprired">The access has expired.</p>';
                    }
                ?>
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
