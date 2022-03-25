<?php
if (!isVideo()) {
    echo '<!-- mediaSession is not a video -->';
    return false;
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    echo '<!-- mediaSession videos id is empty -->';
    return false;
}

$MediaMetadata = Video::getMediaSession($videos_id)
?>
<script>
    if ('mediaSession' in navigator) {
        navigator.mediaSession.metadata = new MediaMetadata(<?php echo _json_encode($MediaMetadata); ?>);

        setActionHandlerIfSupported('play', function () { /* Code excerpted. */
            player.play();
        });
        setActionHandlerIfSupported('pause', function () { /* Code excerpted. */
            player.pause();
        });
        setActionHandlerIfSupported('stop', function () { /* Code excerpted. */
            player.pause();
        });
        setActionHandlerIfSupported('seekbackward', function () { /* Code excerpted. */
            player.currentTime(player.currentTime() - 5);
        });
        setActionHandlerIfSupported('seekforward', function () { /* Code excerpted. */
            player.currentTime(player.currentTime() + 5);
        });
        setActionHandlerIfSupported('seekto', function () { /* Code excerpted. */
            console.log('mediaSession seekto');
        });
        setActionHandlerIfSupported('previoustrack', function () { /* Code excerpted. */
            try {
                player.playlist.previous();
            } catch (e) {
            }
        });
        setActionHandlerIfSupported('nexttrack', function () { /* Code excerpted. */
            try {
                player.playlist.next();
            } catch (e) {
                if (playNextURL) {
                    playNext(playNextURL);
                }
            }
        });

        setActionHandlerIfSupported('skipad', function () { /* Code excerpted. */
            console.log('mediaSession skipad');
        });
        setPlaylistUpdate();
    }

    function setPlaylistUpdate() {

        if (typeof player == 'undefined' || typeof player.playlist == 'undefined') {
            setTimeout(function () {
                setPlaylistUpdate();
            }, 1000);
            return false;
        }
        console.log('setPlaylistUpdate');
        player.on('playlistitem', function () {
            updateMediaSessionMetadata();
        });
    }

    function updateMediaSessionMetadata() {
        index = player.playlist.currentIndex();
        videos_id = playerPlaylist[index].videos_id;
        console.log('updateMediaSessionMetadata', videos_id);

        $.ajax({
            url: webSiteRootURL + 'plugin/PlayerSkins/mediaSession.json.php',
            method: 'POST',
            data: {
                'videos_id': videos_id,
            },
            success: function (response) {
                navigator.mediaSession.metadata = new MediaMetadata(response);
            }
        });
    }

    function setActionHandlerIfSupported(action, func) {
        try {
            navigator.mediaSession.setActionHandler(action, func);
        } catch (e) {
            if (e.name != "TypeError")
                throw e;
        }
    }

</script>