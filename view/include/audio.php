<div class="row main-video" style="padding: 10px;">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 ">
        <audio controls class="center-block video-js vjs-default-skin "  id="mainAudio" autoplay data-setup='{}'
               poster="<?php echo $global['webSiteRootURL']; ?>img/recorder.gif">
            <?php if(file_exists($global['systemRootPath']."videos/".$video['filename'].".ogg")){ ?>
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg">horse</a>
            <?php } else { ?>
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" /> 
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
            <?php } ?>
        </audio>
    </div>
    <script>
        $(document).ready(function () {
        player = videojs('mainAudio');
 player.ready(function () {
<?php
if ($config->getAutoplay()) {
    echo "setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainAudio');}player.play();}, 150);";
} else {
    ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainAudio');} player.play();}, 150);                    
                }
<?php }
?>
<?php if (!empty($logId)) { ?>
                isPlayingAd = true;
                this.on('ended', function () {
                    console.log("Finish Audio");
                    if (isPlayingAd) {
                        isPlayingAd = false;
                        $('#adButton').trigger("click");
                    }
    <?php
    // if autoplay play next video
    if (!empty($autoPlayVideo)) {
        ?>
                        else if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                            document.location = '<?php echo $autoPlayVideo['url']; ?>';
                        }
        <?php
    }
    ?>

                });
                this.on('timeupdate', function () {
                    var durationLeft = fullDuration - this.currentTime();
                    $("#adUrl .time").text(secondsToStr(durationLeft + 1, 2));
    <?php if (!empty($ad['skip_after_seconds'])) {
        ?>
                        if (isPlayingAd && this.currentTime() ><?php echo intval($ad['skip_after_seconds']); ?>) {
                            $('#adButton').fadeIn();
                        }
    <?php }
    ?>
                });
<?php } else {
    ?>
                this.on('ended', function () {
                    console.log("Finish Audio");
    <?php
    // if autoplay play next video
    if (!empty($autoPlayVideo)) {
        ?>
                        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                            document.location = '<?php echo $autoPlayVideo['url']; ?>';
                        }
        <?php
    }
    ?>

                });
<?php }
?>
        }); });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->