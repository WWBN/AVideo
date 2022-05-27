<?php
if (empty($streamName)) {
    $live = isLive();
    $streamName = $live['key'];
    $live_servers_id = $live['live_servers_id'];
} else {
    $live_servers_id = Live::getCurrentLiveServersId();
}
if (empty($streamName)) {
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

<script>
    function isInLive(json) {
        selector1 = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
        selector2 = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
        selector3 = '#liveViewStatusID_' + json.cleanKey + '_' + json.live_servers_id;
        selector4 = '.liveViewStatusClass_' + json.cleanKey + '_' + json.live_servers_id;
        //console.log('isInLive 1', json);
        //console.log('isInLive 2', selector1, selector2, selector3, selector4);
        var _isInLive = $(selector1).length || $(selector2).length || $(selector3).length || $(selector4).length;
        //console.log('isInLive 3', $(selector1).length, $(selector2).length, $(selector3).length, $(selector4).length, _isInLive);
        return _isInLive;
    }

    function socketLiveONCallback(json) {
        //console.log('socketLiveONCallback processLiveStats', json);
        if(typeof processLiveStats == 'function'){
            processLiveStats(json.stats);
        }
        var selector = '.live_' + json.live_servers_id + "_" + json.key;
        $(selector).slideDown();

        if (typeof onlineLabelOnline == 'function') {
            selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
            onlineLabelOnline(selector);
            selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
            onlineLabelOnline(selector);
            selector = '.liveViewStatusClass_' + json.cleanKey;
            ////console.log('socketLiveOFFCallback 3', selector);
            onlineLabelOnline(selector);
        }

        // update the chat if the history changes
        var IframeClass = ".yptchat2IframeClass_" + json.key + "_" + json.live_servers_id;
        if ($(IframeClass).length) {
            var src = $(IframeClass).attr('src');
            if (src) {
                avideoToast('Loading new chat');
                var newSRC = addGetParam(src, 'live_transmitions_history_id', json.live_transmitions_history_id);
                $(IframeClass).attr('src', newSRC);
            }
        }
        if (isInLive(json)) {
            playerPlay();
            showImage('prerollPoster', json.cleanKey);
        }
    }
    function socketLiveOFFCallback(json) {
        //console.log('socketLiveOFFCallback', json);
        var selector = '.live_' + json.live_servers_id + "_" + json.key;
        selector += ', .liveVideo_live_' + json.live_servers_id + "_" + json.key;
        selector += ', .live_' + json.key;
        ////console.log('socketLiveOFFCallback 1', selector);
        $(selector).slideUp("fast", function () {
            $(this).remove();
        });
        if (typeof onlineLabelOffline == 'function') {
            selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
            ////console.log('socketLiveOFFCallback 2', selector);
            onlineLabelOffline(selector);
            selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
            ////console.log('socketLiveOFFCallback 3', selector);
            onlineLabelOffline(selector);
            selector = '.liveViewStatusClass_' + json.cleanKey;
            ////console.log('socketLiveOFFCallback 3', selector);
            onlineLabelOffline(selector);
        }
        if(typeof processLiveStats == 'function'){
            setTimeout(function () {
                //console.log('socketLiveOFFCallback processLiveStats');
                processLiveStats(json.stats);
                setTimeout(function () {
                    hideExtraVideosIfEmpty();
                }, 500);
            }, 500);
        }
        if (isInLive(json)) {
            showImage('postrollPoster', json.cleanKey);
        }
    }
    var prerollPosterAlreadyPlayed = false;
    function showImage(type, key) {
        if (typeof closeLiveImageRoll == 'function') {
            closeLiveImageRoll();
        }
        $('.' + type).remove();
        var img = false;
        console.log('showImage', type, key, player.paused());
        eval('prerollPoster = prerollPoster_' + key);
        eval('postrollPoster = postrollPoster_' + key);
        eval('liveImgCloseTimeInSecondsPreroll = liveImgCloseTimeInSecondsPreroll_' + key);
        eval('liveImgTimeInSecondsPreroll = liveImgTimeInSecondsPreroll_' + key);
        eval('liveImgCloseTimeInSecondsPostroll = liveImgCloseTimeInSecondsPostroll_' + key);
        eval('liveImgTimeInSecondsPostroll = liveImgTimeInSecondsPostroll_' + key);
        var liveImgTimeInSeconds = 30;
        var liveImgCloseTimeInSeconds = 30;
        if (type == 'prerollPoster' && prerollPoster) {
            if (prerollPosterAlreadyPlayed) {
                console.log('showImage prerollPosterAlreadyPlayed');
                return false;
            }
            prerollPosterAlreadyPlayed = true;
            if (player.paused()) {
                setTimeout(function () {
                    showImage(type, key);
                }, 1000);
                return false;
            }
            liveImgTimeInSeconds = liveImgTimeInSecondsPreroll;
            liveImgCloseTimeInSeconds = liveImgCloseTimeInSecondsPreroll;
            img = prerollPoster;
        } else if (type == 'postrollPoster' && postrollPoster) {
            liveImgTimeInSeconds = liveImgTimeInSecondsPostroll;
            liveImgCloseTimeInSeconds = liveImgCloseTimeInSecondsPostroll;
            img = postrollPoster;
        }
        console.log('showImage Poster', type, img, key);
        if (img) {

            var _liveImageBGTemplate = liveImageBGTemplate.replace('{liveImgCloseTimeInSeconds}', liveImgCloseTimeInSeconds);
            var _liveImageBGTemplate = _liveImageBGTemplate.replace('{liveImgTimeInSeconds}', liveImgTimeInSeconds);
            var _liveImageBGTemplate = _liveImageBGTemplate.replace('{src}', img);
            _liveImageBGTemplate = _liveImageBGTemplate.replace(/\{class\}/g, type);

            $(_liveImageBGTemplate).appendTo("#mainVideo");
        }

        //console.log('prerollPoster', prerollPoster);
        //console.log('postrollPoster', postrollPoster);
        //console.log('liveImgTimeInSeconds', liveImgTimeInSeconds);
        //console.log('liveImgCloseTimeInSeconds', liveImgCloseTimeInSeconds);
    }

    function hideExtraVideosIfEmpty() {
        $('#liveScheduleVideos .extraVideos').each(function (index, currentElement) {
            var somethingIsVisible = false;
            $(this).children('div').each(function (index2, currentElement2) {
                if ($(this).is(":visible")) {
                    somethingIsVisible = true;
                    return false;
                }
            });
            if (!somethingIsVisible) {
                $('#liveScheduleVideos').slideUp();
            }
        });
        $('#liveVideos .extraVideos').each(function (index, currentElement) {
            var somethingIsVisible = false;
            $(this).children('div').each(function (index2, currentElement2) {
                if ($(this).is(":visible")) {
                    somethingIsVisible = true;
                    return false;
                }
            });
            if (!somethingIsVisible) {
                $('#liveVideos').slideUp();
            }
        });
    }
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
                if (!isWebRTC()) {
                    player.currentTime(0);
                }
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
                    $('.onlineApplications').text($('#availableLiveStream > div').length);
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