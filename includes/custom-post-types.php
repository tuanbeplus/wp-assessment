<?php

class WPA_CustomPostType
{
    function __construct()
    {
        add_action('init', array($this, 'register_assessment_custom_post_type'));
        add_action('init', array($this, 'register_index_submissions_post_type'));
        add_action('init', array($this, 'register_dcr_submissions_post_type'));
        add_action('init', array($this, 'register_index_reports_custom_post_type'));
        add_action('init', array($this, 'register_dcr_reports_custom_post_type'));
        add_action('init', array($this, 'register_assessment_categories'));

        add_filter('manage_assessments_posts_columns', array($this, 'customize_assessments_admin_column'));
        add_action('manage_assessments_posts_custom_column', array($this, 'customize_assessments_admin_column_value'), 10, 2);

        add_filter('manage_submissions_posts_columns', array($this, 'customize_submissions_admin_column'));
        add_action('manage_submissions_posts_custom_column', array($this, 'customize_submissions_admin_column_value'), 10, 2);

        add_filter('manage_dcr_submissions_posts_columns', array($this, 'customize_submissions_admin_column'));
        add_action('manage_dcr_submissions_posts_custom_column', array($this, 'customize_submissions_admin_column_value'), 10, 2);

        add_filter('manage_reports_posts_columns', array($this, 'customize_reports_admin_column'));
        add_action('manage_reports_posts_custom_column', array($this, 'customize_reports_admin_column_value'), 10, 2);

        add_filter('manage_dcr_reports_posts_columns', array($this, 'customize_reports_admin_column'));
        add_action('manage_dcr_reports_posts_custom_column', array($this, 'customize_reports_admin_column_value'), 10, 2);

        add_filter('manage_attachment_posts_columns', array($this, 'customize_attachment_admin_column'));
        add_action('manage_attachment_posts_custom_column', array($this, 'customize_attachment_admin_column_value'), 10, 2);

        add_action('comment_post', array($this, 'submission_comments_post_hook'), 10, 2);
        add_action('publish_submissions', array($this, 'on_submissions_created'), 10, 2);
        add_action('publish_dcr_submissions', array($this, 'on_submissions_created'), 10, 2);
        add_action('template_redirect', array($this, 'redirect_post_type_archives_to_404'));
        add_filter('single_template', array($this, 'redirect_single_front_template'));

        // Test mail log endpoint (admin only) — remove after testing
        add_action('wp_ajax_test_wpa_mail_log', array($this, 'test_mail_log'));
    }

    function activate(): void
    {
        flush_rewrite_rules();
    }

    function deactivate(): void
    {
        flush_rewrite_rules();
    }

    function redirect_post_type_archives_to_404() {
        global $wp_query;
        // List of post types to hide their archives
        $post_types_hidden = array('assessments', 'submissions', 'dcr_submissions', 'reports', 'dcr_reports');
        // Get the current queried post type
        $post_type = get_query_var('post_type');

        // Check if the current page is an archive of one of the hidden post types
        if (is_post_type_archive() && in_array($post_type, $post_types_hidden)) {
            // Set 404 status
            $wp_query->set_404();
            status_header(404);

            // Redirect to 404 template
            include(get_query_template('404'));
            exit;
        }
    }

    function redirect_single_front_template($template)
    {
        global $post;

        if ($post->post_type == 'assessments') {
            $question_templates = get_post_meta($post->ID, 'question_templates', true);
            if ($question_templates == 'Simple Assessment') {
                return wpa_get_template_front_view('simple-assessment');
            }
            if ($question_templates == 'Comprehensive Assessment') {
                return wpa_get_template_front_view('comprehensive-assessment');
            }
        }
        else if ($post->post_type == 'submissions' || $post->post_type == 'dcr_submissions') {
            return wpa_get_template_front_view('single-submission');
        }
        else if ($post->post_type == 'reports') {
            return wpa_get_template_front_view('single-report');
        }
        else if ($post->post_type == 'dcr_reports') {
            return wpa_get_template_front_view('single-dcr-report');
        }
        
        return $template;
    }

