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

// Schedule ad with a check for playback state
function scheduleAd(seconds) {
    if (seconds > 5) {
        setTimeout(() => {
            console.log(`Ad will play in 5 seconds at ${seconds} seconds`);
        }, (seconds - 5) * 1000);
    }

    setTimeout(() => {
        try {
            if (!player.paused()) { // Play ad if video is playing
                console.log(`Triggering ad at ${seconds} seconds`);
                player.ima.requestAds();
                player.ima.playAd();
            } else {
                console.log(`Skipped ad at ${seconds} seconds because the video is paused`);
                skippedAdsQueue.push(() => {
                    console.log(`Playing skipped ad scheduled for ${seconds} seconds`);
                    player.ima.requestAds();
                    player.ima.playAd();
                });
            }
        } catch (error) {
            console.error(error);
        }
    }, seconds * 1000);
}

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
