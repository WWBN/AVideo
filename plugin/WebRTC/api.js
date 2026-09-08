
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
    socketWebRTC.emit('check-connections');
}

// Check RTMP status on-demand
function checkRTMPStatus() {
    console.log("Requesting RTMP status...");
    socketWebRTC.emit('check-rtmp-status', { rtmpURLEncrypted });
}

function checkRemainingTime() {
    console.log("Requesting remaining time...");
    socketWebRTC.emit('check-live-time', { rtmpURLEncrypted });
}

const WEBRTC_DEVICE_STORAGE_KEY = 'webrtcSelectedDevices';
let isPublishing = false; // Guards against duplicate Start clicks while a publish attempt is in flight
let publishWatchdog;
let webrtcSourcesReadyPromise = null;

// Maps getUserMedia/getDisplayMedia errors to a clear, user-visible message
function handleMediaError(error) {
    const name = error && error.name ? error.name : '';
    let message;

    switch (name) {
        case 'NotAllowedError':
        case 'PermissionDeniedError':
            message = __('Camera or microphone access was denied. Please allow access in your browser settings and try again.');
            break;
        case 'NotFoundError':
        case 'DevicesNotFoundError':
            message = __('No camera or microphone was found. Please connect a device and try again.');
            break;
        case 'NotReadableError':
        case 'TrackStartError':
            message = __('Your camera or microphone is already in use by another application.');
            break;
        case 'OverconstrainedError':
        case 'ConstraintNotSatisfiedError':
            message = __('The selected camera or microphone is not available. Please choose another device.');
            break;
        case 'SecurityError':
            message = __('Camera and microphone access requires a secure (HTTPS) connection.');
            break;
        case 'AbortError':
            message = __('The camera or microphone request was interrupted. Please try again.');
            break;
        default:
            message = __('Unable to access the camera or microphone. Please check your devices and try again.');
    }

    avideoToastError(message);
}

function getSavedWebRTCDevices() {
    try {
        const raw = localStorage.getItem(WEBRTC_DEVICE_STORAGE_KEY);
        if (!raw) return null;
        const saved = JSON.parse(raw);
        if (!saved || (!saved.videoDeviceId && !saved.audioDeviceId)) return null;
        return saved;
    } catch (error) {
        console.warn('Unable to read saved WebRTC device preferences:', error);
        return null;
    }
}

function saveWebRTCDevices(videoDeviceId, audioDeviceId) {
    try {
        localStorage.setItem(WEBRTC_DEVICE_STORAGE_KEY, JSON.stringify({
            videoDeviceId: videoDeviceId || '',
            audioDeviceId: audioDeviceId || ''
        }));
    } catch (error) {
        console.warn('Unable to save WebRTC device preferences:', error);
    }
}

function clearSavedWebRTCDevices() {
    try {
        localStorage.removeItem(WEBRTC_DEVICE_STORAGE_KEY);
    } catch (error) {
        console.warn('Unable to clear saved WebRTC device preferences:', error);
    }
}

// Marks the Quick Go Live device setup as confirmed, revealing the Start button (see style.css)
function markWebrtcSetupConfirmed() {
    document.body.classList.add('webrtcSetupConfirmed');
}

function startWebcamLive(rtmpURLEncrypted) {
    if (isLive || isPublishing) {
        console.warn('startWebcamLive ignored: a publish attempt is already in progress or already live.');
        return;
    }

    if (!localStream) {
        avideoToastError(__('No camera or microphone preview is available. Please check your devices and try again.'));
        return;
    }

    isPublishing = true;
    $('#startLive').prop('disabled', true);

    // Notify server of new connection
    socketWebRTC.emit('join', { rtmpURLEncrypted, id: socketWebRTC.id });

    // Send stream to the server for RTMP forwarding
    sendStreamToServer(localStream);

    // Safety net in case the server never confirms the publish (network/publication failure)
    clearTimeout(publishWatchdog);
    publishWatchdog = setTimeout(() => {
        if (isPublishing && !isLive) {
            console.warn('Publish attempt timed out waiting for server confirmation.');
            avideoToastError(__('We could not confirm the live stream started. Please try again.'));
            isPublishing = false;
            $('#startLive').prop('disabled', false);
            stopStreamToServer();
        }
    }, 15000);
}

// Stop the live stream
function stopWebcamLive(rtmpURLEncrypted) {
    socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
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
                //console.log(`video-chunk`);
                socketWebRTC.emit('video-chunk', { rtmpURLEncrypted, chunk: event.data });
            }
        };

        mediaRecorder.onerror = (event) => {
            console.error('MediaRecorder error:', event.error);
        };

        const chunkSize = isIPhone() ? 250 : 1000; // 250ms for iPhone, 1000ms for others
        mediaRecorder.start(chunkSize); // Record and send chunks every second
        console.log(`MediaRecorder started`);
    } catch (error) {
        console.error('Failed to initialize MediaRecorder:', error);
    }
}

