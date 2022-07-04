// Create an instance of a offlineDb object for us to store our database in
let offlineDb;
let offlineDbIsReady;
offlineDbIsReady = false;

function downloadOfflineVideo() {
    $("#mainVideo source").each(function (index) {
        var src = $(this).attr("src");
        var type = $(this).attr("type");
        const objectStore = offlineDb.transaction('videos_os').objectStore('videos_os');
        const offlineDbRequest = objectStore.get(src);
        offlineDbRequest.addEventListener('success', () => {
            // If the result exists in the database (is not undefined)
            if (offlineDbRequest.result) {
                // Grab the videos from IofflineDb and display them using displayVideo()
                console.log('taking videos from IofflineDb');
                displayVideo(offlineDbRequest.result.fileBlob, type);
            } else {
                // Fetch the videos from the network
                fetchVideoFromNetwork(src, type);
            }
        });
    });
}

// Define the fetchVideoFromNetwork() function
function fetchVideoFromNetwork(src, type) {
    console.log('fetching videos from network');
    // Fetch the MP4 and WebM versions of the video using the fetch() function,
    // then expose their response bodies as blobs
    const fileBlob = fetch(src).then(response => response.blob());
    // Only run the next code when both promises have fulfilled
    Promise.all([src, fileBlob, type]).then(values => {
        // display the video fetched from the network with displayVideo()
        displayVideo(values[1], type);
        // store it in the IofflineDb using storeVideo()
        storeVideo(src, values[1], type);
    });
}

// Define the storeVideo() function
function storeVideo(src, fileBlob, type) {
    // Open transaction, get object store; make it a readwrite so we can write to the IofflineDb
    const objectStore = offlineDb.transaction(['videos_os'], 'readwrite').objectStore('videos_os');
    // Create a record to add to the IofflineDb
    const record = {
        src: src,
        fileBlob: fileBlob,
        type: type
    }
    // Add the record to the IofflineDb using add()
    const offlineDbRequest = objectStore.add(record);
    offlineDbRequest.addEventListener('success', () => console.log('Record addition attempt finished'));
    offlineDbRequest.addEventListener('error', () => console.error(offlineDbRequest.error));
}

// Define the displayVideo() function
function displayVideo(fileBlob, type) {
    // Create object URLs out of the blobs
    console.log('displayVideo', fileBlob, type);
    const videoURL = URL.createObjectURL(fileBlob);
    var source = $('<source />', {
        src: videoURL,
        type: type,
    });
    $("#mainVideo").append(source);
}

// Open our database; it is created if it doesn't already exist
// (see upgradeneeded below)
const offlineDbRequest = window.indexedDB.open('videos_offlineDb', 1);

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
    const objectStore = offlineDb.createObjectStore('videos_os', {keyPath: 'src'});
    console.log('Database setup complete');
});