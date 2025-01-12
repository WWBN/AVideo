document.addEventListener('socketLiveONCallback', function(event) {
    let json = event.detail;
    if(json === null){
        console.error('socketLiveONCallback socket EventListener error', event);
        return false;
    }
    console.log('socketLiveONCallback socket EventListener', json);
    if (typeof json === 'string') {
        try {
            json = JSON.parse(json);
        } catch (error) {
            console.error("Invalid JSON string:", error);
            return; // Exit the listener if JSON parsing fails
        }
    }

    if(typeof json.key == 'undefined'){
        if(typeof json.json !== 'undefined' && typeof json.json.key !== 'undefined' ){
            json = json.json;
        }else{
            console.error("socketLiveONCallback Invalid JSON key not found:", json);
            return; // Exit the listener if JSON parsing fails
        }
    }

    console.log('socketLiveONCallback live plugin', json);

    if (typeof processLiveStats == 'function') {
        processLiveStats(json.stats);
    }

    let selector = '.live_' + json.live_servers_id + "_" + json.key;
    $(selector).slideDown();

    if (typeof onlineLabelOnline == 'function') {
        selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
        selector += ', .liveViewStatusClass_' + json.key;
        selector += ', .liveViewStatusClass_' + json.cleanKey;
        console.log('socketLiveONCallback ', selector);
        onlineLabelOnline(selector);
    }

    let IframeClass = ".yptchat2IframeClass_" + json.key + "_" + json.live_servers_id;
    if ($(IframeClass).length) {
        let src = $(IframeClass).attr('src');
        if (src) {
            avideoToast('Loading new chat');
            let newSRC = addGetParam(src, 'live_transmitions_history_id', json.live_transmitions_history_id);
            $(IframeClass).attr('src', newSRC);
        }
    }

    if (isInLive(json)) {
        playerPlay();
        showImage('prerollPoster', json.cleanKey);
    }
});

document.addEventListener('socketLiveOFFCallback', function(event) {
    let json = event.detail;
    if(json === null){
        console.error('socketLiveOFFCallback socket EventListener error', event);
        console.trace();
        return false;
    }
    console.log('socketLiveOFFCallback socket EventListener', json);

    if (typeof json === 'string') {
        try {
            json = JSON.parse(json);
        } catch (error) {
            console.error("Invalid JSON string:", error);
            return; // Exit the listener if JSON parsing fails
        }
    }

    if(typeof json.key == 'undefined'){
        if(typeof json.json !== 'undefined' && typeof json.json.key !== 'undefined' ){
            json = json.json;
        }else{
            console.error("socketLiveOFFCallback Invalid JSON key not found:", json);
            return; // Exit the listener if JSON parsing fails
        }
    }

    console.log('socketLiveOFFCallback live socket', json);

    let selector = '.live_' + json.live_servers_id + "_" + json.key;
    selector += ', .liveVideo_live_' + json.live_servers_id + "_" + json.key;
    selector += ', .live_' + json.key;
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
        if (typeof processLiveStats == 'function') {
            processLiveStats(json.stats);
        }
        setTimeout(function () {
            if (typeof hideExtraVideosIfEmpty == 'function') {
                hideExtraVideosIfEmpty();
            }
        }, 500);
    }, 500);

    if (isInLive(json)) {
        showImage('postrollPoster', json.cleanKey);
    }

    if (typeof updateUserNotificationCount == 'function') {
        updateUserNotificationCount();
    }
});


function redirectLive(json, countdown = 15) {
    if (typeof json === 'string') {
        try {
            json = JSON.parse(json);
        } catch (error) {
            console.error("Invalid JSON string:", error);
            return null;
        }
    }

    var viewerUrl = json.redirectLive && json.redirectLive.viewerUrl;
    var customMessage = json.redirectLive && json.redirectLive.customMessage;
    if (!viewerUrl) {
        console.error("Viewer URL not found.");
        return;
    }

    var countdownInterval;
    var initialCountdown = countdown;

    // Function to handle the redirection
    function redirectToUrl(url) {
        window.location.href = url;
    }

    // Function to update the progress bar and auto-redirect
    function startCountdown() {
        countdownInterval = setInterval(function () {
            countdown--;

            // Calculate the progress percentage
            var progressPercent = ((initialCountdown - countdown) / initialCountdown) * 100;

            // Update the progress bar in the avideoConfirm modal with smooth transition
            $('#countdownProgressBar').css({
                'width': progressPercent + '%',
                'transition': 'width 1s linear'
            });

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                redirectToUrl(viewerUrl);
            }
        }, 1000);
    }

    // Use avideoConfirm to ask for user confirmation and include a progress bar
    avideoConfirm(customMessage + '<hr>' + __("You will be redirected to the following URL:") + "<br><strong>" + viewerUrl + "</strong><br><div class='progress' style='height: 10px;'><div id='countdownProgressBar' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div></div>").then(function (confirmed) {
        if (confirmed) {
            clearInterval(countdownInterval); // Stop countdown if user confirms
            redirectToUrl(viewerUrl);
        } else {
            clearInterval(countdownInterval); // Stop countdown if user cancels
        }
    });

    // Start countdown
    startCountdown();
}

