<link href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fa fa-arrows"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fa fa-close"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <video poster="<?php echo $global['webSiteRootURL']; ?>img/youphptubeLiveStreaming.jpg" controls crossorigin 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" 
                       id="mainVideo" data-setup='{ "aspectRatio": "16:9",  "techorder" : ["flash", "html5"] }'>
                    <source src="<?php echo $p->getPlayerServer(); ?>/<?php echo $uuid; ?>/index.m3u8" type='application/x-mpegURL'>
                </video>
            </div>
            <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);">
                <?php 
                    $streamName = $uuid;
                    include $global['systemRootPath'].'plugin/Live/view/onlineLabel.php';
                    include $global['systemRootPath'].'plugin/Live/view/onlineUsers.php';
                ?>
            </div>
            
            
        </div>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>

    $(document).ready(function () {
        player = videojs('mainVideo');
        player.ready(function () {
<?php
if ($config->getAutoplay()) {
    echo "this.play();";
}
?>

        });
        player.persistvolume({
            namespace: "YouPHPTube"
        });
    });
</script>
