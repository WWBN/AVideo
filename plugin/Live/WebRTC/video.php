<?php
require_once __DIR__.'/functions.php';

// Use parse_url to extract components of the URL
$parsedUrl = parse_url($global['webSiteRootURL']);

// Get the domain (host) from the parsed URL
$domain = $parsedUrl['host'] ?? null;

?>
<script class="doNotSepareteTag">
    // Send streamKey to the server when joining
    var rtmpURLEncrypted = '<?php echo encrypt_data(Live::getRTMPLink(User::getId(), 'Webcam'), $global['saltV2']); ?>';
    var WebRTC2RTMPURL = 'https://<?php echo $domain; ?>:3000';
</script>
<link href="<?php echo getURL('plugin/Live/WebRTC/style.css'); ?>" rel="stylesheet" type="text/css" />
<div id="liveIndicator" class="showWhenIsLive indicator" style="display: none;">LIVE</div>
<div id="offLineIndicator" class="showWhenIsNotLive indicator" style="display: none;">OFFLINE</div>
<video id="localVideo" autoplay muted playsinline></video>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/Live/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/Live/WebRTC/events.js'); ?>" type="text/javascript"></script>