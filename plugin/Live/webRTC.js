
window.addEventListener('message', event => {
    if (event.data.startLiveRestream) {
        startLiveRestream(event.data.m3u8, forceIndex);
    }else if (event.data.onStreamReady) {
        onStreamReady();
    }else if (event.data.webRTCPleaseWaitHide) {
        webRTCPleaseWaitHide();
    }else if (event.data.showPleaseWait) {
        modal.showPleaseWait();
    }else if (event.data.hidePleaseWait) {
        modal.hidePleaseWait();
    }else
    if (event.data.webRTCModalConfig) {
        console.log('event.data.webRTCModalConfig', event.data.webRTCModalConfig, typeof webRTCModalConfigShow);
        if(event.data.webRTCModalConfig==1){
            if(typeof webRTCModalConfigShow =='function'){
                webRTCModalConfigShow();
            }
        }else{
            if(typeof webRTCModalConfigHide =='function'){
                webRTCModalConfigHide();
            }
        }
    }
});

function onStreamReady(){
    $('#webRTCConnect').prop('disabled', false);
}

function startLiveRestream(m3u8, forceIndex) {
    console.log('WebRTCLiveCam: startLiveRestream', m3u8, forceIndex);
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/Live/webRTCToLive.json.php',
        method: 'POST',
        data: {
            'm3u8': m3u8,
            'live_servers_id': live_servers_id,
            'forceIndex': forceIndex,
            'user': webrtcUser,
            'pass': webrtcPass
        },
        success: function (response) {
            if (response.error) {
                webRTCDisconnect();
                avideoAlertError(response.msg);
            } else {
                avideoToastSuccess(response.msg);
                //document.querySelector("iframe").contentWindow.postMessage({setLiveStart: 1}, "*");
            }
            modal.hidePleaseWait();
        }
    });
}

function webRTCConnect() {
    modal.showPleaseWait();
    document.querySelector("iframe").contentWindow.postMessage({setLiveStart: 1}, "*");
    webRTCPleaseWaitShow();
}

function webRTCDisconnect() {
    document.querySelector("iframe").contentWindow.postMessage({setLiveStop: 1}, "*");
    webRTCPleaseWaitHide();
}

function webRTCConfiguration() {
    document.querySelector("iframe").contentWindow.postMessage({setConfiguration: 1}, "*");
}

var _webRTCPleaseWaitShowTimeout;
function webRTCPleaseWaitShow(){
    $('body').addClass('webRTCPleaseWait');
    clearTimeout(_webRTCPleaseWaitShowTimeout);
    _webRTCPleaseWaitShowTimeout = setTimeout(function(){
        avideoToastError('Live error')
        webRTCPleaseWaitHide();
    },120000);
}

function webRTCPleaseWaitHide(){
    clearTimeout(_webRTCPleaseWaitShowTimeout);
    $('body').removeClass('webRTCPleaseWait');
}

function webRTCisLive(){
    $('body').addClass('webRTCisLive');
    webRTCPleaseWaitHide();
}

function webRTCisOffline(){
    $('body').removeClass('webRTCisLive');
    webRTCPleaseWaitHide();
}