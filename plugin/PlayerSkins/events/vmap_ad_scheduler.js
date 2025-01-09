// Queue to track skipped ads
let skippedAdsQueue = [];

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
                    scheduleAd(0);
                } else if (timeOffset === 'end') {
                    scheduleAdAtEnd();
                } else if (timeOffset) {
                    var seconds = convertTimeOffsetToSeconds(timeOffset);
                    scheduleAd(seconds);
                }
            });
        })
        .catch(error => console.error('Error fetching VMAP:', error));
}

// Convert timeOffset to seconds
function convertTimeOffsetToSeconds(timeOffset) {
    var parts = timeOffset.split(':');
    return parts.length === 3
        ? parseInt(parts[0], 10) * 3600 + parseInt(parts[1], 10) * 60 + parseInt(parts[2], 10)
        : parts.length === 2
            ? parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10)
            : 0;
}

// Ensure ad display container is initialized on mobile
function initializeAdContainer() {
    if (player.ima && typeof player.ima.initializeAdDisplayContainer === "function") {
        player.ima.initializeAdDisplayContainer();
    }
}

// Schedule ad playback
function scheduleAd(seconds) {
    if (seconds > 5) {
        setTimeout(() => {
            console.log(`Ad will play in 5 seconds at ${seconds} seconds`);
        }, (seconds - 5) * 1000);
    }
    setTimeout(() => {
        console.log(`Checking playback state for ad at ${seconds} seconds`);
        if (!player.paused()) {
            console.log(`Triggering ad at ${seconds} seconds`);
            try {
                // Ensure the ad container is ready (for mobile)
                initializeAdContainer();
                
                // Request and play ads
                player.ima.requestAds();
            } catch (error) {
                console.error(`Error while triggering ad: ${error.message}`);
            }
        } else {
            console.log(`Video is paused, skipping ad at ${seconds} seconds`);
            // Add logic to handle skipped ads if needed
        }
    }, seconds * 1000);
}

// Example usage
player.on('ads-manager', function(response) {
    console.log('Ads manager ready:', response.adsManager);
});

player.on('ads-ad-started', () => {
    console.log('Ad started');
});


// Schedule ad at the end of the video
function scheduleAdAtEnd() {
    player.on('timeupdate', function handleTimeUpdate() {
        var remainingTime = player.duration() - player.currentTime();
        try {
            if (remainingTime <= 10 && remainingTime > 5) {
                console.log('Ad will play in 5 seconds at the end of the video');
            }
            if (remainingTime <= 5 && !player.paused()) {
                console.log('Triggering ad at the end of the video');
                player.ima.requestAds();
                player.ima.playAd();
                player.off('timeupdate', handleTimeUpdate); // Remove listener after ad is triggered
            } else if (remainingTime <= 5) {
                skippedAdsQueue.push(() => {
                    console.log('Playing skipped ad at the end of the video');
                    player.ima.requestAds();
                    player.ima.playAd();
                });
                player.off('timeupdate', handleTimeUpdate);
            }
        } catch (error) {
            console.error(error);
        }
    });
}

// Play skipped ads after the video resumes
player.on('play', () => {
    while (skippedAdsQueue.length > 0) {
        const playSkippedAd = skippedAdsQueue.shift();
        playSkippedAd();
    }
});
