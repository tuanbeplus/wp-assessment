<?php
global $post;
$main = new WP_Assessment();
$questions_repeater = get_post_meta($post->ID, 'question_group_repeater', true);
$questions_repeater = $main->wpa_unserialize_metadata($questions_repeater);
$question_templates = get_post_meta($post->ID, 'question_templates', true);
$is_assessment_completed = get_post_meta($post->ID, 'is_assessment_completed', true);
$report_key_areas = get_assessment_key_areas($post->ID);
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
        <?php if ($question_templates == 'Comprehensive Assessment' && !empty($questions_repeater)) : ?>
            <?php $i = 0; $j = 0; ?>
            <?php foreach ($questions_repeater as $group_id => $group_field) : ?>
                <?php
                    $group_question_title = $group_field['title'] ?? null;
                    $is_locked_group = $group_field['is_locked'] ?? null;
                    $group_question_point = $group_field['point'] ?? null;
                    $group_question_sub = isset($group_field['list'] )?  $group_field['list'] : array();
                ?>
                <div id="question-group-row-<?php echo $group_id; ?>" data-id="<?php echo $group_id; ?>"
                    class="group-question-wrapper question <?php if($is_locked_group == true) echo 'disabled'; ?>">

                    <input class="is_locked_input" type="hidden" 
                            name="group_questions[<?php echo $group_id; ?>][is_locked]"
                            value="<?php if($is_locked_group == true) echo '1'; ?>">
                    
                    <div class="question-wrapper-top">
                        <span class="button btn-remove-group">
                            - Remove Group
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
                                    value="<?php echo esc_attr($group_question_title) ?>"
                                    placeholder="Group Question Title"
                                    tabindex="<?php if($is_locked_group == true) echo '-1'; ?>"/>
                        </div>
                    </div>

                    <div class="question-field-container">
                        <?php
                            $group_question_sub = array_filter(array_merge(array(0), $group_question_sub));
                        ?>
                        <?php foreach ($group_question_sub as $question_id => $field) : ?>
                                <?php
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
                                $selected_key_area = $field['key_area'] ?? '';
                                ?>

                                <input type="hidden" name="question_repeater[]"/>
                                <div class="question-row-container" id="question-main-row-<?php echo $parent_question_id.'_'.$question_id; ?>" data-id="<?php echo $question_id; ?>">

                                    <div class="remove-question-block" >
                                        <span class="button btn-remove-question">
                                            - Remove Question
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
                                            <input type="number" step="0.1" class="question-point-input"
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
                                    <div class="question-other-info-container">
                                        <div>
                                            <strong class="checkbox-label-heading">Rules:</strong>
                                            <div class="question-rule-checkbox-inner-container">
                                                <label>Supporting documentation required..</label>
                                                <input type="checkbox" class="question-rule-checkbox-input" name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][supporting_doc]"
                                                    value="1" <?php checked( $supporting_doc, 1 ); ?>/>
                                            </div>
                                        </div>
                                        <div class="multi-choice-btn-container">
                                            <button class="button add-multi-choice-btn" type="button" data-group-id="<?php echo $parent_question_id; ?>" data-id="<?php echo $question_id; ?>">
                                                <i class="fa-solid fa-plus"></i> Add Multiple Choice
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
                                        <div>
                                            <div class="btn-add-files-wrapper">
                                                <label for="additional-files-<?php echo $parent_question_id.'-'.$question_id; ?>">
                                                    <span class="button" role="button" aria-disabled="false">
                                                        <i class="fa-solid fa-file-arrow-up"></i> Add Additional Files
                                                    </span>
                                                </label>
                                                <input id="additional-files-<?php echo $parent_question_id.'-'.$question_id; ?>"
                                                        class="additional-files"
                                                        type="file"
                                                        name="file[]"
                                                        style="visibility:hidden; position:absolute;"/>
                                                <div class="uploading-wrapper">
                                                    <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="uploading">
                                                </div>
                                                <div class="__message">File Uploaded</div>
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
                                                            <a class="name" href="<?php echo $file_url; ?>" target="_blank"><?php echo $file_name; ?></a>
                                                            <span class="file-delete"><i class="fa-solid fa-xmark"></i></span>
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
                                    <!-- Key Areas -->
                                    <div class="key-areas">
                                        <label class="col-12"><strong>Select Key Area</strong></label>
                                        <select class="select-key-area" name="group_questions[<?php echo $parent_question_id; ?>][list][<?php echo $question_id; ?>][key_area]">
                                            <option value="">Choose Key Area</option>
                                            <?php if (!empty($report_key_areas)): ?>
                                                <?php foreach ($report_key_areas as $key_area): ?>
                                                    <option value="<?php echo $key_area; ?>"
                                                        <?php if ($selected_key_area == $key_area) echo 'selected'; ?>>
                                                        <?php echo $key_area; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <!-- /Key Areas -->
                                </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Button add more sub questions -->
                    <div class="add-row-field">
                        <span class="add-row button button-primary">Add Sub Question</span>
                    </div>
                    <div class="question-wrapper-bottom">
                        <a class="btn-lock-question <?php if($is_assessment_completed == true) echo 'disabled'; ?>" role="button">
                            <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/lock.svg" width="36" height="36">
                            <span class="lock-text"> <?php echo ($is_locked_group == true) ? 'Unlock' : 'Lock'; ?></span>
                        </a>
                        <a class="btn-expland-wrapper" role="button">
                            <span class="text">Expland Group</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- End Comprehensive Assessment -->

        <!-- Begin Simple Assessment -->
        <?php if ($question_templates == 'Simple Assessment' && !empty($questions_repeater)) : ?>
            <?php $i = 0; $j = 0; ?>
            <?php foreach ($questions_repeater as $question_id => $field): 
                $i++;
                $multiple_choice = $field['choice'];
                $question_title = $field['title'] ?? '';
                $is_locked_question = $field['is_locked'] ?? '';
                $question_point = $field['point'] ?? '';
                $question_advice = $field['advice'] ?? '';
                $question_description = $field['description'] ?? '';
                $is_question_description = $field['is_description'] ?? '';
                $is_question_supporting = $field['is_question_supporting'] ?? '';
                ?>
                <div id="question-main-row-<?php echo $i; ?>" 
                    class="simple-question-container question question-row-container <?php if($is_locked_question == true) echo 'disabled'; ?>">

                    <input class="is_locked_input" type="hidden" 
                            name="group_questions[<?php echo $i; ?>][is_locked]"
                            value="<?php if($is_locked_question == true) echo '1'; ?>">

                    <div class="question-wrapper-top">
                        <span class="button btn-remove-question">
                            - Remove Question
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
                                    value="<?php echo esc_attr($question_title) ?>"
                                    placeholder="Question Title"
                                    tabindex="<?php if($is_locked_question == true) echo '-1'; ?>"/>
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
                        <div class="col-2 question-row-points-container"></div>
                    </div>
                    <div class="row question-other-info-container">
                        <div class="col-12">
                            <strong class="checkbox-label-heading">Rules:</strong>
                            <div class="question-rule-checkbox-inner-container" style="display:none;">
                                <label>Supporting documentation required. </label>
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
                        <a class="btn-lock-question <?php if($is_assessment_completed == true) echo 'disabled'; ?>" role="button">
                            <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/lock.svg" width="36" height="36">
                            <span class="lock-text"> <?php echo ($is_locked_question == true) ? 'Unlock' : 'Lock'; ?></span>
                        </a>
                        <a class="btn-expland-wrapper" role="button">
                            <span class="text">Expland Question</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- End Simple Assessment -->
    </div>
    <!-- Button add more Group questions -->
    <?php if($question_templates == 'Simple Assessment'): ?>
        <p id="add-simple-row-block">
            <span id="add-simple-row" class="button button-primary button-large">Add Simple Question</span>
        </p>
    <?php endif; ?>

    <?php if($question_templates == 'Comprehensive Assessment'): ?>
        <p id="add-group-row-block">
            <span id="add-group-row" class="button button-primary button-large">Add Group Question</span>
        </p>
    <?php endif; ?>
</div>
