<?php
$_GET['isMediaPlaySite'] = $video['id'];
$isAudio = 1;
$timerDuration = "";
if ($video['type'] == "linkAudio") {
    $timerDuration = '$("time.duration").hide();';
}


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
<div class="row main-video" style="padding: 10px;" id="mvideo">
    <div class="col-md-2 firstC"></div>
    <div class="col-md-8 secC">
        <div id="videoContainer">
            <?php
            $poster = $global['webSiteRootURL'] . "view/img/recorder.gif";
            if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".jpg")) {
                $poster = $global['webSiteRootURL'] . "videos/" . $video['filename'] . ".jpg";
            }
            ?>
            <audio controls class="center-block video-js vjs-default-skin " id="mainVideo" poster="<?php echo $poster; ?>" style="width: 100%;" >
                <?php
                if ($waveSurferEnabled == false) {
                    echo getSources($video['filename']);
                }
                ?>
            </audio>

            <?php
            include $global['systemRootPath'] . 'view/include/youtubeModeOnFullscreenCloseButton.php';
            ?>
        </div>
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
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
