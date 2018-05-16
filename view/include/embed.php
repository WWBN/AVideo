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
            /*$autoPlayVideo = Video::getVideo($video['next_videos_id']);
            if($video==$autoPlayVideo){
                unset($autoPlayVideo);
            }*/
            if ($video['rotation'] === "90" || $video['rotation'] === "270") {
                $aspectRatio = "9:16";
                $vjsClass = "vjs-9-16";
                $embedResponsiveClass = "embed-responsive-9by16";
            } else {
                $aspectRatio = "16:9";
                $vjsClass = "vjs-16-9";
                $embedResponsiveClass = "embed-responsive-16by9";
            }
                $playNowVideo = $video;
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
                    class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered"
                       controls
                       <?php
                if ($config->getAutoplay()) {
                    echo " autoplay ";
                }
                ?>
                       data-setup='{  "aspectRatio": "16:9", "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "<?php echo $video['videoLink']; ?>"}] }' >
            </video>
                <script>
                var player;
                var mediaId = <?php echo $video['id']; ?>;
                
                    <?php if (!$config->getAllow_download()) { ?>
                    // Prevent HTML5 video from being downloaded (right-click saved)?
                    $('#mainVideo').bind('contextmenu', function () {
                        return false;
                    });
                    <?php } ?>
                
                $(document).ready(function () {
                    player = videojs('mainVideo');
                            player.ready(function () {
                            <?php if ($config->getAutoplay()) {
	                           echo "setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainVideo');} player.play(); }, 50);";
                            } else { ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainVideo');} player.play();}, 50);                    
                }
                <?php } ?>
                            num = $('#videosList').find('.pagination').find('li.active').attr('data-lp');
                            loadPage(num);                 
                            });
                    

                    //$(".vjs-big-play-button").hide();
                    $(".vjs-control-bar").css("opacity: 1; visibility: visible;");
                    
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