    /**
     * Register Assessments post type
     * 
     */
    function register_assessment_custom_post_type(): void
    {
        $labels = array(
            'name' => _x('Assessments', 'assessment'),
            'singular_name' => _x('Assessment', 'assessment'),
            'add_new' => _x('Add New', 'assessment'),
            'add_new_item' => _x('Add New Assessment', 'assessment'),
            'edit_item' => _x('Edit Assessment', 'assessment'),
            'new_item' => _x('New Assessment', 'assessment'),
            'view_item' => _x('View Assessment', 'assessment'),
            'search_items' => _x('Search Assessments', 'assessment'),
            'not_found' => _x('No assessments found', 'assessment'),
            'not_found_in_trash' => _x('No assessments found in Trash', 'assessment'),
            'parent_item_colon' => _x('Parent Assessment:', 'assessment'),
            'menu_name' => _x('Assessments', 'assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail', 'author'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'map_meta_cap' => true,
            'capabilities' => array(
                'read_post' => 'read_assessment',
                'publish_posts' => 'publish_assessments',
                'edit_posts' => 'edit_assessments',
                'edit_others_posts' => 'edit_others_assessments',
                'delete_posts' => 'delete_assessments',
                'delete_others_posts' => 'delete_others_assessments',
                'read_private_posts' => 'read_private_assessments',
                'edit_post' => 'edit_assessment',
                'delete_post' => 'delete_assessment',
                'edit_published_post' => 'edit_published_assessment',
                'edit_published_posts' => 'edit_published_assessments',
            ),
        );

        register_post_type('assessments', $args);
    }

    /**
     * Register Assessment categories
     * 
     */
    function register_assessment_categories() {
        register_taxonomy(
            'category', 
            'assessments', 
            array(
                'hierarchical' => true,
                'label' => 'Categories', 
                'show_ui'=> true,
                'show_admin_column' => true,
                'query_var' => true,
            )
        );
    }

    /**
     * Register Index submissions post type
     * 
     * @param Index
     * 
     */
    function register_index_submissions_post_type(): void
    {
        $labels = array(
            'name'               => _x('Index Submissions', 'wp-assessment'),
            'singular_name'      => _x('Index Submission', 'wp-assessment'),
            'add_new'            => _x('Add New', 'wp-assessment'),
            'add_new_item'       => _x('Add New Submission', 'wp-assessment'),
            'edit_item'          => _x('Edit Index submission', 'wp-assessment'),
            'new_item'           => _x('New Index submission', 'wp-assessment'),
            'view_item'          => _x('View Submission', 'wp-assessment'),
            'search_items'       => _x('Search Submissions', 'wp-assessment'),
            'not_found'          => _x('No Submissions found', 'wp-assessment'),
            'not_found_in_trash' => _x('No Submissions found in Trash', 'wp-assessment'),
            'parent_item_colon'  => _x('Parent Index submission:', 'wp-assessment'),
            'menu_name'          => _x('Index Submissions', 'wp-assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail', 'author', 'comments'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'map_meta_cap' => true,
            'capabilities' => array(
                'read_post' => 'read_submission',
                'publish_posts' => 'publish_submissions',
                'edit_posts' => 'edit_submissions',
                'edit_others_posts' => 'edit_others_submissions',
                'delete_posts' => 'delete_submissions',
                'delete_others_posts' => 'delete_others_submissions',
                'read_private_posts' => 'read_private_submissions',
                'edit_post' => 'edit_submission',
                'delete_post' => 'delete_submission',
                'edit_published_post' => 'edit_published_submission',
                'edit_published_posts' => 'edit_published_submissions',
            ),
        );

        register_post_type('submissions', $args);
    }

    /**
     * Register DCR submissions post type
     * 
     * @param DCR
     * 
     */
    function register_dcr_submissions_post_type(): void
    {
        $labels = array(
            'name'               => _x('DCR Submissions', 'wp-assessment'),
            'singular_name'      => _x('DCR Submission', 'wp-assessment'),
            'add_new'            => _x('Add New', 'wp-assessment'),
            'add_new_item'       => _x('Add New Submission', 'wp-assessment'),
            'edit_item'          => _x('Edit DCR submission', 'wp-assessment'),
            'new_item'           => _x('New DCR submission', 'wp-assessment'),
            'view_item'          => _x('View DCR submission', 'wp-assessment'),
            'search_items'       => _x('Search Submissions', 'wp-assessment'),
            'not_found'          => _x('No Submissions found', 'wp-assessment'),
            'not_found_in_trash' => _x('No Submissions found in Trash', 'wp-assessment'),
            'parent_item_colon'  => _x('Parent DCR Submission:', 'wp-assessment'),
            'menu_name'          => _x('DCR Submissions', 'wp-assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail', 'author', 'comments'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'map_meta_cap' => true,
            'capabilities' => array(
                'read_post' => 'read_submission',
                'publish_posts' => 'publish_submissions',
                'edit_posts' => 'edit_submissions',
                'edit_others_posts' => 'edit_others_submissions',
                'delete_posts' => 'delete_submissions',
                'delete_others_posts' => 'delete_others_submissions',
                'read_private_posts' => 'read_private_submissions',
                'edit_post' => 'edit_submission',
                'delete_post' => 'delete_submission',
                'edit_published_post' => 'edit_published_submission',
                'edit_published_posts' => 'edit_published_submissions',
            ),
        );

        register_post_type('dcr_submissions', $args);
    }

    /**
     * Register Index Reports post type
     * 
     * @param Reports
     * 
     */
    function register_index_reports_custom_post_type(): void
    {
        $labels = array(
            'name'               => _x('Index Reports', 'wp-assessment'),
            'singular_name'      => _x('Report', 'wp-assessment'),
            'add_new'            => _x('Add New Report', 'wp-assessment'),
            'add_new_item'       => _x('Add New Report', 'wp-assessment'),
            'edit_item'          => _x('Edit Report', 'wp-assessment'),
            'new_item'           => _x('New Report', 'wp-assessment'),
            'view_item'          => _x('View Report', 'wp-assessment'),
            'search_items'       => _x('Search Reports', 'wp-assessment'),
            'not_found'          => _x('No Reports found', 'wp-assessment'),
            'not_found_in_trash' => _x('No reports found in Trash', 'wp-assessment'),
            'parent_item_colon'  => _x('Parent Report:', 'wp-assessment'),
            'menu_name'          => _x('Index Reports', 'wp-assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail', 'author'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-format-aside',
        );

        register_post_type('reports', $args);
    }

    /**
     * Register DCR Reports post type
     * 
     * @param DCR_Reports
     * 
     */
    function register_dcr_reports_custom_post_type(): void
    {
        $labels = array(
            'name'               => _x('DCR Reports', 'wp-assessment'),
            'singular_name'      => _x('Report', 'wp-assessment'),
            'add_new'            => _x('Add New Report', 'wp-assessment'),
            'add_new_item'       => _x('Add New Report', 'wp-assessment'),
            'edit_item'          => _x('Edit Report', 'wp-assessment'),
            'new_item'           => _x('New Report', 'wp-assessment'),
            'view_item'          => _x('View Report', 'wp-assessment'),
            'search_items'       => _x('Search Reports', 'wp-assessment'),
            'not_found'          => _x('No Reports found', 'wp-assessment'),
            'not_found_in_trash' => _x('No reports found in Trash', 'wp-assessment'),
            'parent_item_colon'  => _x('Parent Report:', 'wp-assessment'),
            'menu_name'          => _x('DCR Reports', 'wp-assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail', 'author'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-format-aside',
        );

        register_post_type('dcr_reports', $args);
    }

    function customize_reports_admin_column($columns)
    {
        $columns['user'] = 'User';
        $columns['organisation'] = 'Organisation';
        $columns['assessment'] = 'Assessment';
        return $columns;
    }

    function customize_reports_admin_column_value($column_key, $post_id): void
    {
        // Column "User"
        if ($column_key == 'user') {
            $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
            if ($sf_user_name) {
                echo $sf_user_name ?? '';
            }
        }
        // Column "Organisation"
        if ($column_key == 'organisation') {
            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (!empty($org_metadata)) {
                echo $org_metadata['Name'] ?? '';
            }
        }
        // Column "Assessment"
        if ($column_key == 'assessment') {
            $assessment_id = get_post_meta($post_id, 'assessment_id', true);
            if (isset($assessment_id)) {
                echo '<a href="/wp-admin/post.php?post='.$assessment_id.'&action=edit" target="_blank">'
                        .get_the_title($assessment_id).
                    '</a>' ?? '';
            }
        }
    }

    function customize_attachment_admin_column($columns)
    {
        $columns['sf_user'] = 'SF Uploader';
        return $columns;
    }

    function customize_attachment_admin_column_value($column_key, $post_id): void
    {
        if ($column_key == 'sf_user') {
            $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
            if ($sf_user_name) {
                echo $sf_user_name;
            }
        }
    }

    function customize_assessments_admin_column($columns)
    {
        $columns['assigned_moderator'] = 'Assigned To';
        return $columns;
    }

    function customize_assessments_admin_column_value($column_key, $post_id): void
    {
        if ($column_key == 'assigned_moderator') {
            $moderator_id = get_post_meta($post_id, 'assigned_moderator', true);
            if ($moderator_id) {
                $user = get_user_by('id', $moderator_id);
                echo $user->display_name;
            } else {
                echo 'N/A';
            }
        }
    }

    function customize_submissions_admin_column($columns)
    {
        global $post_type;
        $columns['user_id'] = 'Submitted by';
        $columns['organisation'] = 'Organisation';
        if ($post_type == 'dcr_submissions') {
            $columns['version'] = 'Version';
        }
        return $columns;
    }

    function customize_submissions_admin_column_value($column_key, $post_id): void
    {
        global $post_type;
        // Column "Submitted by"
        if ($column_key === 'user_id') {
            $user_id = get_post_meta($post_id, 'user_id', true);
            $sf_user_id = get_post_meta($post_id, 'sf_user_id', true);
            if ($sf_user_id) {
                $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
                echo $sf_user_name;
            } else {
                $user = get_user_by('id', $user_id);
                if (isset ($user->display_name)) echo $user->display_name;
            }
        }
        // Column "Organisation"
        if ($column_key === 'organisation') {
            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (!empty($org_metadata)) {
                echo $org_metadata['Name'];
            }
        }
        // Column "Version"
        if ($column_key === 'version' && $post_type === 'dcr_submissions') {
            // Retrieve meta values
            $this_sub_ver = get_post_meta($post_id, 'submission_version', true);
            $is_latest_version = get_post_meta($post_id, 'is_latest_version', true);
            // Check if current post is the latest
            $is_latest = ($is_latest_version == true) ? '(Latest)' : '';
            // Display Version Information
            if (!empty($this_sub_ver)) {
                echo 'Version ' . esc_html($this_sub_ver) . ' ' . esc_html($is_latest) . '<br><br>';
            }
            // Display Created Date
            $created_date = get_post_meta($post_id, 'created_date', true);
            if (!empty($created_date)) {
                echo 'Created on: <br>' . esc_html(date('M d, Y \a\t H:i a', strtotime($created_date)));
            }
        }
    }

    function submission_comments_post_hook($comment_ID, $comment_approved): void
    {
        if (1 === $comment_approved) {
            $comment = get_comment($comment_ID);
            $post_id = $comment->comment_post_ID;
            $submission = get_post($post_id);
            if ($submission->post_type == 'submissions') {
                $assessment_id = get_post_meta($post_id, 'assessment_id', true);
                $user_id = get_post_meta($post_id, 'user_id', true);
                $user = get_user_by('id', $user_id);
                $assessment = get_post($assessment_id);
                $subject = 'Remarks against ' . $assessment->post_title;
                $msg = 'Remarks against ' . $assessment->post_title;
                $headers = 'From: <your-email@example.com>' . "\r\n" . 'Reply-To: your-email@example.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                mail($user->user_email, $subject, $msg, $headers);
            }
        }
    }

    function on_submissions_created($post_id)
    {
        $post = get_post($post_id); 
        $assessment_id = get_post_meta($post_id, 'assessment_id', true);
        $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
        $sf_user_email = get_post_meta($post_id, 'sf_user_email', true);
        $org_data = get_post_meta($post_id, 'org_data', true);
        $org_name = $org_data['Name'] ?? '';
        $dcr_sub_notifi_email = get_field('dcr_submission_notification_email', 'option');
        $index_sub_notifi_email = get_field('index_submission_notification_email', 'option');

        if ($post->post_date == $post->post_modified) {
            if ($post->post_type == 'submissions' || $post->post_type == 'dcr_submissions') {
                $to = '';
                $subject = 'Saturn - New Submission Added';

                if ($post->post_type == 'dcr_submissions') {
                    if (!empty($dcr_sub_notifi_email)) {
                        $to = $dcr_sub_notifi_email;
                    }
                    else {
                        $to = $this->get_all_users_email($assessment_id);
                    }
                    $subject = 'Saturn - New DCR Submission Added #' .$post_id. ' - ' .$org_name;
                }
                else {
                    if (!empty($index_sub_notifi_email)) {
                        $to = $index_sub_notifi_email;
                    }
                    else {
                        $to = $this->get_all_users_email($assessment_id);
                    }
                    $subject = 'Saturn - New Index Submission Added #' .$post_id. ' - ' .$org_name;
                }

                // Validate recipients before sending
                if (empty($to) || (is_array($to) && empty(array_filter($to)))) {
                    $this->wpa_mail_log('MAIL_NO_RECIPIENTS', [
                        'sf_user_name'  => $sf_user_name,
                        'sf_user_email' => $sf_user_email,
                        'org_name'      => $org_name,
                        'post_id'       => $post_id,
                        'post_type'     => $post->post_type,
                        'assessment_id' => $assessment_id,
                        'subject'       => $subject,
                    ]);
                    return false;
                }
                
                $message  = '<div style="font-size:15px;">';
                $message .= '<p style="font-size:16px;">You have a new submission of <strong>'. get_the_title($assessment_id). '</strong>.</p>';
                $message .= '<p>From:</p>';
                $message .= '<ul style="padding:0;">';
                if (!empty($sf_user_name)) {
                    $message .= '<li>User: <strong>'. $sf_user_name .'</strong></li>';
                }
                if (!empty($sf_user_email)) {
                    $message .= '<li>Email: '. $sf_user_email .'</li>';
                }
                if (!empty($org_name)) {
                    $message .= '<li>Organisation: '. $org_name .'</li>';
                }
                $message .= '</ul>';
                $message .= 'View <a href='. home_url() .'/wp-admin/post.php?post='. $post_id .'&action=edit>'.get_the_title($post_id).'</a>';
                $message .= '</div>';

                $headers = array('Content-Type: text/html; charset=UTF-8');

                $sent = wp_mail($to, $subject, $message, $headers);

                // Log mail result
                $this->wpa_mail_log($sent ? 'MAIL_SENT' : 'MAIL_FAILED', [
                    'sf_user_name'  => $sf_user_name,
                    'sf_user_email' => $sf_user_email,
                    'org_name'      => $org_name,
                    'post_id'       => $post_id,
                    'post_type'     => $post->post_type,
                    'assessment_id' => $assessment_id,
                    'to'            => $to,
                    'subject'       => $subject,
                ]);

                return $sent;
            }
        }
    }

    function get_all_users_email($assessment_id)
    {
        $users_id = array();
        $assigned_moderator = get_post_meta($assessment_id, 'assigned_moderator', true);
        $author_id = get_post_field('post_author', $assessment_id);

        if (!empty($author_id)) {
            $users_id[] = $author_id;
        }
        if (!empty($assigned_moderator)) {
            $users_id[] = $assigned_moderator;
        }
        
        $email = array();
        foreach($users_id as $id) {
            $user = get_user_by('id', $id);
            if ($user && !empty($user->user_email)) {
                $email[] = $user->user_email;
            }
        }
        return $email;
    }

    /**
     * Log mail events to file, following the same pattern as user login logs.
     *
     * @param string $log_code  A short code describing the event (e.g. MAIL_SENT, MAIL_FAILED).
     * @param array  $info      Additional context to log.
     */
    function wpa_mail_log($log_code = '', $info = []) {
        if (empty($log_code)) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $log_dir_path = $upload_dir['basedir'] . '/saturn-mail-logs';
        $log_file_path = $log_dir_path . '/mail-logs-' . wp_date('m-Y') . '.log';

        // Create the logs directory if it doesn't exist.
        if (!file_exists($log_dir_path)) {
            if (!mkdir($log_dir_path, 0755, true) && !is_dir($log_dir_path)) {
                error_log('WPA: Failed to create mail log directory: ' . $log_dir_path);
                return;
            }
        }

        // Prepare the log message with the current timestamp.
        $log_message  = PHP_EOL;
        $log_message .= "[" . sanitize_text_field($log_code) . "] at [" . wp_date('d-m-Y H:i:s') . "]" . PHP_EOL;
        $log_message .= json_encode($info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append the log message to the file.
        $result = file_put_contents($log_file_path, $log_message, FILE_APPEND | LOCK_EX);

        // Log to WordPress debug log if file writing fails.
        if ($result === false) {
            error_log('WPA: Failed to write to mail log file: ' . $log_file_path);
        }
    }

    /**
     * Test endpoint for mail logging — remove after testing.
     * Trigger via: /wp-admin/admin-ajax.php?action=test_wpa_mail_log
     */
    function test_mail_log() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $this->wpa_mail_log('MAIL_TEST', [
            'sf_user_name'  => 'Tom',
            'sf_user_email' => 'test@test.com',
            'org_name'      => 'YSN',
            'post_id'       => 0,
            'post_type'     => 'test',
            'assessment_id' => 0,
            'to'            => 'tom@ysnstudios.com',
            'subject'       => 'Test mail log entry',
        ]);

        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/saturn-mail-logs/mail-logs-' . wp_date('m-Y') . '.log';

        wp_send_json_success([
            'message'  => 'Test log entry written.',
            'log_file' => $log_file,
            'exists'   => file_exists($log_file),
        ]);
    }
}

if (class_exists('WPA_CustomPostType')) {
    $instance = new WPA_CustomPostType();
}

register_activation_hook(__FILE__, array($instance, 'activate'));
register_deactivation_hook(__FILE__, array($instance, 'deactivate'));
//register_uninstall_hook(__FILE__, array($book, 'uninstall'));