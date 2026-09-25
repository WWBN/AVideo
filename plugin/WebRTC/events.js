//const socketWebRTC = io('https://t.ypt.me:3000'); // Connect to the Socket.IO server
const socketWebRTC = io(WebRTC2RTMPURL); // Connect to the Socket.IO server
const peers = {};
const localVideo = document.getElementById('localVideo');
let localStream;
let liveStatusTimeout; // Timeout to track live status
let isLive = false; // Track live status

// Handle connection errors
socketWebRTC.on('connect_error', (error) => {
    setIsWebcamServerNotConnected();
    console.error('Connection error:', error.message);
    setWebRTCError(__('Unable to connect. Check your internet connection and try again.'));
});

// Handle successful connection
socketWebRTC.on('connect', () => {
    setIsWebcamServerConnected();
    if (webrtcStopPending) {
        socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
    }
    requestNotifications();
});

// Handle disconnection
socketWebRTC.on('disconnect', (reason) => {
    setIsWebcamServerNotConnected();

    console.log('Disconnected from the server:', reason);
    if (reason === 'io server disconnect') {
        socketWebRTC.connect(); // Optionally reconnect

    }
});

// Handle reconnection attempts
socketWebRTC.io.on('reconnect_attempt', () => {
    console.log('Attempting to reconnect...');
    renderWebRTCStudio();
});

// Handle live-start
socketWebRTC.on('live-start', ({ rtmpURL }) => {
    console.log('live-start', rtmpURL);
    // The forwarding process has started; wait for RTMP status before saying Live.
    if (webrtcStopPending) socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
    renderWebRTCStudio();
    requestNotifications();
});

// Handle live-resumed
socketWebRTC.on('live-resumed', ({ rtmpURL }) => {
    console.log('live-resumed', rtmpURL);
    if (webrtcStopPending) socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
    requestNotifications();
});

// Handle live-stopped
socketWebRTC.on('live-stopped', ({ rtmpURL, message }) => {
    console.log('live-stopped', rtmpURL, message);
    finishWebRTCStop();
    requestNotifications();
});

socketWebRTC.on('stream-will-stop', ({ rtmpURL, message }) => {
    console.log('stream-will-stop', rtmpURL, message);
    avideoToastWarning(__('Your broadcast will end soon. Check your connection.'), 30000);
});

// Handle general errors
socketWebRTC.on('error', ({ message }) => {
    console.error(`Error: ${message}`);
    if (isPublishing || isLive) failWebRTCPublish(__('The broadcast was interrupted. Please try again.'));
    else setWebRTCError(__('The live service could not complete the request. Please try again.'));
    requestNotifications();
});

// Handle FFMPEG errors
socketWebRTC.on('ffmpeg-error', ({ code }) => {
    console.error(`FFMPEG Error: ${code}`);
    failWebRTCPublish(__('The broadcast was interrupted. Please try again.'));
    requestNotifications();
});

// Handle active connections
socketWebRTC.on('connections', ({ current, max }) => {
    console.log(`Current number of active connections: ${current}/${max}`);
    //avideoToastInfo(`Active connections: ${current}/${max}`);
});

// Handle live-time
socketWebRTC.on('live-time', ({ startTime, elapsedSeconds, remainingSeconds }) => {
    console.log(`Time remaining is: ${remainingSeconds} seconds`);
    //avideoToastInfo(`Live stream time remaining: ${remainingSeconds} seconds.`);
});

// Handle RTMP status
socketWebRTC.on('rtmp-status', ({ rtmpURL, isRunning }) => {
    webrtcStatusKnown = true;
    clearTimeout(liveStatusTimeout);
    if (isRunning) {
        if (webrtcStopPending) socketWebRTC.emit('stop-live', { rtmpURLEncrypted });
        setIsLive();
    } else if (webrtcStopPending) {
        finishWebRTCStop();
    } else if (!isPublishing) {
        // A status reply sent before join must not cancel an in-flight start.
        setIsNotLive();
    } else {
        // The server may acknowledge join before its forwarding process is ready.
        setTimeout(() => {
            if (isPublishing && socketWebRTC.connected) checkRTMPStatus();
        }, 1000);
    }
    renderWebRTCStudio();
});

// Handle stream-stopped
socketWebRTC.on('stream-stopped', ({ rtmpURL, reason }) => {
    console.log(`Stream for ${rtmpURL} stopped: ${reason}`);
    avideoToastWarning(__('Broadcast ended.'));
    requestNotifications();
    finishWebRTCStop();
});
