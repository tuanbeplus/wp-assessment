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
