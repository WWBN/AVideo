<?php
require_once __DIR__ . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';

if (!User::canStream()) {
    return false;
}
global $isAdminPanel;
if(User::isAdmin()){
   $isAdminPanel = 1;
}

if (!empty($isAdminPanel)) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true, false, 30, 'recent');
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true, false, 30, 'recent');
}
$labelsArray = [];
$valueArray = [];
$valueArraySameTime = [];

foreach ($lives as $value) {
    //var_dump($lives);
    $total_viewers = intval($value['total_viewers']);
    if(empty($total_viewers)){
        $total_viewers = intval($value['total_viewers_from_history']);
    }
    if (!intval($total_viewers)) {
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n" . $value['title'] . " - " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArray[] = $label;
    $valueArraySameTime[] = (intval($value['max_viewers_sametime']) ?: null);
    $valueArray[] = intval($total_viewers );
}
//var_dump($labelsArray, $valueArraySameTime, $valueArray);

if ($isAdminPanel) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true, false, 30, 'views');
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true, false, 30, 'views');
}
$labelsArrayMoreViews = [];
$valueArrayMoreViews = [];
$valueArraySameTimeMoreViews = [];

foreach ($lives as $value) {
    //var_dump($lives);
    $total_viewers = intval($value['total_viewers']);
    if(empty($total_viewers)){
        $total_viewers = intval($value['total_viewers_from_history']);
    }
    if (!intval($total_viewers)) {
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n" . $value['title'] . " - " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArrayMoreViews[] = $label;
    $valueArraySameTimeMoreViews[] = (intval($value['max_viewers_sametime']) ?: null);
    $valueArrayMoreViews[] = intval($total_viewers);
}


if (!empty($isAdminPanel)) {
    $lives = LiveTransmitionHistory::getAllFromUser(0, true, false, 30, 'peak');
} else {
    $lives = LiveTransmitionHistory::getAllFromUser(User::getId(), true, false, 30, 'peak');
}
$labelsArrayMoreViewsSameTime = [];
$valueArrayMoreViewsSameTime = [];
$valueArraySameTimeMoreViewsSameTime = [];

foreach ($lives as $value) {
    //var_dump($lives);
    if (!intval($value['max_viewers_sametime'])) {
        continue;
    }
    if (!empty($isAdminPanel)) {
        $label = $value['created'] . "\n" . $value['title'] . " - " . User::getNameIdentificationById($value['users_id']);
    } else {
        $label = $value['created'] . "\n" . $value['title'];
    }
    $labelsArrayMoreViewsSameTime[] = $label;
    $valueArraySameTimeMoreViewsSameTime[] = (intval($value['max_viewers_sametime']) ?: null);
    $valueArrayMoreViewsSameTime[] = intval($value['total_viewers']);
}
$liveReports = [
    ['liveChart', 'Recent broadcasts', 'Views and peak concurrent viewers for up to 30 recent broadcasts, oldest to newest.', array_reverse($labelsArray), array_reverse($valueArray), array_reverse($valueArraySameTime)],
    ['liveChartMoreViews', 'Most watched broadcasts', 'Up to 30 broadcasts ranked by recorded views.', $labelsArrayMoreViews, $valueArrayMoreViews, null],
    ['liveChartMoreViewsSameTime', 'Highest simultaneous audience', 'Up to 30 broadcasts ranked by their peak number of concurrent viewers.', $labelsArrayMoreViewsSameTime, null, $valueArraySameTimeMoreViewsSameTime]
];
?>
<div id="liveVideosMenu" class="tab-pane fade">
    <header class="report-section-heading">
        <h2><?php echo __('Live performance'); ?></h2>
        <p class="text-muted"><?php echo __('Views count recorded browser sessions, not unique people. Peak concurrent viewers is estimated from recent viewer activity. Older broadcasts may have no recorded peak; this is shown as Not recorded, not zero.'); ?></p>
        <p class="help-block"><?php echo !empty($isAdminPanel) ? __('All channels / All time / Refresh the page to update') : __('Your broadcasts / All time / Refresh the page to update'); ?></p>
    </header>
    <?php if (empty($labelsArray) && empty($labelsArrayMoreViews) && empty($labelsArrayMoreViewsSameTime)) { ?>
        <div class="well"><?php echo __('No live viewing activity recorded yet. Broadcasts with viewers will appear here.'); ?></div>
    <?php } else { ?>
        <h3><?php echo __('Latest broadcasts with viewers'); ?></h3>
        <div class="report-summary">
            <?php foreach (array_slice($labelsArray, 0, 4) as $i => $label) { ?>
                <div class="panel panel-default report-live-card"><div class="panel-body">
                    <h4><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></h4>
                    <dl><dt><?php echo __('Views'); ?></dt><dd><?php echo number_format($valueArray[$i]); ?></dd><dt><?php echo __('Peak concurrent viewers'); ?></dt><dd><?php echo ($valueArraySameTime[$i] === null ? __('Not recorded') : number_format($valueArraySameTime[$i])); ?></dd></dl>
                </div></div>
            <?php } ?>
        </div>
        <?php foreach ($liveReports as $liveReport) { ?>
            <section class="panel panel-default">
                <div class="panel-heading"><h2><?php echo __($liveReport[1]); ?></h2><p><?php echo __($liveReport[2]); ?></p></div>
                <div class="panel-body">
                    <?php if (empty($liveReport[3])) { ?><p><?php echo __('No audience peaks recorded yet. New viewer activity will populate this report when live audience tracking is enabled.'); ?></p><?php } else { ?>
                        <div class="report-chart-body"><canvas id="<?php echo $liveReport[0]; ?>" role="img" aria-label="<?php echo __($liveReport[1]); ?>"></canvas></div>
                        <details><summary><?php echo __('View data'); ?></summary><div class="table-responsive"><table class="table table-striped">
                            <thead><tr><th><?php echo __('Broadcast'); ?></th><?php if ($liveReport[4] !== null) { ?><th><?php echo __('Views'); ?></th><?php } ?><?php if ($liveReport[5] !== null) { ?><th><?php echo __('Peak concurrent viewers'); ?></th><?php } ?></tr></thead>
                            <tbody><?php foreach ($liveReport[3] as $i => $label) { ?><tr><td><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></td><?php if ($liveReport[4] !== null) { ?><td><?php echo number_format($liveReport[4][$i]); ?></td><?php } ?><?php if ($liveReport[5] !== null) { ?><td><?php echo ($liveReport[5][$i] === null ? __('Not recorded') : number_format($liveReport[5][$i])); ?></td><?php } ?></tr><?php } ?></tbody>
                        </table></div></details>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    <?php } ?>
</div>
<script>
$(function () {
    var drawn = false;
    function drawLive() {
        if (drawn || !$('#liveVideosMenu').is(':visible')) { return; }
        drawn = true;
        <?php foreach ($liveReports as $liveReport) { if (empty($liveReport[3])) { continue; } ?>
        (function () {
            var labels = <?php echo json_encode($liveReport[3], JSON_HEX_TAG | JSON_HEX_AMP); ?>, datasets = [];
            <?php if ($liveReport[4] !== null) { ?>datasets.push({label: <?php echo json_encode(__('Views')); ?>, data: <?php echo json_encode($liveReport[4]); ?>});<?php } ?>
            <?php if ($liveReport[5] !== null) { ?>datasets.push({label: <?php echo json_encode(__('Peak concurrent viewers')); ?>, data: <?php echo json_encode($liveReport[5]); ?>});<?php } ?>
            AVideoReports.chart(document.getElementById('<?php echo $liveReport[0]; ?>'), labels.map(function (label) { return label.slice(0, 10); }), datasets, {fullLabels: labels});
        })();
        <?php } ?>
    }
    $('a[href="#liveVideosMenu"]').on('shown.bs.tab', drawLive); drawLive();
});
</script>
