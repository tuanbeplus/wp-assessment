<?php 
global $post;
$post_id = $post->ID;
$report_recommendation = get_post_meta($post_id, 'report_recommendation', true);
$executive_summary = get_post_meta($post_id, 'executive_summary', true);
$evalution_findings = get_post_meta($post_id, 'evalution_findings', true);
?>
<div class="recommendation-wrapper">
    <div class="field-heading">
        <h3 class="_heading">Add Recommendations</h3>
        <a type="button" class="add-row-recommendation button button-primary" data-position="top">+ Add more</a>
    </div>
    <!-- Key Recommendation Fields -->
    <div class="key-recommendation-field-container">
        <ol class="key-recommendations-list">
        <?php if (!empty($report_recommendation)): $index = 0; ?>
            <?php foreach($report_recommendation as $row_area): $index++; ?>
                <li id="row-recommendation-<?php echo esc_attr($index); ?>" class="row-recommendation">
                    <div class="key-title">
                        <textarea class="form-control" 
                                placeholder="Prefilled Recommendation Title"
                                name="key_recommendation[<?php echo esc_attr($index); ?>][key]"><?php echo $row_area['key']; ?></textarea>
                    </div>
                    <div class="key-action">
                        <span class="remove-row"><i class="fa-regular fa-circle-xmark"></i></span>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Blank Key Recommendation Fields -->
            <li id="row-recommendation-1" class="row-recommendation">
                <div class="key-title">
                    <textarea class="form-control" 
                            placeholder="Prefilled Recommendation Title"
                            name="key_recommendation[1][key]"></textarea>
                </div>
                <div class="key-action">
                    <span class="remove-row"><i class="fa-regular fa-circle-xmark"></i></span>
                </div>
            </li>
        <?php endif; ?>
        </ol>
    </div>
    <div class="add-row-recommendation-block">
        <a type="button" class="add-row-recommendation button button-primary" data-position="bottom">+ Add more</a>
    </div>
</div>