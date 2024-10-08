<?php
const WP_ASSESSMENT_ADMIN_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/admin';
const WP_ASSESSMENT_FRONT_VIEW_DIR = WP_ASSESSMENT_DIR . '/views/front';
const WP_ASSESSMENT_TEMPLATE = WP_ASSESSMENT_DIR . 'templates';

// define admin views Assessments
const ADMIN_QUESTIONAIRE_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/questionaire.php';
const ADMIN_ASSESSMENT_OPTION_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/assessment-options-view.php';
const MODERATOR_LIST_ADMIN_SELECT = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/moderator-list.php';
const ADMIN_ACCESS_CONTROL_PANEL = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/access-control-panel.php';
const ASSESSMENT_BLACKLIST_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/assessment-blacklist.php';
const ASSESSMENT_FORMULA_OPTIONS_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/assessments/scoring-formula-options.php';

const ADMIN_REPORT_CONTENT_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/report-content.php';
const ADMIN_REPORT_SECTION_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/report-section.php';
const ADMIN_REPORT_KEY_AREAS_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/report-key-areas.php';

// define admin views Submissions
const ADMIN_SUBMISSION_INDEX_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submissions/submission-index-view.php';
const ADMIN_SUBMISSION_DCR_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submissions/submission-dcr-view.php';
const ADMIN_SUBMISSION_INFO_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submissions/submission-info-view.php';
const ADMIN_SUBMISSION_SCORING_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submissions/submission-scoring.php';
const ADMIN_SATURN_INVITE_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/submissions/saturn-invite.php';

// define admin views Reports
const LINK_REPORT_TO_ASSESSMENT = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/link-report-to-assessment.php';
const REPORT_DASHBOARD_CHART_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/report-dashboard-charts.php';
const REPORT_DASHBOARD_SHARE_REPORTS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/reports/share-reports.php';

// define admin views Users
const USER_ASSESSMENTS_PERCHASED_FIELDS = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/users/assessments-purchased-view.php';

// define admin views Attachments
const ADMIN_ATTACHMENT_UPLOADER_INFO_VIEW = WP_ASSESSMENT_ADMIN_VIEW_DIR . '/attachments/attachment-uploader-info-view.php';

// define front views
const QUIZ_TEMPLATE_VIEW = WP_ASSESSMENT_FRONT_VIEW_DIR . '/quiz.php';
const SINGLE_REPORTS_TEMPLATE = WP_ASSESSMENT_FRONT_VIEW_DIR . '/single-reports.php';
const SINGLE_SUBMISSIONS_TEMPLATE = WP_ASSESSMENT_FRONT_VIEW_DIR . '/single-submissions.php';

// Plugin enqueue scripts
add_action('admin_enqueue_scripts', 'admin_enqueue_scripts');
function admin_enqueue_scripts()
{
    global $post_type;
    if( $post_type == 'assessments' || $post_type == 'submissions' || $post_type == 'dcr_submissions' || $post_type == 'reports') {
        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_script('chart-lib', 'https://cdn.jsdelivr.net/npm/chart.js');
        wp_enqueue_script('admin-js', WP_ASSESSMENT_ASSETS . '/js/admin/main.js', true, WP_ASSESSMENT_VER);

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
    wp_enqueue_style('admin-css', WP_ASSESSMENT_ASSETS . '/css/style.css', false, WP_ASSESSMENT_VER);
}

add_action('wp_enqueue_scripts', 'enqueue_scripts');
function enqueue_scripts()
{
    global $post_type;
    if( $post_type == 'assessments' || $post_type == 'submissions' || $post_type == 'dcr_submissions' || $post_type == 'reports') {
        wp_enqueue_media();
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_style('front-style', WP_ASSESSMENT_ASSETS . '/css/front/style.css', true, WP_ASSESSMENT_VER);
        wp_enqueue_style('front-responsive', WP_ASSESSMENT_ASSETS . '/css/front/responsive.css', true, WP_ASSESSMENT_VER);
        wp_enqueue_script('jquery', WP_ASSESSMENT_ASSETS . '/js/jquery.min.js');
        wp_enqueue_script('bootstrap-min-js', WP_ASSESSMENT_ASSETS . '/js/bootstrap.min.js');
        wp_enqueue_script('main-js', WP_ASSESSMENT_ASSETS . '/js/front/main.js', true, WP_ASSESSMENT_VER);
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
