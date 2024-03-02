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
                            <div class="col" style="float:right;border-left:1px solid #333">';
        foreach ($section['list'] as $j => $recom) {
            if (!empty($recom)) {
                $recom_table .= '<div>'. $i.'.'.$j.' '.$recom .'</div><br>';
            }
            else {
                $recom_table .= '<span>&nbsp;</span>';
            }
        }
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