// Detect if the device is an iPhone
function isIPhone() {
    return /iPhone|iPad|iPod/i.test(navigator.userAgent);
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

function isWebcamServerConnected() {
    return $('body').hasClass('WebcamServerConnected');
}

function setIsWebcamServerConnected() {
    console.log('Connection success');
    // Custom logic to handle connection failure
    $('body').removeClass('WebcamServerNotConnected').addClass('WebcamServerConnected');
}

function setIsWebcamServerNotConnected() {
    console.log('Connection error');
    $('body').removeClass('WebcamServerConnected').addClass('WebcamServerNotConnected');
    setIsNotLive();
}

function setIsLive() {
    document.body.classList.remove('isNotLive');
    document.body.classList.add('isLive');
    isLive = true;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    $('#startLive').prop('disabled', false);
    lockScreenOrientation(); // Lock screen orientation
}

function setIsNotLive() {
    document.body.classList.remove('isLive');
    document.body.classList.add('isNotLive');
    isLive = false;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    $('#startLive').prop('disabled', false);
    stopStreamToServer()
    unlockScreenOrientation(); // Unlock screen orientation
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
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100
                }
            };
        } else {
            // Constraints for selected devices or default devices
            const isLandscape = window.screen.orientation.type.startsWith('landscape');

            const videoConstraints = buildVideoConstraints(videoDeviceId);
            videoConstraints.aspectRatio = isLandscape ? 16 / 9 : 9 / 16;

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
        $('body').addClass('webCamIsOn');
        console.log('Stream started successfully:', newStream);
        return true;
    } catch (error) {
        console.error('Error starting the stream:', error);
        handleMediaError(error);
        return false;
    }
}

function stopWebRTC() {
    if (localStream) {
        // Stop all tracks of the stream
        localStream.getTracks().forEach((track) => track.stop());

        // Optionally clear the video element's stream
        localVideo.srcObject = null;
        localStream = null;
        $('body').removeClass('webCamIsOn');
        console.log('Camera and microphone stopped.');
    } else {
        console.log('No active stream to stop.');
    }
}


function toggleMediaSelector() {
    if (!$('#mediaSelector').is(':visible')) {
        $('#mediaSelector').fadeIn(); // Fade in #mediaSelector if not visible
        $('#webrtcChat').hide();      // Hide #webrtcChat
    } else {
        $('#webrtcChat').show();      // Show #webrtcChat
        $('#mediaSelector').fadeOut(); // Fade out #mediaSelector
    }
}

// Utility to lock screen orientation
function lockScreenOrientation() {
    if (screen.orientation && screen.orientation.lock) {
        screen.orientation.lock('portrait').then(() => {
            console.log('Screen orientation locked.');
        }).catch((err) => {
            console.error('Failed to lock screen orientation:', err);
        });
    } else {
        console.warn('Screen Orientation API is not supported.');
    }
}

// Utility to unlock screen orientation
function unlockScreenOrientation() {
    if (screen.orientation && screen.orientation.unlock) {
        screen.orientation.unlock();
        console.log('Screen orientation unlocked.');
    }
}

// Populate the video/audio source dropdowns. Returns a previously saved device
// preference (see getSavedWebRTCDevices) if it is still valid, otherwise null.
async function populateSources() {
    let validatedSavedDevices = null;
    try {
        // Request camera/mic access briefly to unlock device labels (required in iOS/Android).
        // This stream is only used to read labels, so it is stopped immediately.
        const labelStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        labelStream.getTracks().forEach((track) => track.stop());

        const devices = await navigator.mediaDevices.enumerateDevices();

        // Clear existing options
        $('#videoSource').empty().append('<option value="">Default</option>');
        $('#audioSource').empty().append('<option value="">Default</option>');

        const videoInputs = devices.filter(device => device.kind === 'videoinput');
        const audioInputs = devices.filter(device => device.kind === 'audioinput');

        // Populate video sources
        videoInputs.forEach((device, index) => {
            $('#videoSource').append(
                `<option value="${device.deviceId}">${device.label || `Camera ${index + 1}`}</option>`
            );
        });

        // If only 1 camera is available or labels are missing, add facingMode fallback options
        if (videoInputs.length <= 1) {
            $('#videoSource').append('<option value="facing-user">Front Camera</option>');
            $('#videoSource').append('<option value="facing-environment">Rear Camera</option>');
        }

        // Populate audio sources
        audioInputs.forEach((device, index) => {
            $('#audioSource').append(
                `<option value="${device.deviceId}">${device.label || `Mic ${index + 1}`}</option>`
            );
        });

        // Validate any previously saved device preference against the devices that actually exist
        const saved = getSavedWebRTCDevices();
        if (saved) {
            const videoStillExists = !saved.videoDeviceId ||
                saved.videoDeviceId.startsWith('facing-') ||
                videoInputs.some((d) => d.deviceId === saved.videoDeviceId);
            const audioStillExists = !saved.audioDeviceId ||
                audioInputs.some((d) => d.deviceId === saved.audioDeviceId);

            if (videoStillExists && audioStillExists) {
                if (saved.videoDeviceId) $('#videoSource').val(saved.videoDeviceId);
                if (saved.audioDeviceId) $('#audioSource').val(saved.audioDeviceId);
                validatedSavedDevices = saved;
            } else {
                console.warn('Saved WebRTC device preference is no longer available, clearing it.');
                clearSavedWebRTCDevices();
            }
        }
    } catch (error) {
        console.error('Error populating media sources:', error);
        handleMediaError(error);
    }
    return validatedSavedDevices;
}

// Build video constraints, supporting facingMode fallback
function buildVideoConstraints(deviceIdOrFacingMode) {
    const base = {
        width: { ideal: 1280 },
        height: { ideal: 720 },
        frameRate: { ideal: 30 }
    };

    if (!deviceIdOrFacingMode) return base;

    if (deviceIdOrFacingMode === 'facing-user') {
        return { ...base, facingMode: { exact: 'user' } };
    } else if (deviceIdOrFacingMode === 'facing-environment') {
        return { ...base, facingMode: { exact: 'environment' } };
    } else {
        return { ...base, deviceId: { exact: deviceIdOrFacingMode } };
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

// Detect a camera/microphone being unplugged or connected mid-session
if (navigator.mediaDevices && typeof navigator.mediaDevices.addEventListener === 'function') {
    navigator.mediaDevices.addEventListener('devicechange', async () => {
        console.log('Media devices changed.');
        const previousVideoTrack = localStream ? localStream.getVideoTracks()[0] : null;
        const previousAudioTrack = localStream ? localStream.getAudioTracks()[0] : null;

        await populateSources();

        if (previousVideoTrack && previousVideoTrack.readyState === 'ended') {
            avideoToastError(__('Your camera was disconnected. Please select another device.'));
        }
        if (previousAudioTrack && previousAudioTrack.readyState === 'ended') {
            avideoToastError(__('Your microphone was disconnected. Please select another device.'));
        }
    });
}


$(document).ready(function () {
    webrtcSourcesReadyPromise = populateSources();

    // Start Screen Sharing
    $('#startScreenShare').click(async function () {
        startWebRTC({ useScreen: true });
    });

    // Confirm setup: apply the selected devices and persist them for future visits
    $('#applyChanges').click(async function () {
        if (typeof isLive !== 'undefined' && isLive) {
            avideoToastWarning(__('Please stop the live stream before changing your camera or microphone.'));
            return;
        }

        const videoDeviceId = $('#videoSource').val();
        const audioDeviceId = $('#audioSource').val();
        const btn = $(this);

        btn.prop('disabled', true);
        try {
            const started = await startWebRTC({ videoDeviceId: videoDeviceId, audioDeviceId: audioDeviceId });
            if (started) {
                saveWebRTCDevices(videoDeviceId, audioDeviceId);
                markWebrtcSetupConfirmed();
                avideoToastSuccess(__('Setup confirmed.'));
                console.log('Media devices updated successfully.');
                // Free up the screen (chat/preview) now that setup is done; gear icon reopens it.
                $('#mediaSelector').fadeOut(function () {
                    $('#webrtcChat').show();
                });
            }
        } catch (error) {
            console.error('Error applying changes to media devices:', error);
        } finally {
            btn.prop('disabled', false);
        }
    });

    // Apply Changes (Change Video and Audio Sources)
    $('#stopWebRTC').click(function () {
        try {
            stopWebRTC();
            console.log('Media devices stop successfully.');
        } catch (error) {
            console.error('Error on stop', error);
        }
    });

    // Apply Changes (Change Video and Audio Sources)
    $('#startWebRTC').click(function () {
        try {
            startWebRTC();
            console.log('Media devices start successfully.');
        } catch (error) {
            console.error('Error on start', error);
        }
    });

    // Listen for the tab activation event
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Check if the 'Webcam' tab is activated
        if ($(e.target).attr('href') === '#tabWebcam') {
            startWebRTC(); // Call the startWebRTC function
        }
    });
});
