<?php 
global $post;

if ($post->post_type == 'reports') {
    $assessment_id = get_post_meta($post->ID, 'assessment_id', true);
    $report_template = get_post_meta($post->ID, 'report_template', true);

    if (empty($report_template)) {
        $report_template = get_post_meta($assessment_id, 'report_template', true);
    }
} 
else {
    $assessment_id = $post->ID;
    $report_template = get_post_meta($post->ID, 'report_template', true);
}

$report_logo_url = $report_template['front_page']['logo_url'] ?? null;
$report_bg_img_url = $report_template['front_page']['bg_img'] ?? null;
$report_front_title = $report_template['front_page']['title'] ?? null;
$report_front_content = $report_template['front_page']['content'] ?? null;
$report_front_heading_2 = $report_template['front_page']['heading_2'] ?? null;
$is_include_toc = $report_template['is_include_toc'] ?? null;
$terms = get_assessment_terms($post->ID);
$index_2023 = get_field('assessment_index_2023', 'option');
$index_2023_id = !empty($index_2023) ? $index_2023 : 17158;
?>

<div id="report-template-wrapper" class="report-template-wrapper">
    <div class="field-include-toc">
        <label for="report-include-toc">
            <input id="report-include-toc" type="checkbox" 
                    name="report_template[is_include_toc]" placeholder="Add title" value="1"
                    <?php if ($is_include_toc == true) echo 'checked'; ?>>
            Include Table of Contents
        </label>
    </div>
    <!-- begin Report section -->
    <div id="report-template" class="report-template">
        <!-- Front page -->
        <div id="report-front-page" class="_section">
            <h3 class="_heading">Report front page (Cover)</h3>
            <div class="field-content">
                <label for="heading-1">Report Title</label>
                <input id="heading-1" type="text" name="report_template[front_page][title]" 
                        placeholder="Enter report title"
                        value="<?php echo $report_front_title; ?>">
                
                <?php if ($assessment_id == $index_2023_id): ?>
                <label for="wp-report-front-page-wpeditor-wrap">Content</label>
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
                <?php else: ?>
                    <label for="heading-2">Sub Title</label>
                    <input id="heading-2" type="text" name="report_template[front_page][heading_2]" 
                            placeholder="Enter report sub title"
                            value="<?php echo $report_front_heading_2; ?>">
                <?php endif; ?>
            </div>
            <div class="_container">
                <div class="field-upload-logo">
                    <h4>ADN Logo</h4>
                    <a id="report-add-logo" class="button button-medium">+ Add Image</a>
                    <input id="front-page-logo-url" type="hidden" 
                            name="report_template[front_page][logo_url]" 
                            value="<?php echo $report_logo_url; ?>">
                    <div class="img-preview-block">
                        <img id="report-front-logo-preview" src="<?php echo $report_logo_url; ?>" 
                            class="<?php if(!empty($report_logo_url)) echo 'active'; ?>"
                            alt="Image Preview">
                    </div>               
                    <a id="btn-remove-logo" class="button_remove <?php if(!empty($report_logo_url)) echo 'active'; ?>">
                        Remove this image
                    </a> 
                </div>
                <?php if ($assessment_id != '31523'): ?>
                    <div class="field-upload-logo">
                        <h4>Background Image</h4>
                        <a id="report-add-bg-img" class="button button-medium">+ Add Image</a>
                        <input id="front-page-bg-img-url" type="hidden" 
                                name="report_template[front_page][bg_img]" 
                                value="<?php echo $report_bg_img_url; ?>">
                        <div class="img-preview-block">
                            <img id="report-front-bg-img-preview" src="<?php echo $report_bg_img_url; ?>" 
                                class="<?php if(!empty($report_bg_img_url)) echo 'active'; ?>"
                                alt="Image Preview">
                        </div>               
                        <a id="btn-remove-bg-img" class="button_remove <?php if(!empty($report_bg_img_url)) echo 'active'; ?>">
                            Remove this image
                        </a> 
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- /Front page -->
        <?php 
        $generic_page_type = array(
            array(
                'type' => 'before',
                'description' => 'Add page to the front of the Document before generated content',
            ),
            array(
                'type' => 'after',
                'description' => 'Add page to the back of the Document after generated content',
            ),
        );
        ?>
        <?php foreach ($generic_page_type as $page_type): ?>
            <div class="generic-page-wrapper">
                <div class="add-row-block-top">
                    <p class="_description"><?php echo $page_type['description']; ?></p>
                    <a class="btn-add-generic-page button button-primary" 
                        data-position="<?php echo $page_type['type']; ?>"
                        data-insert="top">
                        + Add row
                    </a>
                </div>
                <!-- Generic Pages before List -->
                <ul class="generic-pages-list <?php echo $page_type['type']; ?>" 
                    data-position="<?php echo $page_type['type']; ?>">
                <?php if (!empty($report_template['generic_page_'.$page_type['type']])): ?>
                    <?php foreach ($report_template['generic_page_'.$page_type['type']] as $index => $generic_page): ?>
                        <li id="generic-page-<?php echo $page_type['type']; ?>-<?php echo $index; ?>" 
                            class="_section generic-page">
                            <h3 class="_heading">Generic page <?php echo $page_type['type']; ?> 
                                #<span class="page-index"><?php echo $index; ?></span>
                            </h3>
                            <input type="text" name="report_template[generic_page_<?php echo $page_type['type']; ?>][<?php echo $index; ?>][title]" 
                                    placeholder="Add title" 
                                    value="<?php echo $generic_page['title'] ?? null; ?>">
                            <?php
                                $content   = $generic_page['content'] ?? null;
                                $editor_id = 'report-generic-wpeditor-'.$page_type['type'].$index;
                                $editor_settings = array(
                                    'textarea_name' => "report_template[generic_page_".$page_type['type']."][". $index ."][content]",
                                    'textarea_rows' => 12,
                                    'quicktags' => true, // Remove view as HTML button.
                                    'default_editor' => 'tinymce',
                                    'tinymce' => true,
                                );
                                wp_editor( $content, $editor_id, $editor_settings );
                            ?>
                            <div class="add-row-block">
                                <a class="btn-remove-generic-page button_remove">
                                    <span><i class="fa-solid fa-xmark"></i></span>
                                    <span>Remove this row</span>
                                </a>
                                <a class="btn-add-generic-page button button-primary" 
                                    data-position="<?php echo $page_type['type']; ?>"
                                    data-insert="bottom">
                                    + Add row
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                </ul>
                <!-- /Generic Pages before List -->
            </div>
        <?php endforeach; ?>
            
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