
// Function to request notifications
function requestNotifications() {
    if (typeof rtmpURLEncrypted === 'undefined' || !socketWebRTC.connected) return;
    clearTimeout(liveStatusTimeout);
    liveStatusTimeout = setTimeout(() => {
        webrtcStatusKnown = false;
        setWebRTCError(__('Unable to confirm broadcast status. Check your connection and try again.'));
        // Never claim a broadcast ended just because a status request timed out.
    }, 10000);
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

    setWebRTCError(message);
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

// Retained for integrations that used the previous setup confirmation step.
function markWebrtcSetupConfirmed() {
    document.body.classList.add('webrtcSetupConfirmed');
}

function startWebcamLive(rtmpURLEncrypted) {
    if (isLive || isPublishing || webrtcStopPending || webrtcMediaBusy) return;
    if (!socketWebRTC.connected || !webrtcStatusKnown) {
        setWebRTCError(__('Wait for the connection before starting your broadcast.'));
        return;
    }
    if (!localStream || !localStream.getVideoTracks().some(track => track.readyState === 'live') ||
        (!webrtcUsingScreen && !localStream.getAudioTracks().some(track => track.readyState === 'live'))) {
        setWebRTCError(__('A camera and microphone are needed. Check your devices and update the preview.'));
        return;
    }
    webrtcError = '';
    isPublishing = true;
    renderWebRTCStudio();
    $('#stopLive').trigger('focus');
    socketWebRTC.emit('join', { rtmpURLEncrypted, id: socketWebRTC.id });
    if (!sendStreamToServer(localStream)) return;
    clearTimeout(publishWatchdog);
    publishWatchdog = setTimeout(() => {
        if (isPublishing && !isLive) {
            failWebRTCPublish(__('We could not confirm the live stream started. Please try again.'));
        }
    }, 15000);
}

function stopWebcamLive(rtmpURLEncrypted) {
    if (webrtcStopping) return;
    webrtcError = '';
    webrtcStopPending = true;
    webrtcStopping = true;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    stopStreamToServer();
    // Do not queue a stale stop in Socket.IO's offline send buffer. Send it on reconnect.
    if (socketWebRTC.connected) socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
    renderWebRTCStudio();
    clearTimeout(webrtcStopWatchdog);
    webrtcStopWatchdog = setTimeout(() => {
        webrtcStopping = false;
        setWebRTCError(__('Unable to confirm the broadcast ended. Check your connection and choose End broadcast again.'));
    }, 10000);
}

let mediaRecorder; // Declare a global variable to manage the MediaRecorder

function sendStreamToServer(stream) {
    try {
        if (!window.MediaRecorder) throw new Error('MediaRecorder unavailable');
        mediaRecorder = new MediaRecorder(stream);
        const recorder = mediaRecorder;
        mediaRecorder.ondataavailable = (event) => {
            if (mediaRecorder === recorder && recorder.state !== 'inactive' && event.data.size > 0 &&
                socketWebRTC.connected && !webrtcStopPending && (isPublishing || isLive)) {
                socketWebRTC.emit('video-chunk', { rtmpURLEncrypted, chunk: event.data });
            }
        };
        mediaRecorder.onerror = (event) => {
            if (mediaRecorder !== recorder || webrtcStopPending) return;
            console.error('MediaRecorder error:', event.error);
            failWebRTCPublish(__('Unable to share your camera and microphone. Update your browser or try again.'));
        };
        mediaRecorder.start(isIPhone() ? 250 : 1000);
        return true;
    } catch (error) {
        console.error('Failed to initialize MediaRecorder:', error);
        failWebRTCPublish(__('Unable to share your camera and microphone. Update your browser or try again.'));
        return false;
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
    }
}

function isWebcamServerConnected() {
    return $('body').hasClass('WebcamServerConnected');
}

function setIsWebcamServerConnected() {
    $('body').removeClass('WebcamServerNotConnected').addClass('WebcamServerConnected');
    webrtcStatusKnown = false;
    webrtcError = '';
    webrtcHasConnected = true;
    renderWebRTCStudio();
}

function setIsWebcamServerNotConnected() {
    $('body').removeClass('WebcamServerConnected').addClass('WebcamServerNotConnected');
    webrtcStatusKnown = false;
    clearTimeout(liveStatusTimeout);
    // End forwarding locally and reconcile with the server after reconnecting.
    // Never silently republish a camera or claim a disconnected broadcast has ended.
    if (isLive || isPublishing) webrtcStopPending = true;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    stopStreamToServer();
    renderWebRTCStudio();
}

function setIsLive() {
    document.body.classList.remove('isNotLive');
    document.body.classList.add('isLive');
    isLive = true;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    renderWebRTCStudio();
    lockScreenOrientation();
}

function setIsNotLive() {
    document.body.classList.remove('isLive');
    document.body.classList.add('isNotLive');
    isLive = false;
    isPublishing = false;
    clearTimeout(publishWatchdog);
    stopStreamToServer();
    renderWebRTCStudio();
    unlockScreenOrientation();
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
    if (webrtcMediaBusy || isLive || isPublishing || webrtcStopPending) return false;
    webrtcMediaBusy = true;
    webrtcError = '';
    const request = ++webrtcMediaRequest;
    renderWebRTCStudio();
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw { name: 'SecurityError' };
        }
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
            const isLandscape = window.innerWidth > window.innerHeight;

            const videoConstraints = buildVideoConstraints(videoDeviceId);
            videoConstraints.aspectRatio = { ideal: isLandscape ? 16 / 9 : 9 / 16 };

            console.log('videoConstraints', isLandscape, videoConstraints);


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

        if (request !== webrtcMediaRequest) {
            newStream.getTracks().forEach(track => track.stop());
            return false;
        }
        // Stop existing tracks before replacing them
        if (localStream) {
            localStream.getTracks().forEach((track) => track.stop());
        }

        // Set the new stream
        localStream = newStream;
        webrtcUsingScreen = useScreen;
        localVideo.srcObject = newStream;
        newStream.getTracks().forEach(track => {
            track.addEventListener('ended', () => handleWebRTCTrackEnded(newStream));
        });

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
    } finally {
        webrtcMediaBusy = false;
        renderWebRTCStudio();
    }
}

