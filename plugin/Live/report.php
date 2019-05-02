<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';

if (!User::canViewChart()) {
    return false;
}

$lives = LiveTransmitionHistory::getAllFromUser(User::getId());

$labelsArray = array();
$valueArray = array();

foreach ($lives as $value) {
    $labelsArray[] = $value['created'] . "\n" . $value['title'];
    $valueArray[] = intval($value['totalUsers']);
}
?>
<div id="liveVideosMenu" class="tab-pane fade" style="padding: 10px;">
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("Timelive"); ?></div>
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
                label: '# <?php echo __("Total Views (90 Days)"); ?>',
                data: <?php echo json_encode($valueArray); ?>
            }]
    };

    $(document).ready(function () {

        var liveChart = new Chart(ctxLiveChat, {
            type: 'bar',
            data: liveChartData,
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


    });
</script>