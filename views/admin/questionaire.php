<?php
global $post;
$main = new WP_Assessment();

$single_repeater_group = get_post_meta($post->ID, 'question_group_repeater', true);
$single_repeater_group = $main->wpa_unserialize_metadata($single_repeater_group);
$question_templates = get_post_meta($post->ID, 'question_templates', true);

$i = 0; $j = 0;
?>
<div id="question-template-wrapper" class="question-template-wrapper">
    <div id="question-group-repeater" class="question-group-repeater">
        <div class="row">
            <div class="col-12 select-template">
                <form action="">
                    <p>Please select your Assessment template:</p>
                    <p>
                        <input type="radio" id="simple_assessment_input"
                                <?php if($question_templates == 'Simple Assessment') echo "checked='checked'"; ?>
                                name="assessment_template"
                                value="Simple Assessment">
                        <label for="simple_assessment_input">Simple Assessment</label>
                    </p>
                    <p>
                        <input type="radio" id="comprehensive_assessment_input"
                                <?php if($question_templates == 'Comprehensive Assessment') echo "checked='checked'"; ?>
                                name="assessment_template"
                                value="Comprehensive Assessment">
                        <label for="comprehensive_assessment_input">Comprehensive Assessment</label>
                    </p>
                </form>
            </div>
        </div>

        <!-- Begin Comprehensive Assessment -->
        <?php if ($question_templates == 'Comprehensive Assessment' && $single_repeater_group) : ?>
            <?php $i = 0; $j = 0; ?>
            <?php foreach ($single_repeater_group as $group_id => $group_field) : ?>
                <?php
                    $group_question_title = $group_field['title'];
                    $group_question_title = htmlentities(stripslashes(utf8_decode($group_question_title)));
                    $group_question_point = $group_field['point'] ?? null;
                    $group_question_sub = isset($group_field['list'] )?  $group_field['list'] : array();
                ?>
                <div class="group-question-wrapper question" id="question-group-row-<?php echo $group_id; ?>" data-id="<?php echo $group_id; ?>">
                    <div class="question-wrapper-top">
                        <span class="button btn-remove-group">
                            Remove Group
                            <div class="remove-message">
                                Are you sure? 
                                <span class="btn-remove">Remove</span>
                                <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                            </div>
                        </span>
                        <span class="icon-toggle"></span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h5 class="admin-question-group-label">Question #<?php echo $group_id; ?></h5>
                            <input class="form-field group-question-admin-title form-control"
                                    name="group_questions[<?php echo $group_id; ?>][title]"
                                    value="<?php echo $group_question_title; ?>"
                                    placeholder="Group Question Title"/>
                        </div>
                        <!-- <div class="col-2 question-row-points-container">
                            <label><strong>Question Point</strong></label>

                            <input type="number" class="question-point-input"
                                    value="<?php //echo $group_question_point; ?>"
                                    name="group_questions[<?php //echo $group_id; ?>][point]"/>

                            <div class="question-points-actions-container">
                                <div class="increment-question-point" aria-hidden="true">
                                    <i class="fa fa-plus"></i>
                                </div>
                                <div class="decrement-question-point" aria-hidden="true">
                                    <i class="fa fa-minus"></i>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <div class="question-field-container">
                        <?php
                            $group_question_sub = array_filter(array_merge(array(0), $group_question_sub));
                        ?>
                        <?php foreach ($group_question_sub as $question_id => $field) : ?>
                                <?php
                                // $sub_question_index++;
                                $parent_question_id = $group_id;
                                $multiple_choice = $field['choice'] ?? '';
                                $sub_title = $field['sub_title'] ?? '';
                                $question_point = $field['point'] ?? '';
                                $question_advice = $field['advice'] ?? '';
                                $question_description = $field['description'] ?? '';
                                $additional_files = $field['additional_files'] ?? '';
                                $is_question_description = $field['is_description'] ?? '';
                                $supporting_doc = $field['supporting_doc'] ?? '';
                                $is_description = $field['is_description'] ?? '';

                                // remove Slashes of Quotes character(\", \')
                                $sub_title = htmlentities(stripslashes(utf8_decode($sub_title)));
                                $question_description = stripslashes($question_description);
                                $question_advice = stripslashes($question_advice);
                                ?>

                                <input type="hidden" name="question_repeater[]"/>
                                <div class="question-row-container" id="question-main-row-<?php echo $parent_question_id.'_'.$question_id; ?>" data-id="<?php echo $question_id; ?>">

                                    <div class="remove-question-block" >
                                        <span class="button btn-remove-question">
                                            Remove Question
                                            <div class="remove-message from-left">
                                                Are you sure? 
                                                <span class="btn-remove">Remove</span>
                                                <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                                            </div>
                                        </span>
                                    </div>

                                    <div class="row question-input-area-container">
                                        <div class="col-10">
                                            <p class="admin-question-row-label">Sub Question <?php echo $parent_question_id.'.'.$question_id; ?></p>
                                            <input class="form-field question-admin-title form-control"
                                                    name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][sub_title]"
                                                    value="<?php echo esc_attr($sub_title); ?>"
                                                    placeholder="Question Title"/>

                                            <div class="admin-question-row-textarea">
                                                <div class="visual-textarea-wrapper">
                                                <?php
                                                    $content   = $question_description;
                                                    $editor_id = 'sub-question-wpeditor-'.$parent_question_id.'-'.$question_id;
                                                    $editor_settings = array(
                                                        'media_buttons' => false, // This setting removes the media button.
                                                        'textarea_name' => "group_questions[".$parent_question_id."][list][".$question_id."][description]",
                                                        'textarea_rows' => 15,
                                                        'quicktags' => true, // Remove view as HTML button.
                                                        'default_editor' => 'tinymce',
                                                        'tinymce' => true,
                                                        'editor_class' => 'sub-question-wpeditor',
                                                    );
                                                    wp_editor( $content, $editor_id, $editor_settings );
                                                ?>
                                                </div>
                                                <div class="col-12">
                                                    <div class="question-rule-checkbox-inner-container">
                                                        <label>Requires a custom answer.. </label>
                                                        <input type="checkbox" class="question-rule-description-checkbox-input"
                                                            name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][is_description]"
                                                            value="1" <?php checked( $is_description, 1 ); ?>/>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--  -->
                                            <div class="question-advice-row-container">
                                                <div class="btn-toggle-advice-area">Advice <span class="toggle-icon"></span></div>
                                                <div class="helper-text">(Tips and examples)</div>
                                                <div class="visual-textarea-wrapper">
                                                    <?php
                                                        $content   = $question_advice;
                                                        $editor_id = 'question-advice-row-'.$parent_question_id .'-'. $question_id;
                                                        $editor_settings = array(
                                                            'media_buttons' => false, // This setting removes the media button.
                                                            'textarea_name' => 'group_questions['. $parent_question_id .'][list]['. $question_id .'][advice]',
                                                            'textarea_rows' => 10,
                                                            'quicktags' => true, // Remove view as HTML button.
                                                            'default_editor' => 'tinymce',
                                                            'tinymce' => true,
                                                            'editor_class' => 'advice-area-wpeditor',
                                                        );
                                                        wp_editor( $content, $editor_id, $editor_settings );
                                                    ?>
                                                </div>
                                            </div>
                                            <!-- / -->
                                        </div>
                                        <div class="col-2 sub-point question-row-points-container">
                                            <label><strong>Question Point</strong></label>
                                            <input type="number" step="0.01" class="question-point-input"
                                                    name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][point]"
                                                    value="<?php if ($question_point != '') echo esc_attr($question_point); ?>"/>
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
                                        <div class="col-12">
                                            <strong class="checkbox-label-heading">Rules:</strong>
                                            <div class="question-rule-checkbox-inner-container">
                                                <label>Supporting documentation required..</label>
                                                <input type="checkbox" class="question-rule-checkbox-input" name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][supporting_doc]"
                                                    value="1" <?php checked( $supporting_doc, 1 ); ?>/>
                                            </div>
                                        </div>
                                        <div class="col-10 multi-choice-btn-container">
                                            <button class="button add-multi-choice-btn" type="button" data-group-id="<?php echo $parent_question_id; ?>" data-id="<?php echo $question_id; ?>">
                                                Add multi choice button
                                            </button>
                                            <div class="multi-choice-btn-table-container">
                                                <table class="multi-choice-table" id="multi-check-table-<?php echo $parent_question_id.'_'.$question_id; ?>">
                                                    <tbody>
                                                    <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                                        <?php foreach ($multiple_choice as $key => $item) : ?>
                                                            <tr class="multi-choice-list-item">
                                                                <td>
                                                                    <label>Answer</label>
                                                                    <input type="text"
                                                                            class="choice-item-answer"
                                                                            name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $key; ?>][answer]"
                                                                            value="<?php if ($item['answer'] != '') echo esc_attr($item['answer']); ?>"/>
                                                                </td>
                                                                <td>
                                                                    <label>Point</label>
                                                                    <input type="number" step="0.01"
                                                                            class="choice-item-point"
                                                                            name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $key; ?>][point]"
                                                                            value="<?php if ($item['point'] != '') echo esc_attr($item['point']); ?>"/>
                                                                </td>
                                                                <td>
                                                                    <label></label>
                                                                    <input type="checkbox"
                                                                            name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $key; ?>][is_correct]"
                                                                        <?php if (isset($item['is_correct']) && $item['is_correct'] == 'on') {
                                                                            echo "checked='checked'"; } ?>/>
                                                                </td>
                                                                <td>
                                                                    <label></label>
                                                                    <span class="button btn-remove-choice">Remove</span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row question-add-files-container">
                                        <div class="col-12">
                                            <div class="btn-add-files-wrapper">
                                                <label for="additional-files-<?php echo $parent_question_id.'-'.$question_id; ?>">
                                                    <span class="button" role="button" aria-disabled="false">+ Add Additional Files</span>
                                                </label>
                                                <input id="additional-files-<?php echo $parent_question_id.'-'.$question_id; ?>"
                                                        class="additional-files"
                                                        type="file"
                                                        name="file[]"
                                                        style="visibility: hidden; position: absolute;"/>
                                                <div class="uploading-wrapper">
                                                    <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                                </div>
                                            </div>
                                            <div class="filesList">
                                                <?php if ($additional_files): ?>
                                                    <?php foreach($additional_files as $key => $file): ?>
                                                        <?php
                                                            $file_url = wp_get_attachment_url($file);
                                                            $file_name = get_the_title($file)
                                                        ?>
                                                        <?php if ($file_url): ?>
                                                        <span class="file-item">
                                                            <span class="file-delete"><span>+</span></span>
                                                            <span class="name">
                                                                <a href="<?php echo $file_url; ?>" target="_blank"><?php echo $file_name; ?></a>
                                                            </span>
                                                            <input name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][additional_files][<?php echo $key; ?>]"
                                                                    type="hidden"
                                                                    class="input-file-hiden additional-file-id-<?php echo $key; ?>"
                                                                    value="<?php echo $file; ?>">
                                                        </span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Button add more sub questions -->
                    <div class="add-row-field">
                        <span class="add-row button button-primary">Add Sub Question</span>
                    </div>
                    <div class="question-wrapper-bottom">
                        <span class="btn-expland-wrapper">
                            <span class="text">Expland Group</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- End Comprehensive Assessment -->

        <!-- Begin Simple Assessment -->
        <?php if ($question_templates == 'Simple Assessment' && $single_repeater_group) : ?>
            <?php $i = 0; $j = 0; ?>
            <?php foreach ($single_repeater_group as $question_id => $field) : ?>
                <?php
                    $i++;
                    $multiple_choice = $field['choice'];
                    $question_title = $field['title'] ?? '';
                    $question_point = $field['point'] ?? '';
                    $question_advice = $field['advice'] ?? '';
                    $question_description = $field['description'] ?? '';
                    $is_question_description = $field['is_description'] ?? '';
                    $is_question_supporting = $field['is_question_supporting'] ?? '';

                    // remove Slashes of Quotes character(\", \')
                    $question_title = htmlentities(stripslashes(utf8_decode($question_title)));
                    $question_description = stripslashes($question_description);
                    $question_advice = stripslashes($question_advice);
                ?>
                <div class="simple-question-container question question-row-container" id="question-main-row-<?php echo $i; ?>">
                    <div class="question-wrapper-top">
                        <span class="button btn-remove-question">Remove Question
                            <div class="remove-message">Are you sure? 
                                <span class="btn-remove">Remove</span>
                                <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                            </div>
                        </span>
                        <span class="icon-toggle"></span>
                    </div>
                    <input type="hidden" name="question_repeater[]"/>
                    <div class="row question-input-area-container">
                        <div class="col-12">
                            <p class="admin-question-row-label">Question #<?php echo $i; ?></p>
                            <input class="form-field question-admin-title form-control"
                                    name="group_questions[<?php echo $i; ?>][title]"
                                    value="<?php echo $question_title; ?>"
                                    placeholder="Question Title"/>
                            <div class="admin-question-row-textarea">
                                <div class="visual-textarea-wrapper">
                                    <?php
                                        $content   = $question_description;
                                        $editor_id = 'question-wpeditor-'.$i;
                                        $editor_settings = array(
                                            'media_buttons' => false, // This setting removes the media button.
                                            'textarea_name' => "group_questions[".$i."][description]",
                                            'textarea_rows' => 10,
                                            'quicktags' => true, // Remove view as HTML button.
                                            'default_editor' => 'tinymce',
                                            'tinymce' => true,
                                            'editor_class' => 'question-wpeditor',
                                        );
                                        wp_editor( $content, $editor_id, $editor_settings );
                                    ?>
                                </div>
                                <div class="col-12">
                                    <div class="question-rule-checkbox-inner-container">
                                        <label>Requires a custom answer.. </label>
                                        <input type="checkbox" class="question-rule-description-checkbox-input" name="group_questions[<?php echo $i; ?>][is_description]" value="1" <?php checked( $is_question_description, 1 ); ?>/>
                                    </div>
                                </div>
                            </div>
                            <div class="question-advice-row-container">
                                <label>Advice:</label>
                                <div class="visual-textarea-wrapper">
                                    <?php
                                        $content   = $question_advice;
                                        $editor_id = 'wp-advice-editor-'.$i;
                                        $editor_settings = array(
                                            'media_buttons' => false, // This setting removes the media button.
                                            'textarea_name' => 'group_questions['.$i.'][advice]',
                                            'textarea_rows' => 10,
                                            'quicktags' => true, // Remove view as HTML button.
                                            'default_editor' => 'tinymce',
                                            'tinymce' => true,
                                            'editor_class' => 'advice-area-wpeditor',
                                        );
                                        wp_editor( $content, $editor_id, $editor_settings );
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 question-row-points-container">
                            <!-- <label><strong>Question Point</strong></label>
                            <input type="number"
                                    class="question-point-input"
                                    name="group_questions[ // echo $i; ][point]"
                                    value=" // echo $question_point; "/>
                            <div class="question-points-actions-container">
                                <div class="increment-question-point" aria-hidden="true">
                                    <i class="fa fa-plus"></i>
                                </div>
                                <div class="decrement-question-point" aria-hidden="true">
                                    <i class="fa fa-minus"></i>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="row question-other-info-container">
                        <div class="col-12">
                            <strong class="checkbox-label-heading">Rules:</strong>
                            <div class="question-rule-checkbox-inner-container" style="display:none;">
                                <label>Supporting documentation required.. </label>
                                <input type="checkbox" class="question-rule-checkbox-input" name="group_questions[<?php echo $i; ?>][is_question_supporting]" <?php checked( $is_question_supporting, 1 ); ?>/>
                            </div>
                        </div>
                        <div class="col-10 multi-choice-btn-container">
                            <button class="button add-multi-choice-simple" type="button" data-id="<?php echo $i; ?>">Add multi choice button</button>
                            <div class="multi-choice-btn-table-container">
                                <table class="multi-choice-table" id="multi-check-table-<?php echo $i; ?>">
                                    <tbody>
                                    <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                        <?php foreach ($multiple_choice as $key => $item) :  ?>
                                            <tr class="multi-choice-list-item" style="display:table-row">
                                                <td>
                                                    <label>Answer</label>
                                                    <input type="text" name="group_questions[<?php echo $question_id; ?>][choice][<?php echo $key; ?>][answer]"
                                                            value="<?php if ($item['answer'] != '') echo esc_attr($item['answer']); ?>"/>
                                                </td>
                                                <!-- <td>
                                                    <label>Point</label>
                                                    <input type="number"
                                                            class="choice-item-point"
                                                            name="group_questions[<?php //echo $question_id; ?>][choice][<?php //echo $key; ?>][point]"
                                                            value="<?php //if ($item['point'] != '') echo esc_attr($item['point']); ?>"/>
                                                </td> -->
                                                <td>
                                                    <label></label>
                                                    <input type="checkbox" name="group_questions[<?php echo $question_id; ?>][choice][<?php echo $key; ?>][is_correct]"
                                                        <?php if (isset($item['is_correct']) && $item['is_correct'] == 'on') {
                                                            echo "checked='checked'"; } ?>/>
                                                </td>
                                                <td>
                                                    <label></label>
                                                    <span class="button btn-remove-choice">Remove</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="question-wrapper-bottom">
                        <span class="btn-expland-wrapper">
                            <span class="text">Expland Question</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- End Simple Assessment -->
    </div>
    <!-- Button add more Group questions -->
    <?php if($question_templates == 'Simple Assessment'): ?>
        <p id="add-simple-row-block">
            <span id="add-simple-row" class="button button-primary">Add Simple Question</span>
        </p>
    <?php endif; ?>

    <?php if($question_templates == 'Comprehensive Assessment'): ?>
        <p id="add-group-row-block">
            <span id="add-group-row" class="button button-primary">Add Group Question</span>
        </p>
    <?php endif; ?>
</div>
