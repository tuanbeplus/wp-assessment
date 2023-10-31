<?php 
global $post;

$report_sections = array('intro', 'outro', 'address', 'appendix');
$report_template_content = get_post_meta($post->ID, 'report_template_content', true);
?>

<div id="report-section-wrapper" class="report-section-wrapper">
    <!-- <div class="report-option-container">
        <div class="row">
            <div class="add-report-option-box">
                <span id="btn-add-report-option" class="button button-primary">+ Add report section</span>
            </div>
        </div>
    </div> -->
    <!-- begin Report section -->
    <div id="report-section-container" class="report-section-container">
        <?php foreach($report_sections as $section): ?>
            <?php //if($report_template_content): ?>
                <!-- Section -->
                <div id="<?php echo esc_attr($section); ?>-section" class="_section">
                    <h3 class="_heading"><?php echo esc_html(ucfirst($section)); ?></h3>
                    <?php
                        if ($report_template_content) {
                            $content = $report_template_content[$section];
                        } 
                        else {
                            $content   = '';
                        }
                        
                        $editor_id = $section .'-section-wpeditor';
                        $editor_settings = array(
                            'textarea_name' => "report_sections[".$section."]",
                            'textarea_rows' => 12,
                            'quicktags' => true, // Remove view as HTML button.
                            'default_editor' => 'tinymce',
                            'tinymce' => true,
                        );
                        wp_editor( $content, $editor_id, $editor_settings );
                    ?>
                </div>
                <!-- .Section -->
            <?php //endif; ?>
       <?php endforeach; ?>
    </div>
    <!-- end Report section -->
</div>