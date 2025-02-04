var adsRetry = 0;
// Queue to track skipped ads
let skippedAdsQueue = [];

// Array to store scheduled ad times
let scheduledAdTimes = [];

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

// Function to dynamically schedule ads
function scheduleAd(seconds) {
    if (player.liveTracker && player.liveTracker.isLive()) {
        liveAdInterval = seconds; // Set live ad interval dynamically
        console.log(`vmap_ad_scheduler: Live ad interval set to ${seconds} seconds`);
        setupLiveAdInterval();
    } else {
        console.log(`vmap_ad_scheduler: scheduleAd ${seconds} seconds`);
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
var checkAndPlayAdsTryCount = 1;
function checkAndPlayAds() {
    if (!Array.isArray(scheduledAdTimes) || scheduledAdTimes.length === 0) {
        checkAndPlayAdsTryCount++;
        if(checkAndPlayAdsTryCount < 10){
            console.log('No ads scheduled.');
            setTimeout(checkAndPlayAds, checkAndPlayAdsTryCount*500);
            return;
        }
    }

    const currentTime = Math.floor(player.currentTime());

    scheduledAdTimes = scheduledAdTimes.filter(adTime => {
        if (adTime === 'end') return true;

        if (currentTime >= adTime) {
            console.log(`Triggering ad scheduled for ${adTime} seconds`);
            try {
                initializeAdContainer();
                player.ima.requestAds();
                checkIfAdPlays();  // Add this check after triggering the ad
            } catch (error) {
                console.error(`Error triggering ad at ${adTime} seconds: ${error.message}`);
            }
            return false; // Remove this ad time as it has been triggered
        }

        return true;
    });

    setTimeout(checkAndPlayAds, 1000);
}

function checkIfAdPlays() {
    setTimeout(() => {
        if (!isAdPlaying) {  // If the ad isn't playing after a delay
            console.log('vmap_ad_scheduler: Ad didn’t play, pausing main video and forcing user interaction.');
            forceUserClickToPlayAd();  // Force the user to click to play the ad
        }else{
            console.log('vmap_ad_scheduler: Ad is playing.');
        }
    }, 3000);  // Wait 3 seconds to check if the ad starts playing
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
var forceUserClickToPlayAdAdding = false;

function forceUserClickToPlayAd() {
    return false;
    if (forceUserClickToPlayAdAdding) {
        return false;
    }
    if($('#forceUserClickToPlayAdAdOverlay').length){
        return false;
    }
    player.pause();  // Pause the main video
    setTimeout(() => {
        forceUserClickToPlayAdAdding = true;
    }, 100);
    console.log('vmap_ad_scheduler: forceUserClickToPlayAd');

    // Create an overlay message
    adOverlay = $('<div></div>').attr('id','forceUserClickToPlayAdAdOverlay')
        .css({
            position: 'absolute',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0, 0, 0, 0.5)',
            color: 'white',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '24px',
            textAlign: 'center',
            zIndex: '10',
            pointerEvents: 'none' // Allows clicks to pass through to the video player
        })
        .text(__('This video contains ads. Tap or click here to continue watching'))
        .appendTo('#mainVideo'); // Append inside the video player container

}

checkAndPlayAds();
