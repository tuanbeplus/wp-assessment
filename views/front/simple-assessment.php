<?php
/**
 * Template Simple Assessments Front - Saturn
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
$organisation_id = getUser($user_id)->records[0]->AccountId ?? '';
$org_name = '';
$org_data = sf_get_object_metadata('Account', $organisation_id);
if (!empty($org_data)) {
    $org_name = $org_data->Name ?? '';
}
$questions = get_post_meta($post_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$question_templates = get_post_meta($post_id, 'question_templates', true);
$terms = get_assessment_terms($post_id);

$submission_id = $main->get_latest_submission_id($post_id, $organisation_id);

if (isset($submission_id)) {
    $quizzes = $main->get_quizzes_by_assessment_and_submissions($post_id, $submission_id, $organisation_id);
}
else {
    $quizzes = $main->get_quizzes_by_assessment($post_id, $organisation_id);
}

$show_first_active_view = false;
$total_quiz = is_array($questions) ? count($questions) : 0;

$is_submission_exist = $question_form->is_submission_exist($user_id, $post_id);
$status = get_post_meta($submission_id, 'assessment_status', true);
$is_required_answer_all = get_post_meta($post_id, 'is_required_answer_all', true);
$is_required_document_all = get_post_meta($post_id, 'is_required_document_all', true);
$is_invite_colleagues = get_post_meta($post_id, 'is_invite_colleagues', true);

// Check user access to asessment
$is_all_users_can_access = get_post_meta($post_id, 'is_all_users_can_access', true);

// Get Status of the Saturn Invite
$saturn_invite_status = get_saturn_invite_status($user_id, $post_id);

$is_disabled = $status === 'pending';
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
            <div class="container">
                <div class="topBar <?php if (!in_array('dcr', $terms)) echo 'flex'; ?>">
                    <h1><?php echo get_the_title($post_id); ?></h1>
                    <?php if( (!$is_disabled) ): ?>
                    <div class="topbar-action">
                        <button id="save-progress-btn" class="progressBtn" <?php echo $is_disabled ? 'disabled' : '' ?>>
                            <span class="text">Save Progress</span>
                            <div class="spinner-wrapper"><div class="wpa-spinner"></div></div>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($question_templates == 'Simple Assessment' && !empty($questions)) : ?>
                    <!-- Begin Simple Assessment -->
                    <div class="stepperFormWrap" id="main-quiz-form">
                        <!-- form message -->
                        <div class="form-message">
                            <span class="icon-checked"><i class="fa-solid fa-circle-check"></i></span>
                            <span class="message">Section has been saved.</span>
                        </div>
                        <!-- /form message -->
                        <form onsubmit="return false" id="form_submit_quiz">
                            <div class="stepsWrap">
                                <?php foreach ($questions as $quiz_id => $field) :
                                    $current_quiz_data = $main->is_quiz_exist_in_object($quiz_id, $quizzes, $organisation_id);
                                    $quiz_answer = $current_quiz_data['answers'] ?? '';
                                    $quiz_desc = $current_quiz_data['description'] ?? '';
                                    $step_completed_class = '';
                                    if (!empty($quiz_answer) || !empty($quiz_desc)):
                                        $step_completed_class = 'completed';
                                    endif;
                                ?>
                                    <button id="step-<?php echo $quiz_id; ?>" class="step step-item-container <?php echo $step_completed_class; ?> step-<?php echo $quiz_id; ?>" data-id="<?php echo $quiz_id; ?>">
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
                                <?php foreach ($questions as $quiz_id => $field): 
                                    $multiple_choice = $field['choice'] ?? array();
                                    $question_description = wpa_clean_html_string($field['description']) ?? '';
                                    $question_advice = wpa_clean_html_string($field['advice']) ?? '';
                                    $choices_index = 0;
                                    $is_attachment = $field['is_question_supporting'] ?? '';
                                    $is_question_description = $field['is_description'] ?? '';
                                    $question_title = $field['title'] ?? '';
                                    $item_class = $quiz_id === 1 ? 'active' : '';
                                    $answers = null;
                                    $description = null;
                                    $attachment_id = null;
                                    $feedback = null;
                                    $current_quiz = $main->is_quiz_exist_in_object($quiz_id, $quizzes, $organisation_id);

                                    if ($current_quiz) {
                                        $item_class = $total_quiz === $quiz_id ? 'active' : '';

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
                                            $item_class = 'active';
                                        }
                                    }
                                    ?>
                                    <div class="quiz <?php echo $item_class; ?> quiz-<?php echo $quiz_id; ?>" id="quiz-item-<?php echo $quiz_id ?>" data-group="<?php echo $quiz_id ?>" data-quiz="<?php echo $quiz_id ?>">
                                        <div class="quizTitle"><?php echo esc_html($question_title); ?></div>
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
                                                                    id="checkbox-<?php echo $quiz_id ?>-<?php echo $choices_index; ?>"
                                                                    name="quiz_<?php echo $quiz_id; ?>_choice"
                                                                    data-title="<?php echo $answer; ?>" 
                                                                    data-point="<?php echo $point; ?>" 
                                                                    data-id="<?php echo $choices_index; ?>" <?php echo $is_checked; ?>>
                                                            <label class="form-check-label" for="checkbox-<?php echo $quiz_id ?>-<?php echo $choices_index; ?>">
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
                                                    <label for="quiz-description-<?php echo $quiz_id; ?>">
                                                        Your comments 
                                                        <?php if ($is_required_answer_all == true) echo '(Required)'; ?>
                                                    </label>
                                                    <textarea name="description" <?php echo $is_disabled ? 'disabled' : '' ?> 
                                                            id="quiz-description-<?php echo $quiz_id; ?>"
                                                            class="quiz-description textarea medium" 
                                                            placeholder="Enter comments" 
                                                            rows="10"><?php if (isset($current_quiz['description'])) echo $description; ?></textarea>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($is_attachment == true) : ?>
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
                                                <span class="icon-info"><i class="fa-solid fa-circle-info"></i></span>
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
                                <div class="formController" <?php echo $is_disabled ? 'disabled' : ''; ?>>
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
                                    <div id="saving-spinner" style="display:none;"> 
                                        <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- End Simple Assessment -->
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
