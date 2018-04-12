<div class="row main-video" style="padding: 10px;" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">
        <?php
            $poster = $global['webSiteRootURL']."img/recorder.gif";
            if(file_exists($global['systemRootPath']."videos/".$video['filename'].".jpg")){
               $poster = $global['webSiteRootURL']."videos/".$video['filename'].".jpg"; 
            }
        ?>
        <audio controls class="center-block video-js vjs-default-skin "  id="mainAudio" autoplay poster="<?php echo $poster; ?>">
            <?php
            $ext = "";
            if(file_exists($global['systemRootPath']."videos/".$video['filename'].".ogg")){ ?>
                <?php
                    $ext = ".ogg";
                } else { ?>
                <?php
                    $ext = ".mp3";
                } ?>
        </audio>
            <?php if ($config->getAllow_download()) { ?>
                <a class="btn btn-xs btn-default " role="button" href="<?php echo $global['webSiteRootURL'] . "videos/" . $video['filename'].$ext; ?>" download="<?php echo $video['title'] . $ext; ?>"><?php echo __("Download audio"); ?></a>
            <?php } ?>
    </div>
    <link href="<?php echo $global['webSiteRootURL']; ?>js/videojs-wavesurfer/dist/css/videojs.wavesurfer.min.css" rel="stylesheet" type="text/css"/>

    <script>
        $(document).ready(function () {
            $(".vjs-big-play-button").hide();
            player = videojs('mainAudio', {
            controls: true,
            width: 400,
            height: 200,
                plugins: {
                    wavesurfer: {
                        src: '<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename'].$ext; ?>',
                        debug: true,
                        waveColor: 'yellow',
                        cursorWidth: 0,
                        interact: true,
                        hideScrollbar: false
                    }
                }
            });

            player.ready(function () {
            <?php
                if ($config->getAutoplay()) {
                    echo "setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainAudio');}player.play();}, 150);";
                } else {
            ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainAudio');} player.play();}, 150);                    
                }
            <?php } ?>
            <?php if (!empty($logId)) { ?>
                isPlayingAd = true;
                this.on('ended', function () {
                    console.log("Finish Audio");
                    if (isPlayingAd) {
                        isPlayingAd = false;
                        $('#adButton').trigger("click");
                    }
            <?php // if autoplay play next video
            if (!empty($autoPlayVideo)) { ?>
                else if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    document.location = '<?php echo $autoPlayVideo['url']; ?>';
                }
            <?php } ?>
                });
                this.on('timeupdate', function () {
                    var durationLeft = fullDuration - this.currentTime();
                    $("#adUrl .time").text(secondsToStr(durationLeft + 1, 2));
            <?php if (!empty($ad['skip_after_seconds'])) { ?>
                        if (isPlayingAd && this.currentTime() ><?php echo intval($ad['skip_after_seconds']); ?>) {
                            $('#adButton').fadeIn();
                    }
            <?php } ?>
                });
            <?php } else { ?>
                this.on('ended', function () {
                    console.log("Finish Audio");
    <?php // if autoplay play next video
    if (!empty($autoPlayVideo)) { ?>
        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
            document.location = '<?php echo $autoPlayVideo['url']; ?>';
        }
        <?php } ?>
                });
    <?php } ?>
        }); });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
