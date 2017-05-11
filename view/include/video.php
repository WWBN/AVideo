
 
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div align="center" class="embed-responsive embed-responsive-16by9 ad">
            <video poster="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.jpg" controls crossorigin autoplay
                   class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" id="mainVideo"  data-setup='{ aspectRatio: "16:9" }'>
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp4" type="video/mp4">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.webm" type="video/webm">
                <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                <p class="vjs-no-js">
                  <?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                  <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
            <div id="adUrl" class="adControl" ><?php echo __("Ad"); ?> 0:30 <i class="fa fa-info-circle"></i> <a href="#" >urltoredirect.com <i class="fa fa-external-link"></i></a></div>
            <a id="adButton" href="#" class="adControl"><?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
        </div>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>
$(document).ready(function () {
    player = videojs('mainVideo');
    $('#adButton').click(function(){
        changeVideoSrc(player, "<?php echo $global['webSiteRootURL']; ?>videos/vokoscreen20170508115610_591099db7a7a95.22297409");
        $(".ad").removeClass("ad");
    });
});
</script>