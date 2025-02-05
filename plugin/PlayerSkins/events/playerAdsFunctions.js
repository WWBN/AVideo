var adsRetry = 0;
// Queue to track skipped ads
let skippedAdsQueue = [];

// Dynamic interval for live ads
let liveAdInterval = null;

var isAdPlaying = false;

async function logAdEvent(eventType) {
    console.log('Logging event:', eventType);
    var video_position = player.currentTime();
    $.ajax({
        url: webSiteRootURL + 'plugin/AD_Server/log.php',
        type: 'POST',
        data: {
            label: eventType,
            videos_id: getVideosId(),
            video_position: video_position
        },
        success: function (response) {
            console.log('Event logged successfully:', response);
        },
        error: function (xhr, status, error) {
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
        console.log(`vmap_ad_scheduler: initializeAdContainer: Initializing ad container`);
        player.ima.initializeAdDisplayContainer();
    } else {
        console.error(`vmap_ad_scheduler: player.ima not defined`);
    }
}


// Function to check if an ad is playing
function checkIfAdIsPlaying() {
    console.log('Is an ad playing?', isAdPlaying);
    return isAdPlaying;
}

function fixAdPlaying() {
    if (isAdPlaying) {
        if (!player.paused()) {
            console.log('fixAdPlaying: Pausing main video');
            player.pause(); // Pause the main video
        }
    } else {
        if (player.paused()) {
            console.log('fixAdPlaying: Resuming main video');
            player.play(); // Resume the main video
        }
    }
}
