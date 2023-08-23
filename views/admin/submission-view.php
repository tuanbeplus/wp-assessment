<?php
global $post;
global $wpdb;

$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$user_id = get_post_meta($post_id, 'user_id', true);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$quiz_id = get_post_meta($post_id, 'quiz_id', true);
$assessment_meta = get_post_meta($assessment_id, 'question_templates', true);
$report_template_content = get_post_meta($assessment_id, 'report_template_content', true);
$report_recommendation = get_post_meta($assessment_id, 'report_recommendation', true);
$executive_summary = get_post_meta($assessment_id, 'executive_summary', true);
$evalution_findings = get_post_meta($assessment_id, 'evalution_findings', true);
$quiz_feedbacks = get_post_meta($post_id, 'quiz_feedback', true);
$quiz_answer_points = get_post_meta($post_id, 'quiz_answer_point', true);
$is_required_answer_all = get_post_meta($assessment_id, 'is_required_answer_all', true);

$main = new WP_Assessment();
$quiz = $main->get_user_quiz_by_assessment_id_and_submissions($assessment_id, $post_id, $organisation_id);
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$group_quiz_points = unserialize(get_post_meta($post_id, 'group_quiz_point', true));
$get_quiz_accepted = $main->get_quiz_accepted($assessment_id, $post_id, $organisation_id);

// if ($_GET['test'] == 'test') {
// }

$table_name = $wpdb->prefix . 'user_quiz_submissions';
$sql_query_points = "SELECT quiz_point FROM $table_name WHERE submission_id = $post_id AND user_id = '$user_id'";
$result = $wpdb->get_results($sql_query_points);

$i = 0;
function get_submit_field($array, $index, $key)
{
    if (!key_exists($index, $array) || !key_exists($key, $array[$index])) return;
    return $array[$index][$key];
}
$submission_score_arr = array();
?>

