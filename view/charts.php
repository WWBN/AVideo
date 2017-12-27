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

            <div class="row dashboard">
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-blue">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-play-circle fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalVideos">0</div>
                                    <div><?php echo __("Total Videos"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-eye fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalVideosViews">0</div>
                                    <div><?php echo __("Total Videos Views"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-purple">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-users fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalUsers">0</div>
                                    <div><?php echo __("Total Users"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>users">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-wine">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-plus fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalSubscriptions">0</div>
                                    <div><?php echo __("Total Subscriptions"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>subscribes">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>


                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-comments fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalVideosComents">0</div>
                                    <div><?php echo __("Total Video Comments"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>videos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-orange">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-thumbs-o-up fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalVideosLikes">0</div>
                                    <div><?php echo __("Total Videos Likes"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-thumbs-o-down fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalVideosDislikes">0</div>
                                    <div><?php echo __("Total Videos Dislikes"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-clock-o fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge loading" id="totalDurationVideos">0</div>
                                    <div><?php echo __("Total Duration Videos (Minutes)"); ?></div>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <div class="panel-footer">
                                <span class="pull-left"><?php echo __("View Details"); ?></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <nav class="navbar navbar-default nav-chart">
                <div class="container-fluid">
                    <div class="btn-group">
                        <button class="btn btn-primary navbar-btn active" id="btnAll" ><?php echo __("Total Views"); ?></button>
                        <button class="btn btn-warning navbar-btn" id="btnToday"><?php echo __("Today Views"); ?></button>
                        <button class="btn btn-default navbar-btn" id="btn7"><?php echo __("Last 7 Days"); ?></button>
                        <button class="btn btn-default navbar-btn" id="btn30" ><?php echo __("Last 30 Days"); ?></button>
                        <!--
                        <button class="btn btn-default navbar-btn" id="btnUnique" ><?php echo __("Unique Users"); ?></button>
                        --></div>
                </div>
            </nav>

            <div class="col-md-3">

                <div class="panel panel-default">
                    <div class="panel-heading when"><?php echo __("Color Legend"); ?></div>
                    <div class="panel-body" style="height: 600px; overflow-y: scroll;">
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
                                <canvas id="myChart" height="60" ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Total Views"); ?></div>
                            <div class="panel-body">
                                <canvas id="myChartPie" height="200"  ></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Timeline"); ?></div>
                            <div class="panel-body" id="timeline">
                                <canvas id="myChartLine" height="90"  ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading when"># <?php echo __("Total Views Today"), " - ", date("Y-m-d"); ?></div>
                            <div class="panel-body">
                                <canvas id="myChartLineToday" height="60" ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            
            function countTo(selector, total){
                current = parseInt($(selector).text());
                total = parseInt(total);
                if(!total || current>=total){
                    $(selector).removeClass('loading');
                    return;
                }
                var rest = (total-current);
                var step = parseInt(rest/100);
                if(step<1){
                    step=1;
                }
                current+=step;
                $(selector).text(current);
                var timeout = (500/rest);
                setTimeout(function(){countTo(selector, total);}, timeout);
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
                countTo('#totalVideos', <?php echo count($totalVideos); ?>);
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
