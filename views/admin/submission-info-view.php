<?php
global $post;
global $wpdb;

$post_id = $post->ID;
$user_id = get_post_meta($post_id, 'user_id', true);
$user = get_user_by('id', $user_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
$sf_user_id = get_post_meta($post_id, 'sf_user_id', true);
$sf_user_email = get_post_meta($post_id, 'sf_user_email', true);
$submission_status = get_post_meta($post_id, 'assessment_status', true);
$total_submission_score = get_post_meta($post_id, 'total_submission_score', true);
$report_id = get_post_meta($post_id, 'report_id', true);
$report_url = home_url() . '/wp-admin/post.php?post='. $report_id .'&action=edit';
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$organization_data = sf_get_object_metadata('Account', $organisation_id);

$main = new WP_Assessment();

$quiz = $main->get_user_quiz_by_assessment_id_and_submissions($assessment_id, $user_id, $post_id );
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$group_quiz_points = unserialize(get_post_meta($post_id, 'group_quiz_point', true));

if (empty($user_id) || empty($assessment_id)) return;

$sub_score_points_arr = array();
$sub_total_points_arr = array();

foreach ($questions as $group_id => $group_field) {
    $group_point = $group_field['point'];
    $sub_point_list = $group_field['list'];

    $group_point_input = $group_quiz_points[$group_id]['point'];
    $group_point_input_list = $group_quiz_points[$group_id]['sub_list'];

    foreach ($quiz as $field) {
        if ($field->parent_id == $group_id) {
            $sub_point = $sub_point_list[$field->quiz_id]['point'];
            if (!empty($sub_point) && $group_point) {
                $sub_total_points_arr[] = $sub_point * $group_point;
            }

            $sub_point_input = $group_point_input_list[$field->quiz_id]['point'];
            if (!empty($sub_point_input) && $group_point) {
                $sub_score_points_arr[] = $sub_point_input * $group_point_input;
            }
        }        
    }
}

$total_score_point = array_sum($sub_score_points_arr);
$total_points = array_sum($sub_total_points_arr);

update_post_meta($post_id, 'assessment_total_score', $total_score_point);
update_post_meta($post_id, 'assessment_total_point', $total_points);

?>

<div class="submission-info-container">
    <?php if (isset($sf_user_id)): ?>
        <p class="post-status-display">User: <strong><?php echo $sf_user_name; ?></strong></p>
    <?php else:?>
        <p class="post-status-display">User: <strong><?php echo $user->display_name; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($sf_user_id)): ?>
        <p class="post-status-display">Email: <strong><?php echo $sf_user_email; ?></strong></p>
    <?php else:?>
        <p class="post-status-display">Email: <strong><?php echo $user->user_email; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($organization_data->Name)): ?>
        <p class="post-status-display">Company: <strong><?php echo $organization_data->Name; ?></strong></p>
    <?php endif; ?>

    <p class="post-status-display">Status: <strong><?php echo $submission_status; ?></strong></p>
    <p class="post-status-display">Total Score: <strong><?php echo $total_submission_score; ?></strong></p>

    <?php if ($report_id): ?>
        <a href="<?php echo $report_url; ?>" target="_blank">
            <button type="button" class="button button-primary button-large">View Report</button>
        </a>
    <?php endif; ?>
</div>
