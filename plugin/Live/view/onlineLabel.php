<?php
$live_servers_id = Live::getCurrentLiveServersId();
?>
<span class="label label-danger" id="liveViewStatus<?php echo $live_servers_id; ?>">OFFLINE</span>
<script>

    function isOfflineVideo() {
        if (player.readyState()) {
            var uri = player.tech_.hls.selectPlaylist().uri;
            if (uri.includes("loopBGHLS/res")) {
                return true;
            }
            if (player.tech_.hls.playlists.media_.segments[0].resolvedUri.includes(".ts?seq=")) {
                return true;
            }
            return false;
        }
        return true;
    }
    var playCorrectSource<?php echo $live_servers_id; ?>Timout;
    function playCorrectSource<?php echo $live_servers_id; ?>() {
        if($('#liveViewStatus<?php echo $live_servers_id; ?>').hasClass('isOnline') && !isOfflineVideo()){
            console.log("Change video to Online");
            $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-warning');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-danger');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').addClass('label-success');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').text("<?php echo __("ONLINE"); ?>");
            player.play();
        }else if ($('#liveViewStatus<?php echo $live_servers_id; ?>').hasClass('isOnline') && isOfflineVideo()) {
            console.log("Change video to please wait");
            $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-success');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-danger');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').addClass('label-warning');
            $('#liveViewStatus<?php echo $live_servers_id; ?>').text("<?php echo __("Please Wait ..."); ?>");
            reloadVideoJS();
            playerPlay(0);
            player.on('error', function(){
                console.log("PError 1 "+player.error());
            });
            player.tech_.hls.playlists.on('error', function(){
                console.log("PError 2 "+player.error());
                console.log("PError 2.1 "+this.error());
            });
            clearTimeout(playCorrectSource<?php echo $live_servers_id; ?>Timout);
            playCorrectSource<?php echo $live_servers_id; ?>Timout = setTimeout(function () {
                playCorrectSource<?php echo $live_servers_id; ?>();
            }, 5000);
        } else if (!$('#liveViewStatus<?php echo $live_servers_id; ?>').hasClass('isOnline') && !isOfflineVideo()) {
            if (player.readyState() <= 2) {
                console.log("Change video to offline");
                $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-warning');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-success');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').addClass('label-danger');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').text("<?php echo __("OFFLINE"); ?>");
                player.pause();
                //player.reset();
                $('#mainVideo.liveVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Offline.jpg)'});
                $('#mainVideo.liveVideo').find('.vjs-poster').fadeIn();
                player.trigger('loadstart');
                player.posterImage.show();
                player.bigPlayButton.show();
                player.currentTime(0);
                player.on('play', function(){
                    $('#mainVideo.liveVideo').find('.vjs-poster').fadeOut();
                });
                //reloadVideoJS();
                //playerPlay(0);
            } else {
                console.log("Change video to finishing");
                $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-warning');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('label-success');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').addClass('label-danger');
                $('#liveViewStatus<?php echo $live_servers_id; ?>').text("<?php echo __("Finishing Live..."); ?>");
                clearTimeout(playCorrectSource<?php echo $live_servers_id; ?>Timout);
                playCorrectSource<?php echo $live_servers_id; ?>Timout = setTimeout(function () {
                    playCorrectSource<?php echo $live_servers_id; ?>();
                }, 1000);
            }
        }
    }

    function getStats<?php echo $live_servers_id; ?>() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/stats.json.php?live_servers_id=<?php echo $live_servers_id; ?>&Label',
            data: {"name": "<?php echo $streamName; ?>"},
            type: 'post',
            success: function (response) {
                if (!response.error && response.msg === "ONLINE") {
                    $('#liveViewStatus<?php echo $live_servers_id; ?>').addClass('isOnline');
                } else {
                    $('#liveViewStatus<?php echo $live_servers_id; ?>').removeClass('isOnline');
                }
                playCorrectSource<?php echo $live_servers_id; ?>();
                $('.liveViewCount').text(" " + response.nclients);
                $('#liveViewStatus<?php echo $live_servers_id; ?>').text(response.msg);
                $('#onlineApplications').text(response.applications.lenght);
                setTimeout(function () {
                    getStats<?php echo $live_servers_id; ?>();
                }, 15000);
            }
        });
    }

    $(document).ready(function () {
        getStats<?php echo $live_servers_id; ?>();
    });
</script>