let allDevices = null;

let input = null;

const userResolutions = {
    vga: {
        width: {exact: 640}, height: {exact: 480}
        // width: { min: 0, ideal: 640}, height: { min: 0, ideal: 480 }
        //width: {ideal: 640}, height: {ideal: 480}
    },
    hd: {
        width: {exact: 1280}, height: {exact: 720}
        // width: { min: 640, ideal: 1280}, height: { min: 480, ideal: 720 }
        //width: {ideal: 1280}, height: {ideal: 720}
    },
    fhd: {
        width: {exact: 1920}, height: {exact: 1080}
        // width: { min: 1280, ideal: 1920}, height: { min: 720, ideal: 1080 }
        //width: {ideal: 1920}, height: {ideal: 1080}
    }
};

const displayResolutions = {
    vga: {
        width: 640, height: 480
    },
    hd: {
        width: 1280, height: 720
    },
    fhd: {
        width: 1920, height: 1080
    }
};

let streamingStarted = false;

let videoElement = document.getElementById('mainVideo');

let streamingButton = $('#streamingButton');

let errorTextSpan = $('#errorText');

let webRtcUrlInput = $('#webRtcUrlInput');
let videoSourceSelect = $('#videoSourceSelect');
let videoResolutionSelect = $('#videoResolutionSelect');
let videoBitrateInput = $('#videoBitrateInput');
let videoFrameInput = $('#videoFrameInput');
let audioSourceSelect = $('#audioSourceSelect');
let audioSourceSelectArea = $('#audioSourceSelectArea');

let videoResolutionSpan = $('#videoResolutionSpan');
let videoFrameRateSpan = $('#videoFrameRateSpan');
let iceStateSpan = $('#iceStateSpan');
let bitrateSpan = $('#bitrateSpan');

//let savedWebRtcUrl = localStorage.getItem('savedWebRtcUrl');
let savedVideoSource = localStorage.getItem('savedVideoSource');
//let savedVideoResolution = localStorage.getItem('savedVideoResolution');
let savedVideoBitrate = localStorage.getItem('savedVideoBitrate');
let savedVideoFrame = localStorage.getItem('savedVideoFrame');
let savedAudioSource = localStorage.getItem('savedAudioSource');
/*
 if (savedWebRtcUrl) {
 webRtcUrlInput.val(savedWebRtcUrl);
 }
 
 webRtcUrlInput.on('change', function () {
 localStorage.setItem('savedWebRtcUrl', $(this).val());
 });
 */

videoSourceSelect.on('change', function () {
    localStorage.setItem('savedVideoSource', $(this).val());

    if ($(this).val() === 'displayCapture') {
        audioSourceSelectArea.hide();
    } else {
        audioSourceSelectArea.show();
    }
});
/*
 videoResolutionSelect.on('change', function () {
 localStorage.setItem('savedVideoResolution', $(this).val());
 });
 */

videoBitrateInput.on('change', function () {
    localStorage.setItem('savedVideoBitrate', $(this).val());
});

videoFrameInput.on('change', function () {
    localStorage.setItem('savedVideoFrame', $(this).val());
});

audioSourceSelect.on('change', function () {
    localStorage.setItem('savedAudioSource', $(this).val());
});

$('.constraintSelect').on('change', function () {
    stopStreaming();
});


let frameCalculatorTimer = null;
let totalVideoFrames = 0;

function getResolutionAndCalculateFrame(videoElement) {

    if (frameCalculatorTimer) {

        clearInterval(frameCalculatorTimer);
        videoResolutionSpan.text('-');
        videoFrameRateSpan.empty('-');
        frameCalculatorTimer = null;
        totalVideoFrames = 0;
    }

    frameCalculatorTimer = setInterval(function () {

        videoResolutionSpan.text(videoElement.videoWidth + 'x' + videoElement.videoHeight);

        if (totalVideoFrames === 0) {

            totalVideoFrames = videoElement.getVideoPlaybackQuality().totalVideoFrames;
        } else {

            let currentTotalFrame = videoElement.getVideoPlaybackQuality().totalVideoFrames;
            let frameRate = currentTotalFrame - totalVideoFrames;

            videoFrameRateSpan.text(frameRate + 'fps');

            totalVideoFrames = currentTotalFrame;
        }

    }, 1000);
}

