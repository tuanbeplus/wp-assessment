<?php

class Custom_Fields
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'init_meta_boxes_assessment_admin'));
        add_action('admin_init', array($this, 'init_meta_boxes_submission_admin_view')); 
        // add_action('admin_init', array($this, 'wpa_assessments_additional_files')); 

        add_action('save_post', array($this, 'question_repeatable_meta_box_save'));
        add_action('save_post', array($this, 'report_template_meta_box_save'));
        add_action('save_post', array($this, 'save_assigned_moderator'));
        add_action('save_post', array($this, 'save_assigned_collaborator'));
        add_action('save_post', array($this, 'on_save_submission_custom_fields'));

        add_action( 'show_user_profile', array($this, 'assessments_additional_profile_fields'));
        add_action( 'edit_user_profile', array($this, 'assessments_additional_profile_fields'));

        // moderator user list
    }

    function init_meta_boxes_assessment_admin(): void
    {
        add_meta_box('questions-repeater-field', 'Questions', array($this, 'question_repeatable_meta_box_callback'), 'assessments', 'normal', 'default');

        if (current_user_can('administrator')) {
            add_meta_box('moderator-list', 'Select Moderator', array($this, 'display_moderator_select_list'), 'assessments', 'normal', 'default');
        }

        add_meta_box('report-section-field', 'Report Template', array($this, 'report_section_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('report-recommendation-field', 'Report Recommendation', array($this, 'report_recommendation_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('assessment-options-field', 'Assessment Options', array($this, 'assessment_options_meta_box_callback'), 'assessments', 'side', 'default');
    }

    function init_meta_boxes_submission_admin_view(): void
    {
        add_meta_box('questions-repeater-field', 'Submission detail', array($this, 'submission_list_card_section_admin'), 'submissions', 'normal', 'default');
        add_meta_box('submitted_info_view', 'Submission by: ', array($this, 'submission_info_section_admin'), 'submissions', 'side', 'default');
        add_meta_box('attachment_uploader_info_view', 'Uploaded by member', array($this, 'attachment_uploader_info_section_admin'), 'attachment', 'side', 'default');
    }

    function assessment_options_meta_box_callback()
    {
        return include_once ADMIN_ASSESSMENT_OPTION_VIEW;
    }

    function os_linkto_meta_box_html() 
    {
        global $post;
        echo '<select name=”os_linkto_product[]” id=”os_linkto_product” multiple=”yes” size=”10″>';
        echo '<option value=””>-</option>';
        $val = get_post_meta($post->ID, 'os_linkto_product', true);
        $q = get_posts('post_type=assessments&post_parent=0&numberposts=-1&orderby=menu_order&order=ASC');
        
        foreach ($q as $obj)
        {
        echo '<option value="'.$obj->ID.'" "checked="checked">'.$obj->post_title.'</option>';
        }
        echo '</select>';
    }

    function attachment_uploader_info_section_admin()
    {
        return include_once ADMIN_ATTACHMENT_UPLOADER_INFO_VIEW;
    }

    function report_section_meta_box_callback()
    {
        return include_once ADMIN_REPORT_SECTION_FIELDS;
    }

    function question_repeatable_meta_box_callback()
    {
        return include_once MODERATOR_LIST_ADMIN_QUESTIONAIRE_FIELDS;
    }

    function report_recommendation_meta_box_callback()
    {
        return include_once ADMIN_REPORT_RECOMMENDATION_FIELDS;
    }

    function display_moderator_select_list()
    {
        return include_once MODERATOR_LIST_ADMIN_SELECT;
    }

    function submission_list_card_section_admin()
    {
        return include_once ADMIN_SUBMISSION_VIEW;
    }

    function submission_info_section_admin()
    {
        return include_once ADMIN_SUBMISSION_INFO_VIEW;
    }
    function submission_key_recommendation_section_admin()
    {
        return include_once SUBMISSON_KEY_RECOMMENDATION;
    }
    function submission_e_summary_section_admin()
    {
        return include_once SUBMISSON_E_SUMMARY;
    }
    function submission_key_findings_section_admin()
    {
        return include_once SUBMISSON_KEY_FINDINGS;
    }

    function assessments_additional_profile_fields() 
    {
        return include_once USER_ASSESSMENTS_PERCHASED_FIELDS;
    }

    function question_repeatable_meta_box_save($post_id): void
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) != 'assessments')
            return;

        $assessment_template = $_POST['assessment_template'] ?? null;
        $group_questions = $_POST['group_questions'] ?? array();
        $is_required_answer_all = $_POST['is_required_answer_all'] ?? 0;
        $is_required_document_all = $_POST['is_required_document_all'] ?? 0;
        $is_invite_colleagues = $_POST['is_invite_colleagues'] ?? 0;
        $is_all_users_can_access = $_POST['is_all_users_can_access'] ?? 0;
        $related_sf_products = $_POST['related_sf_products'] ?? null;
        
        // echo "<pre>";
        // print_r($group_questions);
        // echo "</pre>";

        $new_group_questions = array();
        $item = 1;
        foreach ($group_questions as $value) {
            $new_group_questions[$item] = $value;
            $item++;
        }

        update_post_meta($post_id, 'question_group_repeater', base64_encode(serialize($new_group_questions)));
        update_post_meta($post_id, 'question_templates', $assessment_template);
        update_post_meta($post_id, 'is_required_answer_all', $is_required_answer_all);
        update_post_meta($post_id, 'is_required_document_all', $is_required_document_all);
        update_post_meta($post_id, 'is_invite_colleagues', $is_invite_colleagues);
        update_post_meta($post_id, 'is_all_users_can_access', $is_all_users_can_access);
        update_post_meta($post_id, 'related_sf_products', $related_sf_products);

    }

    function report_template_meta_box_save($post_id): void
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) != 'assessments')
            return;

        $report_sections = isset($_POST['report_sections']) ? $_POST['report_sections'] : '';
        $key_recommendation = isset($_POST['key_recommendation']) ? $_POST['key_recommendation'] : '';
        $executive_summary = isset($_POST['executive_summary']) ? $_POST['executive_summary'] : '';
        $evalution_findings = isset($_POST['evalution_findings']) ? $_POST['evalution_findings'] : '';

        update_post_meta($post_id, 'report_template_content', $report_sections);
        update_post_meta($post_id, 'report_recommendation', $key_recommendation);
        update_post_meta($post_id, 'executive_summary', $executive_summary);
        update_post_meta($post_id, 'evalution_findings', $evalution_findings);

    }

    function on_save_submission_custom_fields($post_id): void
    {
        $post_type = get_post_type($post_id);
        if ($post_type != 'submissions') return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;
        $group_quiz_points = $_POST['group_quiz_point'] ?? null;
        $quiz_feedbacks = $_POST['quiz_feedback'] ?? null;
        $quiz_answer_points = $_POST['quiz_answer_point'] ?? null;

        // echo '<pre>';
        // print_r($quiz_feedbacks);
        // echo '</pre>';
        // die;

        $new_group_quiz_points = array();
        $item = 1;
        foreach ($group_quiz_points as $value) {
          $new_group_quiz_points[$item] = $value;
          $item++;
        }

        $new_group_quiz_points = serialize($new_group_quiz_points);
        
        update_post_meta($post_id, 'group_quiz_point', $new_group_quiz_points);
        update_post_meta($post_id, 'quiz_feedback', $quiz_feedbacks);
        update_post_meta($post_id, 'quiz_answer_point', $quiz_answer_points);
    }

    function save_assigned_moderator($post_id): void
    {
        $post_type = get_post_type($post_id);

        if (isset($_POST['assigned_moderator'])) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;
            update_post_meta($post_id, 'assigned_moderator', $_POST['assigned_moderator']);
        } else {
            $init = new WP_Assessment();
            if ($post_type === "assessments" && $init->get_current_user_role() === "moderator") {
                update_post_meta($post_id, 'assigned_moderator', get_current_user_id());
            }
        }
    }

    function save_assigned_collaborator($post_id): void
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) != 'assessments')
            return;

        $post_type = get_post_type($post_id);

        $assigned_collaborator = isset($_POST['assigned_collaborator']) ? $_POST['assigned_collaborator'] : '';

        update_post_meta($post_id, 'assigned_collaborator', $assigned_collaborator);
    }
}

new Custom_Fields();
