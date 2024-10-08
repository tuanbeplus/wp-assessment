<?php

class CustomPostType
{
    function __construct()
    {
        add_action('init', array($this, 'register_assessment_custom_post_type'));
        add_action('init', array($this, 'register_index_submissions_post_type'));
        add_action('init', array($this, 'register_dcr_submissions_post_type'));
        add_action('init', array($this, 'register_reports_custom_post_type'));
        add_action('init', array($this, 'register_assessment_categories'));
        // Hook into the 'acf/init' action to add the ACF options page
        add_action('acf/init', array($this, 'add_assessments_options_page'));
        add_action('acf/init', array($this, 'add_assessments_options_fields'));

        add_filter('manage_assessments_posts_columns', array($this, 'customize_assessments_admin_column'));
        add_action('manage_assessments_posts_custom_column', array($this, 'customize_assessments_admin_column_value'), 10, 2);

        add_filter('manage_submissions_posts_columns', array($this, 'customize_submissions_admin_column'));
        add_action('manage_submissions_posts_custom_column', array($this, 'customize_submissions_admin_column_value'), 10, 2);

        add_filter('manage_dcr_submissions_posts_columns', array($this, 'customize_submissions_admin_column'));
        add_action('manage_dcr_submissions_posts_custom_column', array($this, 'customize_submissions_admin_column_value'), 10, 2);

        add_filter('manage_reports_posts_columns', array($this, 'customize_reports_admin_column'));
        add_action('manage_reports_posts_custom_column', array($this, 'customize_reports_admin_column_value'), 10, 2);

        add_filter('manage_attachment_posts_columns', array($this, 'customize_attachment_admin_column'));
        add_action('manage_attachment_posts_custom_column', array($this, 'customize_attachment_admin_column_value'), 10, 2);

        add_action('comment_post', array($this, 'submission_comments_post_hook'), 10, 2);
        add_action('publish_submissions', array($this, 'on_submissions_created'), 10, 2);
        add_action('publish_dcr_submissions', array($this, 'on_submissions_created'), 10, 2);
    }

    function activate(): void
    {
        flush_rewrite_rules();
    }

