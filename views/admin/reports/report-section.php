<?php 
global $post;
$report_template = get_post_meta($post->ID, 'report_template', true);
$report_logo_url = $report_template['front_page']['logo_url'] ?? null;
$report_front_title = $report_template['front_page']['title'] ?? null;
$report_front_content = $report_template['front_page']['content'] ?? null;
$is_include_toc = $report_template['is_include_toc'] ?? true;
$terms = get_assessment_terms($post->ID);
?>

<div id="report-template-wrapper" class="report-template-wrapper">
    <div class="field-include-toc">
        <label for="report-include-toc">
            <input id="report-include-toc" type="checkbox" 
                    name="report_template[is_include_toc]" placeholder="Add title" value="1"
                    <?php if ($is_include_toc == true) echo 'checked'; ?>>
            Include table of content
        </label>
    </div>
    <!-- begin Report section -->
    <div id="report-template" class="report-template">
        <!-- Front page -->
        <div id="report-front-page" class="_section">
            <h3 class="_heading">Report front page (cover)</h3>
            <div class="_container">
                <div class="field-content">
                    <input type="text" name="report_template[front_page][title]" 
                            placeholder="Add title"
                            value="<?php echo $report_front_title; ?>">
                    <?php
                        $editor_id = 'report-front-page-wpeditor';
                        $editor_settings = array(
                            'textarea_name' => "report_template[front_page][content]",
                            'textarea_rows' => 12,
                            'quicktags' => true, // Remove view as HTML button.
                            'default_editor' => 'tinymce',
                            'tinymce' => true,
                        );
                        wp_editor( $report_front_content, $editor_id, $editor_settings );
                    ?>
                </div>
                <div class="field-upload-logo">
                    <a id="report-add-logo" class="button button-medium">+ Add Logo</a>
                    <input id="front-page-logo-url" type="hidden" 
                            name="report_template[front_page][logo_url]" 
                            value="<?php echo $report_logo_url; ?>">
                    <div class="logo-preview-block">
                        <img id="report-front-logo-preview" src="<?php echo $report_logo_url; ?>" 
                            class="<?php if(!empty($report_logo_url)) echo 'active'; ?>"
                            alt="Image Preview">
                    </div>               
                    <a id="btn-remove-logo" class="button_remove <?php if(!empty($report_logo_url)) echo 'active'; ?>">
                        Remove this image
                    </a> 
                </div>
            </div>
        </div>
        <!-- /Front page -->

        <div class="add-row-block-top">
            <a class="btn-add-generic-page button button-primary" data-insert="top">+ Add row</a>
        </div>
        
        <!-- Generic Pages List -->
        <ul id="generic-pages-list">
        <?php if (!empty($report_template['generic_page'])): ?>
            <?php foreach ($report_template['generic_page'] as $index => $generic_page): ?>
                <li id="generic-page-<?php echo $index; ?>" class="_section generic-page">
                    <h3 class="_heading">Generic page</h3>
                    <input type="text" name="report_template[generic_page][<?php echo $index; ?>][title]" 
                            placeholder="Add title"
                            value="<?php echo $generic_page['title'] ?? null; ?>">
                    <?php
                        $content   = $generic_page['content'] ?? null;
                        $editor_id = 'report-generic-wpeditor-'. $index;
                        $editor_settings = array(
                            'textarea_name' => "report_template[generic_page][". $index ."][content]",
                            'textarea_rows' => 12,
                            'quicktags' => true, // Remove view as HTML button.
                            'default_editor' => 'tinymce',
                            'tinymce' => true,
                        );
                        wp_editor( $content, $editor_id, $editor_settings );
                    ?>
                    <div class="add-row-block">
                        <a class="btn-remove-generic-page button_remove">Remove this row</a>
                        <a class="btn-add-generic-page button button-primary" data-insert="bottom">+ Add row</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
        <!-- /Generic Pages List -->
        
        <!-- Report Footer -->
        <div id="report-footer" class="_section">
            <h3 class="_heading">Add Footer</h3>
            <?php
                $content   = $report_template['footer'] ?? null;
                $editor_id = 'report-footer-wpeditor';
                $editor_settings = array(
                    'textarea_name' => "report_template[footer]",
                    'textarea_rows' => 12,
                    'quicktags' => true, // Remove view as HTML button.
                    'default_editor' => 'tinymce',
                    'tinymce' => true,
                );
                wp_editor( $content, $editor_id, $editor_settings );
            ?>
        </div>
        <!-- /Report Footer -->
    </div>
    <!-- end Report section -->
</div>