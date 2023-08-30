// Remove controls from the player on iPad to stop native controls from stealing
// our click
var contentPlayer = document.getElementById('content_video_html5_api');
if (contentPlayer && (navigator.userAgent.match(/iPad/i) ||
    navigator.userAgent.match(/Android/i)) &&
    contentPlayer.hasAttribute('controls')) {
    contentPlayer.removeAttribute('controls');
}

// Initialize the ad container when the video player is clicked, but only the
if (navigator.userAgent.match(/iPhone/i) ||
    navigator.userAgent.match(/iPad/i) ||
    navigator.userAgent.match(/Android/i)) {
    startEvent = 'touchend';
}