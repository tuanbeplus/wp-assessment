<?php 
/**
 * The template for displaying All Generic page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$count = 0;
foreach ($report_template['generic_page'] as $index => $generic_page) {
    $count++;
    $page_content  = '';
    $page_content .= '<div class="page">';
    $page_content .=    '<h2>'. $generic_page['title'] .'</h2>';
    $page_content .=    $generic_page['content'];
    $page_content .= '</div>';

    $mpdf->TOC_Entry($generic_page['title'],0);
    $mpdf->WriteHTML($page_content);

    // Do not add page break to last page
    if ($index < $count) {
        $mpdf->AddPage();
    }
}