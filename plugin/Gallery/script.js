var tmpPId;
var tmpSave;
var playList = [];
function loadPlayLists(elementId, videos_id) {

    if (!playList.length) {
        setTimeout(function () {
            loadPlayLists(elementId, videos_id)
        }, 500);
        return false;
    }

    $('#searchlist' + elementId).html('');
    for (var i in playList) {
        if (!playList[i].id) {
            continue;
        }
        var icon = "lock";
        if (playList[i].status == "public") {
            icon = "globe";
        }
        var checked = "";
        for (var x in playList[i].videos) {
            if (typeof (playList[i].videos[x]) === 'object' && playList[i].videos[x].videos_id == videos_id) {
                checked = "checked";
            }
        }
        $("#searchlist" + elementId).append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>'
                + playList[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault' + elementId + '" name="someSwitchOption' + playList[i].id + elementId + '" class="playListsIds' + videos_id + ' playListsIds' + videos_id + '_' + playList[i].id + ' " type="checkbox" value="'
                + playList[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault' + elementId + '" class="label-success"></label></div></a>');
    }
    $('#searchlist' + elementId).btsListFilter('#searchinput' + elementId, {itemChild: 'span'});
    $('#someSwitchOptionDefault' + elementId).change(function () {
        modal.showPleaseWait();
        //tmp-variables simply make the values avaible on success.
        tmpPId = $(this).val();
        tmpSave = $(this).is(":checked");
        $.ajax({
            url: webSiteRootURL + 'objects/playListAddVideo.json.php',
            method: 'POST',
            data: {
                'videos_id': videos_id,
                'add': $(this).is(":checked"),
                'playlists_id': $(this).val()
            },
            success: function () {
                if(typeof channelName !== 'undefined'){
                    $("#channelPlaylists").load(webSiteRootURL + "view/channelPlaylist.php?channelName=" + channelName);
                }
                reloadPlayLists();
                $(".playListsIds" + videos_id + "_" + tmpPId).prop("checked", tmpSave);
                modal.hidePleaseWait();
            }
        });
        return false;
    });
}

function reloadPlayLists() {
    $.ajax({
        url: webSiteRootURL + 'objects/playlists.json.php',
        success: function (response) {
            playList = response;
        }
    });
}

$(document).ready(function () {
    reloadPlayLists();
});