<?php
/**
 * The template for displaying Report PDF - Saturn
 *
 * @author Tuan
 * 
 */
 
global $post;
$post_id = $post->ID;
$main = new WP_Assessment();
$mpdf = new \Mpdf\Mpdf();
$report = new WP_Report_PDF();
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$ranking_id = get_ranking_of_assessment($assessment_id);
$org_data = get_post_meta($post_id, 'org_data', true);
$assessment_title = get_the_title($assessment_id);
$pdf_stylesheet = file_get_contents(WP_ASSESSMENT_ASSETS . '/css/report-pdf-style.css');
$report_template = get_post_meta($assessment_id, 'report_template', true);
$recommentdation = get_post_meta($submission_id, 'recommentdation', true);

// Data position from Ranking
$position_by_total_score = json_decode(get_field('position_by_total_score', $ranking_id), true);
$position_by_industry = json_decode(get_field('position_by_industry', $ranking_id), true);
$position_by_framework = json_decode(get_field('position_by_framework', $ranking_id), true);

// echo "<pre>";
// print_r($position_by_framework);
// echo "</pre>";
// die;

// Include Stylesheet
$mpdf->WriteHTML($pdf_stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

// Define the Header/Footer before writing anything so they appear on the first page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-footer.php';

// Render Front page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-front-page.php';

// Render Table of content
if ($report_template['is_include_toc'] == true) { 
    $mpdf->TOCpagebreak();
}

// Render All generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page.php';

// Render Key Recommendations
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-key-recommendations.php';

// Render Highlights table
// require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-highlights.php';

//=========== Begin Part A - Organisation Dashboard =============//
// Render Organisation Total Score
// require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-org-total-score.php';

// Render Self-assessed score and final Australian Network on Disability score 
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-self-assessed-score.php';

// Output a PDF file directly to the browser
$mpdf->Output();
















 
