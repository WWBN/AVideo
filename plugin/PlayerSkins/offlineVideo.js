// Create an instance of a offlineDb object for us to store our database in
let offlineDb;
let offlineDbIsReady;
offlineDbIsReady = false;

function downloadOfflineVideo(source) {
    var src = $(source).attr("src");
    var type = $(source).attr("type");
    var resolution = $(source).attr("res");
    const objectStore = offlineDb.transaction('videos_os').objectStore('videos_os');
    const offlineDbRequest = objectStore.get(getOfflineVideosIDKey(mediaId, resolution));
    offlineDbRequest.addEventListener('success', () => {
        // If the result exists in the database (is not undefined)
        if (offlineDbRequest.result) {
            console.log('taking videos from IofflineDb', offlineDbRequest.result.video_type, offlineDbRequest.result.resolution);
            displayVideo(offlineDbRequest.result.fileBlob, offlineDbRequest.result.video_type, offlineDbRequest.result.resolution);
        } else {
            // Fetch the videos from the network
            fetchVideoFromNetwork(src, type, resolution);
        }
    });
}

function getOneOfflineVideoSource() {
    var first = false;
    var video480 = false;
    $("#mainVideo source").each(function (index) {
        if(empty(first)){
            first = $(this);
        }
        var resolution = $(this).attr("res");    
        if(resolution == 480){
            video480 = $(this);
        }
    });
    if(!empty(video480)){
        console.log('getOneOfflineVideoSource 480p video found', video480);
        return video480;
    }
    console.log('getOneOfflineVideoSource first video found', first);
    return first;
}

function downloadOneOfflineVideo() {
    var source = getOneOfflineVideoSource();
    if(!empty(source)){
        downloadOfflineVideo(source);
        return true;
    }
    return false;
}

// Define the fetchVideoFromNetwork() function
async function fetchVideoFromNetwork(src, type, resolution) {
    // Fetch the MP4 and WebM versions of the video using the fetch() function,
    // then expose their response bodies as blobs

    //const fileBlob = fetch(src).then(response => response.blob());
    if (empty(mediaId)) {
        avideoToastError('Video Not detected');
        return false;
    }

    if (!offlineDbIsReady) {
        avideoToastError('Database is not ready');
        return false;
    }
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

        console.log(`Received ${receivedLength} of ${contentLength}  ${percentageCompleteStr}`);
    }

    let fileBlob = new Blob(chunks);

    // Only run the next code when both promises have fulfilled
    Promise.all([src, fileBlob, type, contentLength, resolution]).then(values => {
        console.log('fetching videos from network complete', src, type, resolution);
        displayVideo(values[1], type, resolution);
        storeOfflineVideo(src, values[1], type, contentLength, mediaId, resolution);
    });
}

function getOfflineVideosIDKey(videos_id, resolution) {
    videos_id = parseInt(videos_id);
    return videos_id + '_' + resolution;
}

// Define the storeOfflineVideo() function
function storeOfflineVideo(src, fileBlob, type, contentLength, videos_id, resolution) {
    videos_id = parseInt(videos_id);
    // Open transaction, get object store; make it a readwrite so we can write to the IofflineDb
    const objectStore = offlineDb.transaction(['videos_os'], 'readwrite').objectStore('videos_os');
    // Create a record to add to the IofflineDb
    const record = {
        src: src,
        fileBlob: fileBlob,
        video_type: type,
        contentLength: contentLength,
        videos_id: videos_id,
        resolution: resolution,
        videos_id_resolution: getOfflineVideosIDKey(videos_id, resolution),
        created: new Date().getTime(),
        modified: new Date().getTime()
    }
    console.log('storeOfflineVideo', videos_id, resolution);
    // Add the record to the IofflineDb using add()
    const offlineDbRequest = objectStore.add(record);
    offlineDbRequest.addEventListener('success', () => console.log('Record addition attempt finished'));
    offlineDbRequest.addEventListener('error', () => console.error(offlineDbRequest.error));
}

function deleteOfflineVideo(videos_id, resolution) {
    videos_id = parseInt(videos_id);
    // Open transaction, get object store; make it a readwrite so we can write to the IofflineDb
    const objectStore = offlineDb.transaction(['videos_os'], 'readwrite').objectStore('videos_os');
    // Add the record to the IofflineDb using add()
    var myIndex = objectStore.index('videos_id');
    var getAllRequest = myIndex.getAll(IDBKeyRange.only(videos_id));
    var videos_id_resolution = getOfflineVideosIDKey(videos_id, resolution);
    
    getAllRequest.onsuccess = function () {
        for (var item in getAllRequest.result) {
            var video = getAllRequest.result[item];
            if(videos_id_resolution === video.videos_id_resolution){
                const offlineDbRequest = objectStore.deleteObjectStore(video);
                offlineDbRequest.addEventListener('success', () => console.log('Record deleted attempt finished'));
                offlineDbRequest.addEventListener('error', () => console.error(offlineDbRequest.error));
            }
        }
        console.log(getAllRequest.result);
    }
    
}

function getAllOfflineVideo(videos_id) {
    videos_id = parseInt(videos_id);
    // Open transaction, get object store; make it a readwrite so we can write to the IofflineDb
    const objectStore = offlineDb.transaction(['videos_os'], 'readwrite').objectStore('videos_os');
    // Add the record to the IofflineDb using add()
    var myIndex = objectStore.index('videos_id');
    var getAllRequest = myIndex.getAll(IDBKeyRange.only(videos_id));
    getAllRequest.onsuccess = function () {
        console.log(getAllRequest.result);
    }
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

// Open our database; it is created if it doesn't already exist
// (see upgradeneeded below)
const offlineDbRequest = window.indexedDB.open(offlineVideoDbName, 1);

// error handler signifies that the database didn't open successfully
offlineDbRequest.addEventListener('error', () => console.error('Database failed to open'));

// success handler signifies that the database opened successfully
offlineDbRequest.addEventListener('success', () => {
    console.log('Database opened succesfully');
    // Store the opened database object in the offlineDb variable. This is used a lot below
    offlineDb = offlineDbRequest.result;
    offlineDbIsReady = true;
});

// Setup the database tables if this has not already been done
offlineDbRequest.addEventListener('upgradeneeded', e => {
    // Grab a reference to the opened database
    const offlineDb = e.target.result;
    // Create an objectStore to store our videos in (basically like a single table)
    // including a auto-incrementing key
    const objectStore = offlineDb.createObjectStore('videos_os', {keyPath: 'videos_id_resolution'});
    objectStore.createIndex('videos_id', 'videos_id', {unique: false});
    console.log('Database setup complete');
});