<div class="container">
    <?php if ($assessment_meta == 'Simple Assessment'): ?>
        <!-- Begin Simple Submission -->
        <?php if ($quiz && is_array($quiz)) : ?>
            <?php foreach ($quiz as $field) :
                $i++;
                $answers = [];
                $attachment_id = null;
                $attachment_type = null;
                $url = null;
                $feedback = null;
                $type = null;

                if ($field->answers) {
                    $answers = json_decode($field->answers);
                }
                if ($field->feedback) {
                    $feedback = $field->feedback;
                }
                if ($field->status) {
                    $type = $field->status;
                }
                if ($field->attachment_id) {
                    $attachment_id = $field->attachment_id;
                    $url = wp_get_attachment_url($attachment_id);
                    $attachment_type = get_post_mime_type($attachment_id);
                }
                $max_point = get_submit_field($questions, $i, 'question_point');
                $question_title = $questions[$i]['title'];
                ?>
                <div class="submission-view-item-row" id="<?php echo $i ?>-main-container">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="quiz-title"><?php echo $i .' - '. $question_title; ?></h4>
                            <?php if (is_array($answers) && count($answers) > 0) : ?>
                                <div class="submission-answers-list">
                                    <strong>Selected Answer:</strong>
                                    <ul>
                                        <?php foreach ($answers as $answer) : ?>
                                            <li><?php echo $answer->title; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($field->description): ?>
                                <div class="user-comment-area">
                                    <p class="description-label"><strong>User Comment: </strong></p>
                                    <p class="description-thin"><?php echo $field->description; ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($attachment_id) : ?>
                                <a href="<?php echo $url ?>" target="_blank"><p>View Supporting Documentation</p></a>
                            <?php endif; ?>
                            <?php if ($assessment_meta == 'Comprehensive Assessment'): ?>
                                <div class="row weighting">
                                    <label class="weighting-label"><strong>Weighting: <?php echo $max_point ?></strong></label>
                                    <input class="input-weighting" type="number" max="<?php echo $max_point ?>" placeholder="Points" name="quiz_point" value="<?php echo $field->quiz_point; ?>" />
                                </div>
                            <?php endif; ?>
                            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id ?>" />
                            <input type="hidden" name="user_id" value="<?php echo $user_id ?>" />
                            <input class="quiz_id" type="hidden" name="quiz_id[]" value="<?php echo $i ?>" class="quiz-input" />
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <input type="hidden" id="assessment_id" value="<?php echo $assessment_id ?>"/>
            <input type="hidden" id="user_id" value="<?php echo $user_id ?>"/>
            <input type="hidden" id="submission_id" value="<?php echo $post_id ?>"/>
            <input type="hidden" id="organisation_id" value="<?php echo $organisation_id ?>"/>

            <?php if ($assessment_meta == 'Comprehensive Assessment'): ?>
                <div class="submission-admin-view-footer">
                    <a type="button" class="button button-primary button-large accept-button" title="Accept this Submission">
                        Accept
                    </a>
                    <a type="button" class="button button-large reject-button" title="Reject this Submission">
                        Reject
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <!-- End Simple Submission -->
    <?php endif; ?>

    <?php if ($assessment_meta == 'Comprehensive Assessment'): ?>
        <!-- Begin Comprehensive Submission -->
        <?php 
            $group_count = count($questions); 
        ?>
        <?php foreach ($questions as $group_id => $field_group):?>
            <?php 
                $group_title = $field_group['title'];
                $group_title = htmlentities(stripslashes(utf8_decode($group_title)));
                $group_max_point = $field_group['point'];
                $group_list = $field_group['list'];
                $group_point = $group_quiz_points[$group_id]['point'];
                $section_score_arr = array();
            ?>
            <div class="group-quiz-wrapper">
                <p class="group-title"><?php echo $group_id.' - '.$group_title; ?></p>
                <!-- <div class="row weighting">
                    <label class="weighting-label"><strong>Weighting: <?php //echo $group_max_point ?></strong></label>
                    <input class="input-group-weighting" type="number" max="<?php //echo $group_max_point ?>" 
                            placeholder="Points" name="group_quiz_point[<?php //echo $group_id; ?>][point]" 
                            value="<?php //echo $group_point; ?>" />
                </div> -->

                <!--  -->
                <?php if ($quiz && is_array($quiz)) : ?>
                    <?php foreach ($quiz as $field) :
                        if ($field->parent_id == $group_id):
                        // $i++;
                        $answers = [];
                        $attachment_id = null;
                        $attachment_type = null;
                        $url = null;
                        $feedback = null;
                        $type = null;

                        $quiz_id = $field->quiz_id;

                        if (!empty($quiz_answer_points[$group_id][$quiz_id])) {
                            $quiz_point = $quiz_answer_points[$group_id][$quiz_id];
                        }
                        else {
                            $quiz_point = $field->quiz_point ? $field->quiz_point : 0;
                        }
                        

                        $question_meta_field = $group_list[$quiz_id];
                        $sub_title = htmlentities(stripslashes(utf8_decode($question_meta_field['sub_title'])));
                        $sub_list_point = $group_quiz_points[$field->parent_id]['sub_list'];
                        $sub_quiz_point = $sub_list_point[$quiz_id]['point'];

                        if ($field->answers) {
                            $answers = json_decode($field->answers);
                        }
                        // if ($field->feedback) {
                        //     $feedback = $field->feedback;
                        //     $feedback = htmlentities(stripslashes(utf8_decode($feedback)));
                        // }
                        if ($field->status) {
                            $type = $field->status;
                        }
                        if ($field->attachment_id) {
                            $attachment_id = $field->attachment_id;
                            $url = wp_get_attachment_url($attachment_id);
                            $attachment_type = get_post_mime_type($attachment_id);
                        }
                        if ($field->attachment_ids) {
                            $arr_attachmentID = $field->attachment_ids;
                            $arr_attachmentID = json_decode($arr_attachmentID, true);
                        }
                        ?>
                        <div class="submission-view-item-row" id="main-container-<?php echo $group_id.'_'.$quiz_id; ?>">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="quiz-title"><?php echo $group_id.'.'.$quiz_id.' - '.$sub_title; ?></h4>
                                </div>
                                <div class="card-body">
                                    <?php if (is_array($answers) && count($answers) > 0) : ?>
                                        <div class="submission-answers-list">
                                            <strong>Selected Answers</strong>
                                            <ul>
                                                <?php foreach ($answers as $answer) : ?>
                                                    <li><?php echo $answer->title; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($field->description): ?>
                                        <div class="user-comment-area">
                                            <p class="description-label"><strong>User Comment: </strong></p>
                                            <div class="description-thin"><?php echo $field->description; ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($field->attachment_ids) : ?>
                                        <div class="filesList_submission">
                                        <p><strong>Supporting Documentation</strong></p>
                                        <?php foreach($arr_attachmentID as $field): ?>
                                            <?php
                                                $file = $field['value'];
                                                $file_url = wp_get_attachment_url($file);
                                                $file_name = get_the_title($file);
                                            ?>
                                            <?php if ($file_url): ?>
                                            <span class="file-item">
                                                <span class="name">
                                                    <a href="<?php echo $file_url; ?>" target="_blank">
                                                        <span class="icon-link"><i class="fa-solid fa-paperclip"></i></i></span>
                                                        <?php echo $file_name; ?>
                                                    </a>
                                                </span>
                                            </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php 
                                        $weighting = $question_meta_field['point'] ? $question_meta_field['point'] : 0;
                                        ?>
                                    <div class="row weighting" data-weighting="<?php echo $weighting; ?>">
                                        <label class="weighting-label"><strong>Weighting: <?php echo $weighting; ?></strong></label>
                                        <div class="field-answer-point">
                                            <label for="input-answer-point-<?php echo $group_id; ?>-<?php echo $quiz_id; ?>" class="weighting-label">
                                                <strong>Answer point:</strong>
                                            </label>
                                            <input class="input-answer-point" 
                                                    id="input-answer-point-<?php echo $group_id; ?>-<?php echo $quiz_id; ?>"
                                                    type="number" placeholder="points"
                                                    name="quiz_answer_point[<?php echo $group_id; ?>][<?php echo $quiz_id; ?>]" 
                                                    value="<?php echo $quiz_point; ?>" />
                                        </div>
                                    </div>
                                    <input type="hidden" name="assessment_id" value="<?php echo $assessment_id ?>" />
                                    <input type="hidden" name="user_id" value="<?php echo $user_id ?>" />
                                    <input class="quiz_id" type="hidden" name="quiz_id[]" value="<?php echo $quiz_id ?>" class="quiz-input" />
                                </div>
                                <div class="card-footer">
                                    <p class="sub-total-score">
                                        Sub Question score: 
                                        <span class="sub-total-score-val" id="sub-total-score-val-<?php echo $quiz_id; ?>"><?php 
                                            if (!empty($weighting) && !empty($quiz_point)) {
                                                $sub_question_score = $weighting * $quiz_point;
                                                $section_score_arr[] = $sub_question_score;
                                                echo $sub_question_score;
                                            } ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="card feedback">
                                <div class="card-body">
                                    <textarea class="form-control-lg feedback-input" 
                                            name="quiz_feedback[<?php echo $group_id ?>][<?php echo $quiz_id ?>]"
                                            placeholder="Your feedback here"
                                            ><?php echo $quiz_feedbacks[$group_id][$quiz_id]; ?></textarea>
                                    <div class="card-action">
                                        <a type="button" class="button button-primary accept-quiz-feedback" 
                                            data-group-id="<?php echo $group_id ?>"
                                            data-id="<?php echo $quiz_id ?>">
                                            Accept
                                        </a>
                                        <a type="button" class="button reject-quiz-feedback" 
                                            data-group-id="<?php echo $group_id ?>"
                                            data-id="<?php echo $quiz_id ?>">
                                            Reject
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="quiz-status <?php echo $type; ?>">
                                        Status: <strong><?php echo $type; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                <?php endif; ?>
                <!--  -->
                <?php if (!empty($section_score_arr)): 
                        $total_section_score = array_sum($section_score_arr);
                        $submission_score_arr[] = $total_section_score;
                ?>
                    <div class="total-section-score">
                        <span>Total Section score: 
                            <span class="total-section-score-val"><?php echo $total_section_score; ?></span>
                        </span>
                    </div>
                <?php endif;?>
            </div>
        <?php endforeach;?>
        
        <input type="hidden" id="assessment_id" value="<?php echo $assessment_id ?>"/>
        <input type="hidden" id="user_id" value="<?php echo $user_id ?>"/>
        <input type="hidden" id="submission_id" name="submission_id" value="<?php echo $post_id ?>"/>
        <input type="hidden" id="organisation_id" value="<?php echo $organisation_id ?>"/>
        <!-- End Comprehensive Submission -->
        <div class="submission-admin-view-footer">
            <?php if ($is_required_answer_all == true): ?>
                <div class="final-accept"
                <?php 
                    if ($get_quiz_accepted == true) {
                        echo 'style="display:block;"';
                    }
                    else {
                        echo 'style="display:none;"';
                    }
                ?>
                >
                    <p class="label">All individual answers from the user have been accepted. Click to accept this submission.</p>
                    <a type="button" class="button button-primary button-large accept-button" title="Accept this Submission">
                        Final Acccept
                        <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
                    </a>
                </div>
                <div class="final-reject"
                <?php 
                    if ($get_quiz_accepted != true) {
                        echo 'style="display:block;"';
                    }
                    else {
                        echo 'style="display:none;"';
                    }
                ?>
                >
                    <p class="label">Reject this submission until the user resubmits.</p>
                    <a type="button" class="button button-large reject-button" title="Reject this Submission">
                        Reject
                        <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
                    </a>
                </div>
                
            <?php else: ?>
                <p class="label">Accept or reject this submission</p>
                <a type="button" class="button button-primary button-large accept-button" title="Accept this Submission">
                    Accept & Create Report
                </a>
                <a type="button" class="button button-large reject-button" title="Reject this Submission">
                    Reject
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
    $total_submission_score = array_sum($submission_score_arr);
    update_post_meta( $post_id, 'total_submission_score', $total_submission_score );
?>

<?php if ($assessment_meta == 'Simple Assessment'): ?>
    <style>
        .submission-view-item-row .card {
            width: 100%;
        }
    </style>
<?php endif; ?>

<!-- Hide meta box if quizs don't exist -->
<?php if (empty($quiz)): ?>
    <style>
        #acf-group_63abf1f270c08,
        #submitted_info_view,
        #questions-repeater-field {
            display: none!important;
        }
    </style>
<?php else: ?>
    <style>
        #acf-group_63abf1f270c08,
        #submitted_info_view,
        #questions-repeater-field {
            display: block!important;
        }
    </style>
<?php endif; ?>

