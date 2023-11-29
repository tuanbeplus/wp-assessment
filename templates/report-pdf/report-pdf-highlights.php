<?php
/**
 * The template for displaying Key Recommendations page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$highlights_table = 
'<div class="page">
    <h2>Highlights</h2>
    <p>The below table highlights existing strengths at [Organisation] 
        identified through the evaluation process for each key area. 
    </p>
    <table class="recom-table" width="100%">
        <tr>
            <th width="40%">Key Area</th>
            <th width="60%">Strengths</th>
        </tr>';
    foreach ($recommentdation as $section){
        $highlights_table .= 
        '<tr>
            <td width="40%">'. $section['key_area'] .'</td>
            <td width="60%"></td>
        </tr>';
    }
$highlights_table .= 
    '</table>
    <caption>Table 2 - Highlights</caption>
</div>';

$mpdf->TOC_Entry('Highlights' ,0);
$mpdf->WriteHTML($highlights_table);
$mpdf->AddPage();