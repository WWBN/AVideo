<script src="<?php echo getURL('node_modules/videojs-contrib-ads/dist/videojs.ads.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/videojs-ima/dist/videojs.ima.min.js'); ?>" type="text/javascript"></script>
<script>
    if (typeof player === 'undefined' && $('#mainVideo').length) {
        player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
    }
    var options = {id: 'mainVideo', adTagUrl: webSiteRootURL+'plugin/AD_Server/VMAP.php?video_length=<?php echo $video_length ?>&vmap_id=<?php echo $vmap_id ?>&random=<?php echo uniqid(); ?>'};
        player.ima(options);
        $(document).ready(function () {

            // Remove controls from the player on iPad to stop native controls from stealing
            // our click
            var contentPlayer = document.getElementById('content_video_html5_api');
            if ((navigator.userAgent.match(/iPad/i) ||
                    navigator.userAgent.match(/Android/i)) &&
                    contentPlayer.hasAttribute('controls')) {
                contentPlayer.removeAttribute('controls');
            }

            // Initialize the ad container when the video player is clicked, but only the
            // first time it's clicked.
            var startEvent = 'click';
            if (navigator.userAgent.match(/iPhone/i) ||
                    navigator.userAgent.match(/iPad/i) ||
                    navigator.userAgent.match(/Android/i)) {
                startEvent = 'touchend';
            }
            player.one(startEvent, function () {
                player.ima.initializeAdDisplayContainer();
            });
            setInterval(function () {
                fixAdSize();
            }, 100);
        });
</script>
<?php
if (!empty($obj->showMarkers)) {

    $rows = array();
    foreach ($vmaps as $value) {
        $vastCampaingVideos = new VastCampaignsVideos($value->VAST->campaing);
        $video = new Video("", "", $vastCampaingVideos->getVideos_id());
        $rows[] = array('timeInSeconds'=>$value->timeOffsetSeconds,'name'=>$video->getTitle());
    }

    PlayerSkins::createMarker($rows);
}
?>