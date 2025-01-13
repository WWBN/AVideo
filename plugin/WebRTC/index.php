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
    });
</script>
<?php

$_page->print();
?>