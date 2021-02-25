<!-- embed -->
<div class="row main-video" id="mvideo">
    <div class="col-md-2 firstC"></div>
    <div class="col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo(); floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php
            $vjsClass = "";
            $playNowVideo = $video;
            $disableYoutubeIntegration = false;
            if (!empty($advancedCustom->disableYoutubePlayerIntegration)) {
                $disableYoutubeIntegration = true;
            }
            $_GET['isEmbedded'] = "";
            if (((strpos($video['videoLink'], "youtu.be") == false) && (strpos($video['videoLink'], "youtube.com") == false) && (strpos($video['videoLink'], "vimeo.com") == false)) || ($disableYoutubeIntegration)) {
                $_GET['isEmbedded'] = "e";
                ?>
                <!-- embed iframe -->
                <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" >
                    <?php
                    if (function_exists('getVTTTracks')) {
                        echo getVTTTracks($video['filename']);
                    }
                    ?>
                </video>
                <div id="main-video" class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="<?php
                    $url = parseVideos($video['videoLink']);
                    if ($config->getAutoplay()) {
                        $url = addQueryStringParameter($url, 'autoplay',1);
                    }
                    echo $url;
                    ?>"></iframe>
                    <script>
                        $(document).ready(function () {
                            addView(<?php echo $video['id']; ?>, 0);
                        });
                    </script>

                </div>
                <?php
            } else {
                // youtube!
                if ((stripos($video['videoLink'], "youtube.com") != false) || (stripos($video['videoLink'], "youtu.be") != false)) {
                    $_GET['isEmbedded'] = "y";
                } else if ((stripos($video['videoLink'], "vimeo.com") != false)) {
                    $_GET['isEmbedded'] = "v";
                }
                $_GET['isMediaPlaySite'] = $video['id'];
                PlayerSkins::playerJSCodeOnLoad($video['id'], @$autoPlayVideo['url']);
                ?>      
                <div id="main-video" class="embed-responsive embed-responsive-16by9">
                    <!-- embed iframe advancedCustom-> YoutubePlayerIntegration isEmbedded =  <?php echo $_GET['isEmbedded']; ?> -->
                    <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" controls <?php
                    if ($config->getAutoplay()) {
                        echo " autoplay ";
                    }
                    ?> ></video>
                    <script>
                        var player;
                        var mediaId = <?php echo $video['id']; ?>;
                        // Prevent HTML5 video from being downloaded (right-click saved)?
                        $('#mainVideo').bind('contextmenu', function () {
                            return false;
                        });

                        $(document).ready(function () {
                            $(".vjs-control-bar").css("opacity: 1; visibility: visible;");
                        });
                    </script>
                </div>
                <?php
            } // youtube! end
            require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
            // the live users plugin
            if (AVideoPlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {

                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>">
                    <a href="<?php echo $url; ?>"  target="_blank">
                        <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" alt="Logo"  class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6">
                    </a>
                </div>
                <?php
            }
            ?>

            <?php
            showCloseButton();
            ?>
        </div>
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
