<?php 
/**
 * The template for displaying Maturity Level for Framework, Implementation, Review and Innovation
 *
 * @author TapTC
 * 
 */

$total_org_score = get_post_meta($submission_id, 'total_submission_score', true);
$overall_org_score = cal_overall_total_score($assessment_id, 'total_submission_score');
$overall_and_score = cal_overall_total_score($assessment_id, 'total_and_score');
$org_score_rank = $position_by_total_score[$org_data['Id']]['org_rank'];
$org_industry_rank = $position_by_industry['rank_data'][$org_data['Id']]['org_rank'];
$average_industry = cal_average_industry_score($position_by_industry['by_indus_data'][$org_data['Industry']]);

$total_index_score = 
"<div class='page'>
    <h3>Maturity Level for Framework, Implementation, Review and Innovation</h3>
    <table class='table-3'>
        <tr>
            <th></th>
            <th>Organisation <br> self-assessment <br> (/100)</th>
            <th>AND assessment <br> and final score <br> (/100)</th>
            <th>Rank (/N)</th>
            <th>Average of other <br> organisations</th>
        </tr>
        <tr>
            <td style='text-align:right;border-bottom:none;background-color:none;'>
                Total Index Score
            </td>
            <td>". $total_org_score['percent'] ."</td>
            <td>". $overall_and_score['percent_average'] ."</td>
            <td>". $org_score_rank ."</td>
            <td>". $overall_org_score['percent_average'] ."</td>
        </tr>
    </table>
    <caption class='table-caption'>Table 3 - Total Index Score and Benchmark</caption>
    <p>". $org_data['Name'] ." scored ". $total_org_score['percent'] ."/100 in the Access and Inclusion Index, 
        which ranked ". $org_score_rank ." overall. The average Access and Inclusion Index score 
        for participating organisations is ". $overall_org_score['percent_average'] .
    ".</p>
</div>";

// Add to table of contents
$mpdf->TOC_Entry('Maturity Level for Framework, Implementation, Review and Innovation' ,1);

// Render HTML
$mpdf->WriteHTML($total_index_score);