<?php 
/**
 * The template for displaying Front page (Cover) Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

if (!empty($report_template['front_page'])) {

    // add page break
    $mpdf->AddPageByArray(
        array(
            'margin-top' => 0, 
            'margin-bottom' => 0, 
            'margin-left' => 0,
            'margin-right' => 0,
        ),
    );

    $index_2023 = get_field('assessment_index_2023', 'option');
    $index_2023_id = !empty($index_2023) ? $index_2023 : 17158;
    $org_name = $org_data['Name'] ?? '';
    $front_page_logo_url = $report_template['front_page']['logo_url'] ?? '';
    $front_page_title = $report_template['front_page']['title'] ?? '';
    if (empty($front_page_title)) {
        $front_page_title = 'Report Title';
    }
    $front_sub_title = $report_template['front_page']['heading_2'] ?? '';
    if (empty($front_sub_title)) {
        $front_sub_title = 'Report Sub Title';
    }
    $front_page_bg_img = $report_template['front_page']['bg_img'] ?? '';
    $custom_class = get_post_type();

    $front_page = 
    '<div class="front-page page" style="text-align:center;">
        <img width="180" src="'. esc_url($front_page_logo_url) .'" alt="ADN Logo">
        <div class="intro">
            <p class="org-name">'. esc_html($org_name) .'</p>
            <h1 class="title" width="400">'. esc_html($front_page_title) .'</h1>
            <p class="year">'. date('Y') .'</p>
        </div>'
        .$report_template['front_page']['content'].
    '</div>';

    $front_page_2024 = 
    '<div class="front-page page front-page-2024 '.$custom_class.'" style="background-image:url('. esc_url($front_page_bg_img) .');">
        <div class="content">
            <img width="300" src="'. esc_url($front_page_logo_url) .'" alt="ADN Logo">
            <h1>'. esc_html($front_page_title) .'</h1>
            <h2>'. esc_html($front_sub_title) .'</h2>
            <h2>'. esc_html($org_name) .'</h2>
        </div>
    </div>';

    if ($assessment_id == $index_2023_id) {
        $mpdf->WriteHTML($front_page);
    }
    else {
        $mpdf->WriteHTML($front_page_2024);
    }
}

