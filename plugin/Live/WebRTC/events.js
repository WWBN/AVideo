//const socket = io('https://t.ypt.me:3000'); // Connect to the Socket.IO server
const socket = io(WebRTC2RTMPURL); // Connect to the Socket.IO server
const peers = {};
const localVideo = document.getElementById('localVideo');
let localStream;
let liveStatusTimeout; // Timeout to track live status
let isLive = false; // Track live status

// Handle connection errors
socket.on('connect_error', (error) => {
    $('body').removeClass('WebRTCReady');
    console.error('Connection error:', error.message);
    // Custom logic to handle connection failure
});

// Handle connection errors
socket.on('connect', () => {
    avideoToastSuccess('Webcam server is ready');
    console.log('Connection success');
    // Custom logic to handle connection failure
    $('body').addClass('WebRTCReady');
});

// Handle disconnection
socket.on('disconnect', (reason) => {
    avideoToastError('Webcam server disconnected');
    console.log('Disconnected from the server:', reason);
    $('body').removeClass('WebRTCReady');
    if (reason === 'io server disconnect') {
        // The server disconnected the client manually
        socket.connect(); // Optionally reconnect
    }
    setIsNotLive();
});

// Handle reconnection attempts if enabled
socket.on('reconnect_attempt', () => {
    console.log('Attempting to reconnect...');
});


// Handle response
socket.on('live-start', ({ rtmpURL }) => {
    console.log('live-start', rtmpURL);
    avideoToastSuccess('Live start');
    setIsLive();
    requestNotifications();
});

// Handle response
socket.on('live-resumed', ({ rtmpURL }) => {
    console.log('live-resumed', rtmpURL);
    avideoToastSuccess('Live Resumed');
    setIsLive();
    requestNotifications();
});

socket.on('live-stopped', ({ rtmpURL, message }) => {
    console.log('live-stopped', rtmpURL, message);
    avideoToastWarning('Live stop');
    setIsNotLive();
    requestNotifications();
});

socket.on('error', ({ message }) => {
    console.error(`Error: ${message}`);
    avideoToastError(message);
    requestNotifications();
});

socket.on('ffmpeg-error', ({ code }) => {
    console.error(`FFMPEG Error: ${code}`);
    //avideoToastError(message);
    requestNotifications();
});

socket.on('connections', ({ current, max }) => {
    console.log(`Current number of active connections: ${current}/${max}`);
});

socket.on('live-time', ({ startTime, elapsedSeconds, remainingSeconds }) => {
    console.log(`Time remaining is: ${remainingSeconds} seconds `);
});

socket.on('rtmp-status', ({ rtmpURL, isRunning }) => {
    if (isRunning) {
        console.log(`This live is running with RTMP URL: ${rtmpURL}`);
        setIsLive();
    } else {
        console.log(`This live is not running`);
        setIsNotLive();
    }

    // Clear the timeout since we received a response
    clearTimeout(liveStatusTimeout);
});

socket.on('stream-stopped', ({ rtmpURL, reason }) => {
    console.log(`Stream for ${rtmpURL} stopped: ${reason}`);
    requestNotifications();
});
