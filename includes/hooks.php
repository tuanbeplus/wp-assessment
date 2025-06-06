<?php
/**
 * Hook WP head to set Login Salesforce redirect URL
 * 
 */
add_action ('wp_head', function () {
    ?>
    <script>
        if (localStorage) {
            localStorage.setItem('sf_login_redirect_url', window.location);
        }
    </script>
    <?php
});

function sf_user_mail_from( $email ) {
    return $_COOKIE['sf_user_email'];
}

function sf_user_mail_from_name( $name ) {
    return $_COOKIE['sf_name'];
}

/**
 * Update Salesforce Contact Quick 10 field after users completed The Quick 10
 * 
 * @param $post_id   Submission ID
 * 
 */
function sf_update_contact_quick_10_field($post_id) {
    // Get the Assessment Quick 10 ID
    $assessment_quick_10 = get_field('assessment_quick_10', 'option');
    // Get the Assessment ID
    $assessment_id = get_post_meta($post_id, 'assessment_id', true);
    
    // This Hook only work on The Quick 10
    if ($assessment_id != $assessment_quick_10) {
        return;
    }
    // Get Salesforce User ID
    $sf_user_id = get_post_meta($post_id, 'user_id', true);
    // Get WP User ID
    $wp_user_id = get_current_user_by_salesforce_id($sf_user_id);
    // Get Salesforce Contact ID
    $contact_id = get_user_meta($wp_user_id, 'salesforce_contact_id', true);

    if (!empty($contact_id)) {
        // Define the data to update
        $data = array(
            'Quick_10__c' => true,
        );
        // Call the function to update Salesforce record
        $response = update_record_sobject_salesforce('Contact', $contact_id, $data);

        $sf_user_name = get_post_meta($post_id, 'sf_user_name', true);
        $sf_user_email = get_post_meta($post_id, 'sf_user_email', true);
        $org_metadata = get_post_meta($post_id, 'org_data', true);

        // Email Content
        $email = 'tom@ysnstudios.com';
        $subject = '';
        $message = '';
        
        if ($response == true) {
            $subject .= 'Update Salesforce Contact Quick 10 Successfuly - '.date('d F Y');
            $message .= 'Status: Successfuly';
        }
        else {
            $subject .= 'Update Salesforce Contact Quick 10 Failed - '.date('d F Y');
            $message .= 'Status: Failed';
            $message .= '<br>';
            $message .= 'User Name: '.$sf_user_name;
            $message .= '<br>';
            $message .= 'User ID: '.$sf_user_id;
            $message .= '<br>';
            $message .= 'Contact ID: '.$contact_id;
            $message .= '<br>';
            $message .= 'Email: '.$sf_user_email;
            $message .= '<br>';
            $message .= 'Org Name: '.$org_metadata['Name'];
            $message .= '<br>';

            wp_mail($email, $subject, $message);
        }
    }
}
add_action('publish_submissions', 'sf_update_contact_quick_10_field');


function wpa_update_missing_created_date_meta() {
    if ($_GET['action'] == 'wpa_update_missing_created_date_meta') {
        $posts = get_posts(array(
            'post_type'      => 'dcr_submissions', // Replace with your desired post type or 'any' for all post types
            'post_status'    => 'any', // Include published, draft, etc.
            'numberposts'    => -1,    // Get all posts
            'fields'         => 'ids', // Only fetch post IDs for better performance
        ));
        foreach ($posts as $post_id) {
            // Check if 'created_date' meta exists or is null
            $created_date_meta = get_post_meta($post_id, 'created_date', true);
    
            if (empty($created_date_meta)) {
                // Get the post's original creation date (post_date)
                $post_date = get_post_field('post_date', $post_id);
                // Update the meta field with the post_date
                $updated = update_post_meta($post_id, 'created_date', $post_date);
    
                if ($updated) {
                    echo 'Updated "created_date" meta for post ID: '.$post_id.' with date: '.$post_date;
                    echo '<br>';
                    echo '<br>';
                }
                else {
                    echo '[Failed] to update "created_date" meta for post ID: '.$post_id;
                    echo '<br>';
                    echo '<br>';
                }
            }
        }
    }
}
// add_action('init', 'wpa_update_missing_created_date_meta');

function wpa_update_missing_submission_version_meta() {
    if (isset($_GET['action']) && $_GET['action'] == 'wpa_update_missing_submission_version_meta') {
        // Basic admin check
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        $posts = get_posts(array(
            'post_type'      => 'dcr_submissions', // Replace with your desired post type or 'any' for all post types
            'post_status'    => 'publish', // Include published, draft, etc.
            'numberposts'    => -1,    // Get all posts
            'fields'         => 'ids', // Only fetch post IDs for better performance
        ));
        
        foreach ($posts as $post_id) {
            // Check if 'submission_version' meta exists or is null
            $submission_version_meta = get_post_meta($post_id, 'submission_version', true);
    
            if (empty($submission_version_meta)) {
                // Update the meta field with the value '1'
                $updated = update_post_meta($post_id, 'submission_version', '1');
    
                if ($updated) {
                    echo 'Updated "submission_version" meta for post ID: '.$post_id;
                    echo '<br>';
                    echo '<br>';
                }
                else {
                    echo '[Failed] to update "submission_version" meta for post ID: '.$post_id;
                    echo '<br>';
                    echo '<br>';
                }
            }
        }
        
        // Stop WordPress from continuing to load unnecessary code
        exit;
    }
}
// add_action('init', 'wpa_update_missing_submission_version_meta');