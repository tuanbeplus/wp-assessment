
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
                    <td style="width: 29%;">
                        <a href="https://www.linkedin.com//ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-linked.png" alt="">
                            /ausdisabilitynetwork
                        </a>
                    </td>
                    <td style="width: 17%;">
                        <a href="https://twitter.com/ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-twitter.png" alt="">
                            /Aus_DN
                        </a>
                    </td>
                    <td style="width: 32%;">
                        <a href="https://www.facebook.com/ANDisability" style="color:#333;">
                            <img width="20" style="margin-bottom:-2px;" src="/wp-content/plugins/wp-assessment/assets/images/and-facebook.png" alt="">
                            /AusDisabilityNetwork
                        </a>
                    </td>
                    <td style="text-align: right; width: 22%;">
                        Page <strong>{PAGENO}</strong> of <strong>{nbpg}</strong>
                    </td>
                </tr>
            </table>
        </div>'
    );
}
?>
