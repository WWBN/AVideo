<?php
require_once __DIR__ . '/../../../videos/configuration.php';
require_once __DIR__ . '/../functions.php';
AVideoPlugin::loadPlugin("Live");
$_GET['avideoIframe'] = 1;
$_page = new Page(array('Call'));
if(empty($_REQUEST['roomId'])){
    forbiddenPage('Room ID is required');
}
?>
<link href="<?php echo getURL('plugin/WebRTC/call/style.css'); ?>" rel="stylesheet" type="text/css" />
<script class="doNotSepareteTag">
    var WebRTC2RTMPURL = '<?php echo getWebRTC2RTMPURL(); ?>';
    const roomId = '<?php echo md5($_REQUEST['roomId']); ?>';
</script>
<div id="videoContainer" class="video-grid">
</div>
<div id="localVideoContainer">
</div>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/call/events.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script>
    $(document).ready(function() {});
</script>
<?php
$_page->print();
?>