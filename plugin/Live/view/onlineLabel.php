<?php
if(empty($streamName)){
    $live = isLive();
    $streamName = $live['key'];
    $live_servers_id = $live['live_servers_id'];
}else{
    $live_servers_id = Live::getCurrentLiveServersId();
}
$liveViewStatusID = str_replace('-', '_', "liveViewStatusID_{$streamName}_{$live_servers_id}");
$liveViewStatusClass = "liveViewStatusClass liveViewStatusClass_{$streamName} liveViewStatusClass_{$streamName}_{$live_servers_id}";

$liveObj = AVideoPlugin::getObjectData('Live');
if($liveObj->doNotShowOnlineOfflineLabel){
    $liveViewStatusClass .= ' hidden';
}

if (isLiveLink() || Live::isLiveAndIsReadyFromKey($streamName, $live_servers_id, @$live['live_index'])) {
    echo "<span class='label label-success liveOnlineLabel {$liveViewStatusClass}' id='{$liveViewStatusID}'>ONLINE</span>";
} else {
    echo "<span class='label label-danger liveOnlineLabel {$liveViewStatusClass}' id='{$liveViewStatusID}'>OFFLINE</span>";
}
?>

<script>

    function isOfflineVideo() {
<?php
if (isMobile()) {
    ?>
            return !$('#<?php echo $liveViewStatusID; ?>').hasClass('isOnline');
    <?php
} else {
    ?>
            if (player.readyState()) {
                if (typeof player.tech_ !== 'undefined') {
                    var uri = player.tech_.hls.selectPlaylist().uri;
                    console.log("isOfflineVideo player.readyState", uri);
                    if (uri.includes("loopBGHLS/res")) {
                        return true;
                    }
                    if (player.tech_.hls.playlists.media_.segments[0].resolvedUri.includes(".ts?seq=")) {
                        return true;
                    }
                }
                return false;
            } else if (player.readyState() === 0 && player.paused()) {
                console.log("isOfflineVideo paused ");
                return false;
            } else {
                console.log("isOfflineVideo player.readyState not ready", player.readyState());
            }
    <?php
}
?>
        return true;
    }
    var isOnlineLabel = false;
    var playCorrectSource<?php echo $liveViewStatusID; ?>Timout;
    function playCorrectSource<?php echo $liveViewStatusID; ?>() {
        if (typeof player === 'undefined') {
            clearTimeout(playCorrectSource<?php echo $liveViewStatusID; ?>Timout);
            playCorrectSource<?php echo $liveViewStatusID; ?>Timout = setTimeout(function () {
                playCorrectSource<?php echo $liveViewStatusID; ?>();
            }, 1000);
            return false;
        }
        var bigPlayButtonModified = false;
        if ($('#<?php echo $liveViewStatusID; ?>').hasClass('isOnline') && !isOfflineVideo()) {
            isOnlineLabel = true;
            player.bigPlayButton.show();
            bigPlayButtonModified = true;
            onlineLabelOnline('#<?php echo $liveViewStatusID; ?>');
            //playerPlayIfAutoPlay(0);
            clearTimeout(_reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>);
            if (isAutoplayEnabled() && !player.paused()) {
                player.play();
            }
        } else if ($('#<?php echo $liveViewStatusID; ?>').hasClass('isOnline') && isOfflineVideo()) {
            isOnlineLabel = true;
            player.bigPlayButton.show();
            bigPlayButtonModified = true;
            onlineLabelPleaseWait('#<?php echo $liveViewStatusID; ?>');
            reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>(10000);
            //reloadVideoJS();
            //playerPlayIfAutoPlay(0);            
            if (isAutoplayEnabled() && !player.paused()) {
                player.play();
            }
            player.on('error', function () {
                console.log("PError 1 " + player.error());
            });
            if (typeof player.tech_ !== 'undefined') {
                player.tech_.hls.playlists.on('error', function () {
                    console.log("PError 2 " + player.error());
                    console.log("PError 2.1 " + this.error());
                });
            }
            clearTimeout(playCorrectSource<?php echo $liveViewStatusID; ?>Timout);
            playCorrectSource<?php echo $liveViewStatusID; ?>Timout = setTimeout(function () {
                playCorrectSource<?php echo $liveViewStatusID; ?>();
                getStats<?php echo $liveViewStatusID; ?>();
            }, 5000);
        } else if (!$('#<?php echo $liveViewStatusID; ?>').hasClass('isOnline') && !isOfflineVideo()) {
            if (player.readyState() <= 2) {
                isOnlineLabel = false;
                onlineLabelOffline('#<?php echo $liveViewStatusID; ?>');
                player.pause();
                //player.reset();
                $('#mainVideo.liveVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Offline.jpg)'});
                $('#mainVideo.liveVideo').find('.vjs-poster').fadeIn();
                player.trigger('loadstart');
                player.posterImage.show();
                player.bigPlayButton.show();
                player.currentTime(0);
                player.on('play', function () {
                    $('#mainVideo.liveVideo').find('.vjs-poster').fadeOut();
                });
                //reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>(10000);
                //reloadVideoJS();
                //playerPlay(0);
            } else {
                onlineLabelFinishing('#<?php echo $liveViewStatusID; ?>');
                clearTimeout(playCorrectSource<?php echo $liveViewStatusID; ?>Timout);
                playCorrectSource<?php echo $liveViewStatusID; ?>Timout = setTimeout(function () {
                    playCorrectSource<?php echo $liveViewStatusID; ?>();
                    getStats<?php echo $liveViewStatusID; ?>();
                }, 15000);
            }
        }
        if (!bigPlayButtonModified) {
            player.bigPlayButton.hide();
        }
    }

    var _reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>;
    function reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>(timeout) {
        if (_reloadVideoJSTimeout<?php echo $liveViewStatusID; ?>) {
            return false;
        }

        _reloadVideoJSTimeout<?php echo $liveViewStatusID; ?> = setTimeout(function () {
            reloadVideoJS();
        }, timeout);
    }

    function getStats<?php echo $liveViewStatusID; ?>() {
        if (avideoSocketIsActive()) {
            return false;
        }
        var timeout = 10000;
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/stats.json.php?live_servers_id=<?php echo $live_servers_id; ?>&Label',
            data: {"name": "<?php echo $streamName; ?>"},
            type: 'post',
            success: function (response) {
                if (avideoSocketIsActive()) {
                    return false;
                }
                if (response.name == "<?php echo $streamName; ?>") {
                    if (response.msg === "ONLINE") {
                        isOnlineLabel = true;
                        $('#<?php echo $liveViewStatusID; ?>').addClass('isOnline');
                    } else {
                        isOnlineLabel = false;
                        $('#<?php echo $liveViewStatusID; ?>').removeClass('isOnline');
                    }
                    playCorrectSource<?php echo $liveViewStatusID; ?>();
                    $('.liveViewCount').text(" " + response.nclients);
                    $('#<?php echo $liveViewStatusID; ?>').text(response.msg);
                    $('#onlineApplications').text(response.applications.lenght);
                    timeout = 15000;
                }
                setTimeout(function () {
                    getStats<?php echo $liveViewStatusID; ?>();
                }, timeout);
            }
        });
    }

    $(document).ready(function () {
        if (!avideoSocketIsActive()) {
            getStats<?php echo $liveViewStatusID; ?>();
        }
    });
</script>