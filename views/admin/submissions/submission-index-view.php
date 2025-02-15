<?php
global $post;
global $wpdb;
$main = new WP_Assessment();
$azure = new WP_Azure_Storage();
$feedback_cl = new AndSubmissionFeedbacks();
$current_user = wp_get_current_user();
$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$user_id = get_post_meta($post_id, 'user_id', true);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$sub_id = get_post_meta($post_id, 'quiz_id', true);
$assessment_meta = get_post_meta($assessment_id, 'question_templates', true);
$report_template = get_post_meta($assessment_id, 'report_template', true);
$is_required_answer_all = get_post_meta($assessment_id, 'is_required_answer_all', true);
$quiz_answer_points = get_post_meta($post_id, 'quiz_answer_point', true);
$org_score = get_post_meta($post_id, 'org_score', true);
$and_score = get_post_meta($post_id, 'and_score', true);
$agreed_score = get_post_meta($post_id, 'agreed_score', true);
$submission_key_area = get_post_meta($post_id, 'submission_key_area', true);
$recommentdation = get_post_meta($post_id, 'recommentdation', true);
$terms = get_assessment_terms($assessment_id);
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$group_quiz_points = unserialize(get_post_meta($post_id, 'group_quiz_point', true));
$get_quiz_accepted = $main->get_quiz_accepted($assessment_id, $post_id, $organisation_id);
$question_feedbacks = $feedback_cl->format_feedbacks_by_question($assessment_id, $organisation_id);
$quizzes = $main->get_quizzes_by_assessment_and_submissions($assessment_id, $post_id, $organisation_id);
$reorganize_quizzes = [];
foreach ($quizzes as $row) {
    $reorganize_quizzes[$row->parent_id][$row->quiz_id][] = $row;
}
$azure_attachments_uploaded = $azure->get_azure_attachments_uploaded($assessment_id, $organisation_id);
$quiz_status_options = array('Pending', 'Accepted', 'Rejected');
$submission_score_arr = array();
?>

<input type="hidden" id="assessment_id" name="assessment_id" value="<?php echo $assessment_id ?>"/>
<input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id ?>"/>
<input type="hidden" id="submission_id" name="submission_id" value="<?php echo $post_id ?>"/>
<input type="hidden" id="organisation_id" name="organisation_id" value="<?php echo $organisation_id ?>"/>

