// Queue to track skipped ads
let skippedAdsQueue = [];

// Array to store scheduled ad times
let scheduledAdTimes = [];

// Dynamic interval for live ads
let liveAdInterval = null;

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
        console.log(`initializeAdContainer: Initializing ad container`);
        player.ima.initializeAdDisplayContainer();
    }
}

// Function to dynamically schedule ads
function scheduleAd(seconds) {
    if (player.liveTracker && player.liveTracker.isLive()) {
        liveAdInterval = seconds; // Set live ad interval dynamically
        console.log(`Live ad interval set to ${seconds} seconds`);
        setupLiveAdInterval();
    } else {
        scheduledAdTimes.push(seconds);
    }
}

// Function to set up live ad interval
function setupLiveAdInterval() {
    if (liveAdInterval && player.liveTracker && player.liveTracker.isLive()) {
        setInterval(() => {
            console.log(`Triggering live ad every ${liveAdInterval} seconds`);
            try {
                initializeAdContainer();
                player.ima.requestAds();
            } catch (error) {
                console.error(`Error triggering live ad: ${error.message}`);
            }
        }, liveAdInterval * 1000);
    }
}

// Function to check and play ads based on current time
function checkAndPlayAds() {
    const currentTime = player.currentTime();
    scheduledAdTimes = scheduledAdTimes.filter(adTime => {
        if (adTime === 'end') return true; // Keep the "end" marker for later handling
        if (currentTime >= adTime) {
            console.log(`Triggering ad scheduled for ${adTime} seconds`);
            try {
                initializeAdContainer();
                player.ima.requestAds();
            } catch (error) {
                console.error(`Error triggering ad at ${adTime} seconds: ${error.message}`);
            }
            return false; // Remove this ad time as it has been triggered
        }
        return true;
    });
}

// Function to check if an ad is playing
function checkIfAdIsPlaying() {
    console.log('Is an ad playing?', isAdPlaying);
    return isAdPlaying;
}

function fixAdPlaying(){
    if(isAdPlaying){
        if (!player.paused()) {
            console.log('fixAdPlaying: Pausing main video');
            player.pause(); // Pause the main video
        }
    }else{
        if (player.paused()) {
            console.log('fixAdPlaying: Resuming main video');
            player.play(); // Resume the main video
        }
    }
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