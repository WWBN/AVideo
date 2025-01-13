//const socket = io('https://t.ypt.me:3000'); // Connect to the Socket.IO server
const socket = io(WebRTC2RTMPURL); // Connect to the Socket.IO server
const peers = {};
const localVideo = document.getElementById('localVideo');
let localStream;
let liveStatusTimeout; // Timeout to track live status
let isLive = false; // Track live status

// Handle connection errors
socket.on('connect_error', (error) => {
    setIsWebcamServerNotConnected();
    console.error('Connection error:', error.message);
    //avideoToastError('Unable to connect to the webcam server. Please check your connection and try again.');
});

// Handle successful connection
socket.on('connect', () => {
    setIsWebcamServerConnected();
    avideoToastSuccess('Successfully connected to the webcam server.');
});

// Handle disconnection
socket.on('disconnect', (reason) => {
    setIsWebcamServerNotConnected();
    avideoToastError(`Disconnected from the webcam server. Reason: ${reason}`);
    console.log('Disconnected from the server:', reason);
    if (reason === 'io server disconnect') {
        socket.connect(); // Optionally reconnect
        avideoToastWarning('Reconnecting to the server...');
    }
});

// Handle reconnection attempts
socket.on('reconnect_attempt', () => {
    console.log('Attempting to reconnect...');
    avideoToastInfo('Attempting to reconnect to the webcam server...');
});

// Handle live-start
socket.on('live-start', ({ rtmpURL }) => {
    console.log('live-start', rtmpURL);
    avideoToastSuccess(`Live streaming started successfully.`);
    setIsLive();
    requestNotifications();
});

// Handle live-resumed
socket.on('live-resumed', ({ rtmpURL }) => {
    console.log('live-resumed', rtmpURL);
    avideoToastSuccess(`Live streaming resumed.`);
    setIsLive();
    requestNotifications();
});

// Handle live-stopped
socket.on('live-stopped', ({ rtmpURL, message }) => {
    console.log('live-stopped', rtmpURL, message);
    avideoToastWarning(`Live streaming stopped. Reason: ${message}`);
    setIsNotLive();
    requestNotifications();
});

// Handle general errors
socket.on('error', ({ message }) => {
    console.error(`Error: ${message}`);
    avideoToastError(`An error occurred: ${message}`);
    requestNotifications();
});

// Handle FFMPEG errors
socket.on('ffmpeg-error', ({ code }) => {
    console.error(`FFMPEG Error: ${code}`);
    avideoToastError(`FFMPEG encountered an error. Error code: ${code}`);
    requestNotifications();
});

// Handle active connections
socket.on('connections', ({ current, max }) => {
    console.log(`Current number of active connections: ${current}/${max}`);
    //avideoToastInfo(`Active connections: ${current}/${max}`);
});

// Handle live-time
socket.on('live-time', ({ startTime, elapsedSeconds, remainingSeconds }) => {
    console.log(`Time remaining is: ${remainingSeconds} seconds`);
    //avideoToastInfo(`Live stream time remaining: ${remainingSeconds} seconds.`);
});

// Handle RTMP status
socket.on('rtmp-status', ({ rtmpURL, isRunning }) => {
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
socket.on('stream-stopped', ({ rtmpURL, reason }) => {
    console.log(`Stream for ${rtmpURL} stopped: ${reason}`);
    avideoToastWarning(`Stream stopped. Reason: ${reason}`);
    requestNotifications();
    setIsNotLive();
});
