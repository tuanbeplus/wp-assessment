<?php 
/**
 * The template for displaying Maturity Level for Framework, Implementation, Review and Innovation
 *
 * @author TapTC & Tuan
 * 
 */

$maturity_level = get_post_meta($submission_id, 'maturity_level', true);

$table_html = "";
foreach ($questions as $gr_id => $gr_field) {
    // Framework
    $framework_lv = $maturity_level[$gr_id]['Framework'] ?? '';
    if (isset($framework_lv)) {
        if (empty($framework_lv) || $framework_lv == 0) {
            $framework_lv = 'Level 1';
        }
        else {
            $framework_lv = 'Level '.$framework_lv;
        }
    }
    // Implementation
    $implementation_lv = $maturity_level[$gr_id]['Implementation'] ?? '';
    if (isset($implementation_lv)) {
        if (empty($implementation_lv) || $implementation_lv == 0) {
            $implementation_lv = 'Level 1';
        }
        else {
            $implementation_lv = 'Level '.$implementation_lv;
        }
    }
    // Review
    $review_lv = $maturity_level[$gr_id]['Review'] ?? '';
    if (isset($review_lv)) {
        if (empty($review_lv) || $review_lv == 0) {
            $review_lv = 'Level 1';
        }
        else {
            $review_lv = 'Level '.$review_lv;
        }
    }
    // Innovation
    $innovation_lv = $maturity_level[$gr_id]['Innovation'] ?? '';
    if (isset($innovation_lv)) {
        if (empty($innovation_lv) || $innovation_lv == 0) {
            $innovation_lv = 'Level 1';
        }
        else {
            $innovation_lv = 'Level '.$innovation_lv;
        }
    }
    // Overall
    $overall_lv = 'Level '.get_maturity_level_org($agreed_gr_score_with_weighting[$gr_id]) ?? 'Level 1';

    $table_html .= "<tr>
                        <td width='20%' style='text-align:right;border-bottom:none;background-color:none;font-style:italic;'>
                            ".$gr_field['title']."
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

// Insert page break
$mpdf->AddPage();