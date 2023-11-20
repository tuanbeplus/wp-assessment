<?php 
/**
 * Template Report Framework Chart meta box
 * 
 * @author Tuan
 * 
 */

$canvas_id = rand(1, 9999);
?>

<div class="framework-chart-wrapper">
    <canvas id="report-framework-chart-<?php echo $canvas_id; ?>" class="framework-chart-canvas"></canvas>
    <a id="btn-download-chart" class="button button-medium" role="button">
        Download as Image
    </a>
    <script>
    // Render Report Framework Chart
    jQuery(document).on('ready', function(e){
        let ctx = jQuery('#report-framework-chart-<?php echo $canvas_id; ?>');
        let FrameworkChart = new Chart(ctx, {
            type: 'radar',
            options: {
                elements: {
                    line: {
                        borderWidth: 3
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Framework Dashboard',
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 15,
                            }
                        }
                    },
                    customCanvasBackgroundColor: {
                        color: '#fff',
                    }
                }
            },
            data: {
                labels: [
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
                ],
                datasets: [
                    {
                        label: '2020',
                        data: [4.0, 2.5, 3.0, 2.0, 3.0, 2.0, 1.5, 0, 2.5, 3.0],
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
                        data: [3.5, 2.5, 4.0, 3.0, 3.5, 3.0, 4.0, 4.0, 3.0, 3.5],
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
</div>