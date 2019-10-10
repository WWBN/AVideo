<script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>
<script>
    window.SILVERMINE_VIDEOJS_CHROMECAST_CONFIG = {
        preloadWebComponents: true,
    };
</script>
<script src="<?php echo $global['webSiteRootURL'] ?>plugin/Chromecast/videojs-chromecast/silvermine-videojs-chromecast.js" type="text/javascript"></script>
<script>
    if (typeof player === 'undefined') {
        player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(",controls: true,techOrder: ['chromecast', 'html5'], plugins: {chromecast: {}}"); ?>);
    }
    player.chromecast();
</script>