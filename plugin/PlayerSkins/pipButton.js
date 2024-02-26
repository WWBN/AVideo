// Check if PiP mode is supported on this platform
if (document.pictureInPictureEnabled && !isIframe()) {
    // Detect when the document's visibility changes
    document.addEventListener('visibilitychange', function () {
        console.log('Picture-in-Picture visibilitychange', document.visibilityState);
        try {
            if (document.visibilityState === 'hidden') {
                if (!player.paused() && !document.pictureInPictureElement) {
                    player.requestPictureInPicture();
                }
            } else {
                // If the player is in PiP mode, exit it
                if (document.pictureInPictureElement) {
                    document.exitPictureInPicture();
                }
            }
        } catch (e) {
            console.log('Picture-in-Picture visibilitychange error', e);
        }
    });

} else {
    console.log('Picture-in-Picture mode is not supported on this platform');
}
