<?php
$TimeLogLimitMYB = 0.05;
$timeLogNameMYB = TimeLogStart("modeYoutubeBundle.php");
$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

if (!$isCompressed) {
    ?>
    <div class="" id="modeYoutubeTop" >
        <?php
        require "{$global['systemRootPath']}view/modeYoutubeTop.php";
    $modeYouTubeTimeLog['After include top '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true); ?>
    </div>
    <?php
}
TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
if (!empty($video['id'])) {
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB); ?>
    <div class="row" id="modeYoutubeBottom" style="margin: 0;">
        <div class="col-lg-1"></div>
        <div class="col-sm-7 col-md-7 col-lg-6" id="modeYoutubeBottomContent">
            <?php
            if ($isCompressed) {
                ?>
                <div class="" id="modeYoutubeTop" >
                    <?php
                    require "{$global['systemRootPath']}view/modeYoutubeTop.php";
                $modeYouTubeTimeLog['After include top '] = microtime(true) - $modeYouTubeTime;
                $modeYouTubeTime = microtime(true); ?>
                </div>
                <?php
            } ?>
            <?php
            TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
    require "{$global['systemRootPath']}view/modeYoutubeBottom.php";
    $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB); ?>
        </div>
        <div class="col-sm-5 col-md-5 col-lg-4 rightBar clearfix" id="yptRightBar" >
            <div class="list-group-item clearfix">
                <?php
                TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
    require "{$global['systemRootPath']}view/modeYoutubeBottomRight.php";
    $modeYouTubeTimeLog['After include bottom right '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB); ?>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <?php
} else {
        TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
        require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php'; ?>
    <div class="row" id="modeYoutubeBottom" style="margin: 0;">
        <div class="col-lg-1"></div>
        <div class="col-sm-7 col-md-7 col-lg-6" id="modeYoutubeBottomContent">
            <?php
            if ($isCompressed) {
                ?>
                <div class="" id="modeYoutubeTop" >
                    <?php
                    require "{$global['systemRootPath']}view/modeYoutubeTop.php";
                $modeYouTubeTimeLog['After include top '] = microtime(true) - $modeYouTubeTime;
                $modeYouTubeTime = microtime(true); ?>
                </div>
                <?php
            } ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    getTrendingVideos(48, 3, 3, 3, 1);
        $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true); ?>
                </div>
            </div>
        </div>
        <div class="col-sm-5 col-md-5 col-lg-4 rightBar clearfix" id="yptRightBar" >
            <div class="list-group-item clearfix">
                <?php
                require "{$global['systemRootPath']}view/modeYoutubeBottomRight.php";
        $modeYouTubeTimeLog['After include bottom right '] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true); ?>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <?php
    TimeLogEnd($timeLogNameMYB, __LINE__, $TimeLogLimitMYB);
    }
?>