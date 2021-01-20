<?php
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('Socket');
if(!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))){
?>
<div style="position: fixed; top: 60px; left: 50px; background-color: rgba(255,255,255,0.8); border: solid 2px black; padding: 5px; z-index: 9999;">
    <div><b>users_id</b> <span class="socket_users_id">0</span></div>
    <div><b>ResourceId</b> <span class="socket_resourceId">0</span></div>
    <div><b>Total Devices Online</b> <span class="total_devices_online">0</span></div>
    <div><b>Total Users Online</b> <span class="total_users_online">0</span></div>
    <div><b>onlineOnThisVideo</b> <span class="total_on_same_video">0</span></div>
    <div><b>onlineOnThisLive</b> <span class="total_on_same_live">0</span></div>
</div>
<?php
}
?>
<script>
    var webSocketToken = '<?php echo getEncryptedInfo(0); ?>';
    var webSocketURL = '<?php echo Socket::getWebSocketURL(true); ?>';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js" type="text/javascript"></script>
