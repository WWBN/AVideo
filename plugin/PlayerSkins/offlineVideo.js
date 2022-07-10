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
            //createSourceElement(video.fileBlob, video.video_type, video.resolution);
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

async function replaceVideoSourcesPerOfflineVersionIfExists(videos_id) {
    videos_id = parseInt(videos_id);
    getOfflineVideo(videos_id).then(function (collection) {
        collection.toArray().then(function (offlineVideoSources) {
            if (offlineVideoSources.length) {
                console.log('something in the array', offlineVideoSources, offlineVideoSources.length);
                var sources = [];
                var firstSource = null;         
                for (var item in offlineVideoSources) {
                    if(typeof offlineVideoSources[item] === 'object'){
                        var video = offlineVideoSources[item];
                        const videoURL = URL.createObjectURL(video.fileBlob);
                        var source = {
                            src: videoURL,
                            type: video.video_type,
                            res: video.resolution,
                            label: video.resolution+ 'p <span class="label label-warning" style="padding: 0 2px; font-size: .8em; display: inline;">(OFFLINE)</span>',
                        };
                        if(!firstSource){
                            firstSource = source;
                        }
                        sources.push(source);
                        createSourceElement(source);
                    }
                }
                console.log('Adding sources ', firstSource, sources);
                player.src(sources);
                videoJSRecreateSources(firstSource);
                Promise.resolve(offlineVideoSources);
            } else {
                console.log('empty array', offlineVideoSources, offlineVideoSources.length);
            }
        }).catch(function (e) {
            console.log("Error: " + (e.stack || e));
        });
    }).catch(function (e) {
        console.log("Error: " + (e.stack || e));
    });
}

async function getOfflineVideo(videos_id) {
    return await offlineDbRequest.offline_videos.where('videos_id').equals(videos_id);
}

function getOfflineSources(videos_id) {
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

async function downloadOneOfflineVideo() {
    var source = getOneOfflineVideoSource();
    if (!empty(source)) {
        return await downloadOfflineVideo(source);
    }
    reject(false);
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
    //createSourceElement(fileBlob, type, resolution);
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
    console.log('displayVideo', source);
    $("video#mainVideo, #mainVideo_html5_api").append(sourceElement);
}

var offlineDbRequest = new Dexie(offlineVideoDbName);
offlineDbRequest.version(1).stores({
    offline_videos: "videos_id_resolution,*videos_id"
});

$(document).ready(function () {
    if(!empty(mediaId) && $("#mainVideo").length){
        replaceVideoSourcesPerOfflineVersionIfExists(mediaId);
    }
});