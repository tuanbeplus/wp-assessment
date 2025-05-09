<?php
/**
 * The template for displaying DCR Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

// Check user permission
if (! current_user_can('administrator')) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    // Redirect to 404 template
    include(get_query_template('404'));
    exit;
}
global $post;
$post_id = $post->ID;
$main = new WP_Assessment();
$azure = new WP_Azure_Storage();
$feedback_cl = new AndSubmissionFeedbacks();
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$report_post_type = get_post_type($post_id);

$user_id = get_post_meta($submission_id, 'user_id', true);
$wp_user_id = get_current_user_by_salesforce_id($user_id);
$salesforce_account_json = get_user_meta($wp_user_id, '__salesforce_account_json', true);
$org_data = json_decode($salesforce_account_json, true);

$organisation_id = $org_data['Id'] ?? '';
$assessment_title = get_the_title($assessment_id);
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$report_title = get_the_title($post_id);
$report_title = html_entity_decode($report_title, ENT_QUOTES, 'UTF-8');
$report_file_name = !empty($report_title) ? $report_title : 'DCR - 2024 Report';
$report_template = get_post_meta($post_id, 'report_template', true);
if (empty($report_template)) {
    $report_template = get_post_meta($assessment_id, 'report_template', true);
}
$quizzes = $main->get_quizzes_by_assessment_and_submissions($assessment_id, $submission_id, $organisation_id);
$reorganize_quizzes = [];
foreach ($quizzes as $row) {
    $reorganize_quizzes[$row->parent_id][$row->quiz_id][] = $row;
}
$assessor_feedbacks = $feedback_cl->format_feedbacks_by_question($assessment_id, $organisation_id);
$azure_documents_uploaded = $azure->get_azure_attachments_uploaded($assessment_id, $organisation_id);

// get mPDF fontDir
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

// get mPDF fontData
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

// mPDF Configuration
$mpdf = new \Mpdf\Mpdf([  
    'fontDir' => array_merge($fontDirs, [
        ABSPATH .'wp-content/plugins/wp-assessment/assets/font/',
    ]),
	'fontdata' => $fontData + 
		[
            'avenir-light' => [
                'R' => 'Avenir-Light.ttf', 
            ],
            'avenir-roman' => [
                'R' => 'Avenir-Roman.ttf', 
            ],
            'avenir-medium' => [
                'R' => 'Avenir-Medium.ttf', 
            ],
            'avenir-heavy' => [
                'R' => 'Avenir-Heavy.ttf', 
            ],
            'avenir-black' => [
                'R' => 'Avenir-Black.ttf', 
            ],
		],
]);

// Include Stylesheet
$pdf_stylesheet = file_get_contents(WP_ASSESSMENT_ASSETS . '/css/report-pdf-style.css');
$mpdf->WriteHTML($pdf_stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

// Render Front page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-front-page.php';

// Define the Header/Footer before writing anything so they appear on the first page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-footer.php';

// add margin for all pages below
$mpdf->AddPageByArray(
    array(
        'margin-top' => 32, 
        'margin-bottom' => 22, 
        'margin-left' => 12,
        'margin-right' => 12,
    ),
);

// Render Table of content
if (isset($report_template['is_include_toc'])) {
    if ($report_template['is_include_toc'] == true) { 
        $mpdf->TOCpagebreakByArray(
            array(
                'links' => true,
                'toc-preHTML' => '<h2>Table of Contents</h2>', 
            ),
        );
    }
}

// Render All before generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page-before.php';

require_once WP_ASSESSMENT_TEMPLATE.'/dcr-report-pdf/draft-pre-report.php';

// Render All after generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page-after.php';

// Output the PDF to the browser with the File name
$mpdf->Output($report_file_name.'.pdf', \Mpdf\Output\Destination::INLINE);
