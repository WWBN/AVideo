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
        console.log(`scheduleAd: initializeAdDisplayContainer`);
        player.ima.initializeAdDisplayContainer();
    }
}

// Schedule ad playback
function scheduleAd(seconds) {
    if (seconds > 5) {
        for (let index = 5; index > 0; index--) {
            setTimeout(() => {
                console.log(`scheduleAd: Ad will play in ${index} seconds at ${seconds} seconds`);
            }, (seconds - index) * 1000);
        }
    }
    setTimeout(() => {
        console.log(`scheduleAd: Checking playback state for ad at ${seconds} seconds`);
        if (!player.paused()) {
            console.log(`scheduleAd: Triggering ad at ${seconds} seconds`);
            try {
                // Ensure the ad container is ready (for mobile)
                initializeAdContainer();
                
                // Request and play ads
                player.ima.requestAds();
            } catch (error) {
                console.error(`scheduleAd: Error while triggering ad: ${error.message}`);
            }
        } else {
            if(checkIfAdIsPlaying()){
                console.log(`scheduleAd: ad is already playing, skipping ad at ${seconds} seconds`);
            }else{
                
                skippedAdsQueue.push(() => {
                    console.log('Playing skipped ad at the end of the video');
                    initializeAdContainer();
                    player.ima.requestAds();
                });
                console.log(`scheduleAd: Video is paused, skipping ad at ${seconds} seconds`);
            }
            // Add logic to handle skipped ads if needed
        }
    }, seconds * 1000);
}

// Schedule ad at the end of the video
function scheduleAdAtEnd() {
    player.on('timeupdate', function handleTimeUpdate() {
        const remainingTime = player.duration() - player.currentTime();

        // Countdown messages for the ad
        if (remainingTime <= 10 && remainingTime > 5) {
            const countdown = Math.floor(remainingTime - 5);
            for (let index = countdown; index > 0; index--) {
                setTimeout(() => {
                    console.log(`scheduleAdAtEnd: Ad will play in ${index} seconds at the end of the video`);
                }, (countdown - index) * 1000);
            }
        }

        // Check if the ad should be triggered
        if (remainingTime <= 5) {
            console.log('scheduleAdAtEnd: Checking playback state for ad at the end of the video');
            try {
                if (!player.paused()) {
                    console.log('scheduleAdAtEnd: Triggering ad at the end of the video');
                    initializeAdContainer();
                    player.ima.requestAds();
                    player.off('timeupdate', handleTimeUpdate); // Remove listener after ad is triggered
                } else {
                    if (checkIfAdIsPlaying()) {
                        console.log('scheduleAdAtEnd: Ad is already playing, skipping ad at the end of the video');
                    } else {
                        skippedAdsQueue.push(() => {
                            console.log('Playing skipped ad at the end of the video');
                            initializeAdContainer();
                            player.ima.requestAds();
                        });
                        console.log('scheduleAdAtEnd: Video is paused, skipping ad at the end of the video');
                    }
                    player.off('timeupdate', handleTimeUpdate); // Remove listener
                }
            } catch (error) {
                console.error(`scheduleAdAtEnd: Error while triggering ad: ${error.message}`);
            }
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


let isAdPlaying = false;

// Listen for ad start event
player.on('ads-ad-started', () => {
    isAdPlaying = true;
    console.log('Ad started playing');
});

// Listen for ad end event
player.on('ads-ad-ended', () => {
    isAdPlaying = false;
    console.log('Ad finished playing');
});

// Listen for ad error
player.on('adserror', () => {
    isAdPlaying = false;
    console.log('Ad playback encountered an error');
});

// Example usage
player.on('ads-manager', function(response) {
    console.log('Ads manager ready:', response.adsManager);
});

// Function to check if an ad is playing
function checkIfAdIsPlaying() {
    console.log('Is an ad playing?', isAdPlaying);
    return isAdPlaying;
}