function saveShowOnTV(playlists_id, showOnTV) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/PlayLists/saveShowOnTV.json.php?playlists_id=' + playlists_id + '&showOnTV=' + showOnTV,
        success: function (response) {
            if (response.errro) {
                avideoAlertError(response.msg);
            } else {
                avideoToast(response.msg);
            }
            modal.hidePleaseWait();
        }
    });
}

var _loadPL_timeout = [];
async function loadPL(videos_id, crc) {
    var uniqid = videos_id + crc;
    clearTimeout(_loadPL_timeout[uniqid]);
    if (typeof $('#addBtn' + uniqid).webuiPopover !== 'function' || typeof loadPlayLists !== 'function') {
        _loadPL_timeout[uniqid] = setTimeout(function () {
            loadPL(videos_id, crc);
        }, 1000);
    } else {
        loadPlayLists(videos_id, crc);
        $('#addBtn' + uniqid).webuiPopover();
        $('#addPlayList' + uniqid).click(function () {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/playlistAddNew.json.php',
                method: 'POST',
                data: {
                    'videos_id': videos_id,
                    'status': $('#publicPlayList' + uniqid).is(":checked") ? "public" : "private",
                    'name': $('#playListName' + uniqid).val()
                },
                success: function (response) {
                    if (response.status > 0) {
                        playList = [];
                        reloadPlayLists();
                        loadPlayLists(videos_id, crc);
                        $('#playListName' + uniqid).val("");
                        $('#publicPlayList' + uniqid).prop('checked', true);
                    }
                    modal.hidePleaseWait();
                }
            });
            return false;
        });
    }
}

var playListsAdding = false;
var playListsReloading = false;
var playList = [];
var isReloadPlayListsExecuting = false;  // Flag for reloadPlayLists
var isReloadPlayListButtonsExecuting = false;  // Flag for reloadPlayListButtons
let loadPlayListsResponseObject = { timestamp: 0, response: false };

// Unified function to handle both reload and load scenarios
async function handlePlayLists(videos_id = null, crc = null, clearCache = 0) {
    if ((playListsReloading || isReloadPlayListsExecuting) && !videos_id) {
        return false;
    }

    // Check if we should update the cache (if 5 seconds have passed or cache is cleared)
    const shouldUpdateCache = loadPlayListsResponseObject.timestamp + 2500 < Date.now() || clearCache;

    // Set execution flags
    isReloadPlayListsExecuting = true;
    playListsReloading = true;

    try {
        // Fetch playlists only if we need to update the cache
        if (shouldUpdateCache) {
            loadPlayListsResponseObject.timestamp = Date.now();
            loadPlayListsResponseObject.response = await fetchPlayLists(clearCache);
        }

        // If no videos_id, assume we're reloading the playlists (e.g., from reloadPlayLists)
        if (!videos_id) {
            playList = loadPlayListsResponseObject.response;
            reloadPlayListButtons();
        } else {
            // Handle loading playlists for a specific video
            loadPlayListsResponse(loadPlayListsResponseObject.response, videos_id, crc);
        }
    } catch (error) {
        console.error('Error handling playlists', error);
    } finally {
        // Reset execution flags
        setTimeout(function () {
            isReloadPlayListsExecuting = false;
            playListsReloading = false;
        }, 200);
    }
}

// Unified function to handle AJAX request to playlists endpoint
var fetchPlayListsRows = [];
var fetchPlayListsPromise = null; // Variable to store the current Promise

