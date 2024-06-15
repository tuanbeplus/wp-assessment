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

function sf_update_contact_quick_10_field($post_id) {

    // Define the data to update
    $data = array(
        'Quick_10__c' => true,
        'Phone' => '12332423',
    );

    // Call the function to update Salesforce record
    $response = update_record_sobject_salesforce('Contact', '0039h000008m2HRAAY', $data);

    if ($response == true) {

        $email = 'tom@ysnstudios.com';
        $subject = 'Update Salesforce Contact Quick 10 Successfuly.';
        $message = 'Successfuly';
        wp_mail($email, $subject, $message);
    }
}
add_action('publish_submissions', 'sf_update_contact_quick_10_field');
