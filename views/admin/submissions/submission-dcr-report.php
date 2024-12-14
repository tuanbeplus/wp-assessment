<?php 
/**
 * Template DCR Submission Report meta box
 * 
 * @author Tuan
 * 
 */
global $post;
$post_id = $post->ID;
$main = new WP_Assessment();
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$terms = get_assessment_terms($assessment_id);

// Exit if this Submission isn't from DCR
if (!in_array('dcr', $terms)) return;

$report_id = is_report_of_submission_exist($post_id, 'dcr_reports');
$report_url = home_url() . '/wp-admin/post.php?post='. $report_id .'&action=edit';
?>

<div class="dcr-report-wrapper">
    <p>Click to create a draft preliminary report for this submission.</p>
    <div class="report-action">
        <a id="btn-create-report" class="button button-primary">
            <span>Create Report</span>
            <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
        </a>
        <a id="btn-view-report" href="<?php echo $report_url; ?>" target="_blank"
            class="button button-medium <?php if (!empty($report_id)) echo 'show'; ?>">
            <span>Edit Report</span>
        </a>
    </div>
</div>