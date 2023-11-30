<?php 
/**
 * The template for displaying Maturity Level for Framework, Implementation, Review and Innovation
 *
 * @author TapTC
 * 
 */

$maturity_level = get_post_meta($submission_id, 'maturity_level', true);

$wp_ass = new WP_Assessment();
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $wp_ass->wpa_unserialize_metadata($questions);
$table_html = "";
foreach ($questions as $parent_id => $parent_question) {
    $parent_title = htmlentities(stripslashes(utf8_decode( $parent_question['title'] )));
    $parent_title = $parent_title;

    $framework_lv = ( $maturity_level[$parent_id]['Framework'] ) ? "Level ". $maturity_level[$parent_id]['Framework'] : "";
    $implementation_lv = ( $maturity_level[$parent_id]['Implementation'] ) ? "Level ". $maturity_level[$parent_id]['Implementation'] : "";
    $review_lv = ( $maturity_level[$parent_id]['Review'] ) ? "Level ". $maturity_level[$parent_id]['Review'] : "";
    $innovation_lv = ( $maturity_level[$parent_id]['Innovation'] ) ? "Level ". $maturity_level[$parent_id]['Innovation'] : "";
    $table_html .= "<tr>
                        <td style='text-align:right;border-bottom:none;background-color:none;'>
                            ".$parent_title."
                        </td>
                        <td>". $framework_lv ."</td>
                        <td>". $implementation_lv ."</td>
                        <td>". $review_lv ."</td>
                        <td>". $innovation_lv ."</td>
                        <td>Tomorrow do this</td>
                 </tr>";
}

$total_index_score = 
"<div class='page'>
    <h3>Maturity Level for Framework, Implementation, Review and Innovation</h3>
    <p>Questions within each of the Key Areas of the Index are grouped into four sections: Framework, Implementation, Review and Innovation (Employee Experience and Customer Experience only). Table 8 provides an overview of your maturity level for each of the four sections.</p>
    <table class='table-3'>
        <tr>
            <th>Key Area</th>
            <th>Framework</th>
            <th>Implementation</th>
            <th>Review</th>
            <th>Innovation</th>
            <th>Overall</th>
        </tr>
        ".$table_html."
    </table>
    <caption class='table-caption'>Table 8 - Maturity level for Framework, Implementation and Review</caption>
</div>";

// Add to table of contents
$mpdf->TOC_Entry('Maturity Level for Framework, Implementation, Review and Innovation' ,1);

// Render HTML
$mpdf->WriteHTML($total_index_score);