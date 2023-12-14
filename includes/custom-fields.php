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
            add_meta_box('moderator-list', 'Assessment Access', array($this, 'display_moderator_select_list'), array('assessments', 'submissions', 'dcr_submissions'), 'normal', 'default');
        }
        add_meta_box('report-key-areas-field', 'Add Key Areas', array($this, 'report_key_areas_meta_box_callback'), 'assessments', 'normal', 'default');

        // Submisions
        add_meta_box('questions-repeater-field', 'Submission detail', array($this, 'index_submission_list_card_section_admin'), array('submissions'), 'normal', 'default');
        add_meta_box('questions-repeater-field', 'Submission detail', array($this, 'dcr_submission_list_card_section_admin'), array('dcr_submissions'), 'normal', 'default');
        add_meta_box('submitted_info_view', 'Submission by ', array($this, 'submission_info_section_admin'), array('submissions', 'dcr_submissions'), 'side', 'default');
        add_meta_box('submission-scoring-field', 'Submission Scoring ', array($this, 'submission_scoring_section_admin'), 'submissions', 'normal', 'default');

        // Reports
        add_meta_box('report-template', 'Report Template', array($this, 'report_template_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('report-dashboard-charts', 'Dashboard Charts', array($this, 'report_dashboard_chart_meta_box_callback'), 'reports', 'normal', 'default');
        // add_meta_box('link-report-to-assessment', 'Link Report to Assessment', array($this, 'link_report_to_assessment_callback'), 'reports', 'side', 'default');
        add_meta_box('report-dashboard-share', 'Share this Report to Users', array($this, 'report_dashboard_share_meta_box_callback'), 'reports', 'side', 'default');

        // Attachments
        add_meta_box('attachment_uploader_info_view', 'Uploaded by member', array($this, 'attachment_uploader_info_section_admin'), 'attachment', 'side', 'default');
    }

    function report_dashboard_share_meta_box_callback(){
      return include_once REPORT_DASHBOARD_SHARE_REPORTS;
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

    function index_submission_list_card_section_admin()
    {
        return include_once ADMIN_SUBMISSION_INDEX_VIEW;
    }

    function dcr_submission_list_card_section_admin()
    {
        return include_once ADMIN_SUBMISSION_DCR_VIEW;
    }

    function submission_info_section_admin()
    {
        return include_once ADMIN_SUBMISSION_INFO_VIEW;
    }

    function submission_scoring_section_admin() {
        return include_once ADMIN_SUBMISSION_SCORING_VIEW;
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

    function report_dashboard_chart_meta_box_callback()
    {
        return include_once REPORT_DASHBOARD_CHART_VIEW;
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
        $report_key_areas = isset($_POST['report_key_areas']) ? $_POST['report_key_areas'] : '';
        $dashboard_chart_imgs = get_post_meta($post_id, 'dashboard_chart_imgs', true);
        $framework_dashboard      = get_field('framework_dashboard',$post_id);
        $implementation_dashboard = get_field('implementation_dashboard',$post_id);
        $review_dashboard         = get_field('review_dashboard',$post_id);
        $overall_dashboard        = get_field('overall_dashboard',$post_id);

        // Renew chart images meta
        if (!empty($dashboard_chart_imgs)) {
            if (isset($dashboard_chart_imgs['Framework']) && empty($framework_dashboard)) {
                wp_delete_attachment( $dashboard_chart_imgs['Framework'], true );
                $dashboard_chart_imgs['Framework'] = null;
            }
            if (isset($dashboard_chart_imgs['Implementation']) && empty($implementation_dashboard)) {
                wp_delete_attachment( $dashboard_chart_imgs['Implementation'], true );
                $dashboard_chart_imgs['Implementation'] = null;
            }
            if (isset($dashboard_chart_imgs['Review']) && empty($review_dashboard)) {
                wp_delete_attachment( $dashboard_chart_imgs['Review'], true );
                $dashboard_chart_imgs['Review'] = null;
            }
            if (isset($dashboard_chart_imgs['Overall']) && empty($overall_dashboard)) {
                wp_delete_attachment( $dashboard_chart_imgs['Overall'], true );
                $dashboard_chart_imgs['Overall'] = null;
            }
        }

        // Renew Index of generic page Report template array
        if (!empty($report_template)) {
            $new_page_before = array();
            $i = 1;
            foreach ($report_template['generic_page_before'] as $page_before) {
                $new_page_before[$i] = $page_before;
                $i++;
            }
            $new_page_after = array();
            $j = 1;
            foreach ($report_template['generic_page_after'] as $page_after) {
                $new_page_after[$j] = $page_after;
                $j++;
            }
            $report_template['generic_page_before'] = $new_page_before ?? null;
            $report_template['generic_page_after'] = $new_page_after ?? null;
        }

        update_post_meta($post_id, 'report_template', $report_template);
        update_post_meta($post_id, 'report_key_areas', $report_key_areas);
        update_post_meta($post_id, 'dashboard_chart_imgs', $dashboard_chart_imgs);
    }

    function on_save_submission_custom_fields($post_id): void
    {
        $post_type = get_post_type($post_id);
        if ($post_type != 'submissions') return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;

        $question_form = new Question_Form();
        $group_quiz_points = $_POST['group_quiz_point'] ?? null;
        $quiz_feedbacks = $_POST['quiz_feedback'] ?? null;
        $quiz_answer_points = $_POST['quiz_answer_point'] ?? null;
        $total_submission_score = $_POST['total_submission_score'] ?? null;
        $org_score = $_POST['org_score'] ?? null;
        $and_score = $_POST['and_score'] ?? null;
        $agreed_score = $_POST['agreed_score'] ?? null;
        $org_section_score = $_POST['org_section_score'] ?? null;
        $recommentdation = $_POST['recommentdation'] ?? null;
        $key_area = $_POST['key_area'] ?? null;

        $maturity_level = array();
        foreach ($key_area as $pr_key => $ka) {
            $framework_cnt = $implementation_cnt = $review_cnt = $innovation_cnt = 0;
            $framework_vl = $implementation_vl = $review_vl = $innovation_vl = 0;
            foreach ($ka as $c_key => $value) {
                switch ($value) {
                    case 'Framework':
                        $framework_cnt++;
                        $framework_vl += $org_score[$pr_key][$c_key];
                        break;
                    case 'Implementation':
                        $implementation_cnt++;
                        $implementation_vl += $org_score[$pr_key][$c_key];
                        break;
                    case 'Review':
                        $review_cnt++;
                        $review_vl += $org_score[$pr_key][$c_key];
                        break;
                    case 'Innovation':
                        $innovation_cnt++;
                        $innovation_vl += $org_score[$pr_key][$c_key];
                        break;
                    default:
                        break;
                }
            }
            $framework_level = ($framework_cnt > 0) ? get_maturity_level_org($framework_vl/$framework_cnt) : 0;
            $implementation_level = ($implementation_cnt > 0) ? get_maturity_level_org($implementation_vl/$implementation_cnt) : 0;
            $review_level = ($review_cnt > 0) ? get_maturity_level_org($review_vl/$review_cnt) : 0;
            $innovation_level = ($innovation_cnt > 0) ? get_maturity_level_org($innovation_vl/$innovation_cnt) : 0;
            $maturity_level[$pr_key] = array(
                'Framework' => $framework_level,
                'Implementation' => $implementation_level,
                'Review' => $review_level,
                'Innovation' => $innovation_level
            );
        }

        // echo '<pre>';
        // print_r();
        // echo '</pre>';

        $new_group_quiz_points = array();
        $item = 1;
        foreach ($group_quiz_points as $value) {
          $new_group_quiz_points[$item] = $value;
          $item++;
        }
        $new_group_quiz_points = serialize($new_group_quiz_points);

        // Save total AND Score
        $total_and_score = array();
        $total_and_score['sum'] = array_sum_submission_score($and_score);
        $total_and_score['percent'] = round((array_sum_submission_score($and_score))/268.8*100);

        // Save total Agreed Score
        $total_agreed_score = array();
        $total_agreed_score['sum'] = array_sum_submission_score($agreed_score);
        $total_agreed_score['percent'] = round((array_sum_submission_score($agreed_score))/268.8*100);

        update_post_meta($post_id, 'group_quiz_point', $new_group_quiz_points);
        update_post_meta($post_id, 'quiz_feedback', $quiz_feedbacks);
        update_post_meta($post_id, 'quiz_answer_point', $quiz_answer_points);
        update_post_meta($post_id, 'total_submission_score', $total_submission_score);
        update_post_meta($post_id, 'org_score', $org_score);
        update_post_meta($post_id, 'and_score', $and_score);
        update_post_meta($post_id, 'agreed_score', $agreed_score);
        update_post_meta($post_id, 'org_section_score', $org_section_score);
        update_post_meta($post_id, 'recommentdation', $recommentdation);
        update_post_meta($post_id, 'total_and_score', $total_and_score);
        update_post_meta($post_id, 'total_agreed_score', $total_agreed_score);
        update_post_meta($post_id, 'key_area', $key_area);
        update_post_meta($post_id, 'maturity_level', $maturity_level);
        $question_form->save_all_submission_feedback();
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
