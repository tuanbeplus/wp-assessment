<?php
/**
 * Template Link Report to Assessments meta box
 *
 * Linked the Report template to Assessments Saturn
 *
 * @author Tuan
 */

global $post;
$linked_assessment = get_post_meta($post->ID, 'linked_assessment', true);
$assessments = get_posts(
    array(
        'post_type' => 'assessments',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    )
);
?>

<div class="link-report-wrapper">
    <select name="linked_assessment" id="select-assessments">
        <option value="">Choose Assessment</option>
        <?php foreach ($assessments as $assessment): ?>
            <option value="<?php echo $assessment->ID; ?>" 
                <?php if($assessment->ID == $linked_assessment) echo 'selected'; ?>>
                <?php echo $assessment->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>