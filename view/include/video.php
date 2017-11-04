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
<style>
    .compress{
        position: absolute;
        top: 50px;
    }
</style>
<div class="row main-video" id="mvideo">
    <div class="col-sm-2 col-md-2 firstC"></div>
    <div class="col-sm-8 col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fa fa-arrows"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fa fa-close"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive <?php
            echo $embedResponsiveClass;
            if (!empty($logId)) {
                echo " ad";
            }
            ?>">
                <video poster="<?php echo $poster; ?>" controls crossorigin 
                       class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                       id="mainVideo"  data-setup='{ "aspectRatio": "<?php echo $aspectRatio; ?>" }'>
                    <!-- <?php echo $playNowVideo['title'], " ", $playNowVideo['filename']; ?> -->
                           <?php
                                echo getSources($playNowVideo['filename']);
                    ?>
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
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
<script>
    function compress(t) {
        console.log("compress");
        $('#mvideo').find('.firstC').removeClass('col-sm-2');
        $('#mvideo').find('.firstC').removeClass('col-md-2');
        $('#mvideo').find('.firstC').addClass('col-sm-1');
        $('#mvideo').find('.firstC').addClass('col-md-1');
        $('#mvideo').find('.secC').removeClass('col-sm-8');
        $('#mvideo').find('.secC').removeClass('col-md-8');
        $('#mvideo').find('.secC').addClass('col-sm-6');
        $('#mvideo').find('.secC').addClass('col-md-6');
        $('.rightBar').addClass('compress');
        setInterval(function(){ $('.principalContainer').css({'min-height':$('.rightBar').height()}); }, 2000);        
        $('#mvideo').removeClass('main-video');
        left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width()+30; 
        $(".compress").css('left', left);
        
        t.removeClass('fa-compress');
        t.addClass('fa-expand');
    }
    function expand(t) {
        $('#mvideo').find('.firstC').removeClass('col-sm-1');
        $('#mvideo').find('.firstC').removeClass('col-md-1');
        $('#mvideo').find('.firstC').addClass('col-sm-2');
        $('#mvideo').find('.firstC').addClass('col-md-2');
        $('#mvideo').find('.secC').removeClass('col-sm-6');
        $('#mvideo').find('.secC').removeClass('col-md-6');
        $('#mvideo').find('.secC').addClass('col-sm-8');
        $('#mvideo').find('.secC').addClass('col-md-8');
        $(".compress").css('left', "");
        $('.rightBar').removeClass('compress');
        $('#mvideo').addClass('main-video');
        console.log("expand");
        t.removeClass('fa-expand');
        t.addClass('fa-compress');
    }
    function toogleEC(t) {
        if (t.hasClass('fa-expand')) {
            expand(t);
            Cookies.set('compress', false, {
                path: '/',
                expires: 365
            });
        } else {
            compress(t);
            Cookies.set('compress', true, {
                path: '/',
                expires: 365
            });
        }
    }
    var player;
    $(document).ready(function () {
        
        
        $(window).on('resize', function () {
            left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width()+30; 
            $(".compress").css('left', left);
        });
                  
        //Prevent HTML5 video from being downloaded (right-click saved)?
        $('#mainVideo').bind('contextmenu', function () {
            return false;
        });
        fullDuration = strToSeconds('<?php echo @$ad['duration']; ?>');
        player = videojs('mainVideo');

        // Extend default
        var Button = videojs.getComponent('Button');
        var teater = videojs.extend(Button, {
            //constructor: function(player, options) {
            constructor: function () {
                Button.apply(this, arguments);
                //this.addClass('vjs-chapters-button');
                this.addClass('fa-compress');
                this.addClass('fa');
                this.controlText("<?php echo __("Teater"); ?>");                
                if (Cookies.get('compress')==="true") {
                    toogleEC(this);
                }
            },
            handleClick: function () {
                toogleEC(this);
            }
        });

        // Register the new component
        videojs.registerComponent('teater', teater);
        player.getChild('controlBar').addChild('teater', {}, 8);

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
                isPlayingAd = false;
                console.log("Change Video");
                fullDuration = strToSeconds('<?php echo $video['duration']; ?>');
                changeVideoSrc(player, <?php echo json_encode(getSources($video['filename'], true)); ?>);
                            $(".ad").removeClass("ad");
                            return false;
                        });
<?php } ?>
                });
</script>
