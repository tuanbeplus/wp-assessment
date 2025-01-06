<?php
const WP_ASSESSMENT_ADMIN_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/admin';
const WP_ASSESSMENT_FRONT_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/front';
const WP_ASSESSMENT_TEMPLATE = WP_ASSESSMENT_DIR . 'templates';

// Admin enqueue scripts
add_action('admin_enqueue_scripts', 'wpa_admin_enqueue_scripts');
function wpa_admin_enqueue_scripts()
{
    global $post_type;
    $post_types_allow = array('assessments', 'submissions', 'dcr_submissions', 'reports', 'dcr_reports');

    if ( in_array($post_type, $post_types_allow) ) {
        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_script('chart-lib', 'https://cdn.jsdelivr.net/npm/chart.js');
        wp_enqueue_script('admin-js', WP_ASSESSMENT_ASSETS . '/js/admin/admin-main.js', array('jquery'), WP_ASSESSMENT_VER, true);

        wp_localize_script(
            'admin-js',
            'ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('assessment_attachment_upload'),
            )
        );

        $dashboard_chart_imgs = get_post_meta(get_the_ID(), 'dashboard_chart_imgs', true);
        // Pass the data to the script
        wp_localize_script('admin-js', 'report_chart_imgs_meta', $dashboard_chart_imgs);

    }
    wp_enqueue_style('admin-css', WP_ASSESSMENT_ASSETS . '/css/admin/admin-main.css', array(), WP_ASSESSMENT_VER, 'all');
}

// Front enqueue scripts
add_action('wp_enqueue_scripts', 'wpa_enqueue_scripts');
function wpa_enqueue_scripts()
{
    global $post_type;
    $post_types_allow = array('assessments', 'submissions', 'dcr_submissions', 'reports', 'dcr_reports');
    
    if ( in_array($post_type, $post_types_allow) ) {
        wp_enqueue_media();
        wp_enqueue_script('jquery', WP_ASSESSMENT_ASSETS . '/js/jquery.min.js');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_style('front-style', WP_ASSESSMENT_ASSETS . '/css/front/main.css', array(), WP_ASSESSMENT_VER, 'all');
        wp_enqueue_script('main-script', WP_ASSESSMENT_ASSETS . '/js/front/main.js', array('jquery'), WP_ASSESSMENT_VER, true);
        wp_localize_script(
            'main-script',
            'ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('assessment_attachment_upload'),
            )
        );
    }
}

/**
 * Loads an admin template from the plugin's template directory.
 *
 * @param string $folder    The folder name where the template is located.
 * @param string $file_name The base file name of the template (without the `.php` extension).
 * @return void
 */
function wpa_get_template_admin_view( $folder, $file_name ) {
    $file = WP_ASSESSMENT_ADMIN_VIEW_DIR . "/$folder/{$file_name}.php";
    if ( file_exists( $file ) ) {
        include_once $file;
    } else {
        echo 'File not found.';
    }
}

/**
 * Loads the front template from the plugin's template directory.
 *
 * @param string $file_name The base file name of the template (without the `.php` extension).
 * @return void
 */
function wpa_get_template_front_view( $file_name ) {
    $file = WP_ASSESSMENT_FRONT_VIEW_DIR . "/{$file_name}.php";
    if ( file_exists( $file ) ) {
        include_once $file;
    } else {
        echo 'File not found.';
    }
}

/**
 * Loads an admin module from the plugin's template directory.
 *
 * @param string $module_name The base file name of the module (without the `.php` extension).
 * @return void
 */
function wpa_get_admin_module( $module_name ) {
    $module_name = sanitize_file_name($module_name);
    $file = WP_ASSESSMENT_ADMIN_VIEW_DIR . "/modules/{$module_name}.php";
    if ( file_exists( $file ) ) {
        require_once $file;
    } else {
        echo 'Module not found.';
    }
}
