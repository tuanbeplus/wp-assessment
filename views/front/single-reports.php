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
$report = new WP_Report_PDF();
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$ranking_id = get_ranking_of_assessment($assessment_id);
$org_data = get_post_meta($post_id, 'org_data', true);
$org_score = get_post_meta($submission_id, 'org_score', true);
$org_section_score = get_post_meta($submission_id, 'org_section_score', true);
$agreed_score = get_post_meta($submission_id, 'agreed_score', true);
$assessment_title = get_the_title($assessment_id);
$col_key_areas = get_assessment_key_areas($assessment_id);
$recommentdation = get_post_meta($submission_id, 'recommentdation', true);
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
$count_index = count_all_index_submissions_finalised($assessment_id);
$report_template = get_post_meta($post_id, 'report_template', true);
if (empty($report_template)) {
    $report_template = get_post_meta($assessment_id, 'report_template', true);
}

// Data position from Ranking
$position_by_total_score = $main->wpa_unserialize_metadata(get_field('position_by_total_score', $ranking_id));
$position_by_industry = $main->wpa_unserialize_metadata(get_field('position_by_industry', $ranking_id));
$position_by_framework = $main->wpa_unserialize_metadata(get_field('position_by_framework', $ranking_id));

$mpdf = new \Mpdf\Mpdf([
    'margin_bottom' => 24,
]);

// Include Stylesheet
$pdf_stylesheet = file_get_contents(WP_ASSESSMENT_ASSETS . '/css/report-pdf-style.css');
$mpdf->WriteHTML($pdf_stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

// Set margin for all page (left, right, top, bottom)
$mpdf->SetMargins(20, 20, 30, 30);

// Define the Header/Footer before writing anything so they appear on the first page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-footer.php';

// Render Front page
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-front-page.php';

// Render Table of content
if (isset($report_template['is_include_toc'])) {
    if ($report_template['is_include_toc'] == true) { 
        $mpdf->TOCpagebreakByArray(
            array(
                'links' => true,
            ),
        );
    }
}

// Render All before generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page-before.php';

// Render Key Recommendations
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-key-recommendations.php';

// Render Highlights table
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-highlights.php';

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

//============== Begin Part B - Evaluation Findings =============//
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-part-b-evaluation-findings.php';
//============== End Part B - Evaluation Findings =============//

// Render All after generic pages
require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-generic-page-after.php';

// Output a PDF file directly to the browser
// $mpdf->Output('Access_and_Inclusion_Index_Comprehensive_Roadmap_Report_2023_'.$org_data['Name'].'.pdf', 'D');
$mpdf->Output();
















 
