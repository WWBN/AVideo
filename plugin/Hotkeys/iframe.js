// add this file in a parent iframe
function sendMessageToAllIframes(message) {
    var iframes = document.getElementsByTagName('iframe');
    for (var i = 0; i < iframes.length; i++) {
        iframes[i].contentWindow.postMessage(message, '*'); // '*' can be replaced with the specific domain if known
    }
}

window.addEventListener('keydown', function(event) {
    var message = {};
    switch(event.which) {
        case 32: // spacebar
            message.command = 'playPause';
            break;
        case 37: // left arrow
            message.command = 'rewind';
            break;
        case 39: // right arrow
            message.command = 'forward';
            break;
        case 38: // up arrow
            message.command = 'volumeUp';
            break;
        case 40: // down arrow
            message.command = 'volumeDown';
            break;
        default:
            return; // Exit for other keys
    }

    // Stop propagation and prevent default action
    event.stopPropagation();
    event.preventDefault();

    console.log('keydown', message, event);
    sendMessageToAllIframes(message);
});
