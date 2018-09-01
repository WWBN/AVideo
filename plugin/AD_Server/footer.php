<script src="<?php echo $global['webSiteRootURL'] ?>plugin/AD_Server/videojs-ima/videojs.ima.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
<?php
if (!empty($obj->showMarkers)) {
    ?>
            $.getScript("<?php echo $global['webSiteRootURL'] ?>plugin/AD_Server/videojs-markers/videojs-markers.js", function (data, textStatus, jqxhr) {
    <?php
}
?>
            if (typeof player == 'undefined') {
                player = videojs('mainVideo');
            }
            player.markers({
                markerStyle: {
                    'width': '5px',
                    'background-color': 'yellow'
                },
                markerTip: {
                    display: true,
                    text: function (marker) {
                        return marker.text;
                    }
                },
                markers: [
<?php
foreach ($vmaps as $value) {
    $vastCampaingVideos = new VastCampaignsVideos($value->VAST->campaing);
    $video = new Video("", "", $vastCampaingVideos->getVideos_id());
    ?>
                        {time: <?php echo $value->timeOffsetSeconds; ?>, text: "<?php echo addcslashes($video->getTitle(), '"'); ?>"},
    <?php
}
?>
                ]
            });
        });
        function fixAdSize() {
            ad_container = $('#mainVideo_ima-ad-container');
            if (ad_container.length) {
                height = ad_container.css('height');
                width = ad_container.css('width');
                $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'height': height});
                $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'width': width});
            }
        }
        player = videojs('mainVideo');
        var options = {
            id: 'mainVideo',
            adTagUrl: '<?php echo $global['webSiteRootURL'] ?>plugin/AD_Server/VMAP.php?video_length=<?php echo $video_length ?>&vmap_id=<?php echo $vmap_id ?>'
                    };
                    player.ima(options);
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
</script>;