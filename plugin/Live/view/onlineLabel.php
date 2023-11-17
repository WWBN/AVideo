<?php 
if (empty($streamName)) {
    $live = isLive();
    $streamName = $live['key'];
    $live_servers_id = $live['live_servers_id'];
} else {
    $live_servers_id = Live::getCurrentLiveServersId();
}
if(empty($streamName)){
    //return false;
}
$liveViewStatusID = str_replace('-', '_', "liveViewStatusID_{$streamName}_{$live_servers_id}");
$liveViewStatusClass = "liveViewStatusClass liveViewStatusClass_{$streamName} liveViewStatusClass_{$streamName}_{$live_servers_id}";

$liveObj = AVideoPlugin::getObjectData('Live');
if ($liveObj->doNotShowOnlineOfflineLabel) {
    $liveViewStatusClass .= ' hidden';
}
if (isLiveLink() || Live::isLiveAndIsReadyFromKey($streamName, $live_servers_id, @$live['live_index'])) {
    echo "<span class='label label-success liveOnlineLabel {$liveViewStatusClass}' id='{$liveViewStatusID}'>ONLINE</span>";
} else {
    echo "<span class='label label-danger liveOnlineLabel {$liveViewStatusClass}' id='{$liveViewStatusID}'>OFFLINE</span>";
}
?>
<script src="<?php echo getURL('plugin/Live/view/live.js');?>" type="text/javascript"></script>
<script>
    <?php
    include_once("{$global['systemRootPath']}plugin/Live/view/socket.js");
    ?>
        
        
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
                if (response.name == "<?php echo $streamName; ?>") {
                    if (response.msg === "ONLINE") {
                        isOnlineLabel = true;
                        $('#<?php echo $liveViewStatusID; ?>').addClass('isOnline');
                    } else {
                        isOnlineLabel = false;
                        $('#<?php echo $liveViewStatusID; ?>').removeClass('isOnline');
                    }
                    playerPlay(0);
                    $('.liveViewCount').text(" " + response.nclients);
                    $('#<?php echo $liveViewStatusID; ?>').text(response.msg);
                    $('.onlineApplications').text($('#availableLiveStream > div').length);
                    timeout = 15000;
                }
                
                if (!avideoSocketIsActive()) {
                    setTimeout(function () {
                        getStats<?php echo $liveViewStatusID; ?>();
                    }, timeout);
                }
            }
        });
    }

    $(document).ready(function () {
        if (!avideoSocketIsActive()) {
            getStats<?php echo $liveViewStatusID; ?>();
        }
    });
</script>