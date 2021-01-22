<?php
if (isBot()) {
    return false;
}
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('Socket');
if (!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))) {
    ?>
    <style>
        #socket_info_container div{
            text-shadow: 0 0 2px #FFF;
            padding: 5px;
            font-size: 11px;
        }
        #socket_info_container div.socketItem span{
            float: right;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            background-color: #777;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 10px;
            text-shadow: none;
            padding: 2px 3px;
        }
        #socket_info_container{
            border-radius: 5px;
            border: 2px solid #777;
            position: fixed; 
            top: 60px; 
            left: 50px; 

            background-color: rgba(255,255,255,0.7);
            color: #000;

            -webkit-transition: background  0.5s ease-in-out;
            -moz-transition: background  0.5s ease-in-out;
            -ms-transition: background  0.5s ease-in-out;
            -o-transition: background  0.5s ease-in-out;
            transition: background  0.5s ease-in-out;
            opacity: 1;
            cursor: move;

            -moz-box-shadow:    0 0 10px #000000;
            -webkit-box-shadow: 0 0 10px #000000;
            box-shadow:         0 0 10px #000000;
            z-index: 9999;

        }
        #socket_info_container:hover{
            opacity: 1;
            background-color: rgba(255,255,255,1);
        }

        #socket_info_container.disconnected div{
            color: #00000077;
        }
        #socket_info_container.disconnected .socketItem span{
            opacity: 0.5;
        }
        .socketTitle, .socketTitle span{
            background-color: #FFF;
            text-align: center;
            font-size: 14px;
            width: 100%;
        }
    </style>

    <div id="socket_info_container" class="socketStatus disconnected" >
        <div class="socketTitle">
            <?php 
                echo getSocketConnectionLabel();
            ?>
        </div>
        <div class="socketItem" ><i class="fas fa-user"></i> Your User ID <span class="socket_users_id">0</span></div>
        <div class="socketItem" ><i class="fas fa-id-card"></i> Socket ResourceId <span class="socket_resourceId">0</span></div>
        <div class="socketItem" ><i class="fas fa-network-wired"></i> Total Different Devices <span class="total_devices_online">0</span></div>
        <div class="socketItem" ><i class="fas fa-users"></i> Total Users Online <span class="total_users_online">0</span></div>
        <div class="socketItem" ><i class="far fa-play-circle"></i> Users online on same video as you <span class="total_on_same_video">0</span></div>
        <div class="socketItem" ><i class="fas fa-podcast"></i> Users online on same live as you <span class="total_on_same_live">0</span></div>
        <div class="socketItem" ><i class="fas fa-podcast"></i> Users online on same live link as you <span class="total_on_same_livelink">0</span></div>
    </div>
    <script>
        if (typeof $("#socket_info_container").draggable === 'function') {
            $("#socket_info_container").draggable();
        }
    </script>
    <?php
}
?>
<script>
    var webSocketToken = '<?php echo getEncryptedInfo(0); ?>';
    var webSocketURL = '<?php echo Socket::getWebSocketURL(); ?>';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js?<?php echo filectime($global['systemRootPath'].'plugin/Socket/script.js'); ?>" type="text/javascript"></script>
