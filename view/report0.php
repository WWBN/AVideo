<?php
$reportMetrics = [
    ['totalVideos', 'totalVideos', 'Videos', 'fa-play-circle', 'mvideos'],
    ['totalVideosViews', 'totalVideosViews', 'Video views', 'fa-eye', 'mvideos'],
    ['totalDurationVideos', 'totalDurationVideos', 'Video duration (minutes)', 'fa-clock', 'mvideos'],
    ['totalSubscriptions', 'totalSubscriptions', 'Active subscriptions', 'fa-user-plus', 'subscribes'],
    ['totalVideosComents', 'totalComents', 'Comments', 'fa-comments', 'comments'],
    ['totalVideosLikes', 'totalLikes', 'Recorded video likes', 'fa-thumbs-up', 'mvideos'],
    ['totalVideosDislikes', 'totalDislikes', 'Recorded video dislikes', 'fa-thumbs-down', 'mvideos']
];
if (User::isAdmin()) {
    array_unshift($reportMetrics, ['totalUsers', 'totalUsers', 'Users', 'fa-users', 'users']);
}
?>
<header class="report-section-heading">
    <h2><?php echo __('Performance at a glance'); ?></h2>
    <p class="text-muted"><?php echo User::isAdmin() ? __('Current totals across the site. Online users updates live; other figures may take up to five minutes to update.') : __('Current totals for your channel. Figures may take up to five minutes to update.'); ?></p>
    <p class="help-block"><?php echo __('Video views use accumulated display counters. Dated reports count retained viewing sessions, so their totals can differ after statistics cleanup. Reactions count current recorded votes; users include active and inactive accounts. Subscriptions exclude artificial display adjustments.'); ?></p>
</header>
<div class="metric-grid">
    <?php if (User::isAdmin()) { ?>
        <div class="panel panel-default"><div class="panel-body"><i class="fas fa-user-friends report-metric-icon" aria-hidden="true"></i><div class="huge total_devices_online" id="total_users_online">&mdash;</div><span><?php echo __('Online users (by device)'); ?></span></div></div>
    <?php } ?>
    <?php foreach ($reportMetrics as $metric) { ?>
        <div class="panel panel-default">
            <div class="panel-body"><i class="fas <?php echo $metric[3]; ?> report-metric-icon" aria-hidden="true"></i><div class="huge" id="<?php echo $metric[0]; ?>">&mdash;</div><span><?php echo __($metric[2]); ?></span></div>
            <a class="panel-footer" href="<?php echo $global['webSiteRootURL'] . $metric[4]; ?>"><?php echo __('View Details'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
        </div>
    <?php } ?>
</div>
<section class="panel panel-default" id="report0ChartsContainer">
    <div class="panel-heading report-page-heading">
        <div><h2><?php echo __('Most watched videos'); ?></h2><p><?php echo __('Ranked by recorded views in the selected rolling period. Views are not unique viewers.'); ?></p></div>
        <div class="report-actions">
            <label for="overviewPeriod" class="sr-only"><?php echo __('Period'); ?></label>
            <select class="form-control" id="overviewPeriod">
                <?php foreach (['today' => 'Last 24 hours', 'last7Days' => 'Last 7 Days', 'last15Days' => 'Last 15 Days', 'last30Days' => 'Last 30 Days', 'last90Days' => 'Last 90 Days'] as $key => $label) { ?>
                    <option value="<?php echo $key; ?>" <?php echo $key === 'last30Days' ? 'selected' : ''; ?>><?php echo __($label); ?></option>
                <?php } ?>
            </select>
            <button type="button" class="btn btn-default" id="refreshOverview"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo __('Refresh'); ?></button>
        </div>
    </div>
    <div class="panel-body">
        <p id="overviewStatus" role="status" aria-live="polite"></p>
        <div class="report-chart-body"><canvas id="overviewChart" role="img" aria-label="<?php echo __('Most watched videos'); ?>"></canvas></div>
        <details><summary><?php echo __('View data'); ?></summary><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo __('Video'); ?></th><th class="text-right"><?php echo __('Views'); ?></th></tr></thead><tbody id="overviewData"></tbody></table></div></details>
    </div>
</section>
<div class="row"><?php include $global['systemRootPath'] . 'view/report4.php'; ?></div>
<script>
$(function () {
    var response, canvas = document.getElementById('overviewChart'), status = $('#overviewStatus');
    function draw() {
        if (!response) { return; }
        var period = response[$('#overviewPeriod').val()];
        var videos = period && Array.isArray(period.videos) ? period.videos : [];
        $('#overviewData').empty();
        videos.forEach(function (video) {
            $('<tr>').append($('<td>').text(video.title || video.clean_title)).append($('<td class="text-right">').text(Number(video.total_views).toLocaleString())).appendTo('#overviewData');
        });
        var labels = videos.map(function (video) { return video.title || video.clean_title; });
        AVideoReports.chart(canvas, labels.map(function (label) { return label.length > 38 ? label.slice(0, 35) + '\u2026' : label; }), [{label: <?php echo json_encode(__('Views')); ?>, data: videos.map(function (video) { return Number(video.total_views) || 0; })}], {horizontal: true, fullLabels: labels});
        $(canvas).parent().toggle(videos.length > 0);
        $('#report0ChartsContainer details').toggle(videos.length > 0);
        status.removeClass('text-danger').text(videos.length ? <?php echo json_encode(__('Showing the top videos for this period.')); ?> : <?php echo json_encode(__('No views recorded in this period. Try a longer period.')); ?>);
    }
    function load() {
        $('#refreshOverview').prop('disabled', true); AVideoReports.busy(true);
        status.removeClass('text-danger').text(<?php echo json_encode(__('Loading...')); ?>);
        function failed() {
            response = null; $('#overviewData').empty(); $(canvas).parent().hide();
            $('#report0ChartsContainer details').hide();
            <?php foreach ($reportMetrics as $metric) { ?>$('#<?php echo $metric[0]; ?>').text('\u2014');<?php } ?>
            status.addClass('text-danger').text(<?php echo json_encode(__('Unable to load the overview. Select Refresh to try again.')); ?>);
        }
        $.ajax({url: webSiteRootURL + 'view/report.json.php', data: {isAdminPanel: <?php echo User::isAdmin() ? 1 : 0; ?>}, dataType: 'json', timeout: 60000,
            success: function (data) {
                if (!data || data.error || ['today','last7Days','last15Days','last30Days','last90Days'].some(function (key) { return !data[key] || !Array.isArray(data[key].videos); })) { failed(); return; }
                response = data;
                <?php foreach ($reportMetrics as $metric) { ?>
                $('#<?php echo $metric[0]; ?>').text(data.<?php echo $metric[1]; ?> == null ? '\u2014' : Number(data.<?php echo $metric[1]; ?>).toLocaleString());
                <?php } ?>
                draw();
            }, error: failed, complete: function () { $('#refreshOverview').prop('disabled', false); AVideoReports.busy(false); }
        });
    }
    $('#overviewPeriod').on('change', draw); $('#refreshOverview').on('click', load); load();
});
</script>
