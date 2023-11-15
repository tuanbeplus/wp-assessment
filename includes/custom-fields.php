<?php

class Custom_Fields
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'init_meta_boxes_admin'));
        add_action('save_post', array($this, 'question_repeatable_meta_box_save'));
        add_action('save_post', array($this, 'report_template_meta_box_save'));
        add_action('save_post', array($this, 'save_assigned_moderator'));
        add_action('save_post', array($this, 'save_assigned_collaborator'));
        add_action('save_post', array($this, 'on_save_submission_custom_fields'));

        add_action( 'show_user_profile', array($this, 'assessments_additional_profile_fields'));
        add_action( 'edit_user_profile', array($this, 'assessments_additional_profile_fields'));

        // moderator user list
    }

    function init_meta_boxes_admin(): void
    {
        // Assessments
        add_meta_box('questions-repeater-field', 'Questions', array($this, 'question_repeatable_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('assessment-options-field', 'Assessment Options', array($this, 'assessment_options_meta_box_callback'), 'assessments', 'side', 'default');
        add_meta_box('access-control-panel', 'Access Control Panel', array($this, 'access_control_panel_meta_box_callback'), 'assessments', 'side', 'default');
        if (current_user_can('administrator')) {
            add_meta_box('moderator-list', 'Assessment Access', array($this, 'display_moderator_select_list'), array('assessments', 'submissions'), 'normal', 'default');
        }
        add_meta_box('report-key-areas-field', 'Add Key Areas', array($this, 'report_key_areas_meta_box_callback'), 'assessments', 'normal', 'default');

        // Submisions
        add_meta_box('questions-repeater-field', 'Submission detail', array($this, 'submission_list_card_section_admin'), 'submissions', 'normal', 'default');
        add_meta_box('submitted_info_view', 'Submission by: ', array($this, 'submission_info_section_admin'), 'submissions', 'side', 'default');

        // Reports
        add_meta_box('report-template', 'Report Template', array($this, 'report_template_meta_box_callback'), 'assessments', 'normal', 'default');
        // add_meta_box('link-report-to-assessment', 'Link Report to Assessment', array($this, 'link_report_to_assessment_callback'), 'reports', 'side', 'default');

        // Attachments
        add_meta_box('attachment_uploader_info_view', 'Uploaded by member', array($this, 'attachment_uploader_info_section_admin'), 'attachment', 'side', 'default');
    }

    function assessment_options_meta_box_callback()
    {
        return include_once ADMIN_ASSESSMENT_OPTION_VIEW;
    }

    function attachment_uploader_info_section_admin()
    {
        return include_once ADMIN_ATTACHMENT_UPLOADER_INFO_VIEW;
    }

    function report_template_meta_box_callback()
    {
        return include_once ADMIN_REPORT_SECTION_FIELDS;
    }

    function question_repeatable_meta_box_callback()
    {
        return include_once ADMIN_QUESTIONAIRE_FIELDS;
    }

    function report_key_areas_meta_box_callback()
    {
        return include_once ADMIN_REPORT_KEY_AREAS_FIELDS;
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

    function assessments_additional_profile_fields() 
    {
        return include_once USER_ASSESSMENTS_PERCHASED_FIELDS;
    }
    
    function access_control_panel_meta_box_callback() 
    {
        return include_once ADMIN_ACCESS_CONTROL_PANEL;
    }

    function link_report_to_assessment_callback()
    {
        return include_once LINK_REPORT_TO_ASSESSMENT;
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
        $is_assessment_completed = $_POST['is_assessment_completed'] ?? 0;
        $assigned_members = $_POST['assigned_members'] ?? null;
        $invited_members = $_POST['invited_members'] ?? null;

        // Renew Index of Questions array
        $new_group_questions = array();
        $item = 1;
        foreach ($group_questions as $value) {
            $new_group_questions[$item] = $value;
            $item++;
        }

        // Renew Index of Members array
        $new_assigned_members = array();
        $index = 1;
        foreach ($assigned_members as $value) {
            $new_assigned_members[$index] = $value;
            $index++;
        }

        update_post_meta($post_id, 'question_group_repeater', base64_encode(serialize($new_group_questions)));
        update_post_meta($post_id, 'question_templates', $assessment_template);
        update_post_meta($post_id, 'is_required_answer_all', $is_required_answer_all);
        update_post_meta($post_id, 'is_required_document_all', $is_required_document_all);
        update_post_meta($post_id, 'is_invite_colleagues', $is_invite_colleagues);
        update_post_meta($post_id, 'is_all_users_can_access', $is_all_users_can_access);
        update_post_meta($post_id, 'related_sf_products', $related_sf_products);
        update_post_meta($post_id, 'is_assessment_completed', $is_assessment_completed);
        update_post_meta($post_id, 'assigned_members', $new_assigned_members);
        update_post_meta($post_id, 'invited_members', $invited_members);

    }

    function report_template_meta_box_save($post_id): void
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id))
            return;

        $report_template = isset($_POST['report_template']) ? $_POST['report_template'] : '';
        $is_report_include_toc = isset($_POST['is_report_include_toc']) ? $_POST['is_report_include_toc'] : '';
        $report_key_areas = isset($_POST['report_key_areas']) ? $_POST['report_key_areas'] : '';

        // Renew Index of generic page Report template array
        $new_report_template = array();
        $index = 1;
        if (!empty($report_template)) {
            foreach ($report_template['generic_page'] as $generic_page) {
                $new_report_template[$index] = $generic_page;
                $index++;
            }
            $report_template['generic_page'] = $new_report_template;
        }

        update_post_meta($post_id, 'report_template', $report_template);
        update_post_meta($post_id, 'is_report_include_toc', $is_report_include_toc);
        update_post_meta($post_id, 'report_key_areas', $report_key_areas);
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
        $total_submission_score = $_POST['total_submission_score'] ?? null;
        $and_score = $_POST['and_score'] ?? null;
        $agreeed_score = $_POST['agreeed_score'] ?? null;
        $submission_key_area = $_POST['submission_key_area'] ?? null;
        $recommentdation = $_POST['recommentdation'] ?? null;

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
        update_post_meta($post_id, 'total_submission_score', $total_submission_score);
        update_post_meta($post_id, 'and_score', $and_score);
        update_post_meta($post_id, 'agreeed_score', $agreeed_score);
        update_post_meta($post_id, 'submission_key_area', $submission_key_area);
        update_post_meta($post_id, 'recommentdation', $recommentdation);
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

        if (!current_user_can('edit_post', $post_id))
            return;

        $assigned_collaborator = isset($_POST['assigned_collaborator']) ? $_POST['assigned_collaborator'] : '';

        update_post_meta($post_id, 'assigned_collaborator', $assigned_collaborator);
    }
}

new Custom_Fields();
