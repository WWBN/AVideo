
// Function to request notifications
function requestNotifications() {
    // Clear any previous timeout
    clearTimeout(liveStatusTimeout);

    // Set a timeout to mark as not live if no response is received
    liveStatusTimeout = setTimeout(() => {
        console.warn("No response received within 10 seconds. Marking as not live.");
        liveIndicator.style.display = 'none'; // Mark as not live
    }, 10000); // 10 seconds

    // Emit events to check RTMP status or other server-side data
    checkRTMPStatus();

    checkRemainingTime();

    checkConnections();
}

function checkConnections() {
    console.log("Requesting connection count...");
    socket.emit('check-connections');
}

// Check RTMP status on-demand
function checkRTMPStatus() {
    console.log("Requesting RTMP status...");
    socket.emit('check-rtmp-status', { rtmpURLEncrypted });
}

function checkRemainingTime() {
    console.log("Requesting remaining time...");
    socket.emit('check-live-time', { rtmpURLEncrypted });
}

function startWebcamLive(rtmpURLEncrypted) {
    // Notify server of new connection
    socket.emit('join', { rtmpURLEncrypted, id: socket.id });

    // Send stream to the server for RTMP forwarding
    sendStreamToServer(localStream);
}

// Stop the live stream
function stopWebcamLive(rtmpURLEncrypted) {
    socket.emit('stop-live', { rtmpURLEncrypted });
}

let mediaRecorder; // Declare a global variable to manage the MediaRecorder

function sendStreamToServer(stream) {
    try {
        if (!window.MediaRecorder) {
            console.error('MediaRecorder API is not supported on this device.');
            avideoToastError('MediaRecorder API is not supported on this device.');
            return;
        }
        
        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                socket.emit('video-chunk', { rtmpURLEncrypted, chunk: event.data });
            }
        };

        mediaRecorder.onerror = (event) => {
            console.error('MediaRecorder error:', event.error);
        };

        mediaRecorder.start(1000); // Record and send chunks every second
        console.log(`MediaRecorder started`);
    } catch (error) {
        console.error('Failed to initialize MediaRecorder:', error);
    }
}

// Function to stop the MediaRecorder
function stopStreamToServer() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop(); // Stop the MediaRecorder
        console.log('MediaRecorder stopped');
    } else {
        console.warn('MediaRecorder is not active or already stopped.');
    }
}


function setIsLive() {
    document.body.classList.remove('isNotLive');
    document.body.classList.add('isLive');
    isLive = true;
}

function setIsNotLive() {
    document.body.classList.remove('isLive');
    document.body.classList.add('isNotLive');
    isLive = false;
    stopStreamToServer()
}

async function getVideoSources() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoSources = devices.filter((device) => device.kind === 'videoinput');
        console.log('Available video sources:', videoSources);
        return videoSources;
    } catch (error) {
        console.error('Error fetching video sources:', error);
        return [];
    }
}

async function getAudioSources() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const audioSources = devices.filter((device) => device.kind === 'audioinput');
        console.log('Available audio sources:', audioSources);
        return audioSources;
    } catch (error) {
        console.error('Error fetching audio sources:', error);
        return [];
    }
}

async function startWebRTC({ videoDeviceId = null, audioDeviceId = null, useScreen = false } = {}) {
    try {
        let constraints;

        if (useScreen) {
            // Constraints for screen sharing
            constraints = {
                video: true,
                audio: false // Change to true if screen sharing audio is needed
            };
        } else {
            // Constraints for selected devices or default devices
            const isLandscape = window.screen.orientation.type.startsWith('landscape');
            
            const videoConstraints = {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                frameRate: { ideal: 30 },
                aspectRatio: isLandscape ? 16 / 9 : 9 / 16,
            };
            
            console.log('videoConstraints', isLandscape, videoConstraints);

            if (videoDeviceId) {
                videoConstraints.deviceId = { exact: videoDeviceId };
            }
            
            const audioConstraints = audioDeviceId ? { deviceId: { exact: audioDeviceId } } : true;
            
            constraints = {
                video: videoConstraints,
                audio: audioConstraints,
            };
            
        }

        //avideoToast(JSON.stringify(constraints));

        // Start or update the media stream
        const newStream = useScreen
            ? await navigator.mediaDevices.getDisplayMedia(constraints)
            : await navigator.mediaDevices.getUserMedia(constraints);

        // Stop existing tracks before replacing them
        if (localStream) {
            localStream.getTracks().forEach((track) => track.stop());
        }

        // Set the new stream
        localStream = newStream;
        localVideo.srcObject = newStream;

        // Optionally replace tracks in WebRTC PeerConnection if needed
        if (peers.localPeerConnection) {
            const senders = peers.localPeerConnection.getSenders();
            const videoTrack = newStream.getVideoTracks()[0];
            const audioTrack = newStream.getAudioTracks()[0];

            if (videoTrack) {
                const videoSender = senders.find((sender) => sender.track.kind === 'video');
                if (videoSender) videoSender.replaceTrack(videoTrack);
            }

            if (audioTrack) {
                const audioSender = senders.find((sender) => sender.track.kind === 'audio');
                if (audioSender) audioSender.replaceTrack(audioTrack);
            }
        }

        console.log('Stream started successfully:', newStream);
    } catch (error) {
        console.error('Error starting the stream:', error);
    }
}

window.addEventListener('orientationchange', () => {
    console.log('Orientation changed.');
    if (isLive) {
        console.log('Live stream is running. Orientation change will not restart the stream.');
        return; // Do not restart the stream if live is running
    }

    console.log('Restarting media stream due to orientation change...');
    startWebRTC(); // Restart stream with updated constraints
});


$(document).ready(function () {
    // Populate Video and Audio Sources
    function populateSources() {
        navigator.mediaDevices.enumerateDevices().then((devices) => {
            // Clear existing options
            $('#videoSource').empty().append('<option value="">Select Video Source</option>');
            $('#audioSource').empty().append('<option value="">Select Audio Source</option>');

            devices.forEach((device) => {
                if (device.kind === 'videoinput') {
                    $('#videoSource').append(
                        `<option value="${device.deviceId}">${device.label || `Camera ${$('#videoSource option').length}`}</option>`
                    );
                } else if (device.kind === 'audioinput') {
                    $('#audioSource').append(
                        `<option value="${device.deviceId}">${device.label || `Microphone ${$('#audioSource option').length}`}</option>`
                    );
                }
            });
        }).catch((error) => console.error('Error enumerating devices:', error));
    }

    // Initialize sources on load
    populateSources();

    // Start Screen Sharing
    $('#startScreenShare').click(async function () {
        startWebRTC({ useScreen: true });
    });

    // Apply Changes (Change Video and Audio Sources)
    $('#applyChanges').click(async function () {
        const videoDeviceId = $('#videoSource').val();
        const audioDeviceId = $('#audioSource').val();

        try {
            startWebRTC({ videoDeviceId: videoDeviceId, audioDeviceId: audioDeviceId });
            console.log('Media devices updated successfully.');
        } catch (error) {
            console.error('Error applying changes to media devices:', error);
        }
    });

    // Listen for the tab activation event
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Check if the 'Webcam' tab is activated
        if ($(e.target).attr('href') === '#tabWebcam') {
            startWebRTC(); // Call the startWebRTC function
        }
    });
    requestNotifications();
});
