<?php 
global $post;
$post_id = $post->ID;
$report_key_areas = get_post_meta($post_id, 'report_key_areas', true);
?>
<div class="key-areas-container">
    <!-- Key Areas List -->
    <ol class="key-areas-list">
    <?php if (!empty($report_key_areas)): $index = 0; ?>
        <?php foreach($report_key_areas as $key_area): $index++; ?>
            <li id="row-key-area-<?php echo esc_attr($index); ?>" class="row-key-area">
                <div class="key-title">
                    <input type="text" class="form-control" 
                            placeholder="Add key Area name"
                            name="report_key_areas[<?php echo esc_attr($index); ?>][key]"
                            value="<?php echo $key_area['key']; ?>">
                </div>
                <div class="key-action">
                    <span class="remove-row"><i class="fa-regular fa-circle-xmark"></i></span>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Blank Key Area -->
        <li id="row-key-area-1" class="row-key-area">
            <div class="key-title">
                <input type="text" class="form-control" 
                        placeholder="Add key Area name"
                        name="report_key_areas[1][key]"
                        value="">
            </div>
            <div class="key-action">
                <span class="remove-row"><i class="fa-regular fa-circle-xmark"></i></span>
            </div>
        </li>
        <!-- /Blank Key Area -->
    <?php endif; ?>
    </ol>
    <!-- /Key Areas List -->

    <!-- Button Add Key Area -->
    <a type="button" class="add-key-area button button-primary" data-position="bottom">+ Add row</a>
    <!-- /Button Add Key Area -->
</div>
