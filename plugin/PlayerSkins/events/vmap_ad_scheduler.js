// Array to store scheduled ad times
let scheduledAdTimes = [];

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
                //console.log('vmap_ad_scheduler: timeOffset found', timeOffset);
                console.log('ADs: timeOffset found', timeOffset);
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
        forceUserClickToPlayAdAdding = false;
        $('#forceUserClickToPlayAdAdOverlay').remove(); // Remove overlay when play button is clicked
        console.log('vmap_ad_scheduler: User clicked play, removing overlay.');
        player.play();
        initializeAdContainer();
        player.ima.requestAds(); // Try to play the ad again
    }else{
        console.log('vmap_ad_scheduler: forceUserClickToPlayAdAdding.', forceUserClickToPlayAdAdding);
    }
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
    var adsManager = response.adsManager;
    var cuePoints = adsManager.getCuePoints();
    console.log('vmap_ad_scheduler: Ads manager ready - Cue Points:', cuePoints);

    // Check for empty ad slots inside VMAP
    if (!cuePoints || cuePoints.length === 0) {
        console.warn('vmap_ad_scheduler: No ads found inside VMAP, skipping ad slot');
        player.play(); // Resume video since no ads are available
    }
});


// Detect when a VAST request is made inside VMAP
player.on('ads-request', function (event) {
    console.log('vmap_ad_scheduler: Ad request sent to VAST URL:', event);
});

// Detect when a VAST response is received inside VMAP
player.on('ads-response', function (event) {
    console.log('vmap_ad_scheduler: VAST response received:', event);

    var adsManager = event.adsManager;

    if (!adsManager || !adsManager.getCuePoints || adsManager.getCuePoints().length === 0) {
        console.warn('vmap_ad_scheduler: VAST response has NO ADS, resuming video');
        player.play(); // Resume the main video if no ads are available
    } else {
        console.log('vmap_ad_scheduler: VAST response contains ads:', adsManager.getCuePoints());
    }
});

// Handle errors in case VAST fails to load ads
player.on('ads-error', function (event) {
    console.error('vmap_ad_scheduler: VAST Ad Error:', event);
    player.play(); // Resume video if there's an ad error
});



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


// Function to check and play ads based on current time
var checkAndPlayAdsTryCount = 1;
function checkAndPlayAds() {
    if (!Array.isArray(scheduledAdTimes) || scheduledAdTimes.length === 0 || typeof player === 'undefined') {
        checkAndPlayAdsTryCount++;
        if(checkAndPlayAdsTryCount < 10){
            console.log('No ads scheduled.');
            setTimeout(checkAndPlayAds, checkAndPlayAdsTryCount*500);
            return;
        }
        console.log('No ads scheduled we give up.');
        return false;
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

checkAndPlayAds();
