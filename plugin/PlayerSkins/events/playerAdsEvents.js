setInterval(function () { fixAdSize(); }, 300);

async function logAdEvent(eventType) {
    console.log('Logging event:', eventType);
    var video_position = player.currentTime();
    $.ajax({
        url: webSiteRootURL+'plugin/AD_Server/log.php',
        type: 'POST',
        data: {
            label: eventType,
            videos_id: getVideosId(),
            video_position: video_position
        },
        success: function(response) {
            console.log('Event logged successfully:', response);
        },
        error: function(xhr, status, error) {
            console.error('Failed to log event:', error);
        }
    });
}

player.on('adsready', function () {
    console.log('ADS: adsready');

    // Set listener for ad break ready
    player.ima.setAdBreakReadyListener(function (e) {
        if (!_adWasPlayed) {
            console.log('ADS: !_adWasPlayed player.ima.playAdBreak();', e);
            player.on('play', function () {
                if (!_adWasPlayed) {
                    player.ima.playAdBreak();
                    _adWasPlayed = 1;
                }
            });
        } else {
            console.log('ADS: _adWasPlayed player.ima.playAdBreak();', e);
            player.ima.playAdBreak();
        }
    });

    // Listen to IMA SDK ad events
    var adsManager = player.ima.getAdsManager();

    adsManager.addEventListener(google.ima.AdEvent.Type.STARTED, function () {
        console.log('ADS: IMA SDK: vmap_ad_scheduler: Ad started.');
        logAdEvent('AdStarted');
    });

    player.ima.addEventListener(google.ima.AdEvent.Type.LOADED, function() {
        console.log('ADS: IMA SDK: vmap_ad_scheduler: Ad LOADED.');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.FIRST_QUARTILE, function () {
        console.log('ADS: IMA SDK: Ad reached first quartile.');
        logAdEvent('AdFirstQuartile');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.MIDPOINT, function () {
        console.log('ADS: IMA SDK: Ad reached midpoint.');
        logAdEvent('AdMidpoint');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.THIRD_QUARTILE, function () {
        console.log('ADS: IMA SDK: Ad reached third quartile.');
        logAdEvent('AdThirdQuartile');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.COMPLETE, function () {
        console.log('ADS: IMA SDK: Ad completed.');
        logAdEvent('AdCompleted');
        player.play();
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.PAUSED, function () {
        console.log('ADS: IMA SDK: Ad paused.');
        logAdEvent('AdPaused');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.RESUMED, function () {
        console.log('ADS: IMA SDK: Ad resumed.');
        logAdEvent('AdResumed');
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.SKIPPED, function () {
        console.log('ADS: IMA SDK: Ad skipped.');
        logAdEvent('AdSkipped');
        //player.play();
    });

    adsManager.addEventListener(google.ima.AdEvent.Type.CLICK, function () {
        console.log('ADS: IMA SDK: Ad clicked.');
        logAdEvent('AdClicked');
    });

    adsManager.addEventListener(google.ima.AdErrorEvent.Type.AD_ERROR, function (event) {
        console.error('ADS: IMA SDK: vmap_ad_scheduler: Ad error occurred:', event.getError());
        logAdEvent('AdError', { error: event.getError() });

        if (adsRetry === 0) {
            adsRetry++;
            preloadVmapAndUpdateAdTag(_adTagUrl); // Retry ad if error
        }
    });
});

// Event fired if there's an error during ad playback
player.on('adserror', function(event) {
    console.log('vmap_ad_scheduler: ADS: error:', event.data.AdError);
    logAdEvent('AdError', { error: event.data.AdError });

    if (adsRetry === 0) {
        adsRetry++;
        preloadVmapAndUpdateAdTag(_adTagUrl);
    }
});

player.one(startEvent, function () {
    player.ima.initializeAdDisplayContainer();
});
