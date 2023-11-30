<?php
/**
 * The template for displaying Part B - Evaluation Findings Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$part_b_intro =
'<div class="page">
    <h2 id="Part_B_Evaluation_Findings">Part B - Evaluation Findings</h2>
    <p>The Access and Inclusion Index comprises of nine key areas determined to drive 
        the greatest benefits for access and inclusion of people with disability. 
        The listing of the ten areas below is hyperlinked for your convenience.
    </p>
    <ul style="list-style:decimal;">';
foreach ($recommentdation as $index => $key_area) {
    $part_b_intro .=
        '<li>
            <a href="#'. $key_area['key_area'] .'">'. $key_area['key_area'] .'</a>
        </li>';
}
$part_b_intro .=   
    '</ul>
    <p>What follows is an evaluation of your assessment, with particular emphasis given 
        to provide suggestions that support your organisation to build disability 
        confidence in identified areas.
    </p>
</div>';

// Add to table of contents
$mpdf->TOC_Entry('Part B - Evaluation Findings' ,0);

// Render HTML
$mpdf->WriteHTML($part_b_intro);

// Part B Items
foreach ($position_by_framework as $index => $key_area) {
    // Add to table of contents
    $mpdf->TOC_Entry($key_area['title'] ,1);
    $org_self_score = array_sum($org_score[$index]) / count($org_score[$index]);
    $org_self_percent = round($org_self_score*100);
    $part_b_item .= 
    '<div class="page">
        <h3>'. $key_area['title'] .'</h3>
        <p>This section of the Index seeks to understand the ways your 
            organisation expresses its commitment to including people with
            disability across all aspects of your business.
        </p>
        <p>'. $org_data['Name'] .' scored '. $org_self_percent .'% and achieved an overall '
            .$key_area['parent_questions'][$org_data['Id']]['org_rank'].
            ' (Level '. get_maturity_level_org_step_2($key_area['parent_questions'][$org_data['Id']]['level']) .
            ') maturity level in the '
            . $key_area['title'] .' key area. 
        </p>
        <p>[Summarise strengths and opportunities for development in 
            this Key Area] (e.g. where they are doing well in this 
            area and where they can improve).
        </p>';
    foreach ($col_key_areas as $key) {
        // Add to table of contents
        $mpdf->TOC_Entry($key ,2);
        $part_b_item .=
        '<h4>'. $key .'</h4>
        <p>Auto Generated Responses</p>';
    }
    $part_b_item .=
    '</div>';
}

// Render HTML
$mpdf->WriteHTML($part_b_item);
?>



