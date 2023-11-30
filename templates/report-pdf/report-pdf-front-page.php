<?php 
/**
 * The template for displaying Front page Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$front_page = 
'<div class="front-page page" style="text-align:center;">
    <img width="180" src="'. $report_template['front_page']['logo_url'] .'" alt="">
    <div class="intro">
        <p class="org-name">'. $org_data['Name'] .'</p>
        <h1 class="title" width="400" style="font-weight:bold;">'
            .$report_template['front_page']['title'].
        '</h1>
        <p class="year">'. date('Y') .'</p>
    </div>'
    .$report_template['front_page']['content'].
'</div>';
$mpdf->WriteHTML($front_page);
$mpdf->AddPage();

