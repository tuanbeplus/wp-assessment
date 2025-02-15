<?php
global $post;
global $wpdb;
global $post_type;

$post_id = $post->ID;
$user_id = get_post_meta($post_id, 'user_id', true);
$user = get_user_by('id', $user_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
$sf_user_id = get_post_meta($post_id, 'sf_user_id', true);
$sf_user_email = get_post_meta($post_id, 'sf_user_email', true);
$submission_status = get_post_meta($post_id, 'assessment_status', true);
$total_submission_score = get_post_meta($post_id, 'total_submission_score', true);
$total_and_score = get_post_meta($post_id, 'total_and_score', true);
$total_agreed_score = get_post_meta($post_id, 'total_agreed_score', true);
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$terms = get_assessment_terms($assessment_id);
$org_metadata = get_post_meta($post_id, 'org_data', true);
if (empty($org_metadata)) {
    $sf_org_data = get_sf_organisation_data($user_id, $organisation_id);
    update_post_meta($post_id, 'org_data', $sf_org_data);
}
$this_sub_ver = get_post_meta($post_id, 'submission_version', true);
$is_latest_version = get_post_meta($post_id, 'is_latest_version', true);
$is_latest = ($is_latest_version == true) ? ' (Latest)' : '';
$created_date = get_post_meta($post_id, 'created_date', true);
$scoring_formula = get_post_meta($assessment_id, 'scoring_formula', true);
?>

<div class="submission-info-container">
    <?php if (isset($sf_user_id)): ?>
        <p>User: <strong><?php echo $sf_user_name; ?></strong></p>
    <?php else:?>
        <p>User: <strong><?php echo $user->display_name; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($sf_user_id)): ?>
        <p>Email: <strong><?php echo $sf_user_email; ?></strong></p>
    <?php else:?>
        <p>Email: <strong><?php echo $user->user_email; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($org_metadata['Name'])): ?>
        <p>Company: <strong><?php echo $org_metadata['Name']; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($org_metadata['Industry'])): ?>
        <p>Industry: <strong><?php echo $org_metadata['Industry']; ?></strong></p>
    <?php endif; ?>

    <p class="status">Status: 
        <strong class="<?php echo $submission_status ?>">
            <?php echo ucwords(str_replace('-', ' ', $submission_status)); ?>
        </strong>
    </p>

    <?php if (!empty($this_sub_ver) && $post_type === 'dcr_submissions'): ?>
        <p>Version: <strong><?php echo esc_html($this_sub_ver) . $is_latest; ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($created_date)): ?>
        <p>Created on: <strong><?php echo esc_html(date('M d, Y \a\t H:i a', strtotime($created_date))); ?></strong></p>
    <?php endif; ?>

    <?php if (in_array('index', $terms)): ?>
        <?php
            $sum_key = 'sum';
            // Using Index formula 2024
            if (!empty($scoring_formula) && $scoring_formula == 'index_formula_2024') {
                $sum_key = 'sum';
            }
            // Using Index formula 2023
            else {
                $sum_key = 'sum_with_weighting';
            }
        ?>
        <?php if (isset($total_submission_score)): ?>
            <p class="post-status-display">
                Total Org Score: 
                <strong class="total-submission-score">
                    <?php echo $total_submission_score[$sum_key] ?? 0; ?>
                    (<?php echo $total_submission_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>

        <?php if (isset($total_and_score)): ?>
            <p class="post-status-display">
                Total AND Score: 
                <strong class="total-submission-score">
                    <?php echo $total_and_score[$sum_key] ?? 0; ?>
                    (<?php echo $total_and_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>

        <?php if (isset($total_agreed_score)): ?>
            <p class="post-status-display">
                Total Agreed Score: 
                <strong class="total-submission-score">
                    <?php echo $total_agreed_score[$sum_key] ?? 0; ?>
                    (<?php echo $total_agreed_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>

        <?php if (isset($scoring_formula) && !empty($scoring_formula)): ?>
            <p class="post-status-display">Scoring formula: <strong><?php echo ucwords(str_replace('_', ' ', $scoring_formula)); ?></strong></p>
        <?php endif; ?>
    <?php endif; ?>
</div>
