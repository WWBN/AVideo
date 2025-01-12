<?php
require_once __DIR__ . '/../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
$_page = new Page(array('Webcam'));
if (empty($_REQUEST['avideoIframe'])) {
?>
    <div class="container">
        <?php
        include __DIR__ . '/panel.php';
        ?>
    </div>
<?php
} else {
?>
    <?php
    include __DIR__ . '/video.php';
    ?>
    <div id="webcamMedias">
        <?php
        ?>
    </div>
    <div id="webcamMediaControls" class="showWhenWebRTCIsConnected">
        <?php
        include __DIR__ . '/panel.medias.php';
        include __DIR__ . '/panel.buttons.php';
        ?>
    </div>
    <div id="webcamMediaControlsMessage" class="alert alert-danger showWhenWebRTCIsNotConnected text-center">
        <div class="fa-3x">
            <i class="fa-solid fa-triangle-exclamation fa-fade"></i>
        </div>
        <strong>Error:</strong> Unable to connect to the Webcam server.<br>
        <span>Please verify the server status and resolve any issues.</span>
    </div>

    <script>
        $(document).ready(function() {
            startWebRTC();
        });
    </script>
<?php
}
$_page->print();
?>