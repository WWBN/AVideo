<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';

if (!User::canStream()) {
    return false;
}
$_POST['sort'] = array();
$_POST['sort']['created'] = 'DESC';
$_REQUEST['rowCount'] = 30;
$lives = LiveTransmitionHistory::getAllFromUser(User::getId());

$labelsArray = [];
$valueArray = [];

foreach ($lives as $value) {
    //var_dump($lives);
    $labelsArray[] = $value['created'] . "\n" . $value['title'];
    $valueArraySameTime[] = intval($value['max_viewers_sametime']);
    $valueArray[] = intval($value['total_viewers']);
}
?>
<div id="liveVideosMenu" class="tab-pane fade" style="padding: 10px;">
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("Last 3"); ?></div>
        <div class="panel-body" id="timelive">
            <?php
            $liveChartLatest = array();

            foreach ($valueArray as $i => $value) {
                if (empty($valueArray[$i])) {
                    continue;
                }
                $liveChartLatest[] = $i;
                ?>
                <div class="col-md-3">
                    <canvas id="liveChartLatest<?php echo $i; ?>"  ></canvas>
                </div>
                <?php
                if (count($liveChartLatest) >= 4) {
                    break;
                }
            }
            ?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("Timeline"); ?></div>
        <div class="panel-body" id="timelive">
            <canvas id="liveChart" height="90"  ></canvas>
        </div>
    </div>
</div>
<script>

    var ctxLiveChat = document.getElementById("liveChart");
    var liveChartData = {
        labels: <?php echo json_encode($labelsArray); ?>,
        datasets: [{
                backgroundColor: 'rgba(255, 0, 0, 0.3)',
                borderColor: 'rgba(255, 0, 0, 0.5)',
                label: '# <?php echo __("Total Views"); ?>',
                data: <?php echo json_encode($valueArray); ?>
            }, {
                backgroundColor: 'rgba(0,255, 0, 0.3)',
                borderColor: 'rgba( 0,255, 0, 0.5)',
                label: '# <?php echo __("Total Viewers Same Time"); ?>',
                data: <?php echo json_encode($valueArraySameTime); ?>
            }]
    };

    $(document).ready(function () {

        var liveChart = new Chart(ctxLiveChat, {
            type: 'bar',
            data: liveChartData,
            fill: false,
            responsive: true,
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

<?php
foreach ($liveChartLatest as $i) {
    ?>

            var liveChartLatest<?php echo $i; ?> = new Chart(document.getElementById("liveChartLatest<?php echo $i; ?>"), {
                type: 'doughnut',
                data: {
                    labels: [<?php echo json_encode(__('Total Viewers')), ' (', count($valueArraySameTime[$i]), ')'; ?>, <?php echo json_encode(__('Max Viewers Same Time')), ' (', count($valueArray[$i]), ')'; ?>],
                    datasets: [{
                            label: '',
                            data: <?php echo json_encode(array($valueArraySameTime[$i], $valueArray[$i])); ?>,
                            backgroundColor: [
                                "#00FF0055",
                                "#FF000055",
                            ],

                        }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: <?php echo json_encode($labelsArray[$i]); ?>
                        }
                    }
                },
            });
    <?php
}
?>
    });
</script>