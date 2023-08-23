<?php
global $post;

$single_repeater_group = get_post_meta($post->ID, 'single_repeater_group', true);
wp_nonce_field('repeaterBox', 'formType');
?>

<table id="repeatable-fieldset-one" style="width: 100%">
    <tbody>
    <?php if ($single_repeater_group) : ?>
        <?php foreach ($single_repeater_group as $field): ?>
            <tr>
                <td><input type="text" style="width:98%;" name="title[]"
                           value="<?php if ($field['title'] != '') echo esc_attr($field['title']); ?>"
                           placeholder="Heading"/></td>
                <td><input type="text" style="width:98%;" name="tdesc[]"
                           value="<?php if ($field['tdesc'] != '') echo esc_attr($field['tdesc']); ?>"
                           placeholder="Description"/></td>
                <td><a class="button remove-row" href="#1">Remove</a></td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td><input type="text" style="width:98%;" name="title[]" placeholder="Heading"/></td>
            <td><input type="text" style="width:98%;" name="tdesc[]" value="" placeholder="Description"/></td>
            <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
        </tr>
    <?php endif; ?>

    <tr class="empty-row custom-repeater-text" style="display: none">
        <td><input type="text" style="width:98%;" name="title[]" placeholder="Heading"/></td>
        <td><input type="text" style="width:98%;" name="tdesc[]" value="" placeholder="Description"/></td>
        <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>

    </tbody>
</table>
<div class="question-field-container">
    <div class="question-row-container row-clone-selector" id="question-main-row-0" style="display: none">
        <div class="row question-input-area-container">
            <div class="col-10">
                <p class="admin-question-row-label">Question #0</p>
                <textarea class="admin-question-row-textarea"></textarea>
            </div>
            <div class="col-2 question-row-points-container">
                <input type="number" class="question-point-input" />
                <div class="question-points-actions-container">
                    <div class="increment-question-point" aria-hidden="true">
                        <i class="fa fa-plus"></i>
                    </div>
                    <div class="decrement-question-point" aria-hidden="true">
                        <i class="fa fa-minus"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row question-other-info-container">
            <div class="col-6">
                <strong class="checkbox-label-heading">Rules:</strong>
                <div class="question-rule-checkbox-inner-container">
                    <label>Supporting.. </label>
                    <input type="checkbox" class="question-rule-checkbox" />
                </div>
            </div>
            <div class="col-6 multi-choice-btn-container">
                <button class="button" type="button">Add multi choice button</button>
            </div>
        </div>
    </div>
</div>
<p><a id="add-row" class="button" href="#">Add row</a></p>
