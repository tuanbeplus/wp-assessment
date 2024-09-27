<?php 
/**
 * The template for displaying Total Index Score & Industry Benchmark page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$total_org_score = get_post_meta($submission_id, 'total_submission_score', true);
$total_agreed_score = get_post_meta($submission_id, 'total_agreed_score', true);
$all_agreed_score = cal_overall_total_score($assessment_id, 'total_agreed_score');
$overall_and_score = cal_overall_total_score($assessment_id, 'total_and_score');
$org_score_rank = $position_by_total_score[$org_data['Id']]['org_rank'] ?? '';
$org_industry_rank = $position_by_industry['rank_data'][$org_data['Id']]['org_rank'] ?? '';
$average_industry = cal_average_industry_score($assessment_id, $position_by_industry['by_indus_data'][$org_data['Industry']]) ?? '';

$total_org_score_percent   = isset($total_org_score['percent']) ? $total_org_score['percent'] : '';
$total_agreed_score_percent = isset($total_agreed_score['percent']) ? $total_agreed_score['percent'] : '';
$all_agreed_score_average = isset($all_agreed_score['percent_average']) ? $all_agreed_score['percent_average'] : '';

$count_org_industry = 0;
if (isset($position_by_industry['rank_data']) && !empty($position_by_industry['rank_data'])) {
    foreach ($position_by_industry['rank_data'] as $org_industry_data) {
        if ($org_industry_data['industry_name'] == $org_data['Industry']) {
            $count_org_industry++;
        }
    }
}
else {
    $count_org_industry = 'N';
}

$total_index_score = 
"<div class='page'>
    <h2>Part A - Organisational Dashboard</h2>
    <p>
        This section contains an overview of your organisation's performance across
        the nine key areas and the benchmarked data against all participating 
        organisations in 2023.
    </p>
    <h3>Total Index Score</h3>
    <table class='table-3'>
        <tr>
            <th></th>
            <th>Organisation <br> self-assessment <br> (/100)</th>
            <th>AND assessment <br> and final score <br> (/100)</th>
            <th>Rank (/".$count_index.")</th>
            <th>Average of other <br> organisations <br> (/100)</th>
        </tr>
        <tr>
            <td style='border-bottom:none;background-color:none;'>
                Total Index Score
            </td>
            <td>". $total_org_score_percent ."</td>
            <td>". $total_agreed_score_percent ."</td>
            <td>". $org_score_rank ."</td>
            <td>". $all_agreed_score_average ."</td>
        </tr>
    </table>
    <caption class='table-caption'>Table 3 - Total Index Score and Benchmark</caption>
    <p>". $org_data['Name'] ." scored ". $total_agreed_score_percent ."/100 in the Access and Inclusion Index, 
        which ranked ". $org_score_rank ." overall. The average Access and Inclusion Index score 
        for participating organisations is ". $all_agreed_score_average .
    ".</p>

    <h3>Industry Benchmark</h3>
    <table class='table-3'>
        <tr>
            <th></th>
            <th>Industry Rank (/".$count_org_industry.")</th>
            <th>Industry Average</th>
        </tr>
        <tr>
            <td style='text-align:right;border-bottom:none;background-color:none;'>
                Industry Benchmark
            </td>
            <td>". $org_industry_rank ."</td>
            <td>". $average_industry ."</td>
        </tr>
    </table>
    <caption>Table 4 - Industry Benchmark</caption>
    <p>". $org_data['Name'] ." was ranked ". $org_industry_rank ." against all submitting 
        organisations in the ". $org_data['Industry'] ." industry. 
        The average Access and Inclusion Index score for 
        organisations in your industry is ". $average_industry .".</p>
</div>";

// Add to table of contents
$mpdf->TOC_Entry('Part A - Organisational Dashboard' ,0);
$mpdf->TOC_Entry('Total Index Score' ,1);
$mpdf->TOC_Entry('Industry Benchmark' ,1);

// Render HTML
$mpdf->WriteHTML($total_index_score);

// Insert page break
$mpdf->AddPage();