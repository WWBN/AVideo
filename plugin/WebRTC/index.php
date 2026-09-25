<?php
require_once __DIR__ . '/../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
$_GET['avideoIframe'] = 1;
$_page = new Page(array('Webcam'), 'quickGoLiveMode');
?>
<div class="container-fluid webrtc-page">
    <?php include __DIR__ . '/panel.php'; ?>
</div>
<script>
    $(document).ready(function () {
        // Use the desktop side panel immediately; keep the phone's preview unobstructed.
        if (window.matchMedia('(min-width: 768px) and (min-height: 500px)').matches) {
            document.getElementById('webrtcChatPanel').open = true;
        }
    });
</script>
<?php $_page->print(); ?>
