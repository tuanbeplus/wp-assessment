<?php
/**
 * The template for displaying Report PDF - Saturn
 *
 * @author Tuan
 * 
 */
 
global $post;
$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$assessment_title = get_the_title($assessment_id);

$mpdf = new \Mpdf\Mpdf();

// Define the Header/Footer before writing anything so they appear on the first page
$mpdf->SetHTMLHeader(
    '<div style="text-align: left;">
        <img src="https://andorg1dev.wpengine.com/wp-content/uploads/2023/09/AND-logo-colour-stacked-1024x729-1.png" alt="" width="100">
    </div>'
);
$mpdf->SetHTMLFooter(
    '<table width="100%">
        <tr>
            <td width="33%">{DATE j-m-Y}</td>
            <td width="33%" align="center">{PAGENO}/{nbpg}</td>
            <td width="33%" style="text-align: right;">My document</td>
        </tr>
    </table>'
);

// Write some HTML code:
$mpdf->WriteHTML(
    '<div width="100%" height="100%" style="background: url(https://img.freepik.com/free-vector/blue-curve-background_53876-113112.jpg?w=1380&t=st=1700644705~exp=1700645305~hmac=547982f3a7257e13979a444b8853307868446ab8f419c934af37ffeb268d38cf);">
        
    </div>'
);
$mpdf->AddPage();

$mpdf->WriteHTML('text page 2');
// $mpdf->AddPage();

// Output a PDF file directly to the browser
$mpdf->Output();
?>

 