async function fetchPlayLists(clearCache = 0) {
    return new Promise((resolve, reject) => {
        if (!isOnline()) {
            return reject('Offline');
        }

        // Check if we already have data in the cache and no cache clearing is requested
        if (!empty(fetchPlayListsRows) && empty(clearCache)) {
            console.log('fetchPlayLists cached');
            syncPlaylistWithFetchedPlayLists();
            resolve(fetchPlayListsRows);
            return fetchPlayListsRows;
        }

        // If a request is already in progress, return the same Promise
        if (fetchPlayListsPromise) {
            console.log('fetchPlayLists already running, waiting for the same Promise');
            return fetchPlayListsPromise.then(resolve).catch(reject);
        }

        // Start a new request and store the Promise
        console.log('fetchPlayLists starting a new AJAX request');
        fetchPlayListsPromise = $.ajax({
            url: webSiteRootURL + 'objects/playlists.json.php?clearPlaylistCache=' + clearCache,
            success: function (response) {
                fetchPlayListsRows = response.rows;
                syncPlaylistWithFetchedPlayLists();
                resolve(fetchPlayListsRows);
            },
            error: function (error) {
                reject(error);
            }
        }).always(() => {
            // Reset the promise when the request finishes (either success or failure)
            fetchPlayListsPromise = null;
        });
    });
}


// reloadPlayLists function utilizing the unified fetchPlayLists
async function reloadPlayLists(clearCache = 0) {
    handlePlayLists(null, null, clearCache);
}

// loadPlayLists function utilizing the unified fetchPlayLists
async function loadPlayLists(videos_id, crc) {
    handlePlayLists(videos_id, crc);
}


function reloadPlayListButtons() {
    if (isReloadPlayListButtonsExecuting) {
        return;
    }

    // Set flag to prevent further execution within 1 second
    isReloadPlayListButtonsExecuting = true;

    for (var i in playList) {
        if (!playList[i].id || (playList[i].status !== 'watch_later' && playList[i].status !== 'favorite')) {
            continue;
        }
        for (var x in playList[i].videos) {
            if (typeof (playList[i].videos[x]) === 'object') {
                setPlaylistStatus(playList[i].videos[x].videos_id, true, playList[i].id, playList[i].status);
            }
        }
    }
    isReloadPlayListButtonsExecuting = false;
}

var isSyncing = false; // Flag to track if the function is already running
var syncDelay = 5000;  // Minimum delay of 5 seconds between calls
var chunkSize = 5;    // Number of playlists to process per chunk (adjust as needed)

async function syncPlaylistWithFetchedPlayLists() {
    if (isSyncing) {
        console.log('syncPlaylistWithFetchedPlayLists is already running. Skipping this call.');
        return;
    }

    isSyncing = true; // Set the flag to indicate the function is running

    // Initial UI updates
    $('.loadingPLBtn').hide();
    $('.watchLaterBtnAdded').hide();
    $('.favoriteBtnAdded').hide();
    $('.watchLaterBtn').show();
    $('.favoriteBtn').show();
    console.trace("syncPlaylistWithFetchedPlayLists backtrace:", fetchPlayListsRows);

    let playlists = fetchPlayListsRows;
    let totalPlaylists = playlists.length;
    let currentIndex = 0;
    let totalVideos = 0;

    function processPlaylists() {
        let start = currentIndex;
        let end = Math.min(currentIndex + chunkSize, totalPlaylists);

        for (let x = start; x < end; x++) {
            let playlist = playlists[x];
            if (typeof playlist === 'object' && typeof playlist.videos === 'object') {
                let videos = playlist.videos;
                for (let y in videos) {
                    let video = videos[y];
                    if (!empty(video)) {
                        totalVideos++;
                        setPlaylistStatus(video.id, true, playlist.id, playlist.status);
                    }
                }
            }
        }

        currentIndex = end;

        if (currentIndex < totalPlaylists) {
            console.log('syncPlaylistWithFetchedPlayLists next processPlaylists total videos='+totalVideos);
            // Schedule the next chunk after a short delay
            setTimeout(processPlaylists, 100);
        } else {
            console.log('syncPlaylistWithFetchedPlayLists done total videos='+totalVideos);
            // All playlists have been processed
            setTimeout(() => {
                isSyncing = false; // Reset the flag after the delay
            }, syncDelay);
        }
    }

    // Start processing playlists
    processPlaylists();
}


