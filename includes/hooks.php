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

/**
 * Hide attachment files from the Media Library's overlay (modal) view
 * if they have a certain meta key set.
 * 
 * @param array $args An array of query variables.
 */
add_filter( 'ajax_query_attachments_args', 'wpa_hide_saturn_media_overlay_view' );
function wpa_hide_saturn_media_overlay_view( $args ) {
    // Bail if this is not the admin area.
    if ( ! is_admin() ) {
        return $args;
    }

    // Modify the query.
    $args['meta_query'] = [
        [
            'key'     => 'sf_user_id',
            'compare' => 'NOT EXISTS',
        ]
    ];
   
    return $args;
}

/**
 * Hide attachment files from the Media Library's list view
 * if they have a certain meta key set.
 * 
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
add_action( 'pre_get_posts', 'wpa_hide_saturn_media_list_view' );
function wpa_hide_saturn_media_list_view( $query ) {
    // Bail if this is not the admin area.
    if ( ! is_admin() ) {
        return;
    }

    // Bail if this is not the main query.
    if ( ! $query->is_main_query() ) {
        return;
    }

    // Only proceed if this the attachment upload screen.
    $screen = get_current_screen();
    if ( ! $screen || 'upload' !== $screen->id || 'attachment' !== $screen->post_type ) {
        return;
    }
    
    // Modify the query.
    $query->set( 'meta_query', [
        [
            'key'     => 'sf_user_id',
            'compare' => 'NOT EXISTS',
        ]
    ]);

    return;
}
