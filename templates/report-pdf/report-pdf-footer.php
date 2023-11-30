
<?php 
/**
 * The template for displaying Footer Report PDF - Saturn
 *
 * @author Tuan
 * 
 */
if (!empty($report_template['footer'])) {
    $mpdf->SetHTMLFooter(
        '<div class="footer">'
            .$report_template['footer'].
        '</div>'
    );
}
else {
    $mpdf->SetHTMLFooter(
        '<div class="footer">
            <a width="100%" href="www.and.org.au/">www.and.org.au</a>
            <table class="hypelinks-list" width="100%">
                <tr>
                    <td width="20%">
                        <a href="https://www.linkedin.com//ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-linked.png" alt="">
                            /ANDisability
                        </a>
                    </td>
                    <td width="20%">
                        <a href="https://twitter.com/ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-twitter.png" alt="">
                            /ANDisability
                        </a>
                    </td>
                    <td width="20%">
                        <a href="https://www.facebook.com/ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-facebook.png" alt="">
                            /ANDisability
                        </a>
                    </td>
                    <td width="40%" style="text-align: right;">
                        Page <strong>{PAGENO}</strong> of <strong>{nbpg}</strong>
                    </td>
                </tr>
            </table>
        </div>'
    );
}
?>
