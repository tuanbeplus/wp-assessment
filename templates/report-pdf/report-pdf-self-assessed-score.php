<?php
/**
 * The template for displaying Scorecard for nine key areas Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$self_assessed_score =
'<div class="page">
    <h3>Self-assessed score and final Australian Disability Network score</h3>
    <p>The self-assessed score and Australian Disability Network score 
        have been provided as maturity levels across the nine key areas 
        (Table 5) and percentage scores (Table 6). Please note the percentage 
        scores in Table 6 have been rounded up.
    </p>
    <table class="table-3 table-5">
        <tr>
            <th width="40%">Key Area</th>
            <th width="30%">Organisation <br> self-assessment</th>
            <th width="30%">AND <br> assessed level</th>
        </tr>';
foreach ($position_by_framework as $index => $key_area) {
    $org_self_score = 0;
    $and_assessed_score = 0;

    // Average Org score in a Key area
    if (isset($org_section_score[$index])) {
        $org_self_score = get_maturity_level_org($org_section_score[$index]);
    }
    // Final AND agreed score level
    $and_assessed_level = get_maturity_level_org($agreed_gr_score_with_weighting[$index]) ?? '1';
    $self_assessed_score .=
        '<tr>
            <td width="40%" style="font-style:italic;border-bottom:none;border-left:none;background:none;">'
                . $key_area['title'] .
            '</td>
            <td width="30%">'. $org_self_score .'</td>
            <td width="30%">'. $and_assessed_level .'</td>
        </tr>';
}
$self_assessed_score .=
    '</table>
    <caption>Table 5 - Scorecard for nine key areas shown as maturity levels</caption>
</div>';

// Add to table of contents
$mpdf->TOC_Entry('Self-assessed score and final Australian Disability Network score' ,1);

// Render HTML
$mpdf->WriteHTML($self_assessed_score);

// Insert page break
$mpdf->AddPage();

$cal_org_score = cal_scores_with_weighting_for_percentages($assessment_id, $org_score, 'sub') ?? array();
$cal_agreed_score = cal_scores_with_weighting_for_percentages($assessment_id, $agreed_score, 'sub') ?? array();
$self_assessed_percent_table =
'<div class="page">
    <table class="table-3 table-5">
        <tr>
            <th width="40%">Key Area</th>
            <th width="30%">Organisation <br> self-assessment</th>
            <th width="30%">AND <br> assessed score</th>
        </tr>';
foreach ($questions as $index => $key_area) {

    $max_score = array();
    $org_self_percent = 0;
    $and_assessed_percent = 0;

    foreach ($key_area['list'] as $quiz) {
        $point = is_numeric($quiz['point']) ? (float)$quiz['point'] : 0;
        $max_score[] = $point * 4;
    }

    // Average Org score in a Key area
    if (is_array($cal_org_score[$index])) {
        $org_self_percent = round(array_sum($cal_org_score[$index]) / array_sum($max_score) * 100);
    }
    
    // Average Agreed score in a Key area
    if (is_array($cal_agreed_score[$index])) {
        $and_assessed_percent = round(array_sum($cal_agreed_score[$index]) / array_sum($max_score) * 100);
    }
    
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
