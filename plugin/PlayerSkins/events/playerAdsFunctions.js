var adsRetry = 0;
// Queue to track skipped ads
let skippedAdsQueue = [];

// Array to store scheduled ad times
let scheduledAdTimes = [];

// Dynamic interval for live ads
let liveAdInterval = null;

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
