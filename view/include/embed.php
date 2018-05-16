<div class="row main-video" id="mvideo">
    <div class="col-sm-2 col-md-2 firstC"></div>
    <div class="col-sm-8 col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="far fa-window-close"></i>
                </button>
            </div>
            
                <?php 
                $disableYoutubeIntegration = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
                if($disableYoutubeIntegration!=false){
                    $disableYoutubeIntegration = $disableYoutubeIntegration->disableYoutubePlayerIntegration;
                }
                $_GET['isEmbedded'] = "";
                if((strpos($video['videoLink'],"youtube.com")==false)||($disableYoutubeIntegration)){ 
                $_GET['isEmbedded'] = "e";
                ?>
                <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="<?php
                echo parseVideos($video['videoLink']);
                if ($config->getAutoplay()) {
                    echo "?autoplay=1";
                }
                ?>"></iframe>

                <?php } else { 
                $_GET['isEmbedded'] = "y";
                ?>
                
                <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <video
                    id="mainVideo"
                    class="video-js vjs-default-skin"
                       controls
                       autoplay
                       data-setup='{  "aspectRatio": "16:9", "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "<?php echo $video['videoLink']; ?>"}] }' >
            </video>
                <script>
                var player;
                $(document).ready(function () {
                    $(".vjs-big-play-button").hide();
                    $(".vjs-control-bar").css("opacity: 1; visibility: visible;");
                    player = videojs('mainVideo');
                    player.on('play', function () {
                        addView(<?php echo $video['id']; ?>);
                    });
                });
                </script>
                
                <?php
                }
                require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
                // the live users plugin
                if (YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {

                    require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                    $style = VideoLogoOverlay::getStyle();
                    $url = VideoLogoOverlay::getLink();
                    ?>
                    <div style="<?php echo $style; ?>">
                        <a href="<?php echo $url; ?>">
                            <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png">
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
<script>
    $(document).ready(function () {
        addView(<?php echo $video['id']; ?>);
    });
</script>
