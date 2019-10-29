<?php
$_GET['isMediaPlaySite'] = $video['id'];

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
<div class="row main-video" style="padding: 10px;" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">
        <div id="videoContainer">
            <?php
            $poster = $global['webSiteRootURL'] . "view/img/recorder.gif";
            if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".jpg")) {
                $poster = $global['webSiteRootURL'] . "videos/" . $video['filename'] . ".jpg";
            }
            ?>
            <audio controls class="center-block video-js vjs-default-skin " <?php if ($waveSurferEnabled == false) { ?> autoplay data-setup='{"controls": true}' <?php } ?> id="mainAudio" poster="<?php echo $poster; ?>" style="width: 100%;" >
                <?php
                if ($waveSurferEnabled == false) {
                    echo getSources($video['filename']);
                }
                ?>
            </audio>
            
            <a href="<?php echo $global["HTTP_REFERER"]; ?>" class="btn btn-outline btn-xs" style="position: absolute; top: 5px; right: 5px; display: none;" id="youtubeModeOnFullscreenCloseButton">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
    <script>
        var mediaId = <?php echo $video['id']; ?>;
        $(document).ready(function () {

            $(".vjs-big-play-button").hide();
            $(".vjs-control-bar").css("opacity: 1; visibility: visible;");
<?php
echo $timerDuration;
if ($waveSurferEnabled) {
    ?>
                player = videojs('mainAudio', {
                    controls: true,
                    autoplay: true,
                    fluid: false,
                    loop: false,
                    width: 600,
                    height: 300,
                    plugins: {
                        wavesurfer: {
                            src: '<?php echo $sourceLink; ?>',
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
                });
<?php } else { ?>
                player = videojs('mainAudio');
<?php } ?>
            // error handling
            player.on('error', function (error) {
                console.warn('VideoJS-ERROR:', error);
            });
            /* was rising an error
             player.on('loadedmetadata', function () {
             fullDuration = player.duration();
             });
             */
            player.ready(function () {
<?php
if ($config->getAutoplay()) {
    echo "setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainAudio');}player.play();}, 150);";
} else {
    ?>
                    if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                        setTimeout(function () {
                            if (typeof player === 'undefined') {
                                player = videojs('mainAudio');
                            }
                            player.play();
                        }, 150);
                    }
<?php } ?>
                this.on('ended', function () {
                    console.log("Finish Audio");
<?php
// if autoplay play next video
if (!empty($autoPlayVideo)) {
    ?>
                        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                            document.location = '<?php echo $autoPlayVideo['url']; ?>';
                        }
<?php } ?>
                });

                this.on('timeupdate', function () {
                    var time = Math.round(this.currentTime());
                    $('#linkCurrentTime').val('<?php echo Video::getURLFriendly($video['id']); ?>?t=' + time);
                    if (time >= 5 && time % 5 === 0) {
                        addView(<?php echo $video['id']; ?>, time);
                    }
                });
                this.on('play', function () {
                    addView(<?php echo $video['id']; ?>, this.currentTime());
                });

                player.on('timeupdate', function () {
                    var time = Math.round(this.currentTime());
                    if (time >= 5 && time % 5 === 0) {
                        addView(<?php echo $video['id']; ?>, time);
                    }
                });

                player.on('ended', function () {
                    var time = Math.round(this.currentTime());
                    addView(<?php echo $video['id']; ?>, time);
                });
            });
        });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
