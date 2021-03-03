<?php
$_GET['isMediaPlaySite'] = $video['id'];
$isAudio = 1;
$timerDuration = "";
if ($video['type'] == "linkAudio") {
    $timerDuration = '$("time.duration").hide();';
}
if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $aspectRatio = "9:16";
    $vjsClass = "vjs-9-16";
    $embedResponsiveClass = "embed-responsive-9by16";
} else {
    $aspectRatio = "16:9";
    $vjsClass = "vjs-16-9";
    $embedResponsiveClass = "embed-responsive-16by9";
}

if (!empty($video['externalOptions']->videoStartSeconds)) {
    $video['externalOptions']->videoStartSeconds = parseDurationToSeconds($video['externalOptions']->videoStartSeconds);
} else {
    $video['externalOptions']->videoStartSeconds = 0;
}

$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");

$ext = "";
if ($video['type'] == "audio") {
    if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".ogg")) {
        $ext = ".ogg";
    } else {
        $ext = ".mp3";
    }
}

$sources = getVideosURL($video['filename']);
$sourceLink = $sources['mp3']['url'];
if ($video['type'] !== "audio") {
    $sourceLink = $video['videoLink'];
}

if ($video['type'] != "audio") {
    $waveSurferEnabled = false;
} else {
    $waveSurferEnabled = !empty($advancedCustom->EnableWavesurfer);
}
?> 
<!-- audio -->
<div class="row main-video" id="mvideo">
    <div class="col-md-2 firstC"></div>
    <div class="col-md-8 secC">
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
                <!-- <?php echo $video['filename']; ?> -->
                <audio playsinline webkit-playsinline="webkit-playsinline" 
                       preload="auto"
                       poster="<?php echo $poster; ?>" controls class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" id="mainVideo">
                           <?php
                           if ($waveSurferEnabled == false) {
                               echo getSources($video['filename']);
                           }
                           ?>
                </audio>

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

            <?php
            showCloseButton();
            ?>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>
<script>
    var mediaId = <?php echo $video['id']; ?>;
<?php
echo $timerDuration;
$getDataSetup = "";
if ($waveSurferEnabled) {

    $getDataSetup = "{
                    controls: true,
                    autoplay: true,
                    fluid: false,
                    loop: false,
                    width: 600,
                    height: 300,
                    plugins: {
                        wavesurfer: {
                            src: '{$sourceLink}',
                            msDisplayMax: 10,
                            debug: false,
                            waveColor: 'green',
                            progressColor: 'white',
                            cursorColor: 'blue',
                            hideScrollbar: true
                        }
                    }
                }, function () {
                    // print version information at startup
                    videojs.log('Using video.js', videojs.VERSION, 'with videojs-wavesurfer', videojs.getPluginVersion('wavesurfer'));
                }";
}


echo PlayerSkins::getStartPlayerJS("", $getDataSetup);
PlayerSkins::playerJSCodeOnLoad($video['id'], @$autoPlayURL);
?>

</script>
<!-- audio finish-->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
