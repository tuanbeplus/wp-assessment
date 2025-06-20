<?php
/**
 * The template for displaying Report PDF - Saturn
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
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$ranking_id = get_ranking_of_assessment($assessment_id);

$user_id = get_post_meta($submission_id, 'user_id', true);
$wp_user_id = get_current_user_by_salesforce_id($user_id);
$salesforce_account_json = get_user_meta($wp_user_id, '__salesforce_account_json', true);
$org_data = json_decode($salesforce_account_json, true);

$org_score = get_post_meta($submission_id, 'org_score', true);
$org_section_score = get_post_meta($submission_id, 'org_section_score', true);
$agreed_score = get_post_meta($submission_id, 'agreed_score', true);
$assessment_title = get_the_title($assessment_id);
$col_key_areas = get_assessment_key_areas($assessment_id);
$recommentdation = get_post_meta($submission_id, 'recommentdation', true);
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$count_index = count_all_index_submissions_finalised($assessment_id);
$report_title = get_the_title($post_id);
$report_title = html_entity_decode($report_title, ENT_QUOTES, 'UTF-8');
$report_file_name = !empty($report_title) ? $report_title : 'Access and Inclusion Index Comprehensive Report';
$report_template = get_post_meta($post_id, 'report_template', true);
if (empty($report_template)) {
    $report_template = get_post_meta($assessment_id, 'report_template', true);
}
$agreed_gr_score_with_weighting = cal_scores_with_weighting($assessment_id, $agreed_score, 'group') ?? array();

// Data position from Ranking
$position_by_total_score = $main->wpa_unserialize_metadata(get_field('position_by_total_score', $ranking_id));
$position_by_industry = $main->wpa_unserialize_metadata(get_field('position_by_industry', $ranking_id));
$position_by_framework = $main->wpa_unserialize_metadata(get_field('position_by_framework', $ranking_id));

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
        'margin-top' => 34, 
        'margin-bottom' => 24, 
        'margin-left' => 14,
        'margin-right' => 14,
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

// Render Key Recommendations
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-key-recommendations.php';

//============== Begin Part A - Organisation Dashboard =============//
// Render Organisation Total Score
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-org-total-score.php';

// Render Self-assessed score and final Australian Network on Disability score 
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-self-assessed-score.php';

// Render Benchmark Results
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-benchmark-results.php';

// Render Maturity Level for Key Areas
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-maturity-level-for-key-area.php';

// Render Overall Maturity Dashboard 
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-overall-maturity-dashboard.php';

// Render All Year on Year history dashboard
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-history.php';
//============== End Part A - Organisation Dashboard =============//

// Render All after generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page-after.php';

// Output the PDF to the browser with the File name
$mpdf->Output($report_file_name.'.pdf', \Mpdf\Output\Destination::INLINE);
