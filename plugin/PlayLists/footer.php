<script>
    var playListsAdding = false;
    var playList = [];
    function reloadPlayLists() {
        //console.log('reloadPlayLists');
        $.ajax({
            url: webSiteRootURL + 'objects/playlists.json.php',
            success: function (response) {
                playList = response;
                reloadPlayListButtons();
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

    loadPlayListsResponseObject = {timestamp: 0, response: false};
    function loadPlayLists(videos_id, crc) {
        //console.log('loadPlayLists');
        if (loadPlayListsResponseObject.timestamp + 5000 < Date.now()) {
            loadPlayListsResponseObject.timestamp = Date.now();
            loadPlayListsResponseObject.response = [];
            setTimeout(function () {
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/playlists.json.php',
                    cache: true,
                    success: function (response) {
                        loadPlayListsResponseObject.response = response;
                        loadPlayListsResponse(loadPlayListsResponseObject.response, videos_id, crc);
                    }
                });
                ;
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

    function loadPlayListsResponse(response, videos_id, crc) {
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
            $(".searchlist" + videos_id + crc).append('<a class="list-group-item"><i class="' + icon + '"></i> <span>'
                    + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault'
                    + randId + '" name="someSwitchOption' + response[i].id + videos_id + '" class="playListsVideosIds' + videos_id +' playListsIds_' + response[i].id + '_videos_id_' + videos_id + ' playListsIds' + response[i].id + ' " type="checkbox" value="'
                    + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault'
                    + randId + '" class="label-success"></label></div></a>');

        }
        $('.searchlist' + videos_id + crc).btsListFilter('#searchinput' + videos_id + crc, {itemChild: 'span'});
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
            url: '<?php echo $global['webSiteRootURL']; ?>objects/playListAddVideo.json.php',
            method: 'POST',
            data: {
                'videos_id': videos_id,
                'add': isChecked,
                'playlists_id': playlists_id
            },
            success: function (response) {
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
        reloadPlayLists();
    });
</script>