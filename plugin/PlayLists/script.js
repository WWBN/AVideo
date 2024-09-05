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


var playListsAdding = false;
var playListsReloading = false;
var playList = [];
async function reloadPlayLists() {
    if (!isOnline() || playListsReloading) {
        return false;
    }
    playListsReloading = true;
    console.log('reloadPlayLists');
        console.trace();
    $.ajax({
        url: webSiteRootURL + 'objects/playlists.json.php',
        success: function (response) {
            playList = response;
            reloadPlayListButtons();
            playListsReloading = false;
        }
    });
}

function reloadPlayListButtons() {
    //console.log('reloadPlayListButtons');
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
    $('.searchlist' + videos_id + crc).btsListFilter('#searchinput' + videos_id + crc, {itemChild: '.nameSearch', initial:false});
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
            if(response.error){
                avideoToastError(__('Error on playlist'));
            }else{
                avideoToastSuccess(__('Success'));
            }
            reloadPlayLists();
            //console.log(".playListsIds_" + playlists_id + '_videos_id_' + videos_id);
            $(".playListsIds_" + playlists_id + '_videos_id_' + videos_id).prop("checked", isChecked);
            modal.hidePleaseWait();
            setTimeout(function () {
                playListsAdding = false
            }, 500);
        }
    });
}

$(document).ready(function () {
    if(empty(mediaId)){
        reloadPlayLists();
    }
});