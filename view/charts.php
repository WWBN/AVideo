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

$videos = Video::getAllVideos("", true, true, array(), true);
$bg = $bc = $labels = $datas = $datas7 = $datas30 = array();
foreach ($videos as $value) {
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
        <title>Report - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script " />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js" integrity="sha256-+q+dGCSrVbejd3MDuzJHKsk2eXd4sF5XYEMfPZsOnYE=" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php
        include 'include/navbar.php';
        //var_dump($videos);
        ?>
        <div class="container-fluid">
            <div class="row bgWhite">
                <canvas id="myChart" ></canvas>
            </div>
            <div class="row bgWhite">

                <div class="btn-group" >
                    <button class="btn btn-primary" id="btnAll" ><?php echo __("Total Views"); ?></button>
                    <button class="btn btn-primary" id="btn7"><?php echo __("Last 7 Days"); ?></button>
                    <button class="btn btn-primary" id="btn30" ><?php echo __("Last 30 Days"); ?></button>
                    <button class="btn btn-primary" id="btnUnique" ><?php echo __("Unique Users"); ?></button>
                </div>
            </div>
        </div>
        <script>
            var ctx = document.getElementById("myChart");
            var barChartData = {
                labels: ["<?php echo implode('","', $labels); ?>"],
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
                data: barChartData,
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
                    }
                }
            });
            $(document).ready(function () {
                $('#btnAll').click(function () {
                    barChartData.datasets[0].data = <?php echo json_encode($datas); ?>;
                    barChartData.datasets[0].label = '# <?php echo __("Total Views"); ?>';
                    myChart.update();
                });
                $('#btn7').click(function () {
                    barChartData.datasets[0].data = <?php echo json_encode($datas7); ?>;
                    barChartData.datasets[0].label = '# <?php echo __("Last 7 Days"); ?>';
                    myChart.update();
                });
                $('#btn30').click(function () {
                    barChartData.datasets[0].data = <?php echo json_encode($datas30); ?>;
                    barChartData.datasets[0].label = '# <?php echo __("Last 30 Days"); ?>';
                    myChart.update();
                });
                $('#btnUnique').click(function () {
                    barChartData.datasets[0].data = <?php echo json_encode($datasUnique); ?>;
                    barChartData.datasets[0].label = '# <?php echo __("Unique Users"); ?>';
                    myChart.update();
                });
            });
        </script>
        <?php
        include 'include/footer.php';
        ?>


    </body>
</html>
