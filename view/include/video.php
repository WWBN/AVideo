<?php
$playNowVideo = $video;
$transformation = "{rotate:" . $video['rotation'] . ", zoom: " . $video['zoom'] . "}";
if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $aspectRatio = "9:16";
    $vjsClass = "vjs-9-16";
    $embedResponsiveClass = "embed-responsive-9by16";
} else {
    $aspectRatio = "16:9";
    $vjsClass = "vjs-16-9";
    $embedResponsiveClass = "embed-responsive-16by9";
}

if (!empty($ad)) {
    $playNowVideo = $ad;
    $logId = Video_ad::log($ad['id']);
}
?>
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div align="center" id="main-video" class="embed-responsive <?php
        echo $embedResponsiveClass;
        if (!empty($logId)) {
            echo " ad";
        }
        ?>">
            <video poster="<?php echo $poster; ?>" controls crossorigin 
                   class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" id="mainVideo"  data-setup='{ aspectRatio: "<?php echo $aspectRatio; ?>" }'>
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

    var changingVideoFloat = 0;
    var mainVideoHeight = $('#main-video').innerHeight();
    var fullDuration = 0;
    var isPlayingAd = false;
    $(document).ready(function () {
        
        fullDuration = strToSeconds('<?php echo $ad['duration']; ?>');
        player = videojs('mainVideo');

        player.zoomrotate(<?php echo $transformation; ?>);
        player.ready(function () {
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
                    console.log("Finish Video");
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
        });
        player.persistvolume({
            namespace: "YouPHPTube"
        });
<?php if (!empty($logId)) { ?>
            $('#adButton').click(function () {
                console.log("Change Video");
                fullDuration = strToSeconds('<?php echo $video['duration']; ?>');
                changeVideoSrc(player, "<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>");
                            $(".ad").removeClass("ad");
                            return false;
                        });
<?php } ?>

                    $(window).resize(function () {

                        mainVideoHeight = $('#main-video').innerHeight();
                    });
                    $(window).scroll(function () {
                        if (changingVideoFloat) {
                            return false;
                        }
                        changingVideoFloat = 1;
                        var s = $(window).scrollTop();
                        if (s > mainVideoHeight) {
                            if (!$('#main-video').hasClass("floatVideo")) {
                                $('#main-video').hide();
                                $('#main-video').addClass('floatVideo');
                                $('#main-video').parent().css('height', mainVideoHeight);
                                changingVideoFloat = 0;
                                $('#main-video').slideDown(1000);
                            } else {
                                changingVideoFloat = 0;
                            }
                        } else {
                            if ($('#main-video').hasClass("floatVideo")) {
                                $('#main-video').fadeOut('fast', function () {
                                    $('#main-video').parent().css('height', '');
                                    $('#main-video').removeClass('floatVideo');
                                    changingVideoFloat = 0;
                                });
                                $('#main-video').fadeIn();
                            } else {
                                changingVideoFloat = 0;
                            }
                        }
                    });
                });
</script>
