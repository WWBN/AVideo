// Fetch and parse VMAP
if (typeof _adTagUrl === 'string') {
    fetch(_adTagUrl)
        .then(response => response.text())
        .then(vmapXml => {
            var parser = new DOMParser();
            var xmlDoc = parser.parseFromString(vmapXml, 'text/xml');
            var adBreaks = xmlDoc.getElementsByTagName('vmap:AdBreak');
            Array.from(adBreaks).forEach(adBreak => {
                var timeOffset = adBreak.getAttribute('timeOffset');
                console.log('vmap_ad_scheduler: timeOffset found', timeOffset);

                if (timeOffset === 'start') {
                    scheduledAdTimes.push(0);
                } else if (timeOffset === 'end') {
                    scheduledAdTimes.push('end');
                } else if (timeOffset) {
                    var seconds = convertTimeOffsetToSeconds(timeOffset);
                    scheduledAdTimes.push(seconds);
                }
            });
        })
        .catch(error => console.error('vmap_ad_scheduler: Error fetching VMAP:', error));
}

// Set up event listener to check ad triggers on time updates
player.on('timeupdate', () => {
    //console.log(`vmap_ad_scheduler: checkAndPlayAds timeupdate.`, player.currentTime());
    //checkAndPlayAds();
});

// Handle ads at the end of the video
player.on('ended', () => {
    if (scheduledAdTimes.includes('end')) {
        console.log('vmap_ad_scheduler: Playing ad at the end of the video');
        try {
            initializeAdContainer();
            player.ima.requestAds();
        } catch (error) {
            console.error(`vmap_ad_scheduler: Error triggering ad at the end of the video: ${error.message}`);
        }
    }
});

// Play skipped ads when the video resumes
player.on('play', () => {
    if(forceUserClickToPlayAdAdding){
        $('#forceUserClickToPlayAdAdOverlay').remove(); // Remove overlay when play button is clicked
        console.log('vmap_ad_scheduler: User clicked play, removing overlay.');
        player.play();
        initializeAdContainer();
        player.ima.requestAds(); // Try to play the ad again
    }else{
        console.log('vmap_ad_scheduler: forceUserClickToPlayAdAdding.', forceUserClickToPlayAdAdding);
    }
    forceUserClickToPlayAdAdding = false;
    while (skippedAdsQueue.length > 0) {
        const playSkippedAd = skippedAdsQueue.shift();
        playSkippedAd();
    }
});

player.on('ads-ad-started', () => {
    isAdPlaying = true;
    console.log('vmap_ad_scheduler: Ad started playing', player.currentTime());
    fixAdPlaying();
});

// Listen for ad end event to resume the main video
player.on('ads-ad-ended', () => {
    isAdPlaying = false;
    console.log('vmap_ad_scheduler: Ad finished playing');
    fixAdPlaying();
});

// Listen for ad error and reset playback states if needed
player.on('adserror', () => {
    isAdPlaying = false;
    console.log('vmap_ad_scheduler: Ad playback encountered an error, resuming main video if paused');
    fixAdPlaying();
});

// Example usage
player.on('ads-manager', function (response) {
    console.log('vmap_ad_scheduler: Ads manager ready:', response.adsManager);
});
