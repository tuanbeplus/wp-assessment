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
    <table class="table-3 table-5">
        <tr>
            <th width="40%">Key Area</th>
            <th width="30%">Organisation self-assessment</th>
            <th width="30%">AND assessed level</th>
        </tr>';
foreach ($position_by_framework as $index => $key_area) {
    $and_assessed_score = array_sum($agreed_score[$index]) / count($agreed_score[$index]);
    $and_assessed_level = get_maturity_level_org($and_assessed_score);
    $self_assessed_score .=
        '<tr>
            <td width="40%" style="font-style:italic;border-bottom:none;border-left:none;background:none;">'
                . $key_area['title'] .
            '</td>
            <td width="30%">'. $key_area['parent_questions'][$org_data['Id']]['level'] .'</td>
            <td width="30%">'. $and_assessed_level .'</td>
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

$self_assessed_percent_table =
'<div class="page">
    <table class="table-3 table-5">
        <tr>
            <th width="40%">Key Area</th>
            <th width="30%">Organisation self-assessment</th>
            <th width="30%">AND assessed score</th>
        </tr>';
foreach ($questions as $index => $key_area) {

    $max_score = array();
    foreach ($key_area['list'] as $quiz) {
        $max_score[] = $quiz['point'] * 4;
    }
    // Average Org score in a Key area
    $org_self_percent = round(array_sum($org_score[$index]) / array_sum($max_score) * 100);

    // Average Agreed score in a Key area
    $and_assessed_percent = round(array_sum($agreed_score[$index]) / array_sum($max_score) * 100);
    $self_assessed_percent_table .=
        '<tr>
            <td width="40%" style="font-style:italic;border-bottom:none;border-left:none;background:none;">'
                . $key_area['title'] .
            '</td>
            <td width="30%">'. $org_self_percent .' %</td>
            <td width="30%">'. $and_assessed_percent .' %</td>
        </tr>';
}
$self_assessed_percent_table .=
    '</table>
    <caption>Table 6 - Scorecard for the nine Key Areas shown as percentages</caption>
    <p>Your self-assessed score and the AND evaluated score may have differed. This can be attributed to reasons such as:</p>
    <ul style="list-style:upper-roman;">
        <li>Insufficient evidence was provided to accurately validate your self-assessment.</li>
        <li>We could not find the answer within the evidence provided.</li>
        <li>Varying interpretation of the Index questions.</li>
    </ul>
</div>';

// Render HTML
$mpdf->WriteHTML($self_assessed_percent_table);

// Insert page break
$mpdf->AddPage();
?>


