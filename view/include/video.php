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
            <video poster="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.jpg" controls crossorigin autoplay
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
            <div id="adUrl" class="adControl" ><a href="<?php echo $global['webSiteRootURL']; ?>adClickLog?video_ads_logs_id=<?php echo $logId; ?>&adId=<?php echo $ad['id']; ?>" target="_blank" ><?php 
                                    $url = parse_url($ad['redirect']);
                                    echo $url['host']; ?> <i class="fa fa-external-link"></i></a></div>
                <a id="adButton" href="#" class="adControl"><?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
            <?php } ?>
        </div>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>
    var addSkiped = false;
    $(document).ready(function () {
        player = videojs('mainVideo').ready(function () {
            player.on('ended', function () {

                console.log("Finish Video");
<?php if (!empty($logId)) { ?>
                    if (!addSkiped) {
                        addSkiped = true;
                        $('#adButton').trigger("click");
                    }
<?php } ?>
            });
        });
<?php if (!empty($logId)) { ?>
            $('#adButton').click(function () {
                console.log("Change Video");
                changeVideoSrc(player, "<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>");
                            $(".ad").removeClass("ad");
                            return false;
                        });
<?php } ?>
                });
</script>