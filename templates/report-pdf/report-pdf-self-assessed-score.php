<?php
/**
 * The template for displaying All Generic page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$self_assessed_score =
'<div class="page">
    <h3>Self-assessed score and final Australian Network on Disability score</h3>
    <p>The self-assessed score and Australian Network on Disability score 
        have been provided as maturity levels across the nine key areas 
        (Table 5) and percentage scores (Table 6). Please note the percentage 
        scores in Table 6 have been rounded up.
    </p>
    <table class="table-5">
        <tr>
            <th width="40%">Key Area</th>
            <th width="30%">Organisation self-assessment</th>
            <th width="30%">AND assessed level</th>
        </tr>';
foreach ($position_by_framework as $key_area) {
    $self_assessed_score .=
        '<tr>
            <td width="40%" style="text-align:right; font-style:italic;">'
                . $key_area['title'] .
            '</td>
            <td width="30%">'. $key_area['parent_questions'][$org_data['Id']]['level'] .'</td>
            <td width="30%"></td>
        </tr>';
}
$self_assessed_score .=
    '</table>
    <caption>Table 5 - Scorecard for nine key areas shown as maturity levels</caption>
</div>';

// Add to table of contents
$mpdf->TOC_Entry('Self-assessed score and final Australian Network on Disability score' ,1);

// Render HTML
$mpdf->WriteHTML($self_assessed_score);

// Insert page break
$mpdf->AddPage();
?>
