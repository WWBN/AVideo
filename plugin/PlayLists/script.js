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

var playListsAdding = false;
var playListsReloading = false;
var playList = [];
var isReloadPlayListsExecuting = false;  // Flag for reloadPlayLists
var isReloadPlayListButtonsExecuting = false;  // Flag for reloadPlayListButtons
var loadPlayListsResponseObject = { timestamp: 0, response: false };

// Unified function to handle both reload and load scenarios
async function handlePlayLists(videos_id = null, clearCache = 0) {
    if ((playListsReloading || isReloadPlayListsExecuting) && !videos_id) {
        return false;
    }

    // Check if we should update the cache (if 1 second have passed or cache is cleared)
    const shouldUpdateCache = loadPlayListsResponseObject.timestamp + 1000 < Date.now() || clearCache;

    // Set execution flags
    isReloadPlayListsExecuting = true;
    playListsReloading = true;

    try {
        // Fetch playlists only if we need to update the cache
        if (shouldUpdateCache) {
            loadPlayListsResponseObject.timestamp = Date.now();
            loadPlayListsResponseObject.response = await fetchPlayLists(clearCache);
        } else {
            console.log('handlePlayLists not update yet');
        }

        // If no videos_id, assume we're reloading the playlists (e.g., from reloadPlayLists)
        if (!videos_id) {
            playList = loadPlayListsResponseObject.response;
            reloadPlayListButtons();
        } else {
            // Handle loading playlists for a specific video
            loadPlayListsResponse(videos_id);
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
var fetchPlayListsRows = null;
var fetchVideosPlaylistsIds = null;
var fetchVideosPlaylists = null;
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
            url: webSiteRootURL + 'objects/playlists.json.php?clearPlaylistCache=' + (empty(clearCache) ? 0 : 1),
            success: function (response) {
                fetchPlayListsRows = response.rows;
                fetchVideosPlaylistsIds = response.videosPlaylistsIds;
                fetchVideosPlaylists = response.videosPlaylists;
                console.log('fetchPlayLists success', response, fetchPlayListsRows);
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
async function loadPlayLists(videos_id) {
    handlePlayLists(videos_id);
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

var isSyncingPlaylists = false; // Flag to track if the function is already running
var syncDelay = 1000;  // Minimum delay between calls

async function syncPlaylistWithFetchedPlayLists() {
    if (isSyncingPlaylists) {
        console.log('syncPlaylistWithFetchedPlayLists is already running. Skipping this call.');
        return;
    }

    isSyncingPlaylists = true; // Set the flag to indicate the function is running

    if (fetchPlayListsRows === null || empty(fetchPlayListsRows)) {
        console.log('syncPlaylistWithFetchedPlayLists fetchPlayListsRows is empty or null');
        setTimeout(() => {
            isSyncingPlaylists = false;
            syncPlaylistWithFetchedPlayLists(); // Try again after a short delay
        }, syncDelay);
        return;
    }

    // Initial UI updates
    $('.loadingPLBtn').hide();
    $('.watchLaterBtnAdded').hide();
    $('.favoriteBtnAdded').hide();
    $('.watchLaterBtn').show();
    $('.favoriteBtn').show();
    console.trace("syncPlaylistWithFetchedPlayLists backtrace:", fetchPlayListsRows);

    let playlists = fetchPlayListsRows;
    let totalVideos = 0;

    let startTime = performance.now();

    // Process all playlists and videos in one go
    playlists.forEach(playlist => {
        if (typeof playlist === 'object' && typeof playlist.videos === 'object') {
            playlist.videos.forEach(video => {
                if (!empty(video)) {
                    totalVideos++;
                    setPlaylistStatus(video.id, true, playlist.id, playlist.status);
                }
            });
        }
    });

    let endTime = performance.now();
    let duration = (endTime - startTime) / 1000; // Convert to seconds

    console.log(`syncPlaylistWithFetchedPlayLists processed ${totalVideos} videos in ${duration.toFixed(2)} seconds.`);

    // Reset the flag after the delay
    setTimeout(() => {
        isSyncingPlaylists = false;
    }, syncDelay);
}

var isSyncingPlaylistsVideos = false;
async function syncPlaylistWithFetchedVideos() {
    if (isSyncingPlaylistsVideos) {
        console.log('syncPlaylistWithFetchedVideos is already running. Skipping this call.');
        return;
    }

    if (fetchVideosPlaylists === null) {
        console.log('syncPlaylistWithFetchedVideos fetchPlayListsRows is null');
        fetchPlayLists();
        setTimeout(() => {
            syncPlaylistWithFetchedVideos(); // Try again after 1 second
        }, 1000);
    }

    if (empty(fetchVideosPlaylists)) {
        console.log('syncPlaylistWithFetchedVideos empty fetchPlayListsRows');
        setTimeout(() => {
            syncPlaylistWithFetchedVideos(); // Try again after 1 second
        }, 1000);
        return;
    }

    isSyncingPlaylistsVideos = true; // Set the flag to indicate the function is running

    //console.trace("syncPlaylistWithFetchedVideos backtrace:", fetchVideosPlaylistsIds);

    for (const videos_id in fetchVideosPlaylistsIds) {
        if (Object.prototype.hasOwnProperty.call(fetchVideosPlaylistsIds, videos_id)) {
            const playlists_ids = fetchVideosPlaylistsIds[videos_id];
            $('.playListsVideosIds' + videos_id).prop("checked", false);

            for (const index in playlists_ids) {
                if (Object.prototype.hasOwnProperty.call(playlists_ids, index)) {
                    const playlists_id = playlists_ids[index];
                    //console.log('syncPlaylistWithFetchedVideos', videos_id, playlists_id);
                    setPlaylistStatus(videos_id, true, playlists_id);
                }
            }
        }
    }

    setTimeout(() => {
        isSyncingPlaylistsVideos = false; // Reset the flag after the delay
    }, syncDelay);

}

async function loadPlayListsResponse(videos_id) {

    if (fetchVideosPlaylists === null || typeof fetchVideosPlaylists == 'undefined') {
        console.log('loadPlayListsResponse fetchVideosPlaylists empty');
        setTimeout(() => {
            loadPlayListsResponse(videos_id); // Try again after 1 second
        }, 1000);
        return false;
    }



    var response = fetchPlayListsRows;

    //console.log('loadPlayListsResponse');
    console.log('loadPlayListsResponse', videos_id, response);
    //syncPlaylistWithFetchedVideos();

    $('.searchlist' + videos_id).html('');
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

        $(".searchlist" + videos_id).append(arrayToTemplate(itemsArray, listGroupItemTemplate));


    }
    $('.searchlist' + videos_id).btsListFilter('#searchinput' + videos_id, { itemChild: '.nameSearch', initial: false });
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
            fetchPlayLists(1);
            if (response.error) {
                var msg = __('Error on playlist');
                if (!empty(response.msg)) {
                    msg = response.msg;
                }
                avideoAlertError(msg);
            } else {
                console.log('addVideoToPlayList success', response);
                setTimeout(function () {
                    setPlaylistStatus(response.videos_id, response.add, playlists_id, response.type, true);
                }, 100);
            }

            modal.hidePleaseWait();
            setTimeout(function () {
                playListsAdding = false
            }, 500);
        }
    });
}

function setPlaylistStatus(videos_id, add, playlists_id = 0, type = '', toast = false) {
    if (typeof videos_id == 'undefined') {
        return false;
    }
    if (toast) {
        console.log('setPlaylistStatus', videos_id, add, playlists_id, type, toast);
    }
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

    var selector = ".playListsIds_" + playlists_id + '_videos_id_' + videos_id;

    $(selector).prop("checked", add);
}

function createNewProgram(playlistName, isPublic, videos_id = 0) {

    // Check if the playlist name is provided
    if (empty(playlistName)) {
        avideoAlertError(__('Please provide a title'));
        return false;
    }

    modal.showPleaseWait();
    // AJAX request to create a new playlist
    $.ajax({
        url: webSiteRootURL + 'objects/playlistAddNew.json.php',
        method: 'POST',
        data: {
            'status': isPublic ? "public" : 'private',
            'name': playlistName.trim(), // Use the provided playlist name
        },
        success: function (response) {
            if (response.status > 0) {
                avideoToastSuccess(playlistName + ' ' + __('saved'));
                fetchPlayLists(1).then(function () {
                    $('.playListName').val('');
                    modal.hidePleaseWait();
                    if (!empty(videos_id)) {
                        loadPlayListsResponse(videos_id);
                        $('.searchlist'+videos_id).scrollTop($('.searchlist'+videos_id)[0].scrollHeight);
                    }
                });
            } else {
                avideoAlertError(__('Unable to create the program. Please try again'));
            }
        },
        error: function () {
            avideoAlertError(__('An error occurred. Please try again'));
        }
    });
    return false;
}


$(function () {
    if (empty(mediaId)) {
        fetchPlayLists(0);
    }
});