function getUserConstraints() {

    let videoDeviceId = videoSourceSelect.val();
    let videoResolution = videoResolutionSelect.val();
    let videoFrame = videoFrameInput.val();
    let audioDeviceId = audioSourceSelect.val();

    let newConstraint = {};

    if (videoDeviceId) {
        newConstraint.video = {
            deviceId: {
                exact: videoDeviceId
            }
        };
    }

    if (audioDeviceId) {
        newConstraint.audio = {
            deviceId: {
                exact: audioDeviceId
            }
        };
    }

    if (videoResolution) {

        const resolution = userResolutions[videoResolution];
        /*
         if (screen.availHeight > screen.availWidth) {
         newConstraint.video.width = resolution.height;
         newConstraint.video.height = resolution.width;
         console.log('Portrait detected');
         } else {
         newConstraint.video.width = resolution.width;
         newConstraint.video.height = resolution.height;
         console.log('Landscape detected');
         }
         */

        newConstraint.video.width = resolution.width;
        newConstraint.video.height = resolution.height;

    }

    if (videoFrame) {
        newConstraint.video.frameRate = {exact: parseInt(videoFrame)};
    }

    return newConstraint;
}

function getDisplayConstraints() {

    let videoResolution = videoResolutionSelect.val();
    let videoFrame = videoFrameInput.val();

    let newConstraint = {};

    newConstraint.video = {};

    if (videoResolution) {

        const resolution = displayResolutions[videoResolution];
        /*
         if (screen.availHeight > screen.availWidth) {
         newConstraint.video.width = resolution.height;
         newConstraint.video.height = resolution.width;
         console.log('Portrait detected');
         } else {
         newConstraint.video.width = resolution.width;
         newConstraint.video.height = resolution.height;
         console.log('Landscape detected');
         }
         */
        newConstraint.video.width = resolution.width;
        newConstraint.video.height = resolution.height;
    } else {

        newConstraint.video = true;
    }

    if (videoFrame) {

        if (!newConstraint.video) {

            newConstraint.video = {};
        }

        newConstraint.video.frameRate = parseInt(videoFrame);
    }

    newConstraint.audio = true;

    return newConstraint;
}

function setDevice(type, select, devices) {

    select.empty();

    if (type === 'audio' && devices.length === 0) {

        select.append('<option value="">No Source Available</option>')
    } else {

        _.each(devices, function (device) {

            let option = $('<option></option>');

            option.text(device.label);
            option.val(device.deviceId);

            select.append(option);
        });

        if (type === 'video') {

            let option = $('<option></option>');

            option.text('Display capture');
            option.val('displayCapture');

            if (!navigator.mediaDevices.getDisplayMedia) {

                option.text('Display capture (Not supported)');
                option.attr('disabled', 'disabled');
            }


            select.append(option);
        }
    }

    select.find('option').eq(0).prop('selected', true);
}

function checkDevice(devices, deviceId) {

    if (deviceId === 'displayCapture') {
        return true;
    }

    let filtered = _.filter(devices, function (device) {

        return device.deviceId === deviceId;
    });

    return filtered.length > 0;
}

function resetMessages() {

    errorTextSpan.empty();

    clearInterval(frameCalculatorTimer);
    frameCalculatorTimer = null;


    videoResolutionSpan.text('-');
    videoFrameRateSpan.text('-');
    bitrateSpan.text('-');
    iceStateSpan.text('-');

}


