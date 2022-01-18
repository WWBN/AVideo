<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';

if (!User::canStream()) {
    return false;
}
global $isAdminPanel;

$_POST['sort'] = array();
$_POST['sort']['created'] = 'DESC';
$_POST['sort']['total_viewers'] = 'DESC';
$_POST['sort']['max_viewers_sametime'] = 'DESC';
$_REQUEST['rowCount'] = 30;
if (!empty($isAdminPanel)) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true);
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true);
}
$labelsArray = [];
$valueArray = [];
$valueArraySameTime = [];

foreach ($lives as $value) {
    //var_dump($lives);
    if(!intval($value['total_viewers'])){
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n users_id#{$value['users_id']} " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArray[] = $label;
    $valueArraySameTime[] = intval($value['max_viewers_sametime']);
    $valueArray[] = intval($value['total_viewers']);
}


$_POST['sort'] = array();
$_POST['sort']['total_viewers'] = 'DESC';
if ($isAdminPanel) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true);
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true);
}
$labelsArrayMoreViews = [];
$valueArrayMoreViews = [];
$valueArraySameTimeMoreViews = [];

foreach ($lives as $value) {
    //var_dump($lives);
    if(!intval($value['total_viewers'])){
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n users_id#{$value['users_id']} " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArrayMoreViews[] = $label;
    $valueArraySameTimeMoreViews[] = intval($value['max_viewers_sametime']);
    $valueArrayMoreViews[] = intval($value['total_viewers']);
}


$_POST['sort'] = array();
$_POST['sort']['max_viewers_sametime'] = 'DESC';
if (!empty($isAdminPanel)) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true);
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true);
}
$labelsArrayMoreViewsSameTime = [];
$valueArrayMoreViewsSameTime = [];
$valueArraySameTimeMoreViewsSameTime = [];

foreach ($lives as $value) {
    //var_dump($lives);
    if(!intval($value['max_viewers_sametime'])){
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n users_id#{$value['users_id']} " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArrayMoreViewsSameTime[] = $label;
    $valueArraySameTimeMoreViewsSameTime[] = intval($value['max_viewers_sametime']);
    $valueArrayMoreViewsSameTime[] = intval($value['total_viewers']);
}
?>
<div id="liveVideosMenu" class="tab-pane fade" style="padding: 10px;">
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("Last Lives"); ?></div>
        <div class="panel-body">
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
        <div class="panel-body">
            <div class="col-md-12">
            <canvas id="liveChart" height="90"  ></canvas>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("More views"); ?></div>
        <div class="panel-body">
            <div class="col-md-12">
            <canvas id="liveChartMoreViews" height="90"  ></canvas>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading when"># <?php echo __("More views same time"); ?></div>
        <div class="panel-body">
            <div class="col-md-12">
            <canvas id="liveChartMoreViewsSameTime" height="90"  ></canvas>
            </div>
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
    
    var ctxLiveChatMoreViews = document.getElementById("liveChartMoreViews");
    var liveChartDataMoreViews = {
        labels: <?php echo json_encode($labelsArrayMoreViews); ?>,
        datasets: [{
                backgroundColor: 'rgba(255, 0, 0, 0.3)',
                borderColor: 'rgba(255, 0, 0, 0.5)',
                label: '# <?php echo __("Total Views"); ?>',
                data: <?php echo json_encode($valueArrayMoreViews); ?>
            }]
    };
    
    var ctxLiveChatMoreViewsSameTime = document.getElementById("liveChartMoreViewsSameTime");
    var liveChartDataMoreViewsSameTime = {
        labels: <?php echo json_encode($labelsArrayMoreViewsSameTime); ?>,
        datasets: [{
                backgroundColor: 'rgba(0,255, 0, 0.3)',
                borderColor: 'rgba( 0,255, 0, 0.5)',
                label: '# <?php echo __("Total Viewers Same Time"); ?>',
                data: <?php echo json_encode($valueArraySameTimeMoreViewsSameTime); ?>
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

        var liveChartMoreViews = new Chart(ctxLiveChatMoreViews, {
            type: 'bar',
            data: liveChartDataMoreViews,
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

        var liveChartMoreViewsSameTime = new Chart(ctxLiveChatMoreViewsSameTime, {
            type: 'bar',
            data: liveChartDataMoreViewsSameTime,
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
                    labels: [<?php echo json_encode(__('Total Viewers')), '+ " (' . $valueArraySameTime[$i] . ')"'; ?>, <?php echo json_encode(__('Max Viewers Same Time')), '+ " (' . $valueArray[$i] . ')"'; ?>],
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