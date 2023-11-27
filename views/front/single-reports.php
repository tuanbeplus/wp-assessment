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
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$org_data = get_post_meta($post_id, 'org_data', true);
$assessment_title = get_the_title($assessment_id);
$pdf_stylesheet = file_get_contents(WP_ASSESSMENT_ASSETS . '/css/report-pdf-style.css');
$report_template = get_post_meta($assessment_id, 'report_template', true);
$recommentdation = get_post_meta($submission_id, 'recommentdation', true);
// $questions = get_post_meta($assessment_id, 'question_group_repeater', true);
// $questions = $main->wpa_unserialize_metadata($questions);

// echo "<pre>";
// print_r($recommentdation);
// echo "</pre>";
// die;

// Include Stylesheet
$mpdf->WriteHTML($pdf_stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

// Define the Header/Footer before writing anything so they appear on the first page
$mpdf->SetHTMLHeader(
    '<div class="header" style="font-family:"Avenir-Roman";">
        <img class="logo" src="/wp-content/plugins/wp-assessment/assets/images/and-logo-hor.png" 
            style="width:200px;opacity:0.6;">
    </div>'
);

// Define the Header/Footer before writing anything so they appear on the first page
if (!empty($report_template['footer'])) {
    $mpdf->SetHTMLFooter(
        '<div class="footer">'
            .$report_template['footer'].
        '</div>'
    );
}
else {
    $mpdf->SetHTMLFooter(
        file_get_contents(WP_ASSESSMENT_ADMIN_VIEW_DIR. '/reports/report-pdf-footer.php')
    );
}

// Render Front page
$front_page = '<div class="front-page page" style="text-align:center;">
    <img width="180" src="'. $report_template['front_page']['logo_url'] .'" alt="">
    <div class="intro">
        <p class="org-name">'. $org_data['Name'] .'</p>
        <p class="title" width="400">'. $report_template['front_page']['title'] .'</p>
        <p class="year">'. date('Y') .'</p>
    </div>'
    .$report_template['front_page']['content'].
'</div>';
$mpdf->WriteHTML($front_page);
$mpdf->AddPage();

// Render Table of content
if ($report_template['is_include_toc'] == true) { 
    // $mpdf->TOCpagebreak();
}

// Render All generic pages
$count = 0;
foreach ($report_template['generic_page'] as $index => $generic_page) {
    $count++;
    $page_content  = '';
    $page_content .= '<div class="page">';
    $page_content .=    '<h2>'. $generic_page['title'] .'</h2>';
    $page_content .=    $generic_page['content'];
    $page_content .= '</div>';

    $mpdf->TOC_Entry($generic_page['title'],0);

    // $mpdf->WriteHTML($page_content);

    // Do not add page break to last page
    // if ($index <div $count) {
        // $mpdf->AddPage();
    // }
}

// Render Key Recommendations
$recom_table  = '<div class="page">';
$recom_table .= '<h2>Key Recommendations</h2>';
$mpdf->TOC_Entry('Key Recommendations' ,0);
$recom_table .= '<p>The below table highlights the top priorities/opportunities identified through the evaluation process for each key area.</p>';
$recom_table .= '<table class="recom-table" width="100%">
                    <tr>
                        <th width="40%">Key Area</th>
                        <th width="60%">Priorities</th>
                    </tr>';
foreach ($recommentdation as $i => $section) {
    $recom_table .= '<tr>
                        <td width="40%">'
                            .$section['key_area'].
                        '</td>
                        <td width="60%">
                            <ul>';
    foreach ($section['list'] as $j => $recom) {
        if (!empty($recom)) {
            $recom_table .=   '<p width="100%">'. $i.'.'.$j.' '.$recom .'</p><br>';
        }
    }
    $recom_table .=         '</ul>';
    $recom_table .=     '</td>';
    $recom_table .= '</tr>';
}
$recom_table .= '</table>';
$recom_table .= '<caption>Table 1 - Key Recommendations</caption>';
$recom_table .= '</div>';

// $mpdf->WriteHTML($recom_table);
// $mpdf->AddPage();

// Begin Part A - Organisation Dashboard
$total_org_score = get_post_meta($submission_id, 'total_submission_score', true);
$overall_org_score = cal_overall_total_score($assessment_id, 'total_submission_score');
$overall_and_score = cal_overall_total_score($assessment_id, 'total_and_score');
$total_index_score = 
"<div class='page'>
    <h2>Part A - Organisational Dashboard</h2>
    <p>
        This section contains an overview of your organisation's performance across
        the nine key areas and the benchmarked data against all participating 
        organisations in 2023.
    </p>
    <h3>Total Index Score</h3>
    <table class='table-3'>
        <tr>
            <th></th>
            <th>Organisation <br> self-assessment <br> (/100)</th>
            <th>AND assessment <br> and final score <br> (/100)</th>
            <th>Rank (/N)</th>
            <th>Average of other <br> organisations</th>
        </tr>
        <tr>
            <td style='text-align:right;border-bottom:none;background-color:none;'>
                Total Index Score
            </td>
            <td>". $total_org_score['percent'] ."</td>
            <td>". $overall_and_score['percent_average'] ."</td>
            <td>4</td>
            <td>". $overall_org_score['percent_average'] ."</td>
        </tr>
    </table>
    <caption class='table-caption'>Table 3 - Total Index Score and Benchmark</caption>
    <p>". $org_data['Name'] ." scored ". $total_org_score['percent'] ."/100 in the Access and Inclusion Index, 
        which ranked [X] overall. The average Access and Inclusion Index score 
        for participating organisations is ". $overall_org_score['percent_average'] .
    ".</p>
    <h3>Industry Benchmark</h3>
    <table class='table-3'>
        <tr>
            <th></th>
            <th>Industry Rank (/N)</th>
            <th>Industry Average</th>
        </tr>
        <tr>
            <td>Industry Benchmark</td>
            <td>1</td>
            <td>56</td>
        </tr>
    </table>
    <caption>Table 4 - Industry Benchmark</caption>
    <p>". $org_data['Name'] ." was ranked [X] against all submitting 
        organisations in the [Industry Name] industry. 
        The average Access and Inclusion Index score for 
        organisations in your industry is [X].</p>
</div>";

// Render Total Index Score section
$mpdf->WriteHTML($total_index_score);

// Output a PDF file directly to the browser
$mpdf->Output();
?>















 
