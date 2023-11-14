<?php 
global $post;

// $report_sections = array('intro', 'outro', 'address', 'appendix');
$report_sections = array('intro');
$report_template = get_post_meta($post->ID, 'report_template', true);
?>

<div id="report-template-wrapper" class="report-template-wrapper">
    <div class="field-checkbox">
        <label for="report-include-toc">
            <input type="checkbox" name="is_report_include_toc" id="report-include-toc">
            Include Table of content
        </label>
    </div>
    <!-- begin Report section -->
    <div id="report-template" class="report-template">
        <!-- Front page -->
        <div id="report-front-page" class="_section">
            <h3 class="_heading">Report front page (cover)</h3>
            <input type="text" name="report_template[front_page][title]" id="">
            <?php
                $content   = '';
                $editor_id = 'report-front-page-wpeditor';
                $editor_settings = array(
                    'textarea_name' => "report_template[front_page][content]",
                    'textarea_rows' => 12,
                    'quicktags' => true, // Remove view as HTML button.
                    'default_editor' => 'tinymce',
                    'tinymce' => true,
                );
                wp_editor( $content, $editor_id, $editor_settings );
            ?>
        </div>
        <!-- /Front page -->

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