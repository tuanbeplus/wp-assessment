<?php
/**
 * The template for displaying Part B - Evaluation Findings Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$part_b_intro =
'<div class="page">
    <h2>Part B - Evaluation Findings</h2>
    <p>The Access and Inclusion Index comprises of nine key areas determined to drive 
        the greatest benefits for access and inclusion of people with disability. 
        The listing of the ten areas below is hyperlinked for your convenience.
    </p>
    <ul style="list-style:decimal;">';
foreach ($position_by_framework as $index => $key_area) {
    $part_b_intro .=
        '<li>
            <a href="#'. $key_area['title'] .'">'. $key_area['title'] .'</a>
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

$key_description = array(
    'Commitment'                    => 'This section of the Index seeks to understand the ways your organisation expresses its commitment to including people with disability across all aspects of your business.',
    'Premises'                      => 'This section of the Index seeks to understand the ways that your organisation ensures that people with disability, as customers, employees, and stakeholders, can access your premises with ease and independence.',
    'Procurement'                   => 'This section of the Index seeks to understand the processes and practices that your organisation takes to support access and inclusion in procurement.',
    'Workplace Adjustments'         => 'This section of the Index seeks to understand the policies and processes that your organisation has in place to support adjustment requests from employees and candidates with disability.',
    'Candidate Experience'          => 'This section of the Index seeks to understand how your organisation equips and resources your recruitment team to be able to identify and eliminate barriers in, and facilitate adjustments for, the recruitment and selection process.',
    'Employee Experience'           => 'This section of the Index seeks to understand how your organisation supports your employees, and people outside of your organisation, with disability to progress and develop their careers.',
    'Customer Experience'           => 'This section of the Index seeks to understand the ways your organisation goes about designing and developing products and services with all customers in mind.',
    'Communication and Marketing'   => 'This section of the Index seeks to understand the accessible communications strategies that your organisation uses to engage with your customers and employees with disability.',
    'Digital Accessibility'         => "The section of the Index seeks to understand your organisation's commitment to providing accessible information technology and the processes you have in place to support this.",
);

// Part B Items
foreach ($position_by_framework as $index => $key_area) {
    // Add to table of contents
    $mpdf->TOC_Entry($key_area['title'] ,1);

    $max_score = array();
    foreach ($questions[$index]['list'] as $quiz) {
        $max_score[] = $quiz['point'] * 4;
    }
    $org_self_percent = round(array_sum($org_score[$index]) / array_sum($max_score) * 100);
    $part_b_item .= 
    '<div class="page">
        <a name="'. $key_area['title'] .'"><h3>'. $key_area['title'] .'</h3></a>
        <p>'. $key_description[$key_area['title']] .'</p>
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



