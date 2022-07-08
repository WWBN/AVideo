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
            displayVideo(video.fileBlob, video.video_type, video.resolution);
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

async function getOfflineVideo(videos_id, resolution) {
    return offlineDbRequest.transaction('r', [offlineDbRequest.offline_videos], async () => {
        const video = await db.offline_videos.get({
            videos_id_resolution: getOfflineVideosIDKey(videos_id, resolution),
            videos_id: videos_id,
            resolution: resolution});
        return video;
    });
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

function changeProgressBarOfflineVideo(progressBarSelector, value){
    value = value.toFixed(2);
    $(progressBarSelector).find('.progress-bar')
            .attr('aria-valuenow', value)
            .css('width', value+'%')
            .text(value+'%');
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
        if(!empty(progressBarSelector)){
            changeProgressBarOfflineVideo(progressBarSelector, percentageComplete);
        }
        console.log(`Received ${receivedLength} of ${contentLength}  ${percentageCompleteStr}`);
    }

    let fileBlob = new Blob(chunks);
    displayVideo(fileBlob, type, resolution);
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

function displayVideo(fileBlob, type, resolution) {
    // Create object URLs out of the blobs
    console.log('displayVideo', type, resolution);
    const videoURL = URL.createObjectURL(fileBlob);
    var source = $('<source />', {
        src: videoURL,
        type: type,
        res: resolution,
    });
    $("#mainVideo").append(source);
}

var offlineDbRequest = new Dexie(offlineVideoDbName);
offlineDbRequest.version(1).stores({
    offline_videos: "videos_id_resolution,*videos_id"
});
