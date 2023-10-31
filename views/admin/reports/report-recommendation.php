<?php 
global $post;
$post_id = $post->ID;
$report_recommendation = get_post_meta($post_id, 'report_recommendation', true);
$executive_summary = get_post_meta($post_id, 'executive_summary', true);
$evalution_findings = get_post_meta($post_id, 'evalution_findings', true);
?>
<div class="recommendation-wrapper">
    <!-- Key Recommendation Fields -->
    <div class="key-recommendation-field-container">
        <h3 class="_heading">Key Recommendation</h3>
        <div class="row">
            <div class="col-5">
                <strong>Key Area</strong>
            </div>
            <div class="col-7">
                <strong>Priorities</strong>
            </div>
        </div>
        <?php if (!empty($report_recommendation)): $index = 0; ?>
            <?php foreach($report_recommendation as $row_area): $index++; ?>
                <div id="row-recommendation-<?php echo esc_attr($index); ?>" class="row row-recommendation">
                    <div class="key-title col-5">
                        <textarea class="form-control description_area" 
                                name="key_recommendation[<?php echo esc_attr($index); ?>][key]"><?php echo $row_area['key']; ?></textarea>
                    </div>
                    <div class="priorities-area col-7">
                        <textarea class="form-control description_area" 
                                name="key_recommendation[<?php echo esc_attr($index); ?>][priority]"><?php echo $row_area['priority']; ?></textarea>
                        <div class="row-recommendation-action">
                            <span class="remove-row-recom" title="remove row"><i class="fa-regular fa-circle-xmark"></i></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Blank Key Recommendation Fields -->
            <div id="row-recommendation-1" class="row row-recommendation">
                <div class="key-title col-5">
                    <textarea class="form-control description_area" 
                            name="key_recommendation[1][key]"></textarea>
                </div>
                <div class="priorities-area col-7">
                    <textarea class="form-control description_area" 
                            name="key_recommendation[1][priority]"></textarea>
                    <div class="row-recommendation-action">
                        <span class="remove-row-recom" title="remove row"><i class="fa-regular fa-circle-xmark"></i></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="add-row-recommendation-block">
        <span type="button" class="add-row-recommendation button button-primary">+ Add row</span>
    </div>

    <!-- Executive Summary Fields -->
    <div class="executive-summary-field-container">
        <h3 class="_heading">Executive Summary</h3>
        <?php
            $content   = $executive_summary;
            $editor_id = 'executive-summary-wpeditor';
            $editor_settings = array(
                'textarea_name' => "executive_summary",
                'textarea_rows' => 15,
                'quicktags' => true, // Remove view as HTML button.
                'default_editor' => 'tinymce',
                'tinymce' => true,
            );
            wp_editor( $content, $editor_id, $editor_settings );
        ?>
    </div>

    <!-- Evalution Findings Fields -->
    <div class="evalution-findings-field-container">
        <h3 class="_heading">Evalution Findings</h3>
        <?php
            $content   = $evalution_findings;
            $editor_id = 'evalution-findings-wpeditor';
            $editor_settings = array(
                'textarea_name' => "evalution_findings",
                'textarea_rows' => 15,
                'quicktags' => true, // Remove view as HTML button.
                'default_editor' => 'tinymce',
                'tinymce' => true,
            );
            wp_editor( $content, $editor_id, $editor_settings );
        ?>
    </div>

</div>