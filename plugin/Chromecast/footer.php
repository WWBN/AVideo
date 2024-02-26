<script>
    window.SILVERMINE_VIDEOJS_CHROMECAST_CONFIG = {
        preloadWebComponents: true,
    };
</script>
<script src="<?php echo getURL('node_modules/@silvermine/videojs-chromecast/dist/silvermine-videojs-chromecast.min.js'); ?>" type="text/javascript"></script>
<script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>
<script>
    <?php
    echo PlayerSkins::getStartPlayerJS('player.chromecast();
    player.on(\'play\', function () {player.chromecast();});',",controls: true,techOrder: ['chromecast', 'html5'], plugins: {chromecast: {}}");
    ?>
</script>