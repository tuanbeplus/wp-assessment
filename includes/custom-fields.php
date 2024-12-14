<?php

class WPA_Custom_Fields
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'init_meta_boxes_admin'));
        add_action('save_post', array($this, 'assessment_meta_boxs_save'));
        add_action('save_post', array($this, 'report_template_meta_box_save'));
        add_action('save_post', array($this, 'save_assigned_moderator'));
        add_action('save_post', array($this, 'save_assigned_collaborator'));
        add_action('save_post', array($this, 'on_save_submission_custom_fields'));
        add_action('show_user_profile', array($this, 'assessments_additional_profile_fields'));
        add_action('edit_user_profile', array($this, 'assessments_additional_profile_fields'));
        // Hook into the 'acf/init' action to add the ACF options page
        add_action('acf/init', array($this, 'add_assessments_options_page'));
        add_action('acf/init', array($this, 'add_assessments_options_fields'));
    }

    function init_meta_boxes_admin(): void
    {
        // Assessments
        add_meta_box('questions-repeater-field', 'Questions', array($this, 'question_repeatable_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('assessment-options-field', 'Assessment Options', array($this, 'assessment_options_meta_box_callback'), 'assessments', 'side', 'default');
        if (current_user_can('administrator')) {
            add_meta_box('moderator-list', 'Assessment Access', array($this, 'display_moderator_select_list'), array('assessments', 'submissions', 'dcr_submissions'), 'normal', 'default');
        }
        add_meta_box('report-key-areas-field', 'Add Key Areas', array($this, 'report_key_areas_meta_box_callback'), 'assessments', 'normal', 'default');
        add_meta_box('scoring-formula-options', 'Scoring formula options', array($this, 'scoring_formula_options_meta_box_callback'), 'assessments', 'side', 'default');

        // Submisions
        add_meta_box('questions-repeater-field', 'Submission Details', array($this, 'index_submission_details_admin'), array('submissions'), 'normal', 'default');
        add_meta_box('questions-repeater-field', 'Submission Details', array($this, 'dcr_submission_details_admin'), array('dcr_submissions'), 'normal', 'default');
        add_meta_box('submitted_info_view', 'Submission by ', array($this, 'submission_info_section_admin'), array('submissions', 'dcr_submissions'), 'side', 'default');
        add_meta_box('submission-scoring-field', 'Submission Scoring ', array($this, 'submission_scoring_section_admin'), 'submissions', 'normal', 'default');
        add_meta_box('saturn-invite', 'Salesforce Saturn Invite', array($this, 'saturn_invite_meta_box_callback'), array('assessments', 'submissions', 'dcr_submissions'), 'normal', 'default');
        add_meta_box('draft-preliminary-report', 'Draft Preliminary Report', array($this, 'submission_dcr_report_section_admin'), 'dcr_submissions', 'normal', 'default');

        // Reports
        add_meta_box('report-template', 'Report Template', array($this, 'report_template_meta_box_callback'), array('assessments', 'reports', 'dcr_reports'), 'normal', 'default');
        add_meta_box('report-dashboard-charts', 'Dashboard Charts', array($this, 'report_dashboard_chart_meta_box_callback'), 'reports', 'normal', 'default');
        add_meta_box('report-dashboard-share', 'Share this Report to Users', array($this, 'report_dashboard_share_meta_box_callback'), 'reports', 'side', 'default');
    }

    /* ---- Assessment Callbacks ---- */

    function question_repeatable_meta_box_callback()
    {
        return wpa_get_template_admin_view('assessments', 'questionaire');
    }

    function assessment_options_meta_box_callback()
    {
        return wpa_get_template_admin_view('assessments', 'assessment-options-view');
    }

    function scoring_formula_options_meta_box_callback()
    {
        return wpa_get_template_admin_view('assessments', 'scoring-formula-options');
    }

    function display_moderator_select_list()
    {
        return wpa_get_template_admin_view('assessments', 'moderator-list');
    }

    function report_key_areas_meta_box_callback()
    {
        return wpa_get_template_admin_view('assessments', 'report-key-areas');
    }
    /* ------------------------------- */

    /* ---- Submission Callbacks ---- */

    function dcr_submission_details_admin()
    {
        return wpa_get_template_admin_view('submissions', 'submission-dcr-view');
    }

    function index_submission_details_admin()
    {
        return wpa_get_template_admin_view('submissions', 'submission-index-view');
    }

    function submission_info_section_admin()
    {
        return wpa_get_template_admin_view('submissions', 'submission-info-view');
    }

    function submission_scoring_section_admin() 
    {
        return wpa_get_template_admin_view('submissions', 'submission-scoring');
    }

    function submission_dcr_report_section_admin() 
    {
        return wpa_get_template_admin_view('submissions', 'submission-dcr-report');
    }

    function saturn_invite_meta_box_callback()
    {
        return wpa_get_template_admin_view('submissions', 'saturn-invite');
    }
    /* ------------------------------ */

    /* ---- Report Callbacks ---- */

    function report_template_meta_box_callback()
    {
        return wpa_get_template_admin_view('reports', 'report-section');
    }

    function report_dashboard_chart_meta_box_callback()
    {
        return wpa_get_template_admin_view('reports', 'report-dashboard-charts');
    }

    function report_dashboard_share_meta_box_callback()
    {
        return wpa_get_template_admin_view('reports', 'share-report');
    }
    /* ------------------------------ */

    /* ---- User Callbacks ---- */

    function assessments_additional_profile_fields()
    {
        return wpa_get_template_admin_view('users', 'assessments-purchased-view');
    }
    /* ------------------------------ */


    function assessment_meta_boxs_save($post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id))
            return;

        if (get_post_type($post_id) != 'assessments') 
            return;

        $assessment_template = $_POST['assessment_template'] ?? null;
        $group_questions = $_POST['group_questions'] ?? array();
        $is_required_answer_all = $_POST['is_required_answer_all'] ?? 0;
        $is_required_document_all = $_POST['is_required_document_all'] ?? 0;
        $is_invite_colleagues = $_POST['is_invite_colleagues'] ?? 0;
        $is_all_users_can_access = $_POST['is_all_users_can_access'] ?? 0;
        $related_sf_products = $_POST['related_sf_products'] ?? array();
        $is_assessment_completed = $_POST['is_assessment_completed'] ?? 0;
        $assigned_members = $_POST['assigned_members'] ?? null;
        $invited_members = $_POST['invited_members'] ?? null;
        $report_key_areas = isset($_POST['report_key_areas']) ? $_POST['report_key_areas'] : '';
        $blacklist_emails = isset($_POST['blacklist_emails']) ? $_POST['blacklist_emails'] : '';
        $scoring_formula = isset($_POST['scoring_formula']) ? $_POST['scoring_formula'] : '';

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
        update_post_meta($post_id, 'report_key_areas', $report_key_areas);
        update_post_meta($post_id, 'blacklist_emails', $blacklist_emails);
        update_post_meta($post_id, 'scoring_formula', sanitize_text_field($scoring_formula));

        // Save Salesforce Saturn Invite metadata
        save_saturn_invite_meta_assessment($post_id, $related_sf_products);
    }

    function report_template_meta_box_save($post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id))
            return;

        $post_types_allow = array('assessments', 'reports', 'dcr_reports');
        
        if ( in_array(get_post_type($post_id), $post_types_allow) ) {
            
            $report_template = isset($_POST['report_template']) ? $_POST['report_template'] : '';
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
            update_post_meta($post_id, 'dashboard_chart_imgs', $dashboard_chart_imgs);
        }
    }

    function on_save_submission_custom_fields($post_id): void
    {
        $post_type = get_post_type($post_id);
        if ($post_type != 'submissions') return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (is_single() || is_page()) return;

        $question_form = new WPA_Question_Form();
        $assessment_id = get_post_meta($post_id, 'assessment_id', true);
        $group_quiz_points = $_POST['group_quiz_point'] ?? null;
        $quiz_answer_points = $_POST['quiz_answer_point'] ?? null;
        $org_score = $_POST['org_score'] ?? array();
        $and_score = $_POST['and_score'] ?? array();
        $agreed_score = $_POST['agreed_score'] ?? array();
        $org_section_score = $_POST['org_section_score'] ?? null;
        $recommentdation = $_POST['recommentdation'] ?? null;
        $key_area = $_POST['key_area'] ?? null;

        $maturity_level = array();
        $agreed_score_with_weighting = cal_scores_with_weighting($assessment_id, $agreed_score, 'sub') ?? array();
        foreach ($key_area as $pr_key => $ka) {
            $framework_cnt = $implementation_cnt = $review_cnt = $innovation_cnt = 0;
            $framework_vl = $implementation_vl = $review_vl = $innovation_vl = 0;
            foreach ($ka as $c_key => $value) {
                switch ($value) {
                    case 'Framework':
                        $framework_cnt++;
                        $framework_vl += (float)$agreed_score_with_weighting[$pr_key][$c_key];
                        break;
                    case 'Implementation':
                        $implementation_cnt++;
                        $implementation_vl += (float)$agreed_score_with_weighting[$pr_key][$c_key];
                        break;
                    case 'Review':
                        $review_cnt++;
                        $review_vl += (float)$agreed_score_with_weighting[$pr_key][$c_key];
                        break;
                    case 'Innovation':
                        $innovation_cnt++;
                        $innovation_vl += (float)$agreed_score_with_weighting[$pr_key][$c_key];
                        break;
                    default:
                        break;
                }
            }
            $framework_level = ($framework_cnt > 0) ? get_maturity_level_org($framework_vl/$framework_cnt) : 1;
            $implementation_level = ($implementation_cnt > 0) ? get_maturity_level_org($implementation_vl/$implementation_cnt) : 1;
            $review_level = ($review_cnt > 0) ? get_maturity_level_org($review_vl/$review_cnt) : 1;
            $innovation_level = ($innovation_cnt > 0) ? get_maturity_level_org($innovation_vl/$innovation_cnt) : 1;
            $maturity_level[$pr_key] = array(
                'Framework' => $framework_level,
                'Implementation' => $implementation_level,
                'Review' => $review_level,
                'Innovation' => $innovation_level
            );
        }

        $new_group_quiz_points = array();
        $item = 1;
        foreach ($group_quiz_points as $value) {
          $new_group_quiz_points[$item] = $value;
          $item++;
        }
        $new_group_quiz_points = serialize($new_group_quiz_points);

        // Get max score of the assessmnet
        $assessment_max_score = get_assessment_max_score($assessment_id);

        // Save total Org Self-Assessed Score
        $total_org_score = array();
        $total_org_score['sum'] = array_sum_submission_score($assessment_id, $org_score);
        $total_org_score['sum_with_weighting'] = array_sum_submission_score_with_weighting($assessment_id, $org_score);
        $total_org_score['percent'] = round((array_sum_submission_score_with_weighting($assessment_id, $org_score))/$assessment_max_score * 100);

        // Save total AND Score
        $total_and_score = array();
        $total_and_score['sum'] = array_sum_submission_score($assessment_id, $and_score);
        $total_and_score['sum_with_weighting'] = array_sum_submission_score_with_weighting($assessment_id, $and_score);
        $total_and_score['percent'] = round((array_sum_submission_score_with_weighting($assessment_id, $and_score))/$assessment_max_score * 100);

        // Save total Agreed Score
        $total_agreed_score = array();
        $total_agreed_score['sum'] = array_sum_submission_score($assessment_id, $agreed_score);
        $total_agreed_score['sum_with_weighting'] = array_sum_submission_score_with_weighting($assessment_id, $agreed_score);
        $total_agreed_score['percent'] = round((array_sum_submission_score_with_weighting($assessment_id, $agreed_score))/$assessment_max_score * 100);

        update_post_meta($post_id, 'group_quiz_point', $new_group_quiz_points);
        update_post_meta($post_id, 'quiz_answer_point', $quiz_answer_points);
        update_post_meta($post_id, 'org_score', $org_score);
        update_post_meta($post_id, 'and_score', $and_score);
        update_post_meta($post_id, 'agreed_score', $agreed_score);
        update_post_meta($post_id, 'org_section_score', $org_section_score);
        update_post_meta($post_id, 'recommentdation', $recommentdation);
        update_post_meta($post_id, 'total_submission_score', $total_org_score);
        update_post_meta($post_id, 'total_and_score', $total_and_score);
        update_post_meta($post_id, 'total_agreed_score', $total_agreed_score);
        update_post_meta($post_id, 'key_area', $key_area);
        update_post_meta($post_id, 'maturity_level', $maturity_level);

        if ( $post_type != 'dcr_submissions') {
            $question_form->save_all_submission_feedback();
        }
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

    /**
     * Add Assessments Settings ACF sub page
     */
    function add_assessments_options_page() {
        if( function_exists('acf_add_options_sub_page') ) {
            acf_add_options_sub_page(array(
                'page_title'    => 'Assessments Settings',
                'menu_title'    => 'Settings',
                'parent_slug'   => 'edit.php?post_type=assessments',
                'capability'    => 'manage_options',
                'redirect'      => false
            ));
        }
    }

    /**
     * Add Assessments options ACF fields
     */
    function add_assessments_options_fields() {
        if( function_exists('acf_add_local_field_group') ) {
            acf_add_local_field_group(array(
                'key' => 'group_assessments_settings',
                'title' => 'Settings',
                'fields' => array(
                    array(
                        'key' => 'field_assessment_quick_10',
                        'label' => 'Assessment Quick 10',
                        'name' => 'assessment_quick_10',
                        'type' => 'post_object',
                        'instructions' => 'Select assessment is the Quick 10.',
                        'post_type' => array(
                            0 => 'assessments',
                        ),
                        'post_status' => 'publish',
                        'taxonomy' => '',
                        'return_format' => 'id',
                        'multiple' => 0,
                    ),
                    array(
                        'key' => 'field_quick_10_register_url',
                        'label' => 'Quick 10 Register URL',
                        'name' => 'quick_10_register_url',
                        'type' => 'text',
                        'instructions' => 'Enter the URL to users register for the Quick 10.',
                    ),
                    array(
                        'key' => 'field_assessment_index_2023',
                        'label' => 'Assessment Index 2023',
                        'name' => 'assessment_index_2023',
                        'type' => 'post_object',
                        'instructions' => 'Select assessment is the Index 2023.',
                        'post_type' => array(
                            0 => 'assessments',
                        ),
                        'post_status' => 'publish',
                        'taxonomy' => '',
                        'return_format' => 'id',
                        'multiple' => 0,
                    ),
                    array(
                        'key' => 'field_dcr_submission_notification_email',
                        'label' => 'DCR Submission Notification Email',
                        'name' => 'dcr_submission_notification_email',
                        'type' => 'email',
                        'instructions' => 'Enter the email address to receive notifications for the new submissions.',
                    ),
                    array(
                        'key' => 'field_repeater_exception_orgs_id',
                        'label' => 'Exception Orgs ID',
                        'name' => 'exception_orgs_id',
                        'type' => 'repeater',
                        'instructions' => 'Add Organisation ID here.',
                        'layout' => 'table',
                        'button_label' => 'Add Org ID',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_organisation_name',
                                'label' => 'Organisation Name',
                                'name' => 'organisation_name',
                                'type' => 'text',
                                'wrapper' => array(
                                    'width' => '50%',
                                ),
                            ),
                            array(
                                'key' => 'field_organisation_id',
                                'label' => 'Organisation ID',
                                'name' => 'organisation_id',
                                'type' => 'text',
                                'wrapper' => array(
                                    'width' => '50%',
                                ),
                            ),
                        ),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'acf-options-settings',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'left',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
            ));
        }
    }

}

new WPA_Custom_Fields();
