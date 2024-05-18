<?php
/**
 * The template for displaying Key Recommendations page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

if (!empty($recommentdation)) {
    $recom_table  = '<div class="page">';
    $recom_table .= '<h2>Key Recommendations</h2>';
    $recom_table .= '<p>The below table highlights the top priorities/opportunities identified through the evaluation 
                        process for each key area.</p>';
    $recom_table .= '<div class="recommendations-table">
                        <div>
                            <div class="col" style="float:left;width:30%;"><strong>Key Area</strong></div>
                            <div class="col" style="float:right;border-left:1px solid #333"><strong>Priorities</strong></div>
                        </div>';
    foreach ($recommentdation as $i => $section) {
        $recom_table .= '<div>
                            <div class="col" style="float:left;width:30%;">'
                                .$section['key_area'].
                            '</div>
                            <div class="col" style="float:right;border-left:1px solid #333">
                                <ul style="padding:0 0 0 16px;">';
        $list_recom = $section['list'] ?? array();
        if (isset($list_recom) && !empty($list_recom)) {
            foreach ($list_recom as $j => $recom) {
                if (isset($recom) && !empty($recom)) {
                    // Remove HTML attributes
                    $clean_recom = clean_html_report_pdf($recom);
                    // Add recommentdation content to table
                    $recom_table .= '<li>'. $clean_recom .'</li>';
                }
            }
        }
        $recom_table .=         '</ul>';
        $recom_table .=     '</div>';
        $recom_table .= '</div>';
    }
    $recom_table .= '</div>';
    $recom_table .= '<caption>Table 1 - Key Recommendations</caption>';
    $recom_table .= '</div>';

    // Add to table of contents
    $mpdf->TOC_Entry('Key Recommendations' ,0);

    // Render HTML
    $mpdf->WriteHTML($recom_table);

    // Insert page break
    $mpdf->AddPage();
}
