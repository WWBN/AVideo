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
<link href="<?php echo getURL('plugin/WebRTC/studio.css'); ?>" rel="stylesheet" type="text/css" />
<div class="webrtc-feedback">
    <div id="webrtcStatus" class="alert alert-info webrtc-status" role="status" aria-live="polite" aria-atomic="true">
        <strong id="webrtcStateLabel"><?php echo __('Preview only'); ?></strong>
        <p id="webrtcStateMessage"><?php echo __('Prepare your camera and microphone. Only you can see this preview.'); ?></p>
    </div>
    <?php echo getTourHelpButton('plugin/WebRTC/studio.help.json', 'btn btn-default btn-sm'); ?>
</div>
<div class="webrtc-preview">
    <video id="localVideo" autoplay muted playsinline aria-label="<?php echo __('Camera preview'); ?>"></video>
    <div id="webrtcPreviewPlaceholder" class="webrtc-placeholder">
        <i class="fa-solid fa-video" aria-hidden="true"></i>
        <span><?php echo __('Your camera preview will appear here'); ?></span>
    </div>
    <span id="webrtcPreviewBadge" class="webrtc-preview-badge"><?php echo __('Not live'); ?></span>
</div>

<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/events.js'); ?>" type="text/javascript"></script>
