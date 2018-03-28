<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$videos = Video::getAllVideos("viewableNotAd", true, true, array(), true);

$totalVideos = Video::getTotalVideos("viewableNotAd");
$totalUsers = User::getTotalUsers();
$totalSubscriptions = Subscribe::getTotalSubscribes();
$totalComents = Comment::getTotalComments();
$totalInfos = Video::getTotalVideosInfo("viewableNotAd", false, false, array(), true);

$labelToday = array();
for ($i = 0; $i < 24; $i++) {
    $labelToday[] = "{$i} h";
}
$label7Days = array();
for ($i = 7; $i >= 0; $i--) {
    $label7Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$label30Days = array();
for ($i = 30; $i >= 0; $i--) {
    $label30Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$label90Days = array();
for ($i = 90; $i >= 0; $i--) {
    $label90Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$statistc_lastToday = VideoStatistic::getTotalToday("");
$statistc_last7Days = VideoStatistic::getTotalLastDays("", 7);
$statistc_last30Days = VideoStatistic::getTotalLastDays("", 30);
$statistc_last90Days = VideoStatistic::getTotalLastDays("", 90);

$bg = $bc = $labels = $labelsFull = $datas = $datas7 = $datas30 = $datasToday = $datasUnique = array();
foreach ($videos as $value) {
    $labelsFull[] = $value["title"];
    $labels[] = substr($value["title"], 0, 20);
    $datas[] = $value["statistc_all"];
    $datasToday[] = $value["statistc_today"];
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
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        
        <style>
            /* Custom Colored Panels */
            .dashboard .panel-heading {
                color: #fff;
            }
            .dashboard .loading {
                color: #FFFFFF55;
            }

            .huge {
                font-size: 40px;
            }

            <?php
            $cssPanel = array(
                'green' => array('5cb85c', '3d8b3d'),
                'red' => array('d9534f', 'b52b27'),
                'yellow' => array('f0ad4e', 'df8a13'),
                'orange' => array('f26c23', 'bd4a0b'),
                'purple' => array('5133ab', '31138b'),
                'wine' => array('ac193d', '9c091d'),
                'blue' => array('2672ec', '0252ac')
            );
            foreach ($cssPanel as $key => $value) {
                ?>
                .panel-<?php echo $key; ?> {
                    border-color: #<?php echo $value[0]; ?>;
                    background-color: #<?php echo $value[0]; ?>;
                }

                .panel-<?php echo $key; ?> > a {
                    color: #<?php echo $value[0]; ?>;
                }

                .panel-<?php echo $key; ?> > a:hover {
                    color: #<?php echo $value[1]; ?>;
                }
                <?php
            }
            ?>

        </style>
    </head>
    <body>
        <?php
        include 'include/navbar.php';
//var_dump($videos);
        ?>
        <div class="container-fluid">
            <div class="list-group-item clear clearfix">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li><a data-toggle="tab" href="#menu1"><i class="fa fa-youtube-play"></i> <i class="fa fa-eye"></i> Video views - per Channel</a></li>
                    <li><a data-toggle="tab" href="#menu2"><i class="fa fa-comments"></i> <i class="fa fa-thumbs-up"></i> Comment thumbs up - per Person</a></li>
                    <li><a data-toggle="tab" href="#menu3"><i class="fa fa-youtube-play"></i> <i class="fa fa-thumbs-up"></i> Video thumbs up - per Channel</a></li>
                </ul>

                <div class="tab-content">
                    <div id="dashboard" class="tab-pane fade in active" style="padding: 10px;">
                        <?php
                            include $global['systemRootPath'].'view/report0.php';
                        ?>
                    </div>
                    <div id="menu1" class="tab-pane fade" style="padding: 10px;">
                        <?php
                            include $global['systemRootPath'].'view/report1.php';
                        ?>
                    </div>
                    <div id="menu2" class="tab-pane fade" style="padding: 10px;">
                        <?php
                            include $global['systemRootPath'].'view/report2.php';
                        ?>
                    </div>
                    <div id="menu3" class="tab-pane fade" style="padding: 10px;">
                        <?php
                            include $global['systemRootPath'].'view/report3.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
        <script>

            function countTo(selector, total) {
                current = parseInt($(selector).text());
                total = parseInt(total);
                if (!total || current >= total) {
                    $(selector).removeClass('loading');
                    return;
                }
                var rest = (total - current);
                var step = parseInt(rest / 100);
                if (step < 1) {
                    step = 1;
                }
                current += step;
                $(selector).text(current);
                var timeout = (500 / rest);
                setTimeout(function () {
                    countTo(selector, total);
                }, timeout);
            }

            var ctx = document.getElementById("myChart");
            var ctxPie = document.getElementById("myChartPie");
            var ctxLine = document.getElementById("myChartLine");
            var ctxLineToday = document.getElementById("myChartLineToday");
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

            var lineChartData = {
                labels: <?php echo json_encode($label90Days); ?>,
                datasets: [{
                        backgroundColor: 'rgba(255, 0, 0, 0.3)',
                        borderColor: 'rgba(255, 0, 0, 0.5)',
                        label: '# <?php echo __("Total Views (90 Days)"); ?>',
                        data: <?php echo json_encode($statistc_last90Days); ?>
                    }]
            };

            var lineChartDataToday = {
                labels: <?php echo json_encode($labelToday); ?>,
                datasets: [{
                        backgroundColor: 'rgba(0, 0, 255, 0.3)',
                        borderColor: 'rgba(0, 0, 255, 0.5)',
                        label: '# <?php echo __("Total Views (Today)"); ?>',
                        data: <?php echo json_encode($statistc_lastToday); ?>
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
                    legend: {
                        display: false
                    },
                    responsive: true,
                    title: {
                        display: true
                    }
                }
            });

            var myChartLineToday = new Chart(ctxLineToday, {
                type: 'line',
                data: lineChartDataToday,
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
                    legend: {
                        display: false
                    },
                    responsive: true,
                    title: {
                        display: true
                    }
                }
            });

            $(document).ready(function () {
                countTo('#totalVideos', <?php echo $totalVideos; ?>);
                countTo('#totalUsers', <?php echo $totalUsers; ?>);
                countTo('#totalSubscriptions', <?php echo $totalSubscriptions; ?>);
                countTo('#totalVideosComents', <?php echo $totalComents; ?>);
                countTo('#totalVideosLikes', <?php echo $totalInfos->likes; ?>);
                countTo('#totalVideosDislikes', <?php echo $totalInfos->disLikes; ?>);
                countTo('#totalVideosViews', <?php echo $totalInfos->views_count; ?>);
                countTo('#totalDurationVideos', <?php echo $totalInfos->total_minutes; ?>);

                $('#btnAll').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datas); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Total Views"); ?>';
                    lineChartData.labels = <?php echo json_encode($label90Days); ?>;
                    lineChartData.datasets[0].data = <?php echo json_encode($statistc_last90Days); ?>;
                    lineChartData.datasets[0].label = '# <?php echo __("Total Views (90 Days)"); ?>';
                    myChart.update();
                    myChartPie.update();
                    myChartLine.update();
                });
                $('#btnToday').click(function () {
                    $('.nav-chart .btn').removeClass('active');
                    $(this).addClass('active');
                    chartData.datasets[0].data = <?php echo json_encode($datasToday); ?>;
                    chartData.datasets[0].label = '# <?php echo __("Today"); ?>';
                    lineChartData.labels = <?php echo json_encode($labelToday); ?>;
                    lineChartData.datasets[0].data = <?php echo json_encode($statistc_lastToday); ?>;
                    lineChartData.datasets[0].label = '# <?php echo __("Today"); ?>';
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
