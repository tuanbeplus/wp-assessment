<?php
/**
 * The template for displaying Benchmark Results Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$benchmark_results =
'<div class="page">
    <h3>Benchmark Results</h3>
    <p>The scorecard in this section is an overview of the final 2023 Index score 
        for your organisation in comparison to the performance of all participating 
        organisations with respect to the nine Key Areas. Table 7 provides an overall 
        ranking for your organisation within each area. 
    </p>
    <table class="table-5">
        <tr>
            <th width="30%">Key Area</th>
            <th width="17%">Maturity Level</th>
            <th width="13%">Rank<br>(/'.$count_index.' )</th>
            <th width="10%">Orgs<br>at<br>Level<br>1</th>
            <th width="10%">Orgs<br>at<br>Level<br>2</th>
            <th width="10%">Orgs<br>at<br>Level<br>3</th>
            <th width="10%">Orgs<br>at<br>Level<br>4</th>
        </tr>';
foreach ($position_by_framework as $index => $key_area) {

    $maturity_level = 'Level '.get_maturity_level_org($agreed_gr_score_with_weighting[$index]) ?? 'Level 1';
    $org_rank = $key_area['parent_questions'][$org_data['Id']]['org_rank'] ?? '';
    $org_at_levels = '';
    $org_at_levels = $key_area['org_at_levels'];
    $benchmark_results .=
        '<tr>
            <td width="30%" style="font-style:italic;border-bottom:none;border-left:none;background:none;">'
                . $key_area['title'] .
            '</td>
            <td width="17%">'. $maturity_level .'</td>
            <td width="13%">'. $org_rank .'</td>
            <td width="10%">'. $org_at_levels['level1'] .'</td>
            <td width="10%">'. $org_at_levels['level2'] .'</td>
            <td width="10%">'. $org_at_levels['level3'] .'</td>
            <td width="10%">'. $org_at_levels['level4'] .'</td>
        </tr>';
    $org_at_levels = null;
}
$benchmark_results .=
    '</table>
    <caption>Table 7 - Benchmark results for the nine Key Areas</caption>
</div>';

// Add to table of contents
$mpdf->TOC_Entry('Benchmark Results' ,1);

// Render HTML
$mpdf->WriteHTML($benchmark_results);

// Insert page break
$mpdf->AddPage();