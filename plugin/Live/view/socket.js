function socketLiveONCallback(json) {
    console.log('socketLiveONCallback', json);
    if(typeof processLiveStats == 'undefined'){
        processLiveStats(json.stats);
    }
    var selector = '.live_' + json.live_servers_id + "_" + json.key;
    $(selector).slideDown();

    if (typeof onlineLabelOnline == 'function') {
        selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key;
        selector += ', .liveViewStatusClass_' + json.cleanKey;
        console.log('socketLiveONCallback ', selector);
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
    console.log('socketLiveOFFCallback', json);
    var selector = '.live_' + json.live_servers_id + "_" + json.key;
    selector += ', .liveVideo_live_' + json.live_servers_id + "_" + json.key;
    selector += ', .live_' + json.key;
    ////console.log('socketLiveOFFCallback 1', selector);
    $(selector).slideUp("fast", function () {
        $(this).remove();
    });
    if (typeof onlineLabelOffline == 'function') {
        selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key;
        selector += ', .liveViewStatusClass_' + json.cleanKey;
        console.log('socketLiveOFFCallback', selector);
        onlineLabelOffline(selector);
    }
    setTimeout(function () {
        //console.log('socketLiveOFFCallback processLiveStats');
        if(typeof processLiveStats == 'undefined'){
            processLiveStats(json.stats);
        }
        setTimeout(function () {
            hideExtraVideosIfEmpty();
        }, 500);
    }, 500);

    if (isInLive(json)) {
        showImage('postrollPoster', json.cleanKey);
    }
    if (typeof updateUserNotificationCount == 'function') {
        updateUserNotificationCount();
    }
}