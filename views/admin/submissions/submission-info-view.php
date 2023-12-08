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
$total_and_score = get_post_meta($post_id, 'total_and_score', true);
$total_agreed_score = get_post_meta($post_id, 'total_agreed_score', true);
$organisation_id = get_post_meta($post_id, 'organisation_id', true);
$terms = get_assessment_terms($assessment_id);

$org_metadata = get_post_meta($post_id, 'org_data', true);
if (empty($org_metadata)) {
    $sf_org_data = get_sf_organisation_data($user_id, $organisation_id);
    update_post_meta($post_id, 'org_data', $sf_org_data);
}
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

    <?php if (isset($org_metadata['Name'])): ?>
        <p class="post-status-display">Company: <strong><?php echo $org_metadata['Name']; ?></strong></p>
    <?php endif; ?>

    <?php if (isset($org_metadata['Industry'])): ?>
        <p class="post-status-display">Industry: <strong><?php echo $org_metadata['Industry']; ?></strong></p>
    <?php endif; ?>

    <p class="post-status-display">Status: <strong><?php echo $submission_status; ?></strong></p>

    <?php if (in_array('index', $terms)): ?>
        <?php if (isset($total_submission_score)): ?>
            <p class="post-status-display">
                Total Org Score: 
                <strong class="total-submission-score">
                    <?php echo $total_submission_score['sum'] ?? 0; ?>
                    (<?php echo $total_submission_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>

        <?php if (isset($total_and_score)): ?>
            <p class="post-status-display">
                Total AND Score: 
                <strong class="total-submission-score">
                    <?php echo $total_and_score['sum'] ?? 0; ?>
                    (<?php echo $total_and_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>

        <?php if (isset($total_agreed_score)): ?>
            <p class="post-status-display">
                Total Agreed Score: 
                <strong class="total-submission-score">
                    <?php echo $total_agreed_score['sum'] ?? 0; ?>
                    (<?php echo $total_agreed_score['percent'] ?? 0; ?>%)
                </strong>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div>
