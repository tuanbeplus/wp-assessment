<?php

class WP_Assessment
{
    public $quiz_table_name;
    public $dcr_quiz_table_name;

    public function __construct()
    {
        $this->remove_custom_roles();
        $this->add_roles();
        $this->add_assessment_caps_to_admin();

        add_action('pre_get_posts', array($this, 'filter_assessment_list_admin'));
        add_filter('views_edit-assessments', array($this, 'update_assessment_list_filters_view'));
        add_filter('theme_page_templates', array($this, 'register_custom_template_for_quiz'));
        add_filter('single_template', array($this, 'quiz_redirect_page_template'));
        add_filter('upload_size_limit', array($this, 'override_file_size'));
        add_filter('wp_mail_content_type', array($this, 'set_email_content_type'));
        add_filter('tiny_mce_before_init', array($this, 'wpa_ptags_tinymce_fix')); 
        
        add_action('wp_ajax_create_comprehensive_report', array($this, 'create_comprehensive_report'));
        add_action('wp_ajax_nopriv_create_comprehensive_report', array($this, 'create_comprehensive_report'));

        add_action('wp_ajax_get_quizs_status_submission', array($this, 'get_quizs_status_submission'));
        add_action('wp_ajax_nopriv_get_quizs_status_submission', array($this, 'get_quizs_status_submission'));

        // Index table
        $this->set_quiz_table();
        $this->init_quiz_tables_for_users();

        // DCR table
        $this->set_dcr_quiz_table();
        $this->init_dcr_quiz_submissions_table();
    }

    function set_email_content_type()
    {
        return "text/html";
    }

    function add_roles(): void
    {
        add_role('moderator', 'Moderator', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
        ));

