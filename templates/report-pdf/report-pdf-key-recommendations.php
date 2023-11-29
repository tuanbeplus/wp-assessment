<?php
/**
 * The template for displaying Key Recommendations page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$recom_table  = '<div class="page">';
$recom_table .= '<h2>Key Recommendations</h2>';
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

// Add to table of contents
$mpdf->TOC_Entry('Key Recommendations' ,0);

// Render HTML
$mpdf->WriteHTML($recom_table);

// Insert page break
$mpdf->AddPage();