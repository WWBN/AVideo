var last_videos_id = 0;
var last_currentTime = -1;
var videoViewAdded = false;
var _addViewCheck = false;
var isVideoPlaying = false;

// Create an encapsulation for seconds_watching_video
var VideoWatchTime = (function () {
    var seconds_watching_video = 0;

    return {
        increment: function() {
            if (isVideoPlaying) {
                seconds_watching_video++;
            }
        },
        reset: function() {
            seconds_watching_video = 0;
        },
        getValue: function() {
            return seconds_watching_video;
        }
    };
})();

// Modify the addView function
function addView(videos_id, currentTime) {
    addViewSetCookie(PHPSESSID, videos_id, currentTime, VideoWatchTime.getValue());
    
    if (_addViewCheck) {
        return false;
    }
    
    if (last_videos_id === videos_id && last_currentTime === currentTime) {
        return false;
    }
    
    // Removed the currentTime condition
    _addViewCheck = true;
    last_videos_id = videos_id;
    last_currentTime = currentTime;
    
    _addView(videos_id, currentTime, VideoWatchTime.getValue());
    
    setTimeout(function() {
        _addViewCheck = false;
    }, 1000);
    
    return true;
}

function addCurrentView() {
    var vid = 0;
    if(typeof mediaId !== 'undefined'){
        vid = mediaId;
    }else if(typeof videos_id !== 'undefined'){
        vid = videos_id;
    }
    var time = Math.round(player.currentTime());     
    addView(vid, time);
}

var isVideoAddViewCount = false;
var doNotCountView = false;

function _addView(videos_id, currentTime, seconds_watching_video) {
    if(doNotCountView){
        return false;
    }

    if (isVideoAddViewCount) {
        return false;
    }
    
    if (typeof PHPSESSID === 'undefined') {
        PHPSESSID = '';
    }
    
    var url = webSiteRootURL + 'objects/videoAddViewCount.json.php';
    
    if (empty(PHPSESSID)) {
        return false;
    }

    isVideoAddViewCount = true;
    url = addGetParam(url, 'PHPSESSID', PHPSESSID);
    // reset seconds_watching_video
    var seconds_watching_video_to_send = seconds_watching_video;
    VideoWatchTime.reset();
    //console.trace();
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            id: videos_id,
            currentTime: currentTime,
            seconds_watching_video: seconds_watching_video_to_send
        },
        success: function(response) {
            $('.view-count' + videos_id).text(response.countHTML);
            PHPSESSID = response.session_id;
        }, complete: function(response) {
            isVideoAddViewCount = false;
        }
    });
}

var _addViewFromCookie_addingtime = false;

async function addViewFromCookie() {
    if (typeof webSiteRootURL === 'undefined') {
        return false;
    }
    
    if (_addViewFromCookie_addingtime) {
        return false;
    }
    
    _addViewFromCookie_addingtime = true;
    
    var addView_PHPSESSID = Cookies.get('addView_PHPSESSID');
    var addView_videos_id = Cookies.get('addView_videos_id');
    var addView_playerCurrentTime = Cookies.get('addView_playerCurrentTime');
    var addView_seconds_watching_video = Cookies.get('addView_seconds_watching_video');
    
    if (!addView_PHPSESSID || addView_PHPSESSID === 'false' ||
        !addView_videos_id || addView_videos_id === 'false' ||
        !addView_playerCurrentTime || addView_playerCurrentTime === 'false' ||
        !addView_seconds_watching_video || addView_seconds_watching_video === 'false') {
        return false;
    }
    
    if (mediaId === addView_videos_id) {
        // it is the same video, play at the last moment
        forceCurrentTime = addView_playerCurrentTime;
    }
    var doNotCountViewOriginal = isVideoAddViewCount;
    doNotCountView = false;
    _addView(addView_videos_id, addView_playerCurrentTime, addView_seconds_watching_video);
    doNotCountView = doNotCountViewOriginal;
    
    setTimeout(function() {
        _addViewFromCookie_addingtime = false;
    }, 2000);
    
    addViewSetCookie(false, false, false, false);
}

async function addViewSetCookie(PHPSESSID, videos_id, playerCurrentTime, seconds_watching_video) {
    Cookies.set('addView_PHPSESSID', PHPSESSID, { path: '/', expires: 1 });
    Cookies.set('addView_videos_id', videos_id, { path: '/', expires: 1 });
    Cookies.set('addView_playerCurrentTime', playerCurrentTime, { path: '/', expires: 1 });
    Cookies.set('addView_seconds_watching_video', seconds_watching_video, { path: '/', expires: 1 });
}

async function startAddViewCountInPlayer(){
    if(typeof player !== 'undefined' && typeof mediaId !== 'undefined'){
        if(!player.paused()){
            isVideoPlaying = true;
        }
        player.on('play', function () {
            isVideoPlaying = true;
            addView(mediaId, this.currentTime());
        });
        player.on('pause', function () {
            isVideoPlaying = false;
            var time = Math.round(this.currentTime());
            addView(mediaId, time);
        });
        player.on('ended', function () {
            isVideoPlaying = false;
            var time = Math.round(this.currentTime());
            addView(mediaId, time);
        });
        
        player.on('timeupdate', function() {
            var time = Math.round(this.currentTime());     
            if (time === 0 || time % 30 === 0) {
                addCurrentView();
            }
        });
    } else {
        setTimeout(function() {
            startAddViewCountInPlayer();
        }, 5000);
    }
}

// Add beforeunload event
window.addEventListener('beforeunload', (event) => {
    addViewFromCookie();
});

$(document).ready(function() {
    // Use setInterval to update seconds_watching_video every second
    setInterval(function () {
        VideoWatchTime.increment();
    }, 1000);
    
    // Call addViewFromCookie on the next page load
    addViewFromCookie();

    startAddViewCountInPlayer();
});