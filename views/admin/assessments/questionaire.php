<?php
global $post;
$main = new WP_Assessment();
$questions_repeater = get_post_meta($post->ID, 'question_group_repeater', true);
$questions_repeater = $main->wpa_unserialize_metadata($questions_repeater);
$question_templates = get_post_meta($post->ID, 'question_templates', true);
$is_assessment_completed = get_post_meta($post->ID, 'is_assessment_completed', true);
$report_key_areas = get_assessment_key_areas($post->ID);
?>
<div id="question-template-wrapper" class="question-template-wrapper">
    <div class="select-template row">
        <form id="assessment-template-form" onsubmit="return false">
            <p>Please select your Assessment template:</p>
            <p>
                <input type="radio" id="simple_assessment_input"
                    <?php if($question_templates === 'Simple Assessment') echo "checked='checked'"; ?>
                    name="assessment_template"
                    value="Simple Assessment">
                <label for="simple_assessment_input">Simple Assessment</label>
            </p>
            <p>
                <input type="radio" id="comprehensive_assessment_input"
                    <?php if($question_templates === 'Comprehensive Assessment') echo "checked='checked'"; ?>
                    name="assessment_template"
                    value="Comprehensive Assessment">
                <label for="comprehensive_assessment_input">Comprehensive Assessment</label>
            </p>
        </form>
    </div>

    <div id="question-group-repeater" class="question-group-repeater">
        <?php if ($question_templates === 'Comprehensive Assessment' && !empty($questions_repeater)): ?>
            <!-- Begin Comprehensive Assessment -->
            <?php foreach ($questions_repeater as $group_id => $group_field): 
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

                    <h3 class="admin-question-group-label">Question #<?php echo $group_id; ?></h3>
                    <input type="text" class="form-field group-question-admin-title form-control"
                        name="group_questions[<?php echo $group_id; ?>][title]"
                        value="<?php echo esc_attr($group_question_title) ?>"
                        placeholder="Group Question Title" required
                        tabindex="<?php if($is_locked_group == true) echo '-1'; ?>"/>

                    <div class="question-field-container">
                        <?php
                        $group_question_sub = array_filter(array_merge(array(0), $group_question_sub));
                        foreach ($group_question_sub as $question_id => $field): 
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
                            <div class="question-row-container" id="question-main-row-<?php echo $group_id.'_'.$question_id; ?>" data-id="<?php echo $question_id; ?>">
                                <div class="question-name">
                                    <h4 class="admin-question-row-label">Sub Question <?php echo $group_id.'.'.$question_id; ?></h4>
                                    <span class="button btn-remove-question">
                                        - Remove Question
                                        <div class="remove-message from-left">
                                            Are you sure? 
                                            <span class="btn-remove">Remove</span>
                                            <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                                        </div>
                                    </span>
                                </div>

                                <!-- Question Title -->
                                <div class="question-input-area-container">
                                    <div class="question-title">
                                        <label>Title</label>
                                        <textarea rows="2" class="form-field question-admin-title form-control"
                                            name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][sub_title]"
                                            placeholder="Question Title" ><?php echo esc_html($sub_title) ?></textarea>
                                    </div>
                                    <div class="sub-point question-row-points-container">
                                        <label>Question Point</label>
                                        <input id="question-point-<?php echo $group_id.'-'.$question_id ?>"
                                                type="number" step="0.1" class="question-point-input"
                                                name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][point]"
                                                value="<?php if ($question_point != '') echo esc_attr($question_point); ?>"/>
                                    </div>
                                </div><!-- /Question Title -->

                                <!-- Description Editor -->
                                <div class="question-row-desc question-row">
                                    <label>Description</label>
                                    <div class="visual-textarea-wrapper">
                                    <?php
                                        $content   = $question_description;
                                        $editor_id = 'sub-question-wpeditor-'.$group_id.'-'.$question_id;
                                        $editor_settings = array(
                                            'media_buttons' => false, // This setting removes the media button.
                                            'textarea_name' => "group_questions[".$group_id."][list][".$question_id."][description]",
                                            'textarea_rows' => 15,
                                            'quicktags' => true, // Remove view as HTML button.
                                            'default_editor' => 'tinymce',
                                            'tinymce' => true,
                                            'editor_class' => 'sub-question-wpeditor',
                                        );
                                        wp_editor( $content, $editor_id, $editor_settings );
                                    ?>
                                    </div>
                                </div><!-- /Description Editor -->
                                
                                <!-- Answer Choices -->
                                <div class="multi-choices-container question-row">
                                    <label>Answer Choices</label>
                                    <button class="button add-multi-choice-btn" type="button" data-group-id="<?php echo $group_id; ?>" data-id="<?php echo $question_id; ?>">
                                        <span>Add Choice</span>
                                    </button>
                                    <div class="multi-choices-table-container">
                                        <table class="multi-choices-table" id="multi-choices-table-<?php echo $group_id.'-'.$question_id; ?>">
                                        <tbody>
                                        <?php if ( !empty($multiple_choice) ): $choice_index = 0; ?>
                                            <?php foreach ($multiple_choice as $key => $item): $choice_index++; ?>
                                                <tr class="multi-choice-list-item">
                                                    <td>
                                                        <label>Answer</label>
                                                        <input type="text"
                                                                class="choice-item-answer"
                                                                name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $choice_index; ?>][answer]"
                                                                value="<?php if ($item['answer'] != '') echo esc_attr($item['answer']); ?>"/>
                                                    </td>
                                                    <td>
                                                        <label>Point</label>
                                                        <input type="number" step="0.1"
                                                                class="choice-item-point"
                                                                name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $choice_index; ?>][point]"
                                                                value="<?php if ($item['point'] != '') echo esc_attr($item['point']); ?>"/>
                                                    </td>
                                                    <td>
                                                        <label>True</label>
                                                        <input type="checkbox"
                                                                name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][choice][<?php echo $choice_index; ?>][is_correct]"
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
                                </div><!-- /Answer Choices -->
                                
                                <!-- Client Comment Area -->
                                <div class="checkbox-field question-row">
                                    <input id="desc-checkbox-<?php echo $group_id.'-'.$question_id ?>" type="checkbox" 
                                        name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][is_description]"
                                        value="1" <?php checked( $is_description, 1 ); ?>/>
                                    <label for="desc-checkbox-<?php echo $group_id.'-'.$question_id ?>">Show client comment area</label>
                                </div><!-- /Client Comment Area -->
                                
                                <!-- Client Supporting Documentation -->
                                <div class="checkbox-field question-row">
                                    <input id="upload-docs-checkbox-<?php echo $group_id.'-'.$question_id ?>" type="checkbox" 
                                        name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][supporting_doc]"
                                        value="1" <?php checked( $supporting_doc, 1 ); ?>/>
                                    <label for="upload-docs-checkbox-<?php echo $group_id.'-'.$question_id ?>">Show upload supporting documentation area</label>
                                </div><!-- /Client Supporting Documentation -->
                                
                                <!-- Advice Editor -->
                                <div class="question-advice-container question-row">
                                    <div class="btn-toggle-advice-area">
                                        <label>Advice (Tips and examples)</label>
                                        <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
                                    </div>
                                    <div class="advice-editor-wrapper">
                                    <?php
                                        $content   = $question_advice;
                                        $editor_id = 'question-advice-row-'.$group_id .'-'. $question_id;
                                        $editor_settings = array(
                                            'media_buttons' => false, // This setting removes the media button.
                                            'textarea_name' => 'group_questions['. $group_id .'][list]['. $question_id .'][advice]',
                                            'textarea_rows' => 10,
                                            'quicktags' => true, // Remove view as HTML button.
                                            'default_editor' => 'tinymce',
                                            'tinymce' => true,
                                            'editor_class' => 'advice-area-wpeditor',
                                        );
                                        wp_editor( $content, $editor_id, $editor_settings );
                                    ?>
                                    </div>
                                </div><!-- /Advice Editor -->
                                
                                <!-- Additional Files -->
                                <div class="question-add-files-container question-row">
                                    <div class="btn-add-files-wrapper">
                                        <label>Additional Files</label>
                                        <label class="additional-files-label" for="additional-files-<?php echo $group_id.'-'.$question_id; ?>">
                                            <span class="button" role="button" aria-disabled="false">
                                                <span class="icon"><i class="fa-solid fa-file-arrow-up"></i></span>
                                                <span>Upload Files</span>
                                            </span>
                                        </label>
                                        <input id="additional-files-<?php echo $group_id.'-'.$question_id; ?>"
                                                class="additional-files"
                                                type="file" name="file[]" multiple/>
                                        <div class="uploading-wrapper">
                                            <img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="">
                                        </div>
                                        <div class="_message">Files uploaded</div>
                                    </div>
                                    <div class="filesList">
                                    <?php if ( !empty($additional_files) ): ?>
                                        <?php foreach($additional_files as $key => $file): ?>
                                            <?php
                                                $file_url = wp_get_attachment_url($file);
                                                $file_name = get_the_title($file)
                                            ?>
                                            <?php if ($file_url): ?>
                                            <span class="file-item">
                                                <a class="name" href="<?php echo $file_url; ?>" target="_blank">
                                                    <span class="icon"><i class="fa-solid fa-paperclip"></i></span>
                                                    <span><?php echo $file_name; ?></span>
                                                </a>
                                                <span class="file-delete"><i class="fa-solid fa-xmark"></i></span>
                                                <input name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][additional_files][<?php echo $key; ?>]"
                                                        type="hidden"
                                                        class="input-file-hiden additional-file-id-<?php echo $key; ?>"
                                                        value="<?php echo $file; ?>">
                                            </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </div>
                                </div><!-- /Additional Files -->

                                <!-- Key Areas -->
                                <div class="question-key-areas">
                                    <label class="col-12"><strong>Select Key Area</strong></label>
                                    <select class="select-key-area" name="group_questions[<?php echo $group_id; ?>][list][<?php echo $question_id; ?>][key_area]">
                                        <?php if ( !empty($report_key_areas) ): ?>
                                            <option value="">Choose Key Area</option>
                                            <?php foreach ($report_key_areas as $key_area): ?>
                                                <option value="<?php echo $key_area; ?>"
                                                    <?php if ($selected_key_area == $key_area) echo 'selected'; ?>>
                                                    <?php echo $key_area; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" selected>Choose Key Area</option>
                                        <?php endif; ?>
                                    </select>
                                </div><!-- /Key Areas -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Button add more sub questions -->
                    <div class="add-row-field">
                        <span class="add-question-row button button-primary">Add Sub Question</span>
                    </div>
                    <div class="question-wrapper-bottom">
                        <a class="btn-lock-question <?php if($is_assessment_completed == true) echo 'disabled'; ?>" role="button">
                            <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/img-lock.png">
                            <span class="lock-text"> <?php echo ($is_locked_group == true) ? 'Unlock' : 'Lock'; ?></span>
                        </a>
                        <a class="btn-expand-wrapper" role="button">
                            <span class="text">Expand Group</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- End Comprehensive Assessment -->
        <?php endif; ?>
        
        <?php if ($question_templates === 'Simple Assessment' && !empty($questions_repeater)): ?>
            <!-- Begin Simple Assessment -->
            <?php $i = 0; 
            foreach ($questions_repeater as $question_id => $field): 
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
                    class="simple-question-container question question-row-container 
                    <?php if($is_locked_question == true) echo 'disabled'; ?>" tabindex="-1">

                    <input class="is_locked_input" type="hidden" 
                            name="group_questions[<?php echo $i; ?>][is_locked]"
                            value="<?php if($is_locked_question == true) echo '1'; ?>">

                    <div class="question-wrapper-top">
                        <span class="button btn-remove-question">- Remove Question
                            <div class="remove-message">Are you sure? 
                                <span class="btn-remove">Remove</span>
                                <span class="icon-close"><i class="fa-solid fa-circle-xmark"></i></span>
                            </div>
                        </span>
                        <span class="icon-toggle"></span>
                    </div>

                    <h3 class="admin-question-group-label">Question #<?php echo $i; ?></h3>

                    <input type="text" class="question-admin-title form-control"
                        name="group_questions[<?php echo $i; ?>][title]"
                        value="<?php echo esc_attr($question_title) ?>"
                        placeholder="Question Title" required
                        tabindex="<?php if($is_locked_question == true) echo '-1'; ?>"/>

                    <!-- Description Editor -->
                    <div class="question-row-desc question-row">
                        <label>Description</label>
                        <div class="visual-textarea-wrapper">
                        <?php
                            $content   = $question_description;
                            $editor_id = 'question-wpeditor-'.$i;
                            $editor_settings = array(
                                'media_buttons' => false, // This setting removes the media button.
                                'textarea_name' => "group_questions[".$i."][description]",
                                'textarea_rows' => 12,
                                'quicktags' => true, // Remove view as HTML button.
                                'default_editor' => 'tinymce',
                                'tinymce' => true,
                                'editor_class' => 'question-wpeditor',
                            );
                            wp_editor( $content, $editor_id, $editor_settings );
                        ?>
                        </div>
                    </div><!-- /Description Editor -->
                    
                    <!-- Answer Choices -->
                    <div class="multi-choices-container question-row">
                        <label>Answer Choices</label>
                        <button class="button add-multi-choice-simple" type="button" data-id="<?php echo $i; ?>">Add Choice</button>
                        <div class="multi-choices-table-container">
                            <table class="multi-choices-table" id="multi-choice-table-<?php echo $i; ?>">
                            <tbody>
                            <?php if (!empty($multiple_choice)): $choice_index = 0; ?>
                                <?php foreach ($multiple_choice as $item): $choice_index++; ?>
                                    <tr class="multi-choice-list-item">
                                        <td>
                                            <label>Answer</label>
                                            <input type="text" name="group_questions[<?php echo $question_id; ?>][choice][<?php echo $choice_index; ?>][answer]"
                                                    value="<?php if ($item['answer'] != '') echo esc_attr($item['answer']); ?>"/>
                                        </td>
                                        <td>
                                            <label>True</label>
                                            <input type="checkbox" name="group_questions[<?php echo $question_id; ?>][choice][<?php echo $choice_index; ?>][is_correct]"
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
                    </div><!-- /Answer Choices -->
                    
                    <!-- Client Comment Area -->
                    <div class="checkbox-field question-row">
                        <input id="desc-checkbox-<?php echo $i; ?>"
                            type="checkbox" class="question-rule-description-checkbox-input" 
                            name="group_questions[<?php echo $i; ?>][is_description]" 
                            value="1" <?php checked( $is_question_description, 1 ); ?>/>
                        <label for="desc-checkbox-<?php echo $i; ?>">Show client comment area</label>
                    </div><!-- /Client Comment Area -->
                    
                    <!-- Advice Editor -->
                    <div class="question-advice-container">
                        <label>Advice (Tips and examples)</label>
                        <div class="advice-editor-wrapper">
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
                    </div><!-- /Advice Editor -->

                    <div class="question-wrapper-bottom">
                        <a class="btn-lock-question <?php if($is_assessment_completed == true) echo 'disabled'; ?>" role="button">
                            <img class="locked-icon" src="/wp-content/plugins/wp-assessment/assets/images/img-lock.png">
                            <span class="lock-text"> <?php echo ($is_locked_question == true) ? 'Unlock' : 'Lock'; ?></span>
                        </a>
                        <a class="btn-expand-wrapper" role="button">
                            <span class="text">Expand Question</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- End Simple Assessment -->
        <?php endif; ?>
    </div>

    <!-- Save changes & Add Row -->
    <div id="actions-row-block">
        <span id="btn-save-changes" class="button button-primary button-large">Save Changes</span>
        <?php if ($question_templates === 'Comprehensive Assessment'): ?>
            <span id="add-group-row" class="button button-primary button-large">Add Group Question</span>
        <?php elseif ($question_templates === 'Simple Assessment'): ?>
            <span id="add-simple-row" class="button button-primary button-large">Add Simple Question</span>
        <?php endif; ?>
    </div><!-- /Save changes & Add Row -->
</div>
