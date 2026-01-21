<?php
require_once __DIR__ . '/../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
$_GET['avideoIframe'] = 1;
$_page = new Page(array('Webcam'));
?>
<style>
    body {
        overflow: hidden;
    }

    video {
        position: fixed;
        top: 0;
        left: 0;
    }
</style>

<?php
include __DIR__ . '/video.php';
?>
<div id="webcamMediaControls" class="showWhenWebRTCIsConnected">
    <?php
    include __DIR__ . '/panel.medias.php';
    include __DIR__ . '/panel.buttons.php';
    ?>
</div>
<div id="webcamMediaControlsMessage" class="showWhenWebRTCIsNotConnected text-center" style="display: none;">
    <div class="row">
        <div class="col-sm-3">
        </div>
        <div class="col-sm-6">
            <div class="alert alert-danger">
                <div class="fa-3x">
                    <i class="fa-solid fa-triangle-exclamation fa-fade"></i>
                </div>
                <strong>Error:</strong> Unable to connect to the Webcam server.<br>
                <span>Please verify the server status and resolve any issues.</span>
            </div>
        </div>
        <div class="col-sm-3">
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        startWebRTC();

        // Auto-start live streaming if requested via Quick Go Live
        <?php if (!empty($_GET['autoStart']) && $_GET['autoStart'] == '1'): ?>
        // Wait for WebRTC to initialize and webcam to be ready
        var autoStartAttempts = 0;
        var maxAutoStartAttempts = 20; // Try for 10 seconds (20 * 500ms)

        var autoStartInterval = setInterval(function() {
            autoStartAttempts++;

            // Check if webcam is connected and not live yet
            if (isWebcamServerConnected() && typeof rtmpURLEncrypted !== 'undefined' && !isLive) {
                console.log('Quick Go Live: Auto-starting live stream...');
                startWebcamLive(rtmpURLEncrypted);
                clearInterval(autoStartInterval);
            } else if (autoStartAttempts >= maxAutoStartAttempts) {
                console.log('Quick Go Live: Auto-start timeout reached');
                avideoToastWarning('<?php echo __('Please click the Go Live button to start streaming'); ?>');
                clearInterval(autoStartInterval);
            }
        }, 500);
        <?php endif; ?>
    });
</script>
<?php

$_page->print();
?>
