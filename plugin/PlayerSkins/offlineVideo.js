// Create an instance of a offlineDb object for us to store our database in
let offlineDb;
let offlineDbIsReady;
offlineDbIsReady = false;

async function downloadOfflineVideo(source) {
    var src = $(source).attr("src");
    var type = $(source).attr("type");
    var resolution = $(source).attr("res");

    getOfflineVideo(mediaId, resolution).then(function (video) {
        if (video) {
            console.log('downloadOfflineVideo', video);
            resolve(video);
        } else {
            // Fetch the videos from the network
            return fetchVideoFromNetwork(src, type, resolution, '');
        }
    }).catch(function (e) {
        console.log("Error: " + (e.stack || e));
        reject(e);
    });
}

function replaceVideoSourcesPerOfflineVersion(){
    replaceVideoSourcesPerOfflineVersionIfExists(mediaId);
}

async function replaceVideoSourcesPerOfflineVersionIfExists(videos_id) {
    videos_id = parseInt(videos_id);
    if(empty(videos_id)){
        return false;
    }
    videoJSRecreateSources(false);
    $('source.offline-video').remove();
    getOfflineVideo(videos_id).then(function (collection) {
        collection.toArray().then(function (offlineVideoSources) {
            var firstSource = null;
            if (offlineVideoSources.length) {
                console.log('something in the array', offlineVideoSources, offlineVideoSources.length);
                var sources = [];
                for (var item in offlineVideoSources) {
                    if (typeof offlineVideoSources[item] === 'object') {
                        var video = offlineVideoSources[item];
                        const videoURL = URL.createObjectURL(video.fileBlob);
                        var source = {
                            src: videoURL,
                            type: video.video_type,
                            res: video.resolution,
                            class: 'offline-video',
                            label: video.resolution + 'p <span class="label label-warning" style="padding: 0 2px; font-size: .8em; display: inline;">(OFFLINE)</span>',
                        };
                        if (!firstSource) {
                            firstSource = source;
                        }
                        sources.push(source);
                        createSourceElement(source);
                    }
                }
                console.log('Adding sources ', firstSource, sources);
                player.src(sources);
            } 
            videoJSRecreateSources(firstSource);
            offlineVideoButtonCheck();
            Promise.resolve(offlineVideoSources);
        }).catch(function (e) {
            console.log("Error: " + (e.stack || e));
        });
    }).catch(function (e) {
        console.log("Error: " + (e.stack || e));
    });
}

async function getOfflineVideo(videos_id) {
    videos_id = parseInt(videos_id);
    return await offlineDbRequest.offline_videos.where('videos_id').equals(videos_id);
}

function getOneOfflineVideoSource() {
    var first = false;
    var video480 = false;
    $("#mainVideo source").each(function (index) {
        if (empty(first)) {
            first = $(this);
        }
        var resolution = $(this).attr("res");
        if (resolution == 480) {
            video480 = $(this);
        }
    });
    if (!empty(video480)) {
        console.log('getOneOfflineVideoSource 480p video found', video480);
        return video480;
    }
    console.log('getOneOfflineVideoSource first video found', first);
    return first;
}

function changeProgressBarOfflineVideo(progressBarSelector, value) {
    value = value.toFixed(2);
    $(progressBarSelector).find('.progress-bar')
            .attr('aria-valuenow', value)
            .css('width', value + '%')
            .text(value + '%');
}

async function fetchVideoFromNetwork(src, type, resolution, progressBarSelector) {
    console.log('fetching videos from network', src, type, resolution);

    // Step 1: start the fetch and obtain a reader
    let response = await fetch(src);

    const reader = response.body.getReader();

    // Step 2: get total length
    const contentLength = +response.headers.get('Content-Length');

    // Step 3: read the data
    let receivedLength = 0; // received that many bytes at the moment
    let chunks = []; // array of received binary chunks (comprises the body)
    while (true) {
        const {done, value} = await reader.read();

        if (done) {
            break;
        }

        chunks.push(value);
        receivedLength += value.length;
        var percentageComplete = (receivedLength / contentLength) * 100;
        var percentageCompleteStr = percentageComplete.toFixed(2) + '%';
        if (!empty(progressBarSelector)) {
            changeProgressBarOfflineVideo(progressBarSelector, percentageComplete);
        }
        console.log(`Received ${receivedLength} of ${contentLength}  ${percentageCompleteStr}`);
    }

    let fileBlob = new Blob(chunks);
    return await storeOfflineVideo(src, fileBlob, type, contentLength, mediaId, resolution);
}

