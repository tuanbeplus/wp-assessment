<?php 
/**
 * Template Report Framework Chart meta box
 * 
 * @author Tuan
 * 
 */

$canvas_id = rand(1, 9999);
$key_areas = array('Framework', 'Implementation', 'Review', 'Overall');
$sections = "[
    'Commitment', 
    'Premises',  
    'Workplace',
    'Adjustments',
    'Communication and Marketing',
    'Products and Services',
    'ICT',
    'Recruitment and Selection',
    'Career Development',
    'Suppliers and Partners',
]";
$dataset_2020 = '[4.0, 2.5, 3.0, 2.0, 3.0, 2.0, 1.5, 1, 2.5, 3.0]';
$dataset_2021 = '[3.5, 2.5, 4.0, 3.0, 3.5, 3.0, 4.0, 4.0, 3.0, 3.5]';
?>

<div class="dashboard-charts-wrapper">
    <ul class="dashboard-charts-list">
    <?php foreach ($key_areas as $key): ?>
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
                        labels: <?php echo $sections; ?>,
                        datasets: [
                            {
                                label: '2020',
                                data: <?php echo $dataset_2020; ?>,
                                fill: true,
                                backgroundColor: 'rgba(255, 120, 40, 0.2)',
                                borderColor: 'rgb(255, 120, 40)',
                                pointBackgroundColor: 'rgb(255, 120, 40)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgb(255, 120, 40)'
                            },
                            {
                                label: '2021',
                                data: <?php echo $dataset_2021; ?>,
                                fill: true,
                                backgroundColor: 'rgba(99, 145, 255, 0.2)',
                                borderColor: 'rgb(99, 145, 255)',
                                pointBackgroundColor: 'rgb(99, 145, 255)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgb(99, 145, 255)'
                            },
                        ]
                    },
                });
            });  
            </script>
        </li>
    <?php endforeach; ?>
    </ul>
</div>