        add_role('student', 'Student', array(
            'read' => true,
        ));
    }

    function remove_custom_roles(): void
    {
        remove_role('student');
        remove_role('moderator');
    }

    function filter_assessment_list_admin($query): void
    {

        if (current_user_can('administrator') || is_single()) return;

        $cpt_key = "assigned_moderator";
        $cpt_value = get_current_user_id();

        global $current_page;
        $type = '';// 'assessments';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        if ('assessments' == $type) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => $cpt_key,
                    'value' => $cpt_value,
                    'compare' => 'IN',
                ),
            );

            $query->set('meta_query', $meta_query);
        }
    }

    function add_assessment_caps_to_admin(): void
    {
        $admin_role = get_role('administrator');
        $moderator_role = get_role('moderator');
        $student_role = get_role('student');

        $admin_role->add_cap('read_assessment');
        $admin_role->add_cap('publish_assessments');
        $admin_role->add_cap('edit_assessments');
        $admin_role->add_cap('edit_others_assessments');
        $admin_role->add_cap('delete_assessments');
        $admin_role->add_cap('delete_others_assessments');
        $admin_role->add_cap('read_private_assessments');
        $admin_role->add_cap('edit_assessment');
        $admin_role->add_cap('delete_assessment');
        $admin_role->add_cap('edit_published_assessment');
        $admin_role->add_cap('edit_published_assessments');

        // capabilities submission for admin
        $admin_role->add_cap('read_submission');
        $admin_role->add_cap('publish_submissions');
        $admin_role->add_cap('edit_submissions');
        $admin_role->add_cap('edit_others_submissions');
        $admin_role->add_cap('delete_submissions');
        $admin_role->add_cap('delete_others_submissions');
        $admin_role->add_cap('read_private_submissions');
        $admin_role->add_cap('edit_submission');
        $admin_role->add_cap('delete_submission');
        $admin_role->add_cap('edit_published_submission');
        $admin_role->add_cap('edit_published_submissions');

        // capabilities assessment for moderator
        $moderator_role->add_cap('read_assessment');
        $moderator_role->add_cap('publish_assessments');
        $moderator_role->add_cap('edit_assessments');
        $moderator_role->add_cap('edit_others_assessments');
        $moderator_role->add_cap('delete_assessments');
        $moderator_role->add_cap('delete_others_assessments');
        $moderator_role->add_cap('read_private_assessments');
        $moderator_role->add_cap('edit_assessment');
        $moderator_role->add_cap('delete_assessment');
        $moderator_role->add_cap('edit_published_assessment');
        $moderator_role->add_cap('edit_published_assessments');

        // capabilities submission for moderator
        $moderator_role->add_cap('read_submission');
        $moderator_role->add_cap('publish_submissions');
        $moderator_role->add_cap('edit_submissions');
        $moderator_role->add_cap('edit_others_submissions');
        $moderator_role->add_cap('delete_submissions');
        $moderator_role->add_cap('delete_others_submissions');
        $moderator_role->add_cap('read_private_submissions');
        $moderator_role->add_cap('edit_submission');
        $moderator_role->add_cap('delete_submission');
        $moderator_role->add_cap('edit_published_submission');
        $moderator_role->add_cap('edit_published_submissions');


        $student_role->add_cap('read_assessment');
        $student_role->add_cap('publish_assessments');
        $student_role->add_cap('edit_assessments');
        $student_role->add_cap('edit_others_assessments');
        $student_role->add_cap('delete_assessments');
        $student_role->add_cap('edit_assessment');
        $student_role->add_cap('delete_assessment');
        $student_role->add_cap('edit_published_assessment');
        $student_role->add_cap('edit_published_assessments');
    }

    function update_assessment_list_filters_view($views): array
    {
        if (current_user_can('manage_options'))
            return $views;

        $remove_views = ['all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash'];

        foreach ((array)$remove_views as $view) {
            if (isset($views[$view]))
                unset($views[$view]);
        }
        return $views;
    }

    function get_current_user_role()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = (array)$user->roles;
            return $roles[0];
        } else {
            return array();
        }
    }

    function register_custom_template_for_quiz($templates)
    {
        $templates['custom_quiz_template'] = 'Quiz Template';
        return $templates;
    }

    function quiz_redirect_page_template($template)
    {
        global $post;

        if ($post->post_type == 'assessments')
            return QUIZ_TEMPLATE_VIEW;

        if ($post->post_type == 'reports')
            return SINGLE_REPORTS_TEMPLATE;
        
        if ($post->post_type == 'submissions' || $post->post_type == 'dcr_submissions')
            return SINGLE_SUBMISSIONS_TEMPLATE;
        
        return $template;
    }

    function override_file_size($size)
    {
        $size = 1024 * 1024 * 200;
        return $size;
    }

    function set_quiz_table(): void
    {
        global $wpdb;
        $this->quiz_table_name = $wpdb->prefix . "user_quiz_submissions";
    }

    function get_quiz_table()
    {
        return $this->quiz_table_name;
    }

    /**
     * Create Index quiz table
     * 
     */
    function init_quiz_tables_for_users()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_quiz_table();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_id varchar(100) NOT NULL,
            organisation_id varchar(100) NOT NULL,
            quiz_id int(11) NOT NULL,
            parent_id int(11) DEFAULT 0,
            assessment_id int(11) NOT NULL,
            submission_id int(11) NOT NULL,
            attachment_ids JSON,
            attachment_id int(11) NOT NULL,
            submit_version int(11) DEFAULT 1,
            answers JSON,
            description LONGTEXT,
            status ENUM ('pending','completed','accepted','rejected') DEFAULT 'pending',
            feedback LONGTEXT,
            quiz_point int(11),
            PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function set_dcr_quiz_table(): void
    {
        global $wpdb;
        $this->dcr_quiz_table_name = $wpdb->prefix . "dcr_quiz_submissions";
    }

    function get_dcr_quiz_table()
    {
        return $this->dcr_quiz_table_name;
    }

    /**
     * Create DCR quiz table
     * 
     */
    function init_dcr_quiz_submissions_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_dcr_quiz_table();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_id varchar(100) NOT NULL,
            organisation_id varchar(100) NOT NULL,
            quiz_id int(11) NOT NULL,
            parent_id int(11) DEFAULT 0,
            assessment_id int(11) NOT NULL,
            submission_id int(11) NOT NULL,
            attachment_ids JSON,
            answers JSON,
            description LONGTEXT,
            status ENUM ('pending','completed','accepted','rejected') DEFAULT 'pending',
            feedback LONGTEXT,
            quiz_point int(11),
            PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get table name by Assessmnt first term
     * 
     * @param $assessment_id    Assessment ID
     * @return Table_Name
     * 
     */
    function get_quiz_submission_table_name($assessment_id) 
    {
        $table_name = '';
        // Get all terms array
        $assessment_terms = get_assessment_terms($assessment_id);

        if (!empty($assessment_terms) && isset($assessment_terms[0])) {
            if ($assessment_terms[0] == 'dcr') {
                // Is DCR assessment
                $table_name = $this->get_dcr_quiz_table();
            }
            else {
                // Is Index & other assessment
                $table_name = $this->get_quiz_table();
            }
        }
        else {
            // Is Index & other assessment
            $table_name = $this->get_quiz_table();
        }
        return $table_name;
    }

    function get_quiz_by_assessment_id($assessment_id, $quiz_id, $organisation_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND quiz_id = $quiz_id AND organisation_id = '$organisation_id' LIMIT 1";

            $result = $wpdb->get_results($sql);

            return $result[0] ?? null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quiz_by_assessment_id_and_submission($assessment_id, $submission_id, $quiz_id, $organisation_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND submission_id = $submission_id AND quiz_id = $quiz_id AND organisation_id = '$organisation_id' LIMIT 1";

            $result = $wpdb->get_results($sql);

            return $result[0] ?? null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quiz_by_assessment_id_and_parent($assessment_id, $quiz_id, $organisation_id, $parent_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND parent_id = $parent_id AND quiz_id = $quiz_id AND organisation_id = '$organisation_id' LIMIT 1";

            $result = $wpdb->get_results($sql);

            return $result[0] ?? null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quiz_by_assessment_id_and_submission_parent($assessment_id, $submission_id, $quiz_id, $organisation_id, $parent_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND submission_id = $submission_id AND parent_id = $parent_id AND quiz_id = $quiz_id AND organisation_id = '$organisation_id' LIMIT 1";

            $result = $wpdb->get_results($sql);

            return $result[0] ?? null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_user_quiz_by_assessment_id($assessment_id, $organisation_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND organisation_id = '$organisation_id'";
            $result = $wpdb->get_results($sql);

            return !empty($result) ? $result : null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_user_quiz_by_assessment_id_and_submissions($assessment_id, $submission_id, $organisation_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND submission_id = $submission_id AND organisation_id = '$organisation_id'";
            
            $result = $wpdb->get_results($sql);

            return !empty($result) ? $result : null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_dcr_quiz_answers_all_submissions($assessment_id, $organisation_id)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);

            $sql = "SELECT time, user_id, organisation_id, description, submission_id, parent_id, quiz_id
                    FROM $table 
                    WHERE assessment_id = $assessment_id 
                    AND organisation_id = '$organisation_id' 
                    ORDER BY time DESC";
            
            $result = $wpdb->get_results($sql);

            return !empty($result) ? $result : null;

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function insert_quiz_by_assessment_id($data)
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($data['assessment_id']);

            $data['time'] = current_time( 'mysql' );
            $wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' , '%s' , '%s', '%s', '%s' ) );

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

          return $wpdb->insert_id;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function update_quiz_assessment($data, $conditions)
    {
        try {
            global $wpdb;
            
            $table = $this->get_quiz_submission_table_name($conditions['assessment_id']);

            $data['time'] = current_time( 'mysql' );
            $wpdb->update($table, $data, $conditions, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' , '%s', '%s', '%s' ), array('%s', '%s', '%s', '%s') );

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function is_group_quiz_exist_in_object($group_id, $obj, $organisation_id, $args)
    {
        if (isset($_COOKIE['userId'])) {
            $user_id = $_COOKIE['userId'];
        } else {
            $user_id = get_current_user_id();
        }
        $data = null;

        if ($obj && is_array($obj)) {
            foreach ($obj as $item) {
                if ($item->organisation_id == $organisation_id && $item->parent_id == $group_id) {

                    if ($args['is_required_answer_all'] == true) {

                        if ($args['choice'] && empty($item->answers)) {
                            return false;
                        }
                        elseif ($args['is_description'] && empty($item->description)) {
                            return false;
                        }
                        elseif ($args['supporting_doc'] && empty($item->attachment_ids)) {
                            return false;
                        }
                        else {
                            $data = true;
                        }
                    }
                    else {
                        $data['answers'] = json_decode($item->answers);
                        $data['description'] = $item->description;
                        $data['attachment_ids'] = $item->attachment_ids;
                    }
                }
            }
        }

        return $data;
    }

    function is_quiz_exist_in_object($quiz_id, $obj, $organisation_id)
    {
        if (isset($_COOKIE['userId'])) {
            $user_id = $_COOKIE['userId'];
        } else {
            $user_id = get_current_user_id();
        }
        $data = null;

        if ($obj && is_array($obj)) {
            foreach ($obj as $item) {
                if ($item->organisation_id == $organisation_id && $item->quiz_id == $quiz_id) {
                    $data['answers'] = json_decode($item->answers);
                    $data['description'] = $item->description;
                    $data['attachment_id'] = $item->attachment_id;
                    $data['feedback'] = $item->feedback;
                    $data['status'] = $item->status;

                    break;
                }
            }
        }

        return $data;
    }

    function is_quiz_exist_in_object_sub($parent_id ,$sub, $obj, $organisation_id)
    {
        if (isset($_COOKIE['userId'])) {
            $user_id = $_COOKIE['userId'];
        } else {
            $user_id = get_current_user_id();
        }
        $data = null;

        if ($obj && is_array($obj)) {
            foreach ($obj as $item) {
                if ($item->organisation_id == $organisation_id && $item->parent_id == $parent_id && $sub == $item->quiz_id) {
                    $data['answers'] = json_decode($item->answers);
                    $data['description'] = $item->description;
                    $data['attachment_id'] = $item->attachment_id;
                    $data['attachment_ids'] = $item->attachment_ids;
                    $data['feedback'] = $item->feedback;
                    $data['status'] = $item->status;

                    break;
                }
            }
        }

        return $data;
    }

    function is_section_quiz_completed($parent_id, $obj, $organisation_id)
    {
        try {
            $is_section_completed = false;

            foreach ($obj as $item) {
                if ($item->organisation_id == $organisation_id && $item->parent_id == $parent_id) {
                    $is_answer = $item->answers;
                    $is_description = $item->description;

                    // if (condition) {
                    //     # code...
                    // }
                }
            }

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function is_check_save_progress_quiz($assessment_id, $organisation_id = null)
    {
        $submission_id = null;

        $args = array(
            'post_type' => 'submissions',
            'posts_per_page' => 1,
            'post_status' => 'draft',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                ),
            ),
        );
        $submission = get_posts($args);
        // Reset Post Data
        wp_reset_postdata();

        if (is_array($submission) && count($submission) > 0) {
            $submission_id = $submission[0]->ID;
            return $submission_id;
        }
        else {
            return '';
        }
    }

    function is_answer_exist($key, $answers): bool
    {
        $is_exist = false;
        if ($answers && is_array($answers)) {
            foreach ($answers as $answer) {
                if ($answer->id == $key) {
                    $is_exist = true;
                    break;
                }
            }
        }

        return $is_exist;
    }

    function is_answer_exist_title($title, $answers): bool
    {
        $is_exist = false;
        if ($answers && is_array($answers)) {
            foreach ($answers as $answer) {
                if ($answer->title == $title) {
                    $is_exist = true;
                    break;
                }
            }
        }

        return $is_exist;
    }

    function wp_insert_attachment_from_url($upload, $parent_post_id = null)
    {
        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);
        $wp_upload_dir = wp_upload_dir();

        $post_info = array(
            'guid' => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title' => $file_name,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment($post_info, $file_path, $parent_post_id);

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    function get_field($array, $index, $key)
    {
        if (!key_exists($index, $array) || !key_exists($key, $array[$index])) return;
        return $array[$index][$key];
    }

    function get_latest_submission_id($assessment_id, $organisation_id)
    {
        $submission_id = null;
        $assessment_terms = get_assessment_terms($assessment_id);

        if (is_array($assessment_terms) && isset($assessment_terms[0])) {
            if ($assessment_terms[0] == 'dcr') {
                $post_type = 'dcr_submissions';
            }
            else {
                $post_type = 'submissions';
            }
        }
        else {
            return null;
        }
        
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                ),
            ),
        );
        $submission = get_posts($args);
        // Reset Post Data
        wp_reset_postdata();

        if (is_array($submission) && count($submission) > 0) {
            $submission_id = $submission[0]->ID;
            return $submission_id;
        }
    }

    function wpa_ptags_tinymce_fix($init)
    {
        //wpautop = yes
        $init['wpautop'] = false;

        // don't remove line breaks
        $init['remove_linebreaks'] = false;

        // convert newline characters to BR
        $init['convert_newlines_to_brs'] = true;

        // don't remove redundant BR
        $init['remove_redundant_brs'] = false;

        // pass back to wordpress
        return $init;
    }

    function wpa_unserialize_metadata($post_metadata)
    {
        $post_metadata = base64_decode($post_metadata);

        $post_metadata = preg_replace_callback('!s:\d+:"(.*?)";!s', 
            function($m) {
                return "s:" . strlen($m[1]) . ':"'.$m[1].'";'; 
            }, $post_metadata
        );

        $post_metadata_unserialize = unserialize($post_metadata);

        return $post_metadata_unserialize;
    }

    function create_comprehensive_report()
    {
        try {
            $post_id = intval($_POST['submission_id']);
            $is_report_exist = is_report_of_submission_exist($post_id);
            
            if (!$is_report_exist) {
                $assessment_id = get_post_meta($post_id, 'assessment_id', true);
                $user_id = get_post_meta($post_id, 'user_id', true);
                $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
                $org_data = get_post_meta($post_id, 'org_data', true) ?? null;
                $assessment_title = get_the_title($assessment_id);

                $report_id = wp_insert_post(array(
                    'post_type' => 'reports',
                    'post_title' => 'Report on ' .$assessment_title. ' - ' .$org_data['Name'],
                    'post_status' => 'publish',
                ));

                if (isset($report_id)) {
                    update_post_meta($report_id, 'user_id', $user_id);
                    update_post_meta($report_id, 'sf_user_name', $sf_user_name);
                    update_post_meta($report_id, 'assessment_id', $assessment_id);
                    update_post_meta($report_id, 'submission_id', $post_id);
                    update_post_meta($report_id, 'org_data', $org_data);
                    update_post_meta($post_id, 'report_id', $report_id);

                    return wp_send_json(array('report_id' => $report_id, 'status' => true));
                }
                else {
                    throw new Exception('The created report failed!');
                }
            }
            else {
                throw new Exception('The report on this submission already exists.');
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function wpa_get_report_content($post_id)
    {
        global $sub_id;
        $sub_id = $post_id;
        return include_once ADMIN_REPORT_CONTENT_FIELDS;
    }

    function wpa_get_attachments_uploaded($assessment_id, $organisation_id)
    {
        $attachments_id_arr = array();

        $args = array(
			'post_type' 	=> 'attachment',
			'post_status' 	 => 'any',
			'posts_per_page' => -1,
			'orderby' 	 	=> 'date',
			'order' 		=> 'ASC',
            // 'meta_key' => 'sf_user_id',
            // 'meta_value' => $sf_user_id,
            'meta_query' => array(
                    array(
                        'key' => 'assessment_id',
                        'value' => $assessment_id,
                    ),
                    array(
                        'key' => 'organisation_id',
                        'value' => $organisation_id,
                    ),
                ),
		);

        $the_query = new WP_Query($args);

        if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$attachments_id_arr[] = get_the_ID();
			}
			wp_reset_postdata();
		}
        return $attachments_id_arr;
    }

    function get_quiz_accepted($assessment_id, $submission_id, $organisation_id) 
    {
        try {
            global $wpdb;

            $table = $this->get_quiz_submission_table_name($assessment_id);
            // 
            $sql = "SELECT id FROM $table WHERE assessment_id = '$assessment_id' AND submission_id = '$submission_id' AND organisation_id = '$organisation_id' ";
            $result = $wpdb->get_results($sql);
            $result = json_encode($result);
            $quiz_arr = json_decode($result, true);
            $count_quiz = count($quiz_arr);
            // 
            $sql_quiz_accept = "SELECT id FROM $table WHERE assessment_id = '$assessment_id' AND submission_id = '$submission_id' AND organisation_id = '$organisation_id' AND status = 'accepted' ";
            $result = $wpdb->get_results($sql_quiz_accept);
            $result = json_encode($result);
            $quiz_accepted_arr = json_decode($result, true);
            $count_quiz_accepted = count($quiz_accepted_arr);
            // 
            if ($count_quiz == $count_quiz_accepted) {
                return true;
            }
            else {
                return false;
            }

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quizs_status_submission()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            $submission_id = intval($_POST['submission_id']);
            $organisation_id = $_POST['organisation_id'];
            $sf_user_id = get_post_meta($submission_id, 'sf_user_id', true);

            if (!empty($sf_user_id)) {
                $user_id = $sf_user_id;
            }
            else {
                $user_id = $_POST['user_id'];
            }
            // 
            $get_quiz_accepted = $this->get_quiz_accepted($assessment_id, $submission_id, $organisation_id);
            // 
            if ($get_quiz_accepted == true) {
                return wp_send_json(true);
            }
            else {
                return wp_send_json(false);
            }

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
            die;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_field_organisation_id($user_id) {

        $account_id = getAccountMember($user_id)['Id'];
        $member_id = is_member_exist($user_id);
        $is_account_id_exist = get_post_meta( $member_id, 'account_id', true);

        if ($is_account_id_exist) {
            return $is_account_id_exist;
        }
        else {
            update_post_meta($member_id, 'account_id', $account_id);
            return $account_id;
        }        
    }

    function get_self_assessed_score($assessment_id, $submission_data_arr)
    {
        $assessment_term_arr = get_assessment_terms($assessment_id);

        if (in_array('self-assessed', $assessment_term_arr)) {

            $self_assessed_score = 0;
            
            foreach ($submission_data_arr as $quiz) {
                $answer = json_decode($quiz['answers'], true);
                $answer_title = strtolower($answer[0]['title']);

                if ($answer_title == 'yes') {
                    $self_assessed_score = $self_assessed_score + 10;
                }
            }

            return $self_assessed_score;
        }
    }

    // echo $wpdb->last_query;

    // Print last SQL query result
    // echo $wpdb->last_result;

    // Print last SQL query Error
    // echo $wpdb->last_error;
}

new WP_Assessment();
