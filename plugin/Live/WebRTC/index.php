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
    <style>
        #localVideo {
            height: 100%;
        }
    </style>
    <?php
    include __DIR__ . '/video.php';
    ?>
    <div id="webcamMedias">
        <?php
        ?>
    </div>
    <div id="webcamMediaControls">
        <?php
        include __DIR__ . '/panel.medias.php';
        include __DIR__ . '/panel.buttons.php';
        ?>
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