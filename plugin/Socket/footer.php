<?php
$refl = new ReflectionClass('SocketMessageType');
?>
<div style="position: fixed; top: 60px; left: 20px; background: #CCC; border: solid 2px black; padding: 5px;">
    <div><b>users_id</b> <span class="socket_users_id">0</span></div>
    <div><b>ResourceId</b> <span class="socket_resourceId">0</span></div>
    <div><b>totalDevicesOnline</b> <span class="total_devices_online">0</span></div>
    <div><b>onlineOnThisVideo</b> <span class="total_on_same_video">0</span></div>
    <div><b>onlineOnThisLive</b> <span class="total_on_same_live">0</span></div>
</div>
<script>
    var webSocketToken = '<?php echo getEncryptedInfo(0); ?>';
    var webSocketURL = '<?php echo Socket::getWebSocketURL(true); ?>';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js" type="text/javascript"></script>
