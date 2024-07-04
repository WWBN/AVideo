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
    var uniqid = videos_id+crc;
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


loadPlayListsResponseObject = {timestamp: 0, response: false};
async function loadPlayLists(videos_id, crc) {
    if (loadPlayListsResponseObject.timestamp + 5000 < Date.now()) {
        console.log('loadPlayLists');
        console.trace();
        loadPlayListsResponseObject.timestamp = Date.now();
        loadPlayListsResponseObject.response = [];
        setTimeout(function () {
            $.ajax({
                url: webSiteRootURL+'objects/playlists.json.php',
                cache: true,
                success: function (response) {
                    loadPlayListsResponseObject.response = response;
                    loadPlayListsResponse(loadPlayListsResponseObject.response, videos_id, crc);
                }
            });
        }, 500);

    } else {
        if (loadPlayListsResponseObject.response) {
            loadPlayListsResponse(loadPlayListsResponseObject.response, videos_id, crc);
        } else {
            setTimeout(function () {
                loadPlayLists(videos_id, crc);
            }, 1500);
        }
    }
}