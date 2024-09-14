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
async function fetchPlayLists(clearCache = 0) {
    return new Promise((resolve, reject) => {
        if (!isOnline()) {
            return reject('Offline');
        }

        if(!empty(fetchPlayListsRows) && empty(clearCache)){
            console.log('fetchPlayLists cached');
            resolve(fetchPlayListsRows);
            return fetchPlayListsRows;
        }

        console.log('fetchPlayLists');
        console.trace();

        $.ajax({
            url: webSiteRootURL + 'objects/playlists.json.php?clearPlaylistCache=' + clearCache,
            success: function (response) {
                fetchPlayListsRows = response.rows;
                resolve(fetchPlayListsRows);
            },
            error: function (error) {
                reject(error);
            }
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

    $('.watchLaterBtnAdded').hide();
    $('.favoriteBtnAdded').hide();
    $('.watchLaterBtn').show();
    $('.favoriteBtn').show();

    for (var i in playList) {
        if (!playList[i].id || (playList[i].status !== 'watch_later' && playList[i].status !== 'favorite')) {
            continue;
        }
        for (var x in playList[i].videos) {
            if (typeof (playList[i].videos[x]) === 'object') {
                if (playList[i].status === 'watch_later') {
                    $('.watchLaterBtn' + playList[i].videos[x].videos_id).hide();
                    $('.watchLaterBtnAdded' + playList[i].videos[x].videos_id).show();
                } else if (playList[i].status === 'favorite') {
                    $('.favoriteBtn' + playList[i].videos[x].videos_id).hide();
                    $('.favoriteBtnAdded' + playList[i].videos[x].videos_id).show();
                }
            }
        }
    }
    isReloadPlayListButtonsExecuting = false;
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
                if(!empty(response.msg)){
                    msg = response.msg;
                }
                avideoAlertError(msg);
            } else {
                console.log('addVideoToPlayList success', response);
                if (response.type == 'favorite') {
                    if (response.add) {
                        $('.favoriteBtn' + response.videos_id).hide();
                        $('.favoriteBtnAdded' + response.videos_id).show();
                        avideoToastSuccess(__('Add to Favorite'));
                    } else {
                        $('.favoriteBtn' + response.videos_id).show();
                        $('.favoriteBtnAdded' + response.videos_id).hide();
                        avideoToastWarning(__('Removed from Favorite'));
                    }
                } else if (response.type == 'watch_later') {
                    if (response.add) {
                        $('.watchLaterBtn' + response.videos_id).hide();
                        $('.watchLaterBtnAdded' + response.videos_id).show();
                        avideoToastSuccess(__('Add to Watch Later'));
                    } else {
                        $('.watchLaterBtn' + response.videos_id).show();
                        $('.watchLaterBtnAdded' + response.videos_id).hide();
                        avideoToastWarning(__('Removed from Watch Later'));
                    }
                }
            }
            reloadPlayLists(1);
            //console.log(".playListsIds_" + playlists_id + '_videos_id_' + videos_id);
            $(".playListsIds_" + playlists_id + '_videos_id_' + videos_id).prop("checked", isChecked);
            modal.hidePleaseWait();
            setTimeout(function () {
                playListsAdding = false
            }, 500);
        }
    });
}

$(function () {
    if (empty(mediaId)) {
        reloadPlayLists();
    }
});