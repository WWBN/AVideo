<link href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <video poster="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg" controls 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered liveVideo vjs-16-9" 
                       id="mainVideo">
                    <source src="<?php echo $p->getM3U8File($uuid); ?>" type='application/x-mpegURL'>
                </video>
                <?php
                if (YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                    require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                    $style = VideoLogoOverlay::getStyle();
                    $url = VideoLogoOverlay::getLink();
                    ?>
                    <div style="<?php echo $style; ?>">
                        <a href="<?php echo $url; ?>" target="_blank"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                    </div>
                <?php } ?>


            </div>
            <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);">
                <?php
                $streamName = $uuid;
                include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
                include $global['systemRootPath'] . 'plugin/Live/view/onlineUsers.php';
                ?>
            </div>


            <?php
            $liveCount = YouPHPTubePlugin::loadPluginIfEnabled('LiveCountdownEvent');
            $html = array();
            if ($liveCount) {
                $html = $liveCount->getNextLiveApplicationFromUser($user_id);
            }
            foreach ($html as $value) {
                echo $value['html'];
            };
            ?>
        </div>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>

    $(document).ready(function () {
        if (typeof player === 'undefined') {
            player = videojs('mainVideo');
        }
        player.ready(function () {
            var err = this.error();
            if (err && err.code) {
                $('.vjs-error-display').hide();
                $('#mainVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Offline.jpg)'});
<?php
if (!empty($html)) {
    echo "showCountDown();";
}
?>
            }
<?php
if ($config->getAutoplay()) {
    echo "this.play();";
}
?>

        });
<?php
if ($config->getAutoplay()) {
    echo "setTimeout(function(){player.play();},1000);";
}
?>
        player.persistvolume({
            namespace: "YouPHPTube"
        });
    });
</script>
