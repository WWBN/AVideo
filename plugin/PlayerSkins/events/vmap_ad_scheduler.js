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
                console.log('timeOffset found', timeOffset);

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
        .catch(error => console.error('Error fetching VMAP:', error));
}

// Set up event listener to check ad triggers on time updates
player.on('timeupdate', () => {
    checkAndPlayAds();
});

// Handle ads at the end of the video
player.on('ended', () => {
    if (scheduledAdTimes.includes('end')) {
        console.log('Playing ad at the end of the video');
        try {
            initializeAdContainer();
            player.ima.requestAds();
        } catch (error) {
            console.error(`Error triggering ad at the end of the video: ${error.message}`);
        }
    }
});

// Play skipped ads when the video resumes
player.on('play', () => {
    while (skippedAdsQueue.length > 0) {
        const playSkippedAd = skippedAdsQueue.shift();
        playSkippedAd();
    }
});

player.on('ads-ad-started', () => {
    isAdPlaying = true;
    console.log('Ad started playing');
    fixAdPlaying();
});

// Listen for ad end event to resume the main video
player.on('ads-ad-ended', () => {
    isAdPlaying = false;
    console.log('Ad finished playing');    
    fixAdPlaying();
});

// Listen for ad error and reset playback states if needed
player.on('adserror', () => {
    isAdPlaying = false;
    console.log('Ad playback encountered an error, resuming main video if paused');
    fixAdPlaying();
});

// Example usage
player.on('ads-manager', function(response) {
    console.log('Ads manager ready:', response.adsManager);
});