<div class="container">
    <?php 
    if ($assessment_meta == 'Simple Assessment'):
        require_once WP_ASSESSMENT_ADMIN_VIEW_DIR . "/submissions/submission-simple-view.php";
    endif; 
    ?>
    <?php if ($assessment_meta == 'Comprehensive Assessment'): ?>
        <!-- Begin Comprehensive Submission -->
        <?php foreach ($questions as $group_id => $field_group):
                $group_title = $field_group['title'] ?? '';
                $group_max_point = $field_group['point'] ?? null;
                $sub_questions = $field_group['list'] ?? array();
                $group_point = $group_quiz_points[$group_id]['point'] ?? null;
                $section_score_arr = array();
            ?>
            <div class="group-quiz-wrapper">
                <p class="group-title"><?php echo $group_id.' - '. esc_html($group_title); ?></p>
                <input type="hidden" name="recommentdation[<?php echo $group_id; ?>][key_area]" value="<?php echo esc_attr($group_title) ?>">
                <?php if (!empty($quizzes) && !empty($sub_questions)): 
                    foreach ($sub_questions as $sub_id => $sub_field):
                        $sub_title = $sub_field['sub_title'] ?? '';
                        $weighting = $sub_field['point'] ?? 0;
                        $key_area = $sub_field['key_area'] ?? '';
                        $sub_list_point = $group_quiz_points[$group_id]['sub_list'] ?? null;
                        $sub_quiz_point = $sub_list_point[$sub_id]['point'] ?? null;
                        $current_quiz_rows = $reorganize_quizzes[$group_id][$sub_id] ?? [];
                        $answers = [];
                        $description = '';
                        $feedback = '';
                        $quiz_status = '';
                        $arr_attachmentID = [];
                        $quiz_point = 0;

                        if (!empty($current_quiz_rows)) {
                            foreach ($current_quiz_rows as $row) {
                                $answers = json_decode($row->answers) ?? [];
                                $description = $row->description ?? '';
                                $feedback = $row->feedback ?? '';
                                $quiz_status = $row->status ?? '';
                                $arr_attachmentID = json_decode($row->attachment_ids) ?? [];
                                $row_submission_id = $row->submission_id ?? null;

                                if (isset($quiz_answer_points[$group_id][$sub_id])) {
                                    $quiz_point = $quiz_answer_points[$group_id][$sub_id];
                                }
                                else {
                                    $quiz_point = $row->quiz_point ? $row->quiz_point : 0;
                                }
                            }
                        }
                        $azure_attachment_rows = $azure_attachments_uploaded[$group_id][$sub_id] ?? '';

                        if (!empty($current_quiz_rows)):
                        ?>
                        <div class="submission-view-item-row" id="main-container-<?php echo $group_id.'_'.$sub_id; ?>"
                            data-submission="<?php echo esc_attr($row_submission_id) ?>">
                            <div class="card-header">
                                <h4 class="quiz-title"><?php echo $group_id.'.'.$sub_id.' - '. esc_html($sub_title); ?></h4>
                            </div>
                            <div class="card content">
                                <div class="card-body">
                                    <input class="quiz_id" type="hidden" name="quiz_id[]" value="<?php echo $sub_id ?>" class="quiz-input"/>
                                    <?php if ( !empty($answers) ) : ?>
                                        <div class="submission-answers-list">
                                            <strong>Selected Answer</strong>
                                            <ul>
                                            <?php foreach ($answers as $answer) : ?>
                                                <li><?php echo $answer->title ?? ''; ?></li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($description)): ?>
                                        <div class="user-comment-area">
                                            <p class="description-label"><strong>User Comment</strong></p>
                                            <div class="description-thin"><?php echo $description; ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($azure_attachment_rows || $arr_attachmentID): ?>
                                        <div class="filesList_submission">
                                            <p><strong>Supporting Documentation</strong></p>
                                            <?php if (!empty($arr_attachmentID)): ?>
                                                <!-- Old WP uploaded -->
                                                <ul class="files-list">
                                                <?php foreach ($arr_attachmentID as $field): ?>
                                                    <?php
                                                        $file = $field->value ?? null;
                                                        $file_url = wp_get_attachment_url($file);
                                                        $file_name = get_the_title($file);
                                                    ?>
                                                    <?php if ( !empty($file_url) ): ?>
                                                    <li class="file-item">
                                                        <span class="name">
                                                            <a href="<?php echo $file_url; ?>" target="_blank">
                                                                <span class="icon-link"><i class="fa-solid fa-paperclip"></i></i></span>
                                                                <?php echo $file_name; ?>
                                                            </a>
                                                        </span>
                                                    </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                </ul>
                                                <!-- /Old WP uploaded -->
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($azure_attachment_rows)): ?>
                                                <!-- New Azure uploaded -->
                                                <ul class="files-list azure">
                                                <?php foreach($azure_attachment_rows as $row): 
                                                    $file_name = $row->attachment_name ?? '';
                                                    $file_url = $row->attachment_path ?? '';
                                                    ?>
                                                    <?php if ( !empty($file_url) ): ?>
                                                        <li class="file-item">
                                                            <span class="name">
                                                                <a class="sas-blob-cta" data-blob="<?php echo $file_url; ?>">
                                                                    <span class="icon-link"><i class="fa-solid fa-paperclip"></i></i></span>
                                                                    <?php echo $file_name; ?>
                                                                </a>
                                                            </span>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                </ul>
                                                <!-- /New Azure uploaded -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (in_array('index', $terms)): ?>
                                        <!-- Question Weighting -->
                                        <div class="weighting" data-weighting="<?php echo $weighting; ?>">
                                            <label class="weighting-label col-6">
                                                Weighting: <strong><?php echo $weighting; ?></strong>
                                            </label>
                                            <label class="answer-point-label col-6">
                                                Answer point: <strong><?php echo $quiz_point; ?></strong>
                                            </label>
                                        </div>
                                        <!-- /Question Weighting -->                                    
                                        <!-- Org Scoring -->
                                        <div class="scoring">
                                            <label class="col-12"><strong>Scoring</strong></label>
                                            <div class="scoring-wrapper">
                                                <div class="org-score">
                                                    <label>Org Score:
                                                        <strong><?php 
                                                            $sub_question_score = 0;
                                                            if (empty($quiz_point)) {
                                                                $section_score_arr[] = 0;
                                                                echo $sub_question_score;
                                                            }
                                                            elseif ($weighting != null && $quiz_point != null) {
                                                                // Get Scoring formula type
		                                                        $scoring_formula = get_post_meta($assessment_id, 'scoring_formula', true);
                                                                if (!empty($scoring_formula) && $scoring_formula == 'index_formula_2024') {
                                                                    $sub_question_score = $quiz_point;
                                                                }
                                                                else {
                                                                    $sub_question_score = $weighting * $quiz_point;
                                                                }
                                                                $section_score_arr[] = $sub_question_score;
                                                                echo $sub_question_score;
                                                            } 
                                                        ?></strong>
                                                        <input class="org-score-input" type="hidden" 
                                                                name="org_score[<?php echo $group_id; ?>][<?php echo $sub_id; ?>]"
                                                                value="<?php echo $sub_question_score; ?>">
                                                    </label>
                                                </div>
                                                <div class="and-score">
                                                    <label for="and-score-input">
                                                        Initial Score
                                                        <input id="and-score-input" type="number" step="0.1" 
                                                                name="and_score[<?php echo $group_id; ?>][<?php echo $sub_id; ?>]" 
                                                                value="<?php echo $and_score[$group_id][$sub_id] ?? null; ?>">
                                                    </label>
                                                </div>
                                                <div class="agreed-score">
                                                    <label for="agreed-score-input">
                                                        Agreed Score
                                                        <input id="agreed-score-input" type="number" step="0.1" 
                                                                name="agreed_score[<?php echo $group_id; ?>][<?php echo $sub_id; ?>]" 
                                                                value="<?php echo $agreed_score[$group_id][$sub_id] ?? null; ?>">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Org Scoring -->
                                        <!-- Recommentdation -->
                                        <div class="recommentdation">
                                            <div class="_top">
                                                <label><strong>Recommendation</strong></label>
                                                <a class="btn-add-recommentdation button button-medium">
                                                    <span class="text">Add Recommendation</span>
                                                    <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                                                </a>
                                            </div>
                                            <div class="_wpeditor">
                                                <?php 
                                                $content   = $recommentdation[$group_id]['list'][$sub_id] ?? null;
                                                $editor_id = 'recommentdation-wpeditor-'.$group_id.'-'.$sub_id;
                                                $editor_settings = array(
                                                    'textarea_name' => 'recommentdation['.$group_id.'][list]['.$sub_id.']',
                                                    'textarea_rows' => 12,
                                                    'quicktags' => true, // Remove view as HTML button.
                                                    'default_editor' => 'tinymce',
                                                    'tinymce' => true,
                                                );
                                                wp_editor( $content, $editor_id, $editor_settings );
                                                ?>
                                            </div>
                                        </div>
                                        <!-- /Recommentdation -->
                                        <div class="key-area">
                                            <label><strong>Section:</strong></label>
                                            <h6 class="_name"><?php echo $key_area; ?></h6>
                                            <input class="org-score-input" type="hidden" 
                                            name="key_area[<?php echo $group_id; ?>][<?php echo $sub_id; ?>]"
                                            value="<?php echo $key_area; ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card feedback">
                                <div class="card-body">
                                    <div class="feedbacks-area">
                                        <label class="heading" for="">Feedbacks</label>
                                        <textarea class="form-control-lg feedback-input" 
                                            name="quiz_feedback[<?php echo $group_id ?>][<?php echo $sub_id ?>]"
                                            placeholder="Add feedback here"
                                            ><?php echo esc_html(wp_unslash($feedback)); ?></textarea>
                                        <a type="button" class="button button-primary btn-save-feedback and-btn" 
                                            data-group-id="<?php echo $group_id ?>"
                                            data-id="<?php echo $sub_id ?>">
                                            Save feedback
                                            <span class="message">Saved</span>
                                        </a>
                                    </div>
                                    
                                    <div class="private-note">
                                        <label class="heading" for="">Private notes</label>
                                        <textarea class="form-control-lg and-feedback-input" 
                                            name="quiz_and_feedback[<?php echo $group_id ?>][<?php echo $sub_id ?>]"
                                            placeholder="Add private note here" ></textarea>
                                        <div class="feedback-actions">
                                            <p class="fb-error-msg"></p>
                                            <a type="button" class="button button-primary and-add-feedback and-btn private-note" 
                                                data-group-id="<?php echo $group_id ?>"
                                                data-id="<?php echo $sub_id ?>">
                                                <span>Add private note</span>
                                            </a>
                                        </div>
                                        <div class="feedback-lst">
                                            <?php 
                                            $q_fb_lst = $question_feedbacks[$group_id][$sub_id] ?? null;
                                            if ( !empty($q_fb_lst) ) {
                                                $q_fb_lst = array_reverse($q_fb_lst);
                                                foreach ($q_fb_lst as $key => $q_fb) {
                                                    $fb_content = strip_tags($q_fb['feedback']) ?? '';
                                                    $fb_class = (strlen($fb_content) > 200) ? 'show_less' : '';
                                                    if ( !empty($fb_content) ) { ?>
                                                        <div id="fb-<?php echo $q_fb['fb_id']; ?>" class="fd-row">
                                                            <div class="fb-content">
                                                                <div class="fb-top">
                                                                    <div class="author">
                                                                        <strong class="name"><?php echo $q_fb['user_name']; ?></strong>
                                                                        <span> - </span>
                                                                        <span class="datetime"><?php echo date("M d Y H:i a", strtotime($q_fb['time'])); ?></span>
                                                                    </div>
                                                                    <?php if ( $current_user->ID == $q_fb['user_id'] ): ?>
                                                                        <span class="ic-delete-feedback" data-fb-id="<?php echo $q_fb['fb_id']; ?>">Remove</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="fb <?php echo $fb_class ?>"><?php echo $fb_content ?></div>
                                                            </div>
                                                            <?php if (strlen($fb_content) > 200): ?>
                                                                <div class="fb-show-more">
                                                                    <a class="btn-showmore">Show more</a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="card-action">
                                    <?php if (!empty($quiz_status_options)): ?>
                                        <strong class="label">Change Status</strong>
                                        <select class="select-quiz-status" 
                                            data-group-id="<?php echo $group_id ?>"
                                            data-quiz-id="<?php echo $sub_id ?>">
                                        <?php foreach ($quiz_status_options as $status_name): ?>
                                            <option value="<?php echo esc_attr($status_name) ?>"
                                                <?php if (strtolower($status_name) === strtolower($quiz_status)) echo "selected"; ?>
                                                ><?php echo esc_html($status_name) ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <div class="quiz-status">
                                    <span>Status: </span>
                                    <strong class="<?php echo esc_attr(wpa_convert_to_slug($quiz_status)) ?>"><?php echo esc_html($quiz_status) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php 
                    if ($section_score_arr != null): 
                        $total_section_score = array_sum($section_score_arr);
                        $count_sub = count($section_score_arr);
                        $section_score = number_format($total_section_score/$count_sub, 1) ?? 0;
                        $submission_score_arr[] = $total_section_score;
                    ?>
                    <div class="total-section-score">
                        <span>Key Area Score (<?php echo $group_title; ?>): 
                            <span class="total-section-score-val">
                                <?php echo $section_score; ?>
                            </span>
                        </span>
                        <input type="hidden" name="org_section_score[<?php echo $group_id; ?>]" 
                                value="<?php echo $section_score; ?>">
                    </div>
                <?php endif;?>
            </div>
        <?php endforeach;?>

        <!-- End Comprehensive Submission -->
        <div class="submission-admin-view-footer">
            <p class="label">Accept or reject this submission</p>
            <div>
                <a type="button" class="button button-primary button-large accept-button" title="Accept this Submission">
                    Accept this Submission
                </a>
                <a type="button" class="button button-large reject-button" title="Reject this Submission">
                    Reject
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
