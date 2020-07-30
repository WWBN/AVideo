<?php
$playNowVideo = $video;
if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $aspectRatio = "9:16";
    $vjsClass = "vjs-9-16";
    $embedResponsiveClass = "embed-responsive-9by16";
} else {
    $aspectRatio = "16:9";
    $vjsClass = "vjs-16-9";
    $embedResponsiveClass = "embed-responsive-16by9";
}
$currentTime = 0;
if (!empty($video['externalOptions']->videoStartSeconds)) {
    $video['externalOptions']->videoStartSeconds = parseDurationToSeconds($video['externalOptions']->videoStartSeconds);
} else {
    $video['externalOptions']->videoStartSeconds = 0;
}
if (isset($_GET['t'])) {
    $currentTime = intval($_GET['t']);
} else if (!empty($video['progress']['lastVideoTime'])) {
    $currentTime = intval($video['progress']['lastVideoTime']);
    $maxCurrentTime = parseDurationToSeconds($video['duration']);
    if ($maxCurrentTime <= $currentTime + 5) {
        if (!empty($video['externalOptions']->videoStartSeconds)) {
            $currentTime = intval($video['externalOptions']->videoStartSeconds);
        } else {
            $currentTime = 0;
        }
    }
} else if (!empty($video['externalOptions']->videoStartSeconds)) {
    $currentTime = intval($video['externalOptions']->videoStartSeconds);
}

$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$dataSetup = PlayerSkins::getDataSetup();
?>
<div class="row main-video" id="mvideo">
    <div class="col-sm-2 col-md-2 firstC"></div>
    <div class="col-sm-8 col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs"
                        onclick="closeFloatVideo(); floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive <?php echo $embedResponsiveClass; ?>">
                <video playsinline webkit-playsinline="webkit-playsinline" 
                <?php if ($config->getAutoplay() && false) { // disable it for now   ?>
                           autoplay="true"
                           muted="muted"
                       <?php } ?>
                       preload="auto"
                       poster="<?php echo $poster; ?>" controls class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" id="mainVideo">
                           <?php if ($playNowVideo['type'] == "video") { ?>
                        <!-- <?php echo $playNowVideo['title'], " ", $playNowVideo['filename']; ?> -->
                        <?php
                        echo getSources($playNowVideo['filename']);
                    } else {
                        ?>
                        <source src="<?php echo $playNowVideo['videoLink']; ?>" type="<?php echo (strpos($playNowVideo['videoLink'], 'm3u8') !== false) ? "application/x-mpegURL" : "video/mp4" ?>" >
                    <?php } ?>
                    <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                    <p class="vjs-no-js"><?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                        <a href="http://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer">supports HTML5 video</a>
                    </p>
                </video>

            </div>
            <?php
            if (AVideoPlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>" class="VideoLogoOverlay">
                    <a href="<?php echo $url; ?>" target="_blank"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" alt="Logo"  class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                </div>
            <?php } ?>

            <a href="<?php echo $global["HTTP_REFERER"]; ?>" class="btn btn-outline btn-xs" style="position: absolute; top: 5px; right: 5px; display: none;" id="youtubeModeOnFullscreenCloseButton">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
<script>
    var mediaId = '<?php echo $playNowVideo['id']; ?>';
    var player;
    $(document).ready(function () {

<?php
$_GET['isMediaPlaySite'] = $playNowVideo['id'];
if ($playNowVideo['type'] == "linkVideo") {
    echo '$("time.duration").hide();';
}
?>
    if (typeof player === 'undefined') {
    player = videojs('mainVideo'<?php echo $dataSetup; ?>);
    }
    player.on('play', function () {
    addView(<?php echo $playNowVideo['id']; ?>, this.currentTime());
    });
    player.ready(function () {

<?php if ($config->getAutoplay()) {
    ?>
        setTimeout(function () {
        if (typeof player === 'undefined') {
        player = videojs('mainVideo'<?php echo $dataSetup; ?>);
        }
        playerPlay(<?php echo $currentTime; ?>);
        }, 150);
<?php } else { ?>

        if (typeof player !== 'undefined') {
        player.currentTime(<?php echo $currentTime; ?>);
        } else{
        setTimeout(function () {
        player.currentTime(<?php echo $currentTime; ?>);
        }, 1000);
        }
        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
        setTimeout(function () {
        if (typeof player === 'undefined') {
        player = videojs('mainVideo'<?php echo $dataSetup; ?>);
        }
        playerPlay(<?php echo $currentTime; ?>);
        }, 150);
        }

        var initdone = false;
        // wait for video metadata to load, then set time 
        player.on("loadedmetadata", function(){
        player.currentTime(<?php echo $currentTime; ?>);
        });
        // iPhone/iPad need to play first, then set the time
        // events: https://www.w3.org/TR/html5/embedded-content-0.html#mediaevents
        player.on("canplaythrough", function(){
        if (!initdone){
        player.currentTime(<?php echo $currentTime; ?>);
        initdone = true;
        }
        });
<?php }
?>
    this.on('ended', function () {
    console.log("Finish Video");
<?php
// if autoplay play next video
if (!empty($autoPlayVideo)) {
    ?>
        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
    <?php
    if ($autoPlayVideo['type'] !== 'video' || empty($advancedCustom->autoPlayAjax)) {
        ?>

            document.location = autoPlayURL;
        <?php
    } else {
        ?>
            $('video, #mainVideo').attr('poster', autoPlayPoster);
            changeVideoSrc(player, autoPlaySources);
            history.pushState(null, null, autoPlayURL);
            $('.vjs-thumbnail-holder, .vjs-thumbnail-holder img').attr('src', autoPlayThumbsSprit);
            $.ajax({
            url: autoPlayURL,
                    success: function (response) {
                    modeYoutubeBottom = $(response).find('#modeYoutubeBottom').html();
                    $('#modeYoutubeBottom').html(modeYoutubeBottom);
                    }
            });
        <?php
    }
    ?>
        }
<?php } ?>

    });
    this.on('timeupdate', function () {
    var time = Math.round(this.currentTime());
    var url = '<?php echo Video::getURLFriendly($video['id']); ?>';
    if (url.indexOf('?') > - 1){
    url += '&t=' + time;
    } else{
    url += '?t=' + time;
    }
    $('#linkCurrentTime').val(url);
    if (time >= 5 && time % 5 === 0) {
    addView(<?php echo $video['id']; ?>, time);
    }
    });
    this.on('ended', function () {
    var time = Math.round(this.currentTime());
    addView(<?php echo $video['id']; ?>, time);
    });
    });
    player.persistvolume({
    namespace: "AVideo"
    });
    // in case the video is muted
    setTimeout(function () {
    if (typeof player === 'undefined') {
    player = videojs('mainVideo'<?php echo $dataSetup; ?>);
    }

    }, 1500);
    }
    );
</script>
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
