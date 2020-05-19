<?php
$sources = getVideosURLPDF($video['filename']);
?>
<div class="row main-video article-video" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">

        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <video playsinline webkit-playsinline="webkit-playsinline" id="mainVideo"
                   style="display: none; height: 0;width: 0;"></video>
            <div id="main-video" class="bgWhite list-group-item article">
                <h1 class="articleTitle">
                    <?php
                    echo $video['title'];
                    ?>
                </h1>
                <?php
                echo $video['description'];
                ?>
                <script>
                    $(document).ready(function () {
                        addView(<?php echo $video['id']; ?>, 0);
                    });
                </script>

            </div>
            <?php
            if (AVideoPlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {

                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>">
                    <a href="<?php echo $url; ?>" target="_blank">
                        <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png"
                             class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6">
                    </a>
                </div>
                <?php
            }
            ?>

            <a href="<?php echo $global["HTTP_REFERER"]; ?>" class="btn btn-outline btn-xs"
               style="position: absolute; top: 5px; right: 5px; display: none;" id="youtubeModeOnFullscreenCloseButton">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
    <script>
        $(document).ready(function () {

        });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