function createInput() {

    streamingButton.prop('disabled', true);

    if (input) {
        input.remove();
        input = null;
    }

    resetMessages();

    input = OvenWebRTCInput.create({
        callbacks: {
            error: function (error) {
                console.error('WebRTC player error:', error);
                let errorMessage = '';

                if (error.message) {

                    errorMessage = error.message;
                } else if (error.name) {

                    errorMessage = error.name;
                } else {

                    errorMessage = error.toString();
                }

                if (errorMessage === 'OverconstrainedError') {

                    errorMessage = 'The input device does not support the specified resolution or frame rate.';
                }

                resetMessages();

                errorTextSpan.text(errorMessage);
                modal.hidePleaseWait();
                window.parent.postMessage({hidePleaseWait: 1}, '*');
                window.parent.postMessage({webRTCPleaseWaitHide: 1}, '*');
                avideoToastSuccess(errorMessage);
            },
            connectionClosed: function (type, event) {
                console.error('WebRTC player connectionClosed:', type, event);
                if (type === 'websocket') {
                    let reason;
                    // See http://tools.ietf.org/html/rfc6455#section-7.4.1
                    if (event.code === 1000)
                        reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
                    else if (event.code === 1001)
                        reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
                    else if (event.code === 1002)
                        reason = "An endpoint is terminating the connection due to a protocol error";
                    else if (event.code === 1003)
                        reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
                    else if (event.code === 1004)
                        reason = "Reserved. The specific meaning might be defined in the future.";
                    else if (event.code === 1005)
                        reason = "No status code was actually present.";
                    else if (event.code === 1006)
                        reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
                    else if (event.code === 1007)
                        reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
                    else if (event.code === 1008)
                        reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
                    else if (event.code === 1009)
                        reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
                    else if (event.code === 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                        reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
                    else if (event.code === 1011)
                        reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
                    else if (event.code === 1015)
                        reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
                    else
                        reason = "Unknown reason";
                    $('#errorText').html('Web Socket is closed. ' + reason);
                }
                if (type === 'ice') {
                    $('#errorText').html('Peer Connection is closed. State: ' + input.peerConnection.iceConnectionState);
                }
            },
            connected: function (state) {
                console.log('WebRTC player connected:', state);
                onStreamConnected();
            },
            iceStateChange: function (state) {
                console.log('WebRTC player iceStateChange:', state);
                iceStateSpan.text(state);
            },
            connectionClose: function (type, e) {
                console.error('WebRTC player connectionClose:', type, e);
                avideoAlertError('Iceconnection Closed');
                modal.hidePleaseWait();
                window.parent.postMessage({hidePleaseWait: 1}, '*');
            }
        }
    });

    input.attachMedia(videoElement);

    if (videoSourceSelect.val()) {

        if (videoSourceSelect.val() === 'displayCapture') {
            input.getDisplayMedia(getDisplayConstraints()).then(function (stream) {
                console.log('input.getDisplayMedia(getDisplayConstraints())', stream, videoElement);
                onStreamReady();
                streamingButton.prop('disabled', false);
                getResolutionAndCalculateFrame(videoElement);
            });
        } else {
            input.getUserMedia(getUserConstraints()).then(function (stream) {
                console.log('input.getUserMedia(getUserConstraints())', stream, videoElement);
                onStreamReady();
                streamingButton.prop('disabled', false);
                getResolutionAndCalculateFrame(videoElement);
            });
        }
    }
}

function onStreamReady(){
    window.parent.postMessage({onStreamReady: 1}, '*');
}

function startStreaming() {

    streamingStarted = true;
    streamingButton.removeClass('btn-primary').addClass('btn-danger');
    streamingButton.text('Stop Streaming');

    if (input) {

        let connectionConfig = {};

        if (videoBitrateInput.val()) {
            connectionConfig.maxVideoBitrate = parseInt(videoBitrateInput.val());
        }

        input.startStreaming(webRtcUrlInput.val(), connectionConfig);
    }
}

function stopStreaming() {
    streamingStarted = false;
    streamingButton.removeClass('btn-danger').addClass('btn-primary');
    streamingButton.text('Start Streaming');

    if (input) {
        createInput();
    }

}

streamingButton.on('click', function () {

    if (!streamingStarted) {
        startStreaming();
    } else {
        stopStreaming();
    }
});

// videoBitrateInput.on('change', function () {
//
//     if (!input || !input.peerConnection) {
//         return;
//     }
//
//     const bandwidth = videoBitrateInput.val();
//
//     const senders = input.peerConnection.getSenders();
//
//     _.each(senders, function (sender) {
//
//         if (sender.track.kind === 'video') {
//
//             const parameters = sender.getParameters();
//             if (!parameters.encodings) {
//                 parameters.encodings = [{}];
//             }
//             if (bandwidth === '') {
//                 delete parameters.encodings[0].maxBitrate;
//             } else {
//                 parameters.encodings[0].maxBitrate = bandwidth * 1000;
//             }
//             sender.setParameters(parameters)
//                 .then(() => {
//
//                 })
//                 .catch(e => console.error(e));
//         }
//     });
// });

let lastResult;

setInterval(function () {

    if (!input || !input.peerConnection) {
        bitrateSpan.text('-');
        return;
    }

    let sender = null;

    input.peerConnection.getSenders().forEach(function (s) {

        if (s.track && s.track.kind === 'video') {
            sender = s;
        }
    });

    if (!sender) {
        bitrateSpan.text('-');
        return;
    }

    sender.getStats().then(res => {
        res.forEach(report => {
            let bytes;
            let headerBytes;
            let packets;

            if (report.type === 'outbound-rtp') {
                if (report.isRemote) {
                    return;
                }

                const now = report.timestamp;
                bytes = report.bytesSent;
                headerBytes = report.headerBytesSent;

                packets = report.packetsSent;
                if (lastResult && lastResult.has(report.id)) {
                    // calculate bitrate
                    const bitrate = 8 * (bytes - lastResult.get(report.id).bytesSent) /
                            (now - lastResult.get(report.id).timestamp);
                    const headerrate = 8 * (headerBytes - lastResult.get(report.id).headerBytesSent) /
                            (now - lastResult.get(report.id).timestamp);

                    const packetsSent = packets - lastResult.get(report.id).packetsSent;

                    bitrateSpan.text(bitrate.toFixed(2) + 'kbps');
                }

                // console.log('framesEncoded', report.framesEncoded, 'keyFramesEncoded', report.keyFramesEncoded, report.framesEncoded / report.keyFramesEncoded + '(' + report.framesPerSecond + 'fps)')
            }

            if (report.type === 'track') {
                // console.log(report)
            }

        });
        lastResult = res;
    });
}, 2000);


// get all devices at the first time
OvenWebRTCInput.getDevices().then(function (devices) {

    allDevices = devices;
    initDemo();
}).catch(function (error) {

    let errorMessage = '';

    if (error.message) {

        errorMessage = error.message;
    } else if (error.name) {

        errorMessage = error.name;
    } else {

        errorMessage = error.toString();
    }

    $('#errorText').text(errorMessage);
});

function initDemo() {

    if (allDevices) {

        setDevice('video', videoSourceSelect, allDevices.videoinput, );
        setDevice('audio', audioSourceSelect, allDevices.audioinput);

        if (savedVideoSource && checkDevice(allDevices.videoinput, savedVideoSource)) {
            videoSourceSelect.val(savedVideoSource);
        }

        if (savedVideoSource === 'displayCapture') {
            audioSourceSelectArea.hide();
        }
        /*
         if (savedVideoResolution) {
         videoResolutionSelect.val(savedVideoResolution);
         }
         */

        if (savedVideoBitrate) {
            videoBitrateInput.val(savedVideoBitrate);
        }

        if (savedVideoFrame) {
            videoFrameInput.val(savedVideoFrame);
        }

        if (savedAudioSource && checkDevice(allDevices.audioinput, savedAudioSource)) {
            audioSourceSelect.val(savedAudioSource);
        }
    }

    createInput();
}
$(document).ready(function () {
    $('#webRTCModalConfig').modal({
        show: false
    });
    $('#webRTCModalConfig').on('hidden.bs.modal', function () {
        console.log('webRTCModalConfig hidden.bs.modal');
        window.parent.postMessage({webRTCModalConfig: -1}, '*');
    });
    $('#webRTCModalConfig').on('show.bs.modal', function () {
        console.log('webRTCModalConfig show.bs.modal');
        window.parent.postMessage({webRTCModalConfig: 1}, '*');
    });
});