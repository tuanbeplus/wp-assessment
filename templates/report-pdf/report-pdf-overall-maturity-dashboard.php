<?php 
/**
 * The template for displaying Maturity Level for Framework, Implementation, Review and Innovation
 *
 * @author TapTC
 * 
 */

$table_html = "";
foreach ($position_by_framework as $parent_id => $parent_question) {
    $pr_orgs = $parent_question['parent_questions'];
    $org_maturity_lv = $pr_orgs[$org_data['Id']]['level'];
    $average_maturity_lv = $parent_question['average_maturity_level'];
    $variance = $org_maturity_lv - $average_maturity_lv;
    $table_html .= "<tr>
                        <td style='text-align:right;border-bottom:none;background-color:none;'>
                            ".$parent_question['title']."
                        </td>
                        <td>". $org_maturity_lv ."</td>
                        <td>". $average_maturity_lv ."</td>
                        <td>". $variance ."</td>
                    </tr>";
}

$total_index_score = 
"<div class='page'>
    <h3>Overall Maturity Dashboard</h3>
    <table class='table-3'>
        <tr>
            <th>Overall Maturity by Key Area</th>
            <th>Your Organisation Maturity Level</th>
            <th>Average Maturity Level - all organisations</th>
            <th>Variance (+/-)</th>
        </tr>
        ".$table_html."
    </table>
    <caption class='table-caption'>Table 9 - Overall maturity level</caption>
</div>";

// Add to table of contents
$mpdf->TOC_Entry('Overall Maturity Dashboard' ,1);

// Render HTML
$mpdf->WriteHTML($total_index_score);