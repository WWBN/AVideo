
window.addEventListener('message', event => {
    if (event.data.startLiveRestream) {
        startLiveRestream(event.data.m3u8, forceIndex);
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

function startLiveRestream(m3u8, forceIndex) {
    console.log('WebRTCLiveCam: startLiveRestream', m3u8);
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + '/plugin/Live/webRTCToLive.json.php',
        method: 'POST',
        data: {
            'm3u8': m3u8,
            'live_servers_id': live_servers_id,
            'forceIndex': forceIndex
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
}

function webRTCDisconnect() {
    document.querySelector("iframe").contentWindow.postMessage({setLiveStop: 1}, "*");
}

function webRTCConfiguration() {
    document.querySelector("iframe").contentWindow.postMessage({setConfiguration: 1}, "*");
}