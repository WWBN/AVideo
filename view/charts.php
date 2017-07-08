<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$videos = Video::getAllVideos("viewableNotAd", true, true, array(), true);

$label7Days = array();
for ($i = 7; $i >= 0; $i--) {
    $label7Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$label30Days = array();
for ($i = 30; $i >= 0; $i--) {
    $label30Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$statistc_last7Days = VideoStatistic::getTotalLastDays("", 7);
$statistc_last30Days = VideoStatistic::getTotalLastDays("", 30);

$bg = $bc = $labels = $labelsFull = $datas = $datas7 = $datas30 = array();
foreach ($videos as $value) {
    $labelsFull[] = $value["title"];
    $labels[] = substr($value["title"], 0, 20);
    $datas[] = $value["statistc_all"];
    $datas7[] = $value["statistc_week"];
    $datas30[] = $value["statistc_month"];
    $datasUnique[] = $value["statistc_unique_user"];
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    $bg[] = "rgba({$r}, {$g}, {$b}, 0.5)";
    $bc[] = "rgba({$r}, {$g}, {$b}, 1)";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Chart - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script " />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js" integrity="sha256-+q+dGCSrVbejd3MDuzJHKsk2eXd4sF5XYEMfPZsOnYE=" crossorigin="anonymous"></script>
        <style>
        </style>
    </head>
    <body>
        <?php
        include 'include/navbar.php';
        //var_dump($videos);
        ?>
        <div class="container-fluid">
            <nav class="navbar navbar-default nav-chart">
                <div class="container-fluid">
                    <button class="btn btn-default navbar-btn active" id="btnAll" ><?php echo __("Total Views"); ?></button>
                    <button class="btn btn-default navbar-btn" id="btn7"><?php echo __("Last 7 Days"); ?></button>
                    <button class="btn btn-default navbar-btn" id="btn30" ><?php echo __("Last 30 Days"); ?></button>
                    <!--
                    <button class="btn btn-default navbar-btn" id="btnUnique" ><?php echo __("Unique Users"); ?></button>
                    -->
                </div>
            </nav>

            <div class="col-md-3">

                <div class="panel panel-default">
                    <div class="panel-heading when"><?php echo __("Color Legend"); ?></div>
                    <div class="panel-body">
                        <div class="list-group">

                            <?php
                            foreach ($labelsFull as $key => $value) {
                                ?>
                                <a class="list-group-item " style="border-color: <?= $bg[$key] ?>; border-width: 1px 20px 1px 5px; font-size: 0.9em;">
                                    <?= $value ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Total Views"); ?></div>
                            <div class="panel-body">
                                <canvas id="myChart" ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Total Views"); ?></div>
                            <div class="panel-body">
                                <canvas id="myChartPie" ></canvas> 
                            </div>
                        </div>                       
                    </div>
                    <div class="col-md-8">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Timeline"); ?></div>
                            <div class="panel-body" id="timeline">
                                <canvas id="myChartLine" ></canvas> 
                            </div>
                        </div>                       
                    </div>
                </div>
            </div>
        </div>
        <script>
            var ctx = document.getElementById("myChart");
            var ctxPie = document.getElementById("myChartPie");
            var chartData = {
                labels: <?php echo json_encode($labelsFull); ?>,
                datasets: [{
                        label: '# <?php echo __("Total Views"); ?>',
                        data: <?php echo json_encode($datas); ?>,
                        backgroundColor: <?php echo json_encode($bg); ?>,
                        borderColor: <?php echo json_encode($bc); ?>,
                        borderWidth: 1
                    }]
            };
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    scales: {
                        yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    callback: function (value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }],
                        xAxes: [{
                                display: false
                            }]
                    },
                    legend: {
                        display: false
                    },
                    responsive: true
                }
            });
            var myChartPie = new Chart(ctxPie, {
                type: 'pie',
                data: chartData,
                options: {
                    legend: {
                        display: false
                    },
                    responsive: true
                }
            });


            var ctxLine = document.getElementById("myChartLine");
            var lineChartData = {
                labels: <?php echo json_encode($label30Days); ?>,
                datasets: [{
                        backgroundColor: 'rgba(255, 0, 0, 0.3)',
                        borderColor: 'rgba(255, 0, 0, 0.5)',
                        label: '<?php echo __("Last 30 Days"); ?>',
                        data: <?php echo json_encode($statistc_last30Days); ?>
                    }]
            };

            var myChartLine = new Chart(ctxLine, {
                type: 'line',
                data: lineChartData,
                fill: false,
                options: {
                    scales: {
                        yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    callback: function (value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }]
                    },
                    responsive: true,
                    title: {
                        display: true
                    }
                }
            });

            $(document).ready(function () {
                $('#btnAll').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datas); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Total Views"); ?>';
                    lineChartData.labels = <?php echo json_encode($label30Days); ?>;
                    lineChartData.datasets[0].data = <?php echo json_encode($statistc_last30Days); ?>;
                    lineChartData.datasets[0].label = '# <?php echo __("Total Views (30 Days)"); ?>';  
                    myChart.update();
                    myChartPie.update();
                    myChartLine.update();
                });
                $('#btn7').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datas7); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Last 7 Days"); ?>';
                    lineChartData.labels = <?php echo json_encode($label7Days); ?>;
                    lineChartData.datasets[0].data = <?php echo json_encode($statistc_last7Days); ?>;
                    lineChartData.datasets[0].label = '# <?php echo __("Last 7 Days"); ?>';                    
                    myChart.update();
                    myChartPie.update();
                    myChartLine.update();
                });
                $('#btn30').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datas30); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Last 30 Days"); ?>';
                    lineChartData.labels = <?php echo json_encode($label30Days); ?>;
                    lineChartData.datasets[0].data = <?php echo json_encode($statistc_last30Days); ?>;
                    lineChartData.datasets[0].label = '# <?php echo __("Last 30 Days"); ?>';  
                    myChart.update();
                    myChartPie.update();
                    myChartLine.update();
                });
                $('#btnUnique').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datasUnique); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Unique Users"); ?>';
                    myChart.update();
                    myChartPie.update();
                });
            });
        </script>
        <?php
        include 'include/footer.php';
        ?>


    </body>
</html>
