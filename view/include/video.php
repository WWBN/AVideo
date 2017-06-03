<?php
$playNowVideo = $video;
if (!empty($ad)) {
    $playNowVideo = $ad;
    $logId = Video_ad::log($ad['id']);
}
?>
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div align="center" class="embed-responsive embed-responsive-16by9 <?php
        if (!empty($logId)) {
            echo "ad";
        }
        ?>">
            <video poster="<?php echo $poster; ?>" controls crossorigin 
                   class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" id="mainVideo"  data-setup='{ aspectRatio: "16:9" }'>
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $playNowVideo['filename']; ?>.mp4" type="video/mp4">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $playNowVideo['filename']; ?>.webm" type="video/webm">
                <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                <p class="vjs-no-js">
                    <?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                    <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
            <?php if (!empty($logId)) { ?>
                <div id="adUrl" class="adControl" ><?php echo __("Ad"); ?> <span class="time">0:00</span> <i class="fa fa-info-circle"></i> 
                    <a href="<?php echo $global['webSiteRootURL']; ?>adClickLog?video_ads_logs_id=<?php echo $logId; ?>&adId=<?php echo $ad['id']; ?>" target="_blank" ><?php
                        $url = parse_url($ad['redirect']);
                        echo $url['host'];
                        ?> <i class="fa fa-external-link"></i>
                    </a>
                </div>
                <a id="adButton" href="#" class="adControl" <?php if (!empty($ad['skip_after_seconds'])) { ?> style="display: none;" <?php } ?>><?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
            <?php } ?>
        </div>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>
    var fullFuration = 0;
    var isPlayingAd = false;
    $(document).ready(function () {
        fullFuration = strToSeconds('<?php echo $ad['duration']; ?>');
        player = videojs('mainVideo').ready(function () {
<?php
if ($config->getAutoplay()) {
    echo "this.play();";
} else {
    ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    this.play();
                }
<?php }
?>
<?php if (!empty($logId)) { ?>
                isPlayingAd = true;
                this.on('ended', function () {
                    console.log("Finish Video");
                    if (isPlayingAd) {
                        isPlayingAd = false;
                        $('#adButton').trigger("click");
                    }
    <?php
    // if autoplay play next video
    if (!empty($autoPlayVideo)) {
        ?>
                        else if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                            document.location = '<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $autoPlayVideo['clean_title']; ?>';
                                            }
        <?php
    }
    ?>

                                    });
                                    this.on('timeupdate', function () {
                                        var durationLeft = fullFuration - this.currentTime();
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
                                        console.log("Finish Video");
    <?php
    // if autoplay play next video
    if (!empty($autoPlayVideo)) {
        ?>
                                            if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                                                document.location = '<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $autoPlayVideo['clean_title']; ?>';
                                                                }
        <?php
    }
    ?>

                                                        });
<?php }
?>
                                                });
                                                player.persistvolume({
                                                    namespace: "YouPHPTube"
                                                });
<?php if (!empty($logId)) { ?>
                                                    $('#adButton').click(function () {
                                                        console.log("Change Video");
                                                        fullFuration = strToSeconds('<?php echo $video['duration']; ?>');
                                                        changeVideoSrc(player, "<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>");
                                                                    $(".ad").removeClass("ad");
                                                                    return false;
                                                                });
<?php } ?>
                                                        });
</script>