<?php 
/**
 * The template for displaying All before Generic page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$generic_page_before_list = $report_template['generic_page_before'] ?? array();
if (isset($generic_page_before_list) && !empty($generic_page_before_list)) {

    foreach ($generic_page_before_list as $index => $generic_page) {

        $year = date('Y',strtotime($org_data['CreatedDate']));
        $content = $generic_page['content'];
        $content = str_replace('[Organisation]', $org_data['Name'], $content);
        $content = str_replace('[organisation]', $org_data['Name'], $content);
        $content = str_replace('[membership level]', $org_data['Membership_Level__c'], $content);
        $content = str_replace('[membership level]', $org_data['Membership_Level__c'], $content);
        $content = str_replace('[Membership Level]', $org_data['Membership_Level__c'], $content);
        $content = str_replace('[Membership level]', $org_data['Membership_Level__c'], $content);
        $content = str_replace('[Year]', $year, $content);
        $content = str_replace('[year]', $year, $content);
        // Remove HTML attributes
        $content = clean_html_report_pdf($content);
    
        $page_content  = '';
        $page_content .= '<div class="page generic-page">';
        $page_content .=    '<h2>'. $generic_page['title'] .'</h2>';
        $page_content .=    $content;
        $page_content .= '</div>';
    
        $mpdf->TOC_Entry($generic_page['title'],0);
        $mpdf->WriteHTML($page_content);
    
        // add page break
        $mpdf->AddPage();
    }
}
