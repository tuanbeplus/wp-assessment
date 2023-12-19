<?php
/**
 * The template for displaying Histoty Year on Year Dashboard Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

$pages_history = array('Framework', 'Implementation', 'Review', 'Overall');
$dashboard_chart_imgs = get_post_meta($post_id, 'dashboard_chart_imgs', true);
$framework_dashboard      = get_field('framework_dashboard',$post_id);
$implementation_dashboard = get_field('implementation_dashboard',$post_id);
$review_dashboard         = get_field('review_dashboard',$post_id);
$overall_dashboard        = get_field('overall_dashboard',$post_id);
$data_dashboard = '';

foreach ($pages_history as $page) {
    $count_figure = 0;
    if ($page == 'Framework') {
        $count_figure++;
        $data_dashboard = $framework_dashboard;
        $caption = 'Table 10 - Framework Dashboard - History';
        $img_alt = 'Figure '.$count_figure.' - Framework Dashboard - comparative';
    }       
    if ($page == 'Implementation') {
        $count_figure++;
        $data_dashboard = $implementation_dashboard;
        $caption = 'Table 11 - Implementation Dashboard - History';
        $img_alt = 'Figure '.$count_figure.' - Implementation Dashboard - comparative';
    }  
    if ($page == 'Review') {
        $count_figure++;
        $data_dashboard = $review_dashboard;
        $caption = 'Table 12 - Review Dashboard - History';
        $img_alt = 'Figure '.$count_figure.' - Review Dashboard - comparative';
    }          
    if ($page == 'Overall') {
        $count_figure++;
        $data_dashboard = $overall_dashboard;
        $caption = 'Table 13 - Overall Dashboard - History';
        $img_alt = 'Figure '.$count_figure.' - Overall Dashboard - comparative';
    }         

    if (isset($dashboard_chart_imgs[$page])) {
        $chart_img_url = wp_get_attachment_url($dashboard_chart_imgs[$page]);
        $chart_img = '<img class="chart" src="'. $chart_img_url .'"><p>'. $img_alt .'</p>';
    }
    else {
        $chart_img = '';
    }

    if (!empty($data_dashboard)) {
        $history_scores = get_history_dashboard_scores($data_dashboard);
        $years = array();
        foreach ($data_dashboard as $record) {
            $years[] = $record['year'];
        }
        if (!empty($years)) {
            if (count($years) > 1) {
                $year_on_year = min($years). '-' .max($years);
            }
            else {
                $year_on_year = min($years);
            }
        }
        else {
            $year_on_year = null;
        }
        
        $page_heading = 'Year-on-year: '. $page .' Dashboard '. $year_on_year;

        // Render Chart image if attachment ID exist
        if (isset($dashboard_chart_imgs[$page])) {
            $chart_img_url = wp_get_attachment_url($dashboard_chart_imgs[$page]);
            if (isset($chart_img_url)) {
                $chart_img = '<img class="chart" src="'. $chart_img_url .'"><p>'. $img_alt .'</p>';
            }
            else {
                $chart_img = '';
            }

            $dashboard_chart_html = 
            '<div class="page">
                <h3>'.$page_heading.'</h3>
                <div style="text-align:center;">'. $chart_img .'</div>
            </div>';

            // Add to table of contents
            $mpdf->TOC_Entry($page_heading ,1);

            // Render HTML
            $mpdf->WriteHTML($dashboard_chart_html);

            // Remove page heading
            $page_heading = null;
        }
        
        $history_dashboard_html =
        '<div class="page">
            <h3>'.$page_heading.'</h3>
            <table class="table-3 table-5">
                <tr>
                    <th>Key Area</th>';
            foreach ($years as $year) {
                $history_dashboard_html .=
                    '<th>'. $year .'</th>';
            }
                $history_dashboard_html .=
                '</tr>';
            foreach ($history_scores as $key_area) {
                $history_dashboard_html .=
                '<tr>';
                foreach ($key_area as $value) {
                    $history_dashboard_html .=
                    '<td>'. $value .'</td>';
                }
                $history_dashboard_html .=
                '</tr>';
            }

        $history_dashboard_html .=
            '</table>
            <caption>'. $caption .'</caption>
        </div>';

        if (empty($dashboard_chart_imgs[$page])) {
            // Add to table of contents
            $mpdf->TOC_Entry($page_heading ,1);
        }

        // Render HTML
        $mpdf->WriteHTML($history_dashboard_html);

        // Insert page break
        $mpdf->AddPage();
    }
}
?>




