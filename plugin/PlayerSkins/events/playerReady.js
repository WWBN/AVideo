player.ready(function () {
    console.log('player.ready');
    player.on('error', () => { AvideoJSError(player.error().code); });

    player.on('timeupdate', function() {
        if (player.liveTracker && player.liveTracker.atLiveEdge()) {
            if (player.playbackRate() !== 1) {
                player.playbackRate(1);
            }
        }
    });
    try {
        var err = this.error();
        if (err && err.code) {
            $('.vjs-error-display').hide();
            $('#mainVideo').find('.vjs-poster').css({ 'background-image': 'url(' + webSiteRootURL + 'plugin/Live/view/Offline.jpg)' });
        }
    } catch (e) {
        console.error('error-display', e);
    }
    setTimeout(() => {
        $('.vjs-menu-item').on('click', function() {
            // Remove vjs-selected class from all chapter menu items
           $(this).parent().find('.vjs-menu-item').removeClass('vjs-selected');

            // Add vjs-selected class back to the clicked chapter
            $(this).addClass('vjs-selected');
        });
    }, 2000);
});
