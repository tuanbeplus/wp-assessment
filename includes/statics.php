<?php
const WP_ASSESSMENT_ADMIN_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/admin';
const WP_ASSESSMENT_FRONT_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/front';

// define admin views
const MODERATOR_LIST_ADMIN_SELECT = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/moderator-list.php';
const MODERATOR_LIST_ADMIN_QUESTIONAIRE_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/questionaire.php';
const ADMIN_REPORT_CONTENT_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/report/report-content.php';
const ADMIN_REPORT_SECTION_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/report/report-section.php';
const ADMIN_REPORT_RECOMMENDATION_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/report/report-recommendation.php';
const USER_ASSESSMENTS_PERCHASED_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/users/assessments-purchased-view.php';
const ADMIN_SUBMISSION_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submission-view.php';
const ADMIN_SUBMISSION_INFO_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submission-info-view.php';
const ADMIN_ASSESSMENT_OPTION_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessment-options-view.php';
const ADMIN_ATTACHMENT_UPLOADER_INFO_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/attachment-uploader-info-view.php';
const SUBMISSON_KEY_RECOMMENDATION = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submission-details/key-recommendation.php';
const SUBMISSON_KEY_FINDINGS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submission-details/key-findings.php';
const SUBMISSON_E_SUMMARY = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submission-details/e-summary.php';

// define front views
const QUIZ_TEMPLATE_VIEW = WP_ASSESSMENT_FRONT_VIEW_DIR . '/quiz.php';
const SINGLE_REPORTS_TEMPLATE = WP_ASSESSMENT_FRONT_VIEW_DIR . '/single-reports.php';
const SINGLE_SUBMISSIONS_TEMPLATE = WP_ASSESSMENT_FRONT_VIEW_DIR . '/single-submissions.php';
const QUIZ_TEMPLATE_VIEW_MULTI = WP_ASSESSMENT_FRONT_VIEW_DIR . '/quiz_multi.php';

// Plugin enqueue scripts
add_action('admin_enqueue_scripts', 'admin_enqueue_scripts');
function admin_enqueue_scripts()
{
    global $post_type;
    if( $post_type == 'assessments' || $post_type == 'submissions' || $post_type == 'reports' || $post_type == 'attachment') {
        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');

        wp_enqueue_script('admin-js', WP_ASSESSMENT_ASSETS . '/js/admin/main.js', true, rand());
        wp_localize_script(
            'admin-js',
            'ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('assessment_attachment_upload'),
            )
        );
    }
    wp_enqueue_style('admin-css', WP_ASSESSMENT_ASSETS . '/css/style.css', false, rand());
}

add_action('wp_enqueue_scripts', 'enqueue_scripts');
function enqueue_scripts()
{
    global $post_type;
    if( $post_type == 'assessments' || $post_type == 'submissions' || $post_type == 'reports') {
        wp_enqueue_media();
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_style('front-style', WP_ASSESSMENT_ASSETS . '/css/front/style.css', true, rand());
        wp_enqueue_style('front-responsive', WP_ASSESSMENT_ASSETS . '/css/front/responsive.css', true, rand());

        wp_enqueue_script('jquery', WP_ASSESSMENT_ASSETS . '/js/jquery.min.js');
        wp_enqueue_script('bootstrap-min-js', WP_ASSESSMENT_ASSETS . '/js/bootstrap.min.js');
        wp_enqueue_script('main-js', WP_ASSESSMENT_ASSETS . '/js/front/main.js', true, rand());
        wp_localize_script(
            'main-js',
            'ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('assessment_attachment_upload'),
            )
        );
    }
}