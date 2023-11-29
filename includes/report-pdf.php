<?php 
/**
 * Class Report PDF for Saturn
 * 
 * @author Tuan
 *
 */

class WP_Report_PDF {

    private $mpdf;

    public function __construct() 
    {
        $this->mpdf = new \Mpdf\Mpdf();
    }

    /**
     * Get Report PDF template file
     * 
     * @param string $file_name		File name
     *
     * @return file Tempalte file
     * 
     */
    function get_report_pdf_template(){
        require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';
    }

    /**
     * Render Header of PDF
     * 
     */
    function report_pdf_header() {
        require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';
    }

    public function generatePdf() {
        // Include your additional PHP file
        require_once WP_ASSESSMENT_TEMPLATE.'/report-pdf/report-pdf-header.php';

        // Output the PDF
        $this->mpdf->Output();
    }
}
// new WP_Report_PDF();