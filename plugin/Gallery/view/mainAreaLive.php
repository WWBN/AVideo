<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
$leaderBoardTop = getAdsLeaderBoardTop();
$objLive = AVideoPlugin::getDataObject('Live');
$_page = new Page(array('Live'));
?>
<div class="<?php echo Gallery::getContaierClass(); ?>">
    <?php
    if (!empty($leaderBoardTop)) {
        echo '<div class="row text-center" style="padding: 10px;">' . $leaderBoardTop . '</div>';
    } else {
        echo '<!-- getAdsLeaderBoardTop is empty -->';
    }
    ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row mainArea">
                <!-- For Live Videos mainAreaLive -->
                <div id="liveVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Schedule Videos  <?php echo basename(__FILE__); ?> -->
                <div id="liveScheduleVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle"> <i class="far fa-calendar-alt"></i> <?php echo __($objLive->live_schedule_label); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Videos End -->
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>
