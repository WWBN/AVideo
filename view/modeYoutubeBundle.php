<?php
$TimeLogLimitMYB = 1;
$timeLogNameMYB = TimeLogStart("modeYoutubeBundle.php");
$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

$removeVideoList = (empty($playlist_id) && !empty($advancedCustom->removeVideoList)) || isBot();
$modeYoutubeBottomCols1 = 'col-sm-7 col-md-7 col-lg-6';
$modeYoutubeBottomCols2 = 'col-sm-5 col-md-5 col-lg-4 rightBar clearfix';
if ($removeVideoList) {
    $modeYoutubeBottomCols1 = 'col-sm-12';
    $modeYoutubeBottomCols2 = 'hidden';
}


?>
<div class="" id="modeYoutubeTop">
    <?php
    require "{$global['systemRootPath']}view/modeYoutubeTop.php";
    $modeYouTubeTimeLog['After include top '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    ?>
</div>
<?php
if (is_object($video)) {
    $video = Video::getVideoLight($video->getId());
}
TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
if (!empty($video['id'])) {
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
?>
    <div class="row" id="modeYoutubeBottom" style="margin: 0;">
        <div class="col-lg-1"></div>
        <!-- <?php echo __FILE__; ?> <?php echo __LINE__; ?> -->
        <div class="<?php echo $modeYoutubeBottomCols1; ?>" id="modeYoutubeBottomContent">
            <?php
            TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
            require "{$global['systemRootPath']}view/modeYoutubeBottom.php";
            $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
            ?>
        </div>
        <div class="<?php echo $modeYoutubeBottomCols2; ?>" id="yptRightBar">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
                    require "{$global['systemRootPath']}view/modeYoutubeBottomRight.php";
                    $modeYouTubeTimeLog['After include bottom right '] = microtime(true) - $modeYouTubeTime;
                    $modeYouTubeTime = microtime(true);
                    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
<?php
} else {
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
    require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
?>
    <div class="row" id="modeYoutubeBottom" style="margin: 0;">
        <div class="col-lg-1"></div>
        <div class="<?php echo $modeYoutubeBottomCols1; ?>" id="modeYoutubeBottomContent">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    getTrendingVideos(48, 3, 3, 3, 1);
                    $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
                    $modeYouTubeTime = microtime(true);
                    ?>
                </div>
            </div>
        </div>
        <div class="<?php echo $modeYoutubeBottomCols2; ?>" id="yptRightBar">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    require "{$global['systemRootPath']}view/modeYoutubeBottomRight.php";
                    $modeYouTubeTimeLog['After include bottom right '] = microtime(true) - $modeYouTubeTime;
                    $modeYouTubeTime = microtime(true);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
<?php
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
}
?>