function getOfflineVideosIDKey(videos_id, resolution) {
    videos_id = parseInt(videos_id);
    return videos_id + '_' + resolution;
}

async function storeOfflineVideo(src, fileBlob, type, contentLength, videos_id, resolution) {
    videos_id = parseInt(videos_id);
    return await offlineDbRequest.offline_videos.put({
        src: src,
        fileBlob: fileBlob,
        video_type: type,
        contentLength: contentLength,
        videos_id: videos_id,
        resolution: resolution,
        videos_id_resolution: getOfflineVideosIDKey(videos_id, resolution),
        created: new Date().getTime(),
        modified: new Date().getTime()
    });
}

function deleteOfflineVideo(videos_id, resolution) {
    videos_id = parseInt(videos_id);
    var videos_id_resolution = getOfflineVideosIDKey(videos_id, resolution);
    return offlineDbRequest.offline_videos.delete(videos_id_resolution);
}

function createSourceElement(source) {
    var sourceElement = $('<source />', source);
    if(!empty(source.class)){
        $(sourceElement).addClass(source.class);
    }
    console.log('displayVideo', source);
    $("video#mainVideo, #mainVideo_html5_api").append(sourceElement);
}

function openDownloadOfflineVideoPage() {
    if (empty(mediaId)) {
        return false;
    }
    var url = webSiteRootURL + 'plugin/PlayerSkins/offlineVideo.php';
    url = addQueryStringParameter(url, 'videos_id', mediaId);
    url = addQueryStringParameter(url, 'socketResourceId', socketResourceId);
    avideoModalIframeSmall(url);
    return true;
}
var offlineVideoButtonCheckTimeout;
function offlineVideoButtonCheck() {
    getOfflineVideo(mediaId).then(function (collection) {
        collection.toArray().then(function (offlineVideoSources) {
            console.log("offlineVideoButtonCheck offlineVideoSources.length: ", offlineVideoSources.length);
            if (offlineVideoSources.length) {
                if(isOfflineSourceSelectedToPlay()){
                    setOfflineButton('playingOffline', false);
                }else{
                    setOfflineButton('readyToPlayOffline', false);
                }
            } else {
                setOfflineButton('download', false);
            }
            clearTimeout(offlineVideoButtonCheckTimeout);
            offlineVideoButtonCheckTimeout = setTimeout(function () {
                offlineVideoButtonCheck();
            }, 5000);
        }).catch(function (e) {
            console.log("Error offlineVideoButtonCheck 1: ", e);
        });
    }).catch(function (e) {
        console.log("Error offlineVideoButtonCheck 2: ", e);
    });
}

function isOfflineSourceSelectedToPlay() {
    var currSource = player.currentSrc();
    console.log("isOfflineSourceSelectedToPlay: ", currSource);
    if (currSource.match(/^blob:http/i)) {
        return true;
    } else {
        return false;
    }
}

function setOfflineButton(type, showLoading) {
    if (showLoading) {
        offlineVideoLoading(true);
    }
    switch (type) {
        case 'download':
            avideoTooltip(".offline-button", "Download");
            $('.offline-button').removeClass('hasOfflineVideo');
            $('body').removeClass('playingOfflineVideo');
            offlineVideoButtonCheck();
            break;
        case 'readyToPlayOffline':
            avideoTooltip(".offline-button", "Ready to play offline");
            $('body').removeClass('playingOfflineVideo');
            $('.offline-button').addClass('hasOfflineVideo');
            break;
        case 'playingOffline':
            avideoTooltip(".offline-button", "Playing offline");
            $('body').addClass('playingOfflineVideo');
            $('.offline-button').addClass('hasOfflineVideo');
            break;
    }
    if (showLoading) {
        offlineVideoLoading(false);
    }
}

function offlineVideoLoading(active) {
    if (active) {
        $('.offline-button').addClass('loading');
        $('.offline-button').addClass('fa-pulse');
    } else {
        $('.offline-button').removeClass('loading');
        $('.offline-button').removeClass('fa-pulse');
    }
}

function socketUpdateOfflineVideoSource(resourceId){
    if(avideoSocketIsActive()){
        sendSocketMessageToResourceId({}, 'replaceVideoSourcesPerOfflineVersion', resourceId)
    }
}

var offlineDbRequest = new Dexie(offlineVideoDbName);
offlineDbRequest.version(1).stores({
    offline_videos: "videos_id_resolution,*videos_id"
});

$(document).ready(function () {
    if (!empty(mediaId) && $("#mainVideo").length) {
        replaceVideoSourcesPerOfflineVersionIfExists(mediaId);
    }
});