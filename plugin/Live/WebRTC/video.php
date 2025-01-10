<link href="<?php echo getURL('plugin/Live/WebRTC/style.css'); ?>" rel="stylesheet" type="text/css" />
<div id="liveIndicator" class="showWhenIsLive indicator">LIVE</div>
<div id="offLineIndicator" class="showWhenIsNotLive indicator">OFFLINE</div>
<video id="localVideo" autoplay muted playsinline class="img-responsive center-block" style="border: 1px solid #ddd; width: 100%;"></video>
<script>
    // Send streamKey to the server when joining
    var rtmpURL = '<?php echo Live::getRTMPLink(User::getId(), 'Webcam'); ?>';
</script>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/Live/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/Live/WebRTC/events.js'); ?>" type="text/javascript"></script>