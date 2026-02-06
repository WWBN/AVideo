var startAudioSpectrumProgressInterval;
function startAudioSpectrumProgress(spectrumImage) {
    if ($('#mainVideo .vjs-play-progress').length) {
        $('#mainVideo .vjs-poster').append('<div id="avideo-audio-progress" style="display:none;"></div>');
        player.on('play', function () {
            $('#avideo-audio-progress').fadeIn();
            $('#mainVideo .vjs-poster').css('background-image', "url(" + spectrumImage + ")");
            $('#mainVideo .vjs-poster').css('background-size', "cover");
            //clearInterval(startAudioSpectrumProgressInterval);
            startAudioSpectrumProgressInterval = setInterval(function () {
                var style = $('#mainVideo .vjs-play-progress').attr('style');
                var percentage = style.replace("width:", "");
                $('#avideo-audio-progress').css('width', percentage.replace(";", ""));
            }, 100);

            // I need to add this because some versions of android chrome keep the error state even after play is pressed, so I cannot see the controls bar
            if ($(player.el()).hasClass('vjs-error')) {
                $(player.el()).removeClass('vjs-error');
                player.error(null);
            }

        });
        player.on('pause', function () {
            //clearInterval(startAudioSpectrumProgressInterval);
        });
    } else {
        setTimeout(function () { startAudioSpectrumProgress(spectrumImage); }, 500);
    }
}

function appendOnPlayer(element) {
    if (typeof player !== 'undefined') {
        $(element).insertBefore($(player.el()).find('.vjs-control-bar'));
    } else {
        setTimeout(function () { appendOnPlayer(element); }, 1000);
    }
}

function checkResolutionsLabelFix() {
    if (!$('#mainVideo .vjs-quality-selector .vjs-menu-item-text').length) {
        if (typeof hlsLog === 'function') {
            hlsLog('[AVIDEO-HLS] Reloading player - no quality menu items found');
        }
        player.load();
    }
}

var errorClassPrevented = false;

// Core function to remove vjs-error class when video is active
// Fixes Android Chrome issue where error state hides control bar
function removeErrorClassIfVideoActive() {
    if (player && $(player.el()).hasClass('vjs-error')) {
        if (!player.paused() || player.currentTime() > 0) {
            $(player.el()).removeClass('vjs-error');
            player.error(null);
            // Error class removed while video is playing
            return true;
        }
    }
    return false;
}

// Simple monitor solution - continuously checks for error class
function startErrorClassMonitor() {
    if (!window.errorClassMonitorInterval) {
        window.errorClassMonitorInterval = setInterval(function() {
            removeErrorClassIfVideoActive();
        }, 500); // Check every 500ms
    }
}

function stopErrorClassMonitor() {
    if (window.errorClassMonitorInterval) {
        clearInterval(window.errorClassMonitorInterval);
        window.errorClassMonitorInterval = null;
    }
}

// Enhanced version to prevent recurring error states
function checkIfIsPlayingWithErrors(checkIfIsPlaying = true) {
    if (player && $(player.el()).hasClass('vjs-error')) {
        if(!checkIfIsPlaying || (!player.paused() || player.currentTime() > 0) ){
            $(player.el()).removeClass('vjs-error');
            player.error(null);

            // Start monitoring to prevent the class from coming back
            startErrorClassMonitor();
        }
    }
}

// Advanced solution using MutationObserver to intercept class changes
function preventErrorClass() {
    if (!errorClassPrevented && player) {
        errorClassPrevented = true;

        // Observer to intercept when VideoJS adds vjs-error class
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    // Use our core function to handle the error removal
                    setTimeout(function() {
                        removeErrorClassIfVideoActive();
                    }, 10);
                }
            });
        });

        // Watch for changes in the player element's class attribute
        observer.observe(player.el(), {
            attributes: true,
            attributeFilter: ['class']
        });

        // Stop observing when video ends
        player.on('ended', function() {
            observer.disconnect();
            errorClassPrevented = false;
        });

        // Handle real errors that occur during playback
        player.on('error', function() {
            setTimeout(function() {
                removeErrorClassIfVideoActive();
            }, 100);
        });
    }
}

