<?php 
/**
 * The template for displaying Maturity Level for Framework, Implementation, Review and Innovation
 *
 * @author TapTC
 * 
 */

$maturity_level = get_post_meta($submission_id, 'maturity_level', true);

$table_html = "";
foreach ($position_by_framework as $parent_id => $parent_question) {
    $overall_lv = "";
    if ( isset($maturity_level[$parent_question['average_maturity_level']]) ) {
        $ov_lv = get_maturity_level_org_step_2( $parent_question['average_maturity_level'] );
        $overall_lv = isset($ov_lv) ? "Level ".$ov_lv : '';
    }
    $framework_lv = "";
    if ( isset($maturity_level[$parent_id]['Framework']) ) {
        $fr_mat_lv = get_maturity_level_org_step_2( $maturity_level[$parent_id]['Framework'] );
        $framework_lv = isset($fr_mat_lv) ? "Level ".$fr_mat_lv : '';
    }
    $implementation_lv = "";
    if ( isset($maturity_level[$parent_id]['Implementation']) ) {
        $ip_mat_lv = get_maturity_level_org_step_2( $maturity_level[$parent_id]['Implementation'] );
        $implementation_lv = isset($ip_mat_lv) ? "Level ".$ip_mat_lv : '';
    }
    $review_lv = "";
    if ( isset($maturity_level[$parent_id]['Review']) ) {
        $rv_mat_lv = get_maturity_level_org_step_2( $maturity_level[$parent_id]['Review'] );
        $review_lv = isset($rv_mat_lv) ? "Level ".$rv_mat_lv : '';
    }
    $innovation_lv = "";
    if ( isset($maturity_level[$parent_id]['Innovation']) ) {
        $in_mat_lv = get_maturity_level_org_step_2( $maturity_level[$parent_id]['Innovation'] );
        $innovation_lv = isset($in_mat_lv) ? "Level ".$in_mat_lv : '';
    }
    $table_html .= "<tr>
                        <td width='20%' style='text-align:right;border-bottom:none;background-color:none;'>
                            ".$parent_question['title']."
                        </td>
                        <td width='16%'>". $framework_lv ."</td>
                        <td width='16%'>". $implementation_lv ."</td>
                        <td width='16%'>". $review_lv ."</td>
                        <td width='16%'>". $innovation_lv ."</td>
                        <td width='16%'>". $overall_lv ."</td>
                 </tr>";
}

$total_index_score = 
"<div class='page'>
    <h3>Maturity Level for Framework, Implementation, Review and Innovation</h3>
    <p>Questions within each of the Key Areas of the Index are grouped into four sections: Framework, Implementation, Review and Innovation (Employee Experience and Customer Experience only). Table 8 provides an overview of your maturity level for each of the four sections.</p>
    <table class='table-5'>
        <tr>
            <th width='20%'>Key Area</th>
            <th width='16%'>Framework</th>
            <th width='16%'>Implementation</th>
            <th width='16%'>Review</th>
            <th width='16%'>Innovation</th>
            <th width='16%'>Overall</th>
        </tr>
        ".$table_html."
    </table>
    <caption class='table-caption'>Table 8 - Maturity level for Framework, Implementation and Review</caption>
</div>";

// Add to table of contents
$mpdf->TOC_Entry('Maturity Level for Framework, Implementation, Review and Innovation' ,1);

// Render HTML
$mpdf->WriteHTML($total_index_score);