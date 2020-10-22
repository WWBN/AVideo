<?php
$liveLink = "Invalid link";
if (filter_var($t['link'], FILTER_VALIDATE_URL)) {
    $url = parse_url($t['link']);
    if ($url['scheme'] == 'https') {
        $liveLink = $t['link'];
    } else {
        $liveLink = "{$global['webSiteRootURL']}plugin/LiveLinks/proxy.php?livelink=" . urlencode($t['link']);
    }
}
?>
<link href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>
<div class="row main-video" id="mvideo">
    <div class="firstC col-sm-2 col-md-2"></div>
    <div class="secC col-sm-8 col-md-8">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <video poster="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg" controls 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" 
                       id="mainVideo">
                    <source src="<?php echo $liveLink; ?>" type='application/x-mpegURL'>
                </video>
            </div>
            <?php
            if (AVideoPlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>" class="VideoLogoOverlay">
                    <a href="<?php echo $url; ?>" target="_blank"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-sm-2 col-md-2"></div>
</div>
<script>
<?php
echo PlayerSkins::getStartPlayerJS();
?>
</script>