this.hotkeys({
    volumeStep: 0.1,
    seekStep: 5,
    enableModifiersForNumbers: false,
    alwaysCaptureHotkeys: true,
    playPauseKey: function(event) {
        return event.which === 32; // spacebar
    },
    rewindKey: function(event) {
        return event.which === 37; // left arrow
    },
    forwardKey: function(event) {
        return event.which === 39; // right arrow
    },
    volumeUpKey: function(event) {
        return event.which === 38; // up arrow
    },
    volumeDownKey: function(event) {
        return event.which === 40; // down arrow
    }
});

var lastTap = 0;
var tapTimeout;
this.el().addEventListener('touchend', function(event) {
    var currentTime = new Date().getTime();
    var tapLength = currentTime - lastTap;
    clearTimeout(tapTimeout);
    if (tapLength < 300 && tapLength > 0) {
        var touchX = event.changedTouches[0].clientX;
        var videoWidth = player.el().clientWidth;
        if (touchX < videoWidth / 2) {
            console.log('currentTime hot key 1');
            // Double tap on the left half - rewind
            player.currentTime(player.currentTime() - 10);
        } else {
            console.log('currentTime hot key 2');
            // Double tap on the right half - forward
            player.currentTime(player.currentTime() + 10);
        }
    } else {
        tapTimeout = setTimeout(function() {
            clearTimeout(tapTimeout);
        }, 300);
    }
    lastTap = currentTime;
});