function stopWebRTC() {
    if (isLive || isPublishing || webrtcStopPending) return;
    releaseWebRTCPreview();
    webrtcError = '';
    renderWebRTCStudio();
    $('#startWebRTC').trigger('focus');
}

function toggleMediaSelector() {
    const selector = document.getElementById('mediaSelector');
    if (selector) selector.open = !selector.open;
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
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return null;
        // Enumeration never opens the camera. The actual preview unlocks device labels.
        const selectedVideo = $('#videoSource').val();
        const selectedAudio = $('#audioSource').val();

        const devices = await navigator.mediaDevices.enumerateDevices();

        // Clear existing options
        $('#videoSource').empty().append(new Option(__('Default'), ''));
        $('#audioSource').empty().append(new Option(__('Default'), ''));

        const videoInputs = devices.filter(device => device.kind === 'videoinput');
        const audioInputs = devices.filter(device => device.kind === 'audioinput');

        // Populate video sources
        videoInputs.forEach((device, index) => {
            $('#videoSource').append(
                new Option(device.label || __('Camera') + ' ' + (index + 1), device.deviceId)
            );
        });

        // If only 1 camera is available or labels are missing, add facingMode fallback options
        if (videoInputs.length <= 1 || (getSavedWebRTCDevices() || {}).videoDeviceId?.startsWith('facing-')) {
            $('#videoSource').append(new Option(__('Front Camera'), 'facing-user'));
            $('#videoSource').append(new Option(__('Rear Camera'), 'facing-environment'));
        }

        // Populate audio sources
        audioInputs.forEach((device, index) => {
            $('#audioSource').append(
                new Option(device.label || __('Microphone') + ' ' + (index + 1), device.deviceId)
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
        if (selectedVideo && videoInputs.some(d => d.deviceId === selectedVideo)) $('#videoSource').val(selectedVideo);
        if (selectedAudio && audioInputs.some(d => d.deviceId === selectedAudio)) $('#audioSource').val(selectedAudio);
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



// Studio state is separate from Socket.IO connectivity: a connection is not a broadcast.
let webrtcMediaBusy = false;
let webrtcUsingScreen = false;
let webrtcMediaRequest = 0;
let webrtcStatusKnown = false;
let webrtcStopping = false;
let webrtcStopPending = false;
let webrtcError = '';
let webrtcStopWatchdog;
let webrtcHasConnected = false;

function renderWebRTCStudio() {
    if (!document.getElementById('webrtcStatus')) return;
    const connected = isWebcamServerConnected();
    const preview = !!localStream;
    const active = isLive || isPublishing || webrtcStopPending;
    let state = 'preview';
    let label = __('Preview only');
    let message = preview ? __('Only you can see this preview. Choose Start broadcast when you are ready.') :
        __('Prepare your camera and microphone. Only you can see this preview.');
    if (preview && webrtcUsingScreen && !localStream.getAudioTracks().length) {
        message = __('Your screen preview has no sound. Choose a source with audio if you want your audience to hear it.');
    }
    if (webrtcMediaBusy && !active) {
        state = 'loading';
        label = __('Preparing camera and microphone');
        message = __('Allow camera and microphone access in your browser to see the preview.');
    } else if (!connected || !webrtcStatusKnown) {
        state = 'connecting';
        label = webrtcHasConnected ? __('Reconnecting') : __('Connecting');
        message = active ? __('Connection interrupted. Checking your broadcast status. You can still choose End broadcast.') :
            __('Connecting to the live service. You can prepare your preview while you wait.');
    } else if (webrtcStopPending) {
        state = 'stopping';
        label = __('Ending broadcast');
        message = __('Waiting for confirmation that your broadcast has ended.');
    } else if (isPublishing) {
        state = 'starting';
        label = __('Starting broadcast');
        message = __('Connecting your broadcast. Your camera and microphone are being shared.');
    } else if (isLive) {
        state = 'live';
        label = __('You are live');
        message = __('Your audience can see and hear your broadcast.');
    }
    if (webrtcError) {
        message = webrtcError;
        if (!active) { state = 'error'; label = __('Action needed'); }
    }
    $('.webrtc-studio').attr('data-state', state);
    $('#webrtcStatus').removeClass('alert-info alert-success alert-warning alert-danger')
        .addClass(webrtcError ? 'alert-danger' : state === 'live' ? 'alert-success' : active ? 'alert-warning' : 'alert-info');
    $('#webrtcStateLabel').text(label);
    $('#webrtcStateMessage').text(message);
    $('#webrtcPreviewBadge').text(active ? label : __('Not live'));
    $('#webrtcPreviewPlaceholder').toggleClass('hidden', preview);
    $('#localVideo').attr('aria-hidden', preview ? 'false' : 'true');
    $('#startWebRTC').toggleClass('hidden', preview || active).prop('disabled', webrtcMediaBusy);
    $('#startLive').toggleClass('hidden', !preview || active).prop('disabled', webrtcMediaBusy || !connected || !webrtcStatusKnown);
    $('#stopWebRTC').toggleClass('hidden', !preview || active).prop('disabled', webrtcMediaBusy);
    $('#stopLive').toggleClass('hidden', !active).prop('disabled', webrtcStopping);
    $('#webrtcDeviceFields').prop('disabled', active || webrtcMediaBusy);
    $('#retryWebRTC').toggleClass('hidden', !webrtcError || active).prop('disabled', webrtcMediaBusy);
}

function setWebRTCError(message) {
    const changed = webrtcError !== message;
    webrtcError = message;
    renderWebRTCStudio();
    if (changed) avideoToastError(message);
}

function finishWebRTCStop() {
    const focusStart = document.activeElement && document.activeElement.id === 'stopLive';
    webrtcStopPending = false;
    webrtcStopping = false;
    webrtcStatusKnown = true;
    clearTimeout(webrtcStopWatchdog);
    setIsNotLive();
    if (focusStart) $(localStream ? '#startLive' : '#startWebRTC').trigger('focus');
}

function failWebRTCPublish(message) {
    // Cancel forwarding even if join succeeded but the confirmation was lost.
    stopWebcamLive(rtmpURLEncrypted);
    setWebRTCError(message);
}

async function prepareWebcam() {
    if (localStream || webrtcMediaBusy || isLive || isPublishing || webrtcStopPending) return;
    const saved = await populateSources();
    const started = await startWebRTC(saved ? {
        videoDeviceId: saved.videoDeviceId, audioDeviceId: saved.audioDeviceId
    } : {});
    if (started) await populateSources();
}

async function retryWebRTC() {
    webrtcError = '';
    if (!socketWebRTC.connected) socketWebRTC.connect();
    else requestNotifications();
    if (!localStream) await prepareWebcam();
    renderWebRTCStudio();
}

function handleWebRTCTrackEnded(stream) {
    if (stream !== localStream) return;
    if (isLive || isPublishing) stopWebcamLive(rtmpURLEncrypted);
    releaseWebRTCPreview();
    setWebRTCError(__('Camera or microphone disconnected. Check your devices and prepare the preview again.'));
}

function releaseWebRTCPreview() {
    // Invalidate outstanding permission requests so a late result cannot reopen the camera.
    webrtcMediaRequest++;
    if (localStream) localStream.getTracks().forEach(track => track.stop());
    localStream = null;
    webrtcUsingScreen = false;
    if (localVideo) localVideo.srcObject = null;
    $('body').removeClass('webCamIsOn');
    renderWebRTCStudio();
}

function cleanupWebRTCStudio() {
    if (isLive || isPublishing || webrtcStopPending) stopWebcamLive(rtmpURLEncrypted);
    stopStreamToServer();
    releaseWebRTCPreview();
}

$(document).ready(function () {
    if (!document.getElementById('webrtcStatus')) return;
    renderWebRTCStudio();
    $('#startScreenShare').click(() => startWebRTC({ useScreen: true }));
    $('#applyChanges').click(async function () {
        const videoDeviceId = $('#videoSource').val();
        const audioDeviceId = $('#audioSource').val();
        if (await startWebRTC({ videoDeviceId, audioDeviceId })) {
            saveWebRTCDevices(videoDeviceId, audioDeviceId);
            await populateSources();
        }
    });
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
        if ($(event.target).attr('href') === '#tabWebcam') prepareWebcam();
    });
    if (document.body.classList.contains('quickGoLiveMode') || $('#tabWebcam').hasClass('active')) prepareWebcam();
    window.addEventListener('pagehide', cleanupWebRTCStudio);
    window.addEventListener('beforeunload', cleanupWebRTCStudio);
    if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
        navigator.mediaDevices.addEventListener('devicechange', async () => {
            if (localStream && localStream.getTracks().some(track => track.readyState === 'ended')) {
                handleWebRTCTrackEnded(localStream);
            }
            await populateSources();
        });
    }
});
