<script>
    var playListsAdding = false;
    var playList = [];
    function reloadPlayLists() {
        $.ajax({
            url: webSiteRootURL + 'objects/playlists.json.php',
            success: function (response) {
                playList = response;
            }
        });
    }

    loadPlayListsResponseObject = {timestamp: 0, response: false};
    function loadPlayLists(videos_id, crc) {
        if (loadPlayListsResponseObject.timestamp + 5000 < Date.now()) {
            console.log('loadPlayLists');
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
                console.log('loadPlayLists NOT empty response');
                loadPlayListsResponse(loadPlayListsResponseObject.response, videos_id, crc);
            } else {
                console.log('loadPlayLists empty response');
                setTimeout(function () {
                    loadPlayLists(videos_id, crc);
                }, 1500);
            }
        }
    }

    function loadPlayListsResponse(response, videos_id, crc) {

        $('.searchlist' + videos_id + crc).html('');
        for (var i in response) {
            if (!response[i].id) {
                continue;
            }
            var icon = "lock"
            if (response[i].status == "public") {
                icon = "globe"
            }
            var checked = "";
            for (var x in response[i].videos) {
                if (typeof (response[i].videos[x]) === 'object' && response[i].videos[x].videos_id == videos_id) {
                    checked = "checked";
                }
            }
            $(".searchlist" + videos_id + crc).append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>'
                    + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault'
                    + response[i].id + videos_id + '" name="someSwitchOption' + response[i].id + videos_id + '" class="playListsIds' + videos_id + ' playListsIds' + response[i].id + ' " type="checkbox" value="'
                    + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault'
                    + response[i].id + videos_id + '" class="label-success"></label></div></a>');

        }
        $('.searchlist' + videos_id + crc).btsListFilter('#searchinput' + videos_id + crc, {itemChild: 'span'});
        $('.playListsIds' + videos_id).change(function () {
            if (playListsAdding) {
                return false;
            }
            playListsAdding = true;
            modal.showPleaseWait();

            //tmp-variables simply make the values avaible on success.
            tmpPIdBigVideo = $(this).val();
            tmpSaveBigVideo = $(this).is(":checked");
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/playListAddVideo.json.php',
                method: 'POST',
                data: {
                    'videos_id': videos_id,
                    'add': $(this).is(":checked"),
                    'playlists_id': $(this).val()
                },
                success: function (response) {
                    $(".playListsIds" + tmpPIdBigVideo).prop("checked", tmpSaveBigVideo);
                    modal.hidePleaseWait();
                    setTimeout(function () {
                        playListsAdding = false
                    }, 500);
                }
            });
            return false;
        });
    }


    $(document).ready(function () {
        reloadPlayLists();
    });
</script>