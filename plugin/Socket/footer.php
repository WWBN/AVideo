<?php
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('Socket');
if (!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))) {
    ?>
    <style>
        #socket_info_container{
            position: fixed; 
            top: 60px; 
            left: 50px; 
            z-index: 9999;

            -webkit-transition: opacity 0.3s ease-in-out;
            -moz-transition: opacity 0.3s ease-in-out;
            -ms-transition: opacity 0.3s ease-in-out;
            -o-transition: opacity 0.3s ease-in-out;
            transition: opacity 0.3s ease-in-out;
            opacity: 0.5;
            cursor: w-resize;

            -moz-box-shadow:    0 0 10px #000000;
            -webkit-box-shadow: 0 0 10px #000000;
            box-shadow:         0 0 10px #000000;

        }
        #socket_info_container .badge{
            margin-left: 10px;

        }
        #socket_info_container:hover{
            opacity: 1;
        }
    </style>
    
    <ul class="list-group" id="socket_info_container" >
        <li class="list-group-item">users_id <span class="badge socket_users_id">0</span></li>
        <li class="list-group-item">ResourceId <span class="badge socket_resourceId">0</span></li>
        <li class="list-group-item">Total Devices Online <span class="badge total_devices_online">0</span></li>
        <li class="list-group-item">Total Users Online <span class="badge total_users_online">0</span></li>
        <li class="list-group-item">onlineOnThisVideo <span class="badge total_on_same_video">0</span></li>
        <li class="list-group-item">onlineOnThisLive <span class="badge total_on_same_live">0</span></li>
    </ul>
    <script>
        $("#socket_info_container").draggable({axis: "x"});
    </script>
    <?php
}
?>
<script>
    var webSocketToken = '<?php echo getEncryptedInfo(0); ?>';
    var webSocketURL = '<?php echo Socket::getWebSocketURL(true); ?>';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js" type="text/javascript"></script>
