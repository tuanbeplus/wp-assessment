<?php 
/**
 * Template Submission Scoring meta box
 * 
 * @author Tuan
 * 
 */
global $post;
$post_id = $post->ID;
$main = new WP_Assessment();
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$total_org_score = get_post_meta($post_id, 'total_submission_score', true);
$org_section_score = get_post_meta($post_id, 'org_section_score', true);
$report_key_areas = get_post_meta($assessment_id, 'report_key_areas', true);
$and_score = get_post_meta($post_id, 'and_score', true);
$agreed_score = get_post_meta($post_id, 'agreed_score', true);
$overall_org_score = cal_overall_total_score($assessment_id, 'total_submission_score');
$overall_and_score =  cal_overall_total_score($assessment_id, 'total_and_score');
$overall_agreed_score =  cal_overall_total_score($assessment_id, 'total_agreed_score');
$report_id = is_report_of_submission_exist($post_id);
$report_url = home_url() . '/wp-admin/post.php?post='. $report_id .'&action=edit';
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);

// echo "<pre>";
// print_r($overall_org_score);
// echo "</pre>";
?>

<div class="scoring-wrapper">
    <div class="maturity-level _field">
        <p><strong>Maturity Level</strong></p>
        <p class="org-score">Org Score: <strong><?php echo $total_org_score['percent']; ?>%</strong></p>
    </div>
    <div class="key-area _field">
        <p><strong>Key Area</strong></p>
        <ol class="key-area-list">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $key_area): ?>
                <li><?php echo $key_area['title']; ?>: 
                    <strong><?php echo 'Level '. get_maturity_level_org($org_section_score[$index]); ?></strong>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No Key Area found</li>
        <?php endif; ?>
        </ol>
    </div>
    <div class="overall _field">
        <p><strong>Overall total score</strong></p>
        <ul class="overall-list">
            <li>Overall Organisation Total Score: 
                <strong><?php echo $overall_org_score['sum_average']; ?></strong> 
                <strong>(<?php echo $overall_org_score['percent_average']; ?>%)</strong>
            </li>
            <li>Overall AND Total Score: 
                <strong><?php echo $overall_and_score['sum_average']; ?></strong> 
                <strong>(<?php echo $overall_and_score['percent_average']; ?>%)</strong>
            </li>
            <li>Overall Agreed Total Score: 
                <strong><?php echo $overall_agreed_score['sum_average']; ?></strong> 
                <strong>(<?php echo $overall_agreed_score['percent_average']; ?>%)</strong>
            </li>
        </ul>
    </div>
    <div class="report _field">
        <p><strong>Report</strong></p>
        <div class="report-action">
            <a id="btn-create-report" class="button button-primary">
                Create Preliminary Report
                <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
            </a>
            <a id="btn-view-report" href="<?php echo $report_url; ?>" target="_blank"
                class="button button-medium <?php if (!empty($report_id)) echo 'show'; ?>">
                View Report
            </a>
        </div>
    </div>
</div>