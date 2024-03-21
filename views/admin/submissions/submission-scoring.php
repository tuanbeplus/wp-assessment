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
$terms = get_assessment_terms($assessment_id);

// Exit if this Submission isn't from Index
if (!in_array('index', $terms)) return;

$total_org_score = get_post_meta($post_id, 'total_submission_score', true);
$org_section_score = get_post_meta($post_id, 'org_section_score', true);
$report_key_areas = get_assessment_key_areas($assessment_id);
$and_score = get_post_meta($post_id, 'and_score', true);
$agreed_score = get_post_meta($post_id, 'agreed_score', true);
$total_submission_score = get_post_meta($post_id, 'total_submission_score', true);
$total_and_score = get_post_meta($post_id, 'total_and_score', true);
$total_agreed_score = get_post_meta($post_id, 'total_agreed_score', true);
$overall_org_score = cal_overall_total_score($assessment_id, 'total_submission_score');
$overall_and_score =  cal_overall_total_score($assessment_id, 'total_and_score');
$overall_agreed_score =  cal_overall_total_score($assessment_id, 'total_agreed_score');
$report_id = is_report_of_submission_exist($post_id);
$report_url = home_url() . '/wp-admin/post.php?post='. $report_id .'&action=edit';
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$is_ranking_exist = get_ranking_of_assessment($assessment_id);
$agreed_gr_score_with_weighting = cal_scores_with_weighting($assessment_id, $agreed_score, 'group') ?? array();
?>

<div class="scoring-wrapper">
    <div class="maturity-level _field">
        <p><strong>Maturity Level</strong></p>
        <p class="org-score">Org Score: <strong><?php echo $total_org_score['percent'] ?? 0; ?>%</strong></p>
    </div>
    <div class="key-area _field">
        <p><strong>Key Area</strong></p>
        <ol class="key-area-list">
        <?php if (!empty($questions) && !empty($org_section_score)): ?>
            <?php foreach ($questions as $gr_id => $gr_field): ?>
                <li><?php echo $gr_field['title']; ?>: 
                    <strong><?php echo 'Level '.get_maturity_level_org($agreed_gr_score_with_weighting[$gr_id]) ?? ''; ?></strong>
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
                <strong><?php echo $total_submission_score['sum'] ?? 0; ?></strong> 
                <strong>(<?php echo $total_submission_score['percent'] ?? 0; ?>%)</strong>
            </li>
            <li>Overall AND Total Score: 
                <strong><?php echo $total_and_score['sum'] ?? 0; ?></strong> 
                <strong>(<?php echo $total_and_score['percent'] ?? 0; ?>%)</strong>
            </li>
            <li>Overall Agreed Total Score: 
                <strong><?php echo $total_agreed_score['sum'] ?? 0; ?></strong> 
                <strong>(<?php echo $total_agreed_score['percent'] ?? 0; ?>%)</strong>
            </li>
        </ul>
    </div>
    <div class="report _field">
        <p><strong>Report</strong></p>
        <div class="report-action">
            <?php if ($is_ranking_exist): ?>
                <a id="btn-create-report" class="button button-primary">
                    Create Preliminary Report
                    <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
                </a>
                <a id="btn-view-report" href="<?php echo $report_url; ?>" target="_blank"
                    class="button button-medium <?php if (!empty($report_id)) echo 'show'; ?>">
                    Edit Report
                </a>
            <?php else: ?>
                <div class="add-ranking-box">
                    <p>The ranking of assessments does not exist!</p>
                    <p>You must add the ranking before creating the preliminary report.</p>
                    <a href="/wp-admin/post-new.php?post_type=ranking" target="_blank">Add new Ranking here</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>