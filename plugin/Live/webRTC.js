
window.addEventListener('message', event => {
    if (event.data.startLiveRestream) {
        startLiveRestream(event.data.m3u8);
    }
});

function startLiveRestream(m3u8) {
    //console.log('WebRTCLiveCam: startLive');
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + '/plugin/Live/webRTCToLive.json.php',
        method: 'POST',
        data: {
            'm3u8': m3u8,
            'live_servers_id': '<?php echo Live::getCurrentLiveServersId(); ?>'
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
    document.querySelector("iframe").contentWindow.postMessage({setLiveStart: 1}, "*");
}

function webRTCDisconnect() {
    document.querySelector("iframe").contentWindow.postMessage({setLiveStop: 1}, "*");
}