<?php
/**
 * Template Report Framework Chart meta box
 *
 * @author Tuan
 *
 */

global $post;
$report_id = $post->ID;
$post_meta = get_post_meta($report_id);
$submission_id = get_post_meta($report_id, 'submission_id', true);
$org_data = get_post_meta($report_id, 'org_data', true);
$user_id = get_post_meta($submission_id, 'user_id', true);
$sf_user_name = get_post_meta($submission_id, 'sf_user_name', true);
$dashboard_chart_imgs = get_post_meta($report_id, 'dashboard_chart_imgs', true);
$framework_dashboard      = get_field('framework_dashboard',$report_id);
$implementation_dashboard = get_field('implementation_dashboard',$report_id);
$review_dashboard         = get_field('review_dashboard',$report_id);
$overall_dashboard        = get_field('overall_dashboard',$report_id);
$canvas_id = rand(1, 9999);
$key_areas = array('Framework', 'Implementation', 'Review', 'Overall');
?>

<div class="dashboard-charts-wrapper">
    <div class="_action">
      <p>Click to add all dashboard charts to this Report PDF file</p>
      <a id="btn-add-charts-report" class="button button-primary">
        Add Charts to Report
        <img class="icon-spinner" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Spinner-0.7s-200px.svg" alt="loading">
      </a>
    </div>
    <ul class="dashboard-charts-list">
    <?php foreach ($key_areas as $key):

        if($key == 'Framework' and empty($framework_dashboard)) continue;
        if($key == 'Implementation' and empty($implementation_dashboard)) continue;
        if($key == 'Review' and empty($review_dashboard)) continue;
        if($key == 'Overall' and empty($overall_dashboard)) continue;

        $img_url = $charts_img_url[$key] ?? null;
        $data_dashboard = array();
        if($key == 'Framework') $data_dashboard = $framework_dashboard;
        if($key == 'Implementation') $data_dashboard = $implementation_dashboard;
        if($key == 'Review') $data_dashboard = $review_dashboard;
        if($key == 'Overall') $data_dashboard = $overall_dashboard;

        ?>
        <li class="chart">
            <canvas id="<?php echo $key; ?>-dashboard-chart-<?php echo $canvas_id; ?>"
                    class="dashboard-chart-canvas"
                    data-key="<?php echo $key; ?>">
            </canvas>
            <a class="btn-download-chart button button-medium" role="button">
                Download as Image
            </a>
            <script>
            // Render Report Framework Chart
            jQuery(document).on('ready', function(e){
                let ctx = jQuery('#<?php echo $key; ?>-dashboard-chart-<?php echo $canvas_id; ?>');
                let labels = [];
                <?php
                  foreach ($data_dashboard as $dashboard) {
                    foreach ($dashboard['data_values'] as $value) {
                        ?>
                        labels.push(' <?php echo $value['key_area']; ?> ')
                        <?php
                    }
                    break;
                  }
                 ?>
                let FrameworkChart = new Chart(ctx, {
                    type: 'radar',
                    options: {
                        elements: {line:{borderWidth: 3}},
                        plugins: {
                            title: {
                                display: true,
                                text: '<?php echo $key; ?> Dashboard',
                                font: {size: 20,}
                            },
                            legend: {
                                labels: {font:{size: 15,}}
                            },
                            customCanvasBackgroundColor: {color: '#fff',}
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 4.0,
                                min: 0.0,
                                stepSize: 1.0,
                                pointLabels: {font: {size: 15,}},
                            },
                        },
                        scale: {ticks: {stepSize: 1.0,},
                        },
                    },
                    data: {
                        labels: labels,
                        datasets: [
                          <?php
                            foreach ($data_dashboard as $dashboard) {
                              $dataset = array();
                              foreach ($dashboard['data_values'] as $value) {
                                  $dataset[] = $value['value'];
                              }
                              $dataset = '[' . implode(', ' , $dataset) . ']';
                              $bg_color     = isset($dashboard['color']) ? 'rgba('.$dashboard['color']['red'].', '.$dashboard['color']['green'].', '.$dashboard['color']['blue'].', 0.2)' : 'rgba(255, 120, 40, 0.2)';
                              $border_color = isset($dashboard['color']) ? 'rgb('.$dashboard['color']['red'].', '.$dashboard['color']['green'].', '.$dashboard['color']['blue'].')' : 'rgb(99, 145, 255)';
                              ?>
                                  {
                                      label: '<?php echo $dashboard['year'] ?>',
                                      data: <?php echo $dataset; ?>,
                                      fill: true,
                                      backgroundColor: '<?php echo $bg_color; ?>',
                                      borderColor: '<?php echo $border_color; ?>',
                                      pointBackgroundColor: '<?php echo $border_color; ?>',
                                      pointBorderColor: '#fff',
                                      pointHoverBackgroundColor: '#fff',
                                      pointHoverBorderColor: '<?php echo $border_color; ?>'
                                  },
                                  <?php
                                }
                           ?>
                        ]
                    },
                });
            });
            </script>
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<script type="text/javascript">
    // Add new data
    jQuery(document).on('ready', function($){
      var data_values = [];
      var myIntervalData;
      var ele_repeater;
      var row = 0;
      function getDataValues(){
        jQuery('td.acf-field[data-name="key_area"]').each(function(index){
          var value = jQuery(this).find('input').val();
          if (value != '' && jQuery.inArray(value, data_values) < 0)
          {
            data_values.push(value);
          }
        });
      }

      function addRowDefault(){
        var template = ele_repeater;
        var temp;
        template.find('.acf-row').each(function(){
          var id = jQuery(this).data('id');
          if(id != 'acfcloneindex' && !id.includes("row") && !jQuery(this).closest('div[data-name="data_values"]').length && !jQuery(this).hasClass('added')){
            jQuery(this).find('.acf-repeater-add-row').click();
            temp = jQuery(this);
            jQuery(this).find('.acf-row').each(function(index){
              if(index == (row)){
                jQuery(this).find('td[data-name="key_area"]').find('input').val(data_values[index]);
              }
            });
            row+=1;
          }
        });
        if(row >= data_values.length){
          row = 0;
          temp.addClass('added');
          clearInterval(myIntervalData);
        }
      }

      getDataValues();
      jQuery('.acfe-repeater-stylised-button .acf-repeater-add-row').on('click',function(){
        getDataValues();
        ele_repeater = jQuery(this).closest('.acf-repeater');
        myIntervalData = setInterval(addRowDefault, 100);
      });

    });

</script>
