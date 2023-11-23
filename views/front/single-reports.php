<?php
/**
 * The template for displaying Report PDF - Saturn
 *
 * @author Tuan
 * 
 */
 
global $post;
$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$submission_id = get_post_meta($post_id, 'submission_id', true);
$org_data = get_post_meta($post_id, 'org_data', true);
$assessment_title = get_the_title($assessment_id);
$pdf_stylesheet = file_get_contents(WP_ASSESSMENT_ASSETS . '/css/report-pdf-style.css');
$report_template = get_post_meta($assessment_id, 'report_template', true);
$mpdf = new \Mpdf\Mpdf();

// echo "<pre>";
// print_r($report_template);
// echo "</pre>";

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
$mpdf->WriteHTML(
    '<div class="front-page page">
        <div class="intro">
            <p class="org-name">'. $org_data['Name'] .'</p>
            <p class="title">'. $report_template['front_page']['title'] .'</p>
            <p class="year">'. date('Y') .'</p>
        </div>'
        . $report_template['front_page']['content'] .
    '</div>'
);
$mpdf->AddPage();

// Render All generic pages
$count = 0;
foreach ($report_template['generic_page'] as $index => $generic_page) {
    $count++;
    $page_content  = '';
    $page_content .= '<div class="page">';
    $page_content .=    '<h2>'. $generic_page['title'] .'</h2>';
    $page_content .=    $generic_page['content'];
    $page_content .= '</div>';

    $mpdf->WriteHTML($page_content);

    // Do not add page break to last page
    if ($index < $count) {
        $mpdf->AddPage();
    }
}

// Output a PDF file directly to the browser
$mpdf->Output();
?>







 
