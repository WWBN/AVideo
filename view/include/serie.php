<div class="row main-video" id="mvideo">
    <div class="col-sm-2 col-md-2 firstC"></div>
    <div class="col-sm-8 col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/embed.php?playlists_id=<?php
                echo $video['serie_playlists_id'];
                if ($config->getAutoplay()) {
                    echo "&autoplay=1";
                }
                ?>"></iframe>
                <script>
                    $(document).ready(function () {
                        addView(<?php echo $video['id']; ?>, 0);
                    });
                </script>

            </div>
            
            
            <a href="<?php echo $global["HTTP_REFERER"]; ?>" class="btn btn-outline btn-xs" style="position: absolute; top: 5px; right: 5px; display: none;" id="youtubeModeOnFullscreenCloseButton">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