async function loadPlayListsResponse(response, videos_id, crc) {
    //console.log('loadPlayListsResponse');
    //console.log(response, videos_id, crc);

    $('.searchlist' + videos_id + crc).html('');
    for (var i in response) {
        if (!response[i].id) {
            continue;
        }

        var icon = "fa fa-lock"
        if (response[i].status == "public") {
            icon = "fa fa-globe"
        } else if (response[i].status == "watch_later") {
            icon = "fas fa-clock"
        } else if (response[i].status == "favorite") {
            icon = "fas fa-heart"
        }
        var checked = "";
        for (var x in response[i].videos) {
            if (typeof (response[i].videos[x]) === 'object' && response[i].videos[x].videos_id == videos_id) {
                checked = "checked";
            }
        }
        var randId = (("_" + response[i].id) + videos_id) + Math.random();

        var itemsArray = {};
        itemsArray.icon = icon;
        itemsArray.id = response[i].id;
        itemsArray.name_translated = response[i].name_translated;
        itemsArray.response_id = response[i].id + '' + videos_id;
        itemsArray.checked = checked;
        itemsArray.videos_id = videos_id;
        itemsArray.randId = randId;

        $(".searchlist" + videos_id + crc).append(arrayToTemplate(itemsArray, listGroupItemTemplate));


    }
    $('.searchlist' + videos_id + crc).btsListFilter('#searchinput' + videos_id + crc, { itemChild: '.nameSearch', initial: false });
    $('.playListsVideosIds' + videos_id).change(function () {
        if (playListsAdding) {
            return false;
        }
        playListsAdding = true;

        addVideoToPlayList(videos_id, $(this).is(":checked"), $(this).val());
        return false;
    });
}

function addVideoToPlayList(videos_id, isChecked, playlists_id) {
    //console.log('addVideoToPlayList');
    modal.showPleaseWait();

    $.ajax({
        url: webSiteRootURL + 'objects/playListAddVideo.json.php',
        method: 'POST',
        data: {
            'videos_id': videos_id,
            'add': isChecked,
            'playlists_id': playlists_id
        },
        success: function (response) {

            if (response.error) {
                var msg = __('Error on playlist');
                if (!empty(response.msg)) {
                    msg = response.msg;
                }
                avideoAlertError(msg);
            } else {
                console.log('addVideoToPlayList success', response);
                setPlaylistStatus(response.videos_id, response.add, playlists_id, response.type, true);
            }
            reloadPlayLists(1);

            modal.hidePleaseWait();
            setTimeout(function () {
                playListsAdding = false
            }, 500);
        }
    });
}

function setPlaylistStatus(videos_id, add, playlists_id = 0, type = '', toast = false) {

    $('.loadingPLBtn' + videos_id).hide();
    $('.watchLaterBtnAdded' + videos_id).hide();
    $('.favoriteBtnAdded' + videos_id).hide();
    $('.watchLaterBtn' + videos_id).show();
    $('.favoriteBtn' + videos_id).show();

    if (type == 'favorite') {
        if (add) {
            $('.favoriteBtn' + videos_id).hide();
            $('.favoriteBtnAdded' + videos_id).show();
            if (toast) {
                avideoToastSuccess(__('Add to Favorite'));
            }
        } else {
            $('.favoriteBtn' + videos_id).show();
            $('.favoriteBtnAdded' + videos_id).hide();
            if (toast) {
                avideoToastWarning(__('Removed from Favorite'));
            }
        }
    } else if (type == 'watch_later') {
        if (add) {
            $('.watchLaterBtn' + videos_id).hide();
            $('.watchLaterBtnAdded' + videos_id).show();
            if (toast) {
                avideoToastSuccess(__('Add to Watch Later'));
            }
        } else {
            $('.watchLaterBtn' + videos_id).show();
            $('.watchLaterBtnAdded' + videos_id).hide();
            if (toast) {
                avideoToastWarning(__('Removed from Watch Later'));
            }
        }
    }

    $(".playListsIds_" + playlists_id + '_videos_id_' + videos_id).prop("checked", add);
}

$(function () {
    if (empty(mediaId)) {
        reloadPlayLists();
    }
});