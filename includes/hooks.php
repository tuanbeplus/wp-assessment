<?php
/**
 * Hook 
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
