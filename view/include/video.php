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
<div class="row main-video" id="mvideo">
	<div class="col-sm-2 col-md-2 firstC"></div>
	<div class="col-sm-8 col-md-8 secC">
		<div id="videoContainer">
			<div id="floatButtons" style="display: none;">
				<p class="btn btn-outline btn-xs move">
					<i class="fa fa-arrows"></i>
				</p>
				<button type="button" class="btn btn-outline btn-xs"
					onclick="closeFloatVideo();floatClosed = 1;">
					<i class="fa fa-close"></i>
				</button>
			</div>
			<div id="main-video" class="embed-responsive <?php echo $embedResponsiveClass; if (!empty($logId)) { echo " ad"; } ?>">
				<video preload="auto" poster="<?php echo $poster; ?>" controls class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" id="mainVideo" data-setup='{ "aspectRatio": "<?php echo $aspectRatio; ?>" }'>
				    <!-- <?php echo $playNowVideo['title'], " ", $playNowVideo['filename']; ?> -->
                    <?php echo getSources($playNowVideo['filename']); ?>
                    <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
				    <p class="vjs-no-js"><?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
				    </p>
				</video>
                <?php require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
                // the live users plugin
if (YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
	require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
	$style = VideoLogoOverlay::getStyle();
	$url = VideoLogoOverlay::getLink(); ?>
                    <div style="<?php echo $style; ?>">
                        <a href="<?php echo $url; ?>"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png"></a>
				    </div>
                    <?php } if (!empty($logId)) { ?>
                <div id="adUrl" class="adControl"><?php echo __("Ad"); ?> <span class="time">0:00</span> <i class="fa fa-info-circle"></i>
                    <a href="<?php echo $global['webSiteRootURL']; ?>adClickLog?video_ads_logs_id=<?php echo $logId; ?>&adId=<?php echo $ad['id']; ?>" target="_blank"><?php $url = parse_url($ad['redirect']); echo $url['host'];?> 
                        <i class="fa fa-external-link"></i>
                    </a>
				</div>
				<a id="adButton" href="#" class="adControl" <?php if (!empty($ad['skip_after_seconds'])) { ?> style="display: none;" <?php } ?>>
                    <?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
                <?php } ?>
            </div>
		</div>
            <?php if ($config->getAllow_download()) { ?>
                <a class="btn btn-xs btn-default " role="button" href="<?php echo $global['webSiteRootURL'] . "videos/" . $playNowVideo['filename']; ?>.mp4" download="<?php echo $playNowVideo['title'] . ".mp4"; ?>"><?php echo __("Download video"); ?></a>
            <?php } ?>
    </div>
	<div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
<script>
    var player;
    $(document).ready(function () {
    <?php if (!$config->getAllow_download()) { ?>
        // Prevent HTML5 video from being downloaded (right-click saved)?
        $('#mainVideo').bind('contextmenu', function () {
            return false;
        });
        <?php } ?>
        fullDuration = strToSeconds('<?php echo @$ad['duration']; ?>');
        player = videojs('mainVideo');
        player.zoomrotate(<?php echo $transformation; ?>);
        player.on('play', function () {
            addView(<?php echo $playNowVideo['id']; ?>);
          });
        player.ready(function () {
<?php if ($config->getAutoplay()) {
	echo "setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainVideo');}player.play();}, 150);";
} else { ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    setTimeout(function () { if(typeof player === 'undefined'){ player = videojs('mainVideo');} player.play();}, 150);                    
                }
<?php }
if (!empty($logId))
	{ ?>
                isPlayingAd = true;
                this.on('ended', function () {
                    console.log("Finish Video");
                    if (isPlayingAd) {
                        isPlayingAd = false;
                        $('#adButton').trigger("click");
                    }
    <?php
	// if autoplay play next video
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
        if (isPlayingAd && this.currentTime() ><?php
		echo intval($ad['skip_after_seconds']); ?>) {
            $('#adButton').fadeIn();
        }
    <?php } ?>
                });
<?php } else { ?>
                this.on('ended', function () {
                    console.log("Finish Video");
    <?php // if autoplay play next video
	if (!empty($autoPlayVideo)) { ?>
        if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
           document.location = '<?php
		echo $autoPlayVideo['url']; ?>';
          }
        <?php } ?>

                });
<?php } ?>
        });
        player.persistvolume({
            namespace: "YouPHPTube"
        });
<?php
if (!empty($logId)){
	$sources = getSources($video['filename'], true); ?>
            $('#adButton').click(function () {
                isPlayingAd = false;
                console.log("Change Video");
                fullDuration = strToSeconds('<?php echo $video['duration']; ?>');
                changeVideoSrc(player, <?php echo json_encode($sources); ?>);
                $(".ad").removeClass("ad");
                return false;
            });
<?php } ?>
    });
</script>
