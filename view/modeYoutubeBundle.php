<?php
$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

if (!$isCompressed) {
    ?>
    <div class="" id="modeYoutubeTop" >
        <?php
        require "{$global['systemRootPath']}view/modeYoutubeTop.php";
        $modeYouTubeTimeLog['After include top '] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        ?>
    </div>
    <?php
}
?>
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
                $modeYouTubeTime = microtime(true);
                ?>
            </div>
            <?php
        }
        ?>
        <?php
        require "{$global['systemRootPath']}view/modeYoutubeBottom.php";
        $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        ?>
    </div>
    <div class="col-sm-5 col-md-5 col-lg-4 rightBar" id="yptRightBar" >
        <div class="list-group-item ">
            <?php
            require "{$global['systemRootPath']}view/modeYoutubeBottomRight.php";
            $modeYouTubeTimeLog['After include bottom right '] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            ?>
        </div>
    </div>
    <div class="col-lg-1"></div>
</div>  