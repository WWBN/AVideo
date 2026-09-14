
<div class="list-group-item clear clearfix report-tabs">
    <header class="report-page-heading" id="analyticsHeading">
        <div><h1><?php echo __('Analytics'); ?></h1><p class="text-muted"><?php echo __('Understand your audience, compare performance and find what is working.'); ?></p></div>
        <?php echo getTourHelpButton('view/charts.help.json', 'btn btn-default', true); ?>
    </header>
    <ul class="nav nav-tabs" id="analyticsTabs" role="tablist" aria-label="<?php echo __('Analytics reports'); ?>">
        <li class="active"><a data-toggle="tab" href="#dashboard"><i class="fas fa-tachometer-alt"></i> <?php echo __("Overview"); ?></a></li>
        <li><a data-toggle="tab" id="viewmyVideosReport" href="#myVideosReport"><i class="fas fa-play-circle"></i> <?php echo __("Video performance"); ?></a></li>
        <li><a data-toggle="tab" id="viewperchannel" href="#menu1"><i class="fas fa-play-circle"></i> <i class="fa fa-eye"></i> <?php echo __("Views by channel"); ?></a></li>
        <li><a data-toggle="tab" id="commentthumbs" href="#menu2"><i class="fa fa-comments"></i> <i class="fa fa-thumbs-up"></i> <?php echo __("Comment reactions"); ?></a></li>
        <li><a data-toggle="tab" id="videothumbs" href="#menu3"><i class="fas fa-play-circle"></i> <i class="fa fa-thumbs-up"></i> <?php echo __("Video reactions"); ?></a></li>
        <?php echo AVideoPlugin::getChartTabs(); ?>
    </ul>

    <div class="tab-content">
        <div id="dashboard" class="tab-pane fade in active">
            <?php
            if (User::isAdmin()) {
                echo diskUsageBars();
            }
            $isAdminPanel = User::isAdmin();
            include $global['systemRootPath'] . 'view/report0.php';
            ?>
        </div>
        <div id="myVideosReport" class="tab-pane fade">
            <?php
            include $global['systemRootPath'] . 'view/reportMyVideos.php';
            ?>
        </div>
        <div id="menu1" class="tab-pane fade">
            <?php
            include $global['systemRootPath'] . 'view/report1.php';
            ?>
        </div>
        <div id="menu2" class="tab-pane fade">
            <?php
            include $global['systemRootPath'] . 'view/report2.php';
            ?>
        </div>
        <div id="menu3" class="tab-pane fade">
            <?php
            include $global['systemRootPath'] . 'view/report3.php';
            ?>
        </div>
        <?php echo AVideoPlugin::getChartContent(); ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var tabs = $('#analyticsTabs a[data-toggle="tab"]');
        tabs.each(function () {
            $(this).attr({role: 'tab', 'aria-controls': $(this).attr('href').slice(1), 'aria-selected': $(this).parent().hasClass('active') ? 'true' : 'false', tabindex: $(this).parent().hasClass('active') ? 0 : -1});
        });
        tabs.on('keydown', function (event) {
            var index = tabs.index(this);
            if (event.key === 'ArrowRight') { index = (index + 1) % tabs.length; }
            else if (event.key === 'ArrowLeft') { index = (index + tabs.length - 1) % tabs.length; }
            else if (event.key === 'Home') { index = 0; }
            else if (event.key === 'End') { index = tabs.length - 1; }
            else { return; }
            event.preventDefault(); tabs.eq(index).tab('show').trigger('focus');
        });
        function openReportHash() {
            $('#analyticsTabs a[data-toggle="tab"]').each(function () {
                if ($(this).attr('href') === window.location.hash) { $(this).tab('show'); }
            });
        }
        $('#analyticsTabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
            tabs.attr({'aria-selected': 'false', tabindex: -1});
            $(this).attr({'aria-selected': 'true', tabindex: 0});
            this.scrollIntoView({block: 'nearest', inline: 'nearest'});
            history.replaceState(null, '', $(this).attr('href'));
        });
        $(window).on('hashchange', openReportHash);
        openReportHash();
<?php if (!empty($_GET['jump'])) { ?>
            $('#<?php echo preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['jump']); ?>').click();
<?php } ?>
    });
</script>
