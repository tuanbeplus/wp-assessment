<?php
global $post;
$main = new WP_Assessment();
$azure = new WP_Azure_Storage();
$feedback_cl = new AndSubmissionFeedbacks();
$current_user = wp_get_current_user();
$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$user_id = get_post_meta($post_id, 'user_id', true);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$submission_status = get_post_meta($post_id, 'assessment_status', true);
$assessment_meta = get_post_meta($assessment_id, 'question_templates', true);
$report_template = get_post_meta($assessment_id, 'report_template', true);
$is_required_answer_all = get_post_meta($assessment_id, 'is_required_answer_all', true);
$terms = get_assessment_terms($assessment_id);
$quiz_status_options = get_submission_quiz_status_options();
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$question_feedbacks = $feedback_cl->format_feedbacks_by_question($assessment_id, $organisation_id);
$quizzes = $main->get_quizzes_by_assessment_and_submissions($assessment_id, $post_id, $organisation_id);
$reorganize_quizzes = [];
foreach ($quizzes as $row) {
    $reorganize_quizzes[$row->parent_id][$row->quiz_id][$row->submission_id] = $row;
}
$azure_attachments_uploaded = $azure->get_azure_attachments_uploaded($assessment_id, $organisation_id);
$all_quizzes_status = get_post_meta($post_id, 'quizzes_status', true);
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
            ?>
            <div class="group-quiz-wrapper dcr">
                <p class="group-title"><?php echo $group_id.' - '. esc_html($group_title); ?></p>
                <input type="hidden" name="recommentdation[<?php echo $group_id; ?>][key_area]" value="<?php echo esc_attr($group_title) ?>">
                <?php if (!empty($sub_questions) && !empty($quizzes)): ?>
                    <?php foreach ($sub_questions as $sub_id => $sub_field):
                        if (!empty($sub_field)):
                        $answers = [];
                        $description = '';
                        $arr_attachmentID = null;
                        $quiz_status = '';
                        $sub_title = $sub_field['sub_title'] ?? '';
                        $weighting = $sub_field['point'] ?? 0;
                        $key_area = $sub_field['key_area'] ?? null;
                        $choices = $sub_field['choice'] ?? [];
                        $is_desc = $sub_field['is_description'] ?? false;
                        $is_supporting_doc = $sub_field['supporting_doc'] ?? false;
                        $quiz_rows = $reorganize_quizzes[$group_id][$sub_id] ?? [];
                        $this_submission_row = $quiz_rows[$post_id] ?? null;
                        $current_quiz_row = null;
                        $azure_attachment_rows = $azure_attachments_uploaded[$group_id][$sub_id] ?? [];
                        $meta_quiz_status = $all_quizzes_status[$group_id][$sub_id]['meta_status'] ?? '';
                        $meta_status_time = $all_quizzes_status[$group_id][$sub_id]['datetime'] ?? '';

                        // Sort the rows by submission_id in DESC order
                        usort($quiz_rows, function($a, $b) {
                            return $b->submission_id <=> $a->submission_id;
                        });

                        if (isset($this_submission_row) && !empty($this_submission_row)) {
                            $current_quiz_row = $this_submission_row;
                        }
                        else {
                            $current_quiz_row = array_reduce($quiz_rows, function ($carry, $item) {
                                return $carry === null || $item->submission_id > $carry->submission_id ? $item : $carry;
                            }, null);
                        }

                        if (!empty($current_quiz_row)) {
                            $row_submission_id = $current_quiz_row->submission_id ?? '';
                            $answers = !empty($current_quiz_row->answers) ? json_decode($current_quiz_row->answers) : '';
                            $arr_attachmentID = !empty($current_quiz_row->attachment_ids) ? json_decode($current_quiz_row->attachment_ids) : '';
                            $description = $current_quiz_row->description ?? '';

                            if (!empty($meta_quiz_status)) {
                                $quiz_status = $meta_quiz_status;
                            }
                            else {
                                $quiz_status = $current_quiz_row->status ?? '';
                                foreach ($quiz_rows as $row) {
                                    if (!empty($row->status) && wpa_convert_to_slug($row->status) !== 'pending') {
                                        $quiz_status = $row->status;
                                        break;
                                    }
                                }
                            }
                        }
                        ?>
                        <?php if (!empty($current_quiz_row) 
                                && (!empty($choices) || $is_desc == true || $is_supporting_doc == true) 
                                && (!empty($answers) || !empty($description) || !empty($arr_attachmentID)) 
                            ): 
                        ?>
                        <!-- Sub Question Row -->
                        <div class="submission-view-item-row" id="main-container-<?php echo $group_id.'_'.$sub_id; ?>"
                            data-submission="<?php echo esc_attr($row_submission_id) ?>">
                            <div class="card-header">
                                <h4 class="quiz-title"><?php echo $group_id.'.'.$sub_id.' - '. esc_html($sub_title); ?></h4>
                            </div>
                            <div class="card content">
                                <div class="card-body">
                                    <input class="quiz_id" type="hidden" name="quiz_id[]" value="<?php echo $sub_id ?>" class="quiz-input"/>
                                    <?php if (is_array($answers) && count($answers) > 0) : ?>
                                        <div class="submission-answers-list">
                                            <strong>Selected Answer</strong>
                                            <ul>
                                            <?php foreach ($answers as $answer) : ?>
                                                <li><?php echo $answer->title; ?></li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($quiz_rows)): ?>
                                        <div class="user-comment-area">
                                            <p class="description-label"><strong>User Comments</strong></p>
                                            <?php foreach ($quiz_rows as $row): 
                                                $cmt_time = date("M d Y H:i a", strtotime($row->time)) ?? '';
                                                $cmt_desc = $row->description ?? '';
                                                $cmt_class = ($row->submission_id == $post_id) ? 'current' : '';
                                                ?>
                                                <?php if (!empty($cmt_desc)): ?>
                                                    <div class="description-thin <?php echo $cmt_class; ?>">
                                                        <span class="datetime"><?php echo $cmt_time; ?></span>
                                                        <div class="description"><?php echo wp_unslash($cmt_desc); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($azure_attachment_rows || $arr_attachmentID): ?>
                                        <div class="filesList_submission">
                                            <p><strong>Supporting Documentation</strong></p>
                                            <?php if (!empty($arr_attachmentID)): ?>
                                                <!-- Old WP uploaded -->
                                                <ul class="files-list">
                                                <?php foreach($arr_attachmentID as $field): ?>
                                                    <?php
                                                        $file = $field->value ?? '';
                                                        $file_url = wp_get_attachment_url($file);
                                                        $file_name = get_the_title($file);
                                                    ?>
                                                    <?php if ($file_url): ?>
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
                                                    $file_datetime = date("M d Y H:i a", strtotime($row->time)) ?? '';
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
                                                                <?php if (!empty($file_datetime)): ?>
                                                                    <span class="datetime"><?php echo $file_datetime ?></span>
                                                                <?php endif; ?>
                                                            </span>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                </ul>
                                                <!-- /New Azure uploaded -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card feedback">
                                <div class="card-body">
                                    <label class="heading" for="">Feedbacks</label>
                                    <?php
                                        $content   = '';
                                        $editor_id = 'feedback-wpeditor-'.$group_id.'-'.$sub_id;
                                        $editor_settings = array(
                                            'media_buttons' => false, // This setting removes the media button.
                                            'textarea_name' => "quiz_and_feedback[$group_id][$sub_id]",
                                            'textarea_rows' => 12,
                                            'quicktags' => true, // Remove view as HTML button.
                                            'default_editor' => 'tinymce',
                                            'tinymce' => true,
                                            'editor_class' => 'and-feedback-input',
                                        );
                                        wp_editor( $content, $editor_id, $editor_settings );
                                    ?>
                                    <div class="feedback-actions">
                                        <p class="fb-error-msg"></p>
                                        <a type="button" class="button button-primary and-add-feedback and-btn button-large" 
                                            data-group-id="<?php echo $group_id ?>"
                                            data-id="<?php echo $sub_id ?>">
                                            <span>Add feedback</span>
                                        </a>
                                    </div>
                                    <div class="feedback-lst">
                                        <?php 
                                        $q_fb_lst = $question_feedbacks[$group_id][$sub_id] ?? null;
                                        if ( !empty($q_fb_lst) ) {
                                            $q_fb_lst = array_reverse($q_fb_lst);
                                            foreach ($q_fb_lst as $key => $q_fb) {
                                                $fb_content = $q_fb['feedback'] ?? '';
                                                if (!empty($fb_content)):  
                                                    $fb_class = (strlen($fb_content) > 200) ? 'show_less' : '';
                                                ?>
                                                    <div id="fb-<?php echo $q_fb['fb_id']; ?>" class="fd-row">
                                                        <div class="fb-content">
                                                            <div class="fb-top">
                                                                <div class="author">
                                                                    <strong class="name"><?php echo esc_html($q_fb['user_name']); ?></strong>
                                                                    <span> - </span>
                                                                    <span class="datetime"><?php echo esc_html(date("M d Y H:i a", strtotime($q_fb['time']))); ?></span>
                                                                </div>
                                                                <?php if ( $current_user->ID == $q_fb['user_id'] ) { ?>
                                                                    <span class="ic-delete-feedback" data-fb-id="<?php echo $q_fb['fb_id']; ?>">Remove</span>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="fb <?php echo $fb_class ?>"><?php echo wp_kses_post(htmlspecialchars_decode($fb_content)); ?></div>
                                                        </div>
                                                        <?php if (strlen($fb_content) > 200): ?>
                                                            <div class="fb-show-more">
                                                                <a class="btn-showmore">Show more</a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif;
                                            }
                                        }
                                        ?>
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
                                    <strong class="<?php echo esc_attr(wpa_convert_to_slug($quiz_status)) ?>"><?php echo ucwords($quiz_status) ?></strong>
                                </div>
                            </div>
                        </div>
                        <!-- .Sub Question Row -->
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach;?>

        <div class="submission-admin-view-footer">
            <div>
                <p>Change review status of this submission</p>
                <?php foreach ($quiz_status_options as $status_name): ?>
                    <a type="button" data-status="<?php echo esc_attr(ucwords($status_name)); ?>" class="btn-update-review-status button button-large">
                        <?php echo ucwords($status_name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="current-status">Current status: 
                <strong class="status-name <?php echo wpa_convert_to_slug($submission_status); ?>">
                    <?php echo ucwords(str_replace('-', ' ', $submission_status)); ?>
                </strong>
            </p>
        </div>
         <!-- End Comprehensive Submission -->
    <?php endif; ?>
</div>



