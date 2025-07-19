var Button = videojs.getComponent('Button');

class AudioToggleButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('audio-toggle-button');
        this.isAudioMode = false;
        this.originalSrc = '';
        this.audioSrc = '';
        this.updateButton();
        this.controlText("Toggle Audio Stream");
    }

    updateButton() {
        if (this.isAudioMode) {
            this.addClass('video-source');
            this.removeClass('audio-source');
            this.el().title = 'Switch to Video';
        } else {
            this.addClass('audio-source');
            this.removeClass('video-source');
            this.el().title = 'Switch to Audio';
        }
    }

    handleClick() {
        this.toggleAudioMode();
    }

    toggleAudioMode() {
        if (typeof player === 'undefined') {
            console.error('Player not available');
            return;
        }

        var currentSrc = player.currentSrc();

        if (!this.originalSrc) {
            this.originalSrc = currentSrc;
            // Generate audio URL by replacing /live/ with /audio/
            this.audioSrc = this.generateAudioUrl(currentSrc);
        }

        this.isAudioMode = !this.isAudioMode;

        var newSrc = this.isAudioMode ? this.audioSrc : this.originalSrc;

        // Save current state
        var currentTime = player.currentTime();
        var wasPlaying = !player.paused();

        console.log('Switching to:', this.isAudioMode ? 'Audio Stream' : 'Video Stream');
        console.log('URL:', newSrc);

        // Pause player first
        // player.pause();

        // Change source
        player.src({
            src: newSrc,
            type: 'application/x-mpegURL'
        });

        // Use loadstart event instead of ready for HLS streams
        var onLoadStart = () => {
            player.off('loadstart', onLoadStart);

            // Small delay to ensure the stream is ready
            setTimeout(() => {
                player.muted(false);
                if (currentTime > 0 && !isNaN(currentTime) && isFinite(currentTime)) {
                    player.currentTime(currentTime);
                }

                if (wasPlaying) {
                    var playPromise = player.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(error => {
                            console.log('Error auto-playing:', error);
                        });
                    }
                }
            }, 500);
        };

        player.one('loadstart', onLoadStart);

        // Load the new source
        player.load();

        this.updateButton();
    }

    generateAudioUrl(videoUrl) {
        console.log('Original video URL:', videoUrl);

        // Different URL patterns that may appear
        var audioUrl = videoUrl;

        // Pattern: /live/ -> /audio/
        if (audioUrl.includes('/live/')) {
            audioUrl = audioUrl.replace('/live/', '/audio/');

            // If video URL doesn't end with /index.m3u8, we need to add it for audio
            if (!audioUrl.endsWith('/index.m3u8')) {
                // If it ends with .m3u8 but not /index.m3u8, replace the filename
                if (audioUrl.endsWith('.m3u8')) {
                    // Remove the .m3u8 extension and add /index.m3u8
                    audioUrl = audioUrl.replace(/\.m3u8$/, '/index.m3u8');
                } else {
                    // Add /index.m3u8 to the end
                    audioUrl += '/index.m3u8';
                }
            }
        }
        // Alternative pattern for different URL structure
        else if (audioUrl.includes('live/')) {
            audioUrl = audioUrl.replace('live/', 'audio/');

            // Apply same logic for alternative pattern
            if (!audioUrl.endsWith('/index.m3u8')) {
                if (audioUrl.endsWith('.m3u8')) {
                    audioUrl = audioUrl.replace(/\.m3u8$/, '/index.m3u8');
                } else {
                    audioUrl += '/index.m3u8';
                }
            }
        }

        console.log('Generated audio URL:', audioUrl);
        return audioUrl;
    }
}

// Register and add button only if we're in a live stream context
function initAudioToggleButton() {
    if (typeof isLive !== 'undefined' && isLive && typeof videojs !== 'undefined') {
        videojs.registerComponent('AudioToggleButton', AudioToggleButton);

        if (typeof player !== 'undefined') {
            player.ready(() => {
                // Add button to control bar (position 1, right after play button)
                if (!player.getChild('controlBar').getChild('AudioToggleButton')) {
                    player.getChild('controlBar').addChild('AudioToggleButton', {}, 1);
                }
            });
        }
    }
}