    function deactivate(): void
    {
        flush_rewrite_rules();
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
     * Add Assessments Settings ACF sub page
     * 
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
     * 
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

    /**
     * Register Index submissions post type
     * 
     * @param Index
     * 
     */
    function register_index_submissions_post_type(): void
    {
        $labels = array(
            'name' => _x('Index submissions', 'submission'),
            'singular_name' => _x('Index submission', 'submission'),
            'add_new' => _x('Add New', 'submission'),
            'add_new_item' => _x('Add New Index submission', 'submission'),
            'edit_item' => _x('Edit Index submission', 'submission'),
            'new_item' => _x('New Index submission', 'submission'),
            'view_item' => _x('View Index submission', 'submission'),
            'search_items' => _x('Search Index submissions', 'submission'),
            'not_found' => _x('No Index submissions found', 'submission'),
            'not_found_in_trash' => _x('No Index submissions found in Trash', 'submission'),
            'parent_item_colon' => _x('Parent Index submission:', 'submission'),
            'menu_name' => _x('Index submissions', 'submission'),
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
            'name' => _x('DCR submissions', 'submission'),
            'singular_name' => _x('DCR submission', 'submission'),
            'add_new' => _x('Add New', 'submission'),
            'add_new_item' => _x('Add New DCR submission', 'submission'),
            'edit_item' => _x('Edit DCR submission', 'submission'),
            'new_item' => _x('New DCR submission', 'submission'),
            'view_item' => _x('View DCR submission', 'submission'),
            'search_items' => _x('Search DCR submissions', 'submission'),
            'not_found' => _x('No DCR submissions found', 'submission'),
            'not_found_in_trash' => _x('No DCR submissions found in Trash', 'submission'),
            'parent_item_colon' => _x('Parent DCR submission:', 'submission'),
            'menu_name' => _x('DCR submissions', 'submission'),
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
     * Register Reports post type
     * 
     * @param Reports
     * 
     */
    function register_reports_custom_post_type(): void
    {
        $labels = array(
            'name' => _x('Reports', 'report'),
            'singular_name' => _x('Reports', 'report'),
            'add_new' => _x('Add New', 'report'),
            'add_new_item' => _x('Add New Report', 'report'),
            'edit_item' => _x('Edit Report', 'report'),
            'new_item' => _x('New Report', 'report'),
            'view_item' => _x('View Report', 'report'),
            'search_items' => _x('Search Reports', 'report'),
            'not_found' => _x('No Reports found', 'report'),
            'not_found_in_trash' => _x('No reports found in Trash', 'report'),
            'parent_item_colon' => _x('Parent Report:', 'report'),
            'menu_name' => _x('Reports', 'report'),
            
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
            // 'show_in_rest' => true,
            'menu_icon' => 'dashicons-format-aside',
        );

        register_post_type('reports', $args);
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
                echo $sf_user_name;
            }
        }

        // Column "Organisation"
        if ($column_key == 'organisation') {
            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (!empty($org_metadata)) {
                echo $org_metadata['Name'];
            }
        }

        // Column "Assessment"
        if ($column_key == 'assessment') {
            $assessment_id = get_post_meta($post_id, 'assessment_id', true);
            if (isset($assessment_id)) {
                echo '<a href="/wp-admin/post.php?post='.$assessment_id.'&action=edit" target="_blank">'
                        .get_the_title($assessment_id).
                    '</a>';
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
        $columns['user_id'] = 'Submitted by';
        $columns['organisation'] = 'Organisation';
        return $columns;
    }

    function customize_submissions_admin_column_value($column_key, $post_id): void
    {
        // Column "Submitted by"
        if ($column_key == 'user_id') {
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
        if ($column_key == 'organisation') {
            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (!empty($org_metadata)) {
                echo $org_metadata['Name'];
            }
        }
    }

    function submission_comments_post_hook($comment_ID, $comment_approved): void
    {
        if (1 === $comment_approved) {
            $comment = get_comment($comment_ID);
            $post_id = $comment->comment_post_ID;
            $submission = get_post($post_id);
            if ($submission->post_type = 'submissions') {
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

        if ($post->post_date == $post->post_modified) {
            if ($post->post_type == 'submissions' || $post->post_type == 'dcr_submissions') {

                if ($post->post_type == 'dcr_submissions') {
                    if (!empty($dcr_sub_notifi_email)) {
                        $to = $dcr_sub_notifi_email;
                    }
                    else {
                        $to = $this->get_all_users_email($assessment_id);
                    }
                }
                else {
                    $to = $this->get_all_users_email($assessment_id);
                }
                
                $subject = 'Saturn - New Submission Added #' .$post_id. ' - ' .$org_name;
                $message  = '<div style="font-size:15px;">';
                $message .= '<p style="font-size:16px;">You have a new submission of <strong>'. get_the_title($assessment_id). '</strong>.</p>';
                $message .= '<p>From:</p>';
                $message .= '<ul style="padding:0;">';
                if (isset($sf_user_name)) {
                    $message .= '<li>User: <strong>'. $sf_user_name .'</strong></li>';
                }
                if (isset($sf_user_email)) {
                    $message .= '<li>Email: '. $sf_user_email .'</li>';
                }
                if (isset($org_name)) {
                    $message .= '<li>Organisation: '. $org_name .'</li>';
                }
                $message .= '</ul>';
                $message .= 'View <a href='. home_url() .'/wp-admin/post.php?post='. $post_id .'&action=edit>'.get_the_title($post_id).'</a>';
                $message .= '</div>';

                $sent = wp_mail($to, $subject, $message);
                return $sent;
            }
        }
    }

    function get_all_users_email($assessment_id)
    {
        $users_id = array();
        $users = array();
        $assigned_moderator = get_post_meta($assessment_id, 'assigned_moderator', true);
        $author_id = get_post_field('post_author', $assessment_id);

        array_push($users_id, $author_id, $assigned_moderator);
        
        foreach($users_id as $id) {
            $users[] = get_user_by('id', $id);
        }
        
        $email = array();
        foreach ($users as $user) {
            $email[] = $user->user_email;
        }
        return $email;
    }
}

if (class_exists('CustomPostType')) {
    $instance = new CustomPostType();
}

register_activation_hook(__FILE__, array($instance, 'activate'));
register_deactivation_hook(__FILE__, array($instance, 'deactivate'));
//register_uninstall_hook(__FILE__, array($book, 'uninstall'));