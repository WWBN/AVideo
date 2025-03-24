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
    //avideoToastError('Unable to connect to the webcam server. Please check your connection and try again.');
});

// Handle successful connection
socketWebRTC.on('connect', () => {
    setIsWebcamServerConnected();
    avideoToastSuccess('Successfully connected to the webcam server.');
});

// Handle disconnection
socketWebRTC.on('disconnect', (reason) => {
    setIsWebcamServerNotConnected();
    avideoToastError(`Disconnected from the webcam server. Reason: ${reason}`);
    console.log('Disconnected from the server:', reason);
    if (reason === 'io server disconnect') {
        socketWebRTC.connect(); // Optionally reconnect
        avideoToastWarning('Reconnecting to the server...');
    }
});

// Handle reconnection attempts
socketWebRTC.on('reconnect_attempt', () => {
    console.log('Attempting to reconnect...');
    avideoToastInfo('Attempting to reconnect to the webcam server...');
});

// Handle live-start
socketWebRTC.on('live-start', ({ rtmpURL }) => {
    console.log('live-start', rtmpURL);
    avideoToastSuccess(`<i class="fa-solid fa-sync fa-spin"></i> Live streaming connecting...`);
    setIsLive();
    requestNotifications();
});

// Handle live-resumed
socketWebRTC.on('live-resumed', ({ rtmpURL }) => {
    console.log('live-resumed', rtmpURL);
    avideoToastSuccess(`Live streaming resumed.`);
    setIsLive();
    requestNotifications();
});

// Handle live-stopped
socketWebRTC.on('live-stopped', ({ rtmpURL, message }) => {
    console.log('live-stopped', rtmpURL, message);
    avideoToastWarning(`${message}`);
    setIsNotLive();
    requestNotifications();
});

socketWebRTC.on('stream-will-stop', ({ rtmpURL, message }) => {
    console.log('stream-will-stop', rtmpURL, message);
    avideoToastWarning(`<i class="fa-solid fa-triangle-exclamation fa-beat-fade"></i> ${message}`, 30000);
});

// Handle general errors
socketWebRTC.on('error', ({ message }) => {
    console.error(`Error: ${message}`);
    avideoToastError(`An error occurred: ${message}`);
    requestNotifications();
});

// Handle FFMPEG errors
socketWebRTC.on('ffmpeg-error', ({ code }) => {
    console.error(`FFMPEG Error: ${code}`);
    avideoToastError(`FFMPEG encountered an error. Error code: ${code}`);
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
    if (isRunning) {
        console.log(`This live is running with RTMP URL: ${rtmpURL}`);
        //avideoToastSuccess(`Live stream is running. RTMP URL: ${rtmpURL}`);
        setIsLive();
    } else {
        console.log(`This live is not running`);
        //avideoToastWarning('Live stream is not running.');
        setIsNotLive();
    }

    // Clear the timeout since we received a response
    clearTimeout(liveStatusTimeout);
});

// Handle stream-stopped
socketWebRTC.on('stream-stopped', ({ rtmpURL, reason }) => {
    console.log(`Stream for ${rtmpURL} stopped: ${reason}`);
    avideoToastWarning(`Stream stopped. ${reason}`);
    requestNotifications();
    setIsNotLive();
});
