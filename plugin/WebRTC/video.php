<?php
require_once __DIR__ . '/functions.php';
global $global;
$global['doNotLoadPlayer'] = 1;
$forceIndex = 'Webcam';
$rtmpURL = Live::getRTMPLink(User::getId(), $forceIndex);
$key = Live::getKeyFromUser(User::getId());
?>
<script class="doNotSepareteTag">
    // Send streamKey to the server when joining
    var rtmpURL = '<?php echo $rtmpURL; ?>';
    var rtmpURLEncrypted = '<?php echo encrypt_data($rtmpURL, $global['saltV2']); ?>';
    var WebRTC2RTMPURL = '<?php echo getWebRTC2RTMPURL(); ?>';
</script>
<link href="<?php echo getURL('plugin/WebRTC/style.css'); ?>" rel="stylesheet" type="text/css" />
<div id="offLineIndicator" 
     class="showWhenWebRTCIsNotConnected indicator" 
     style="display: none;" 
     title="<?php echo __('You are offline. Please check your connection.'); ?>" 
     data-toggle="tooltip">
    <div>
        <i class="fa-solid fa-wifi"></i>
        <i class="fa-solid fa-slash" style="position: absolute;left: 4px;top: 3px;"></i>
    </div>
</div>
<div id="onLineIndicator" 
     class="showWhenWebRTCIsConnected showWhenIsNotLive indicator" 
     style="display: none;" 
     title="<?php echo __('You are online and ready to go live.'); ?>" 
     data-toggle="tooltip">
    <i class="fa-solid fa-wifi"></i>
</div>
<div id="liveIndicator" 
     class="showWhenIsLive indicator" 
     style="display: none;" 
     title="<?php echo __('You are live now.'); ?>" 
     data-toggle="tooltip">
    <i class="fa-solid fa-wifi"></i>
</div>
<video id="localVideo" autoplay muted playsinline></video>
<iframe id="webrtcChat" class="transparent-iframe" src="<?php echo $global['webSiteRootURL']; ?>plugin/MobileYPT/index.php?key=<?php echo $key; ?>&live_index=<?php echo $forceIndex; ?>"></iframe>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/events.js'); ?>" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        requestNotifications();
    });
</script>