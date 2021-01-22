<?php
if(isBot()){
    return false;
}
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('Socket');
if (!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))) {
    $help = "Your socket server is NOT running, to start your socket server, please go to your streamer server SSH terminal and run the code <br>"
            . "<code>nohup php {$global['systemRootPath']}plugin/Socket/server.php &</code>";
    ?>
    <style>
        #socket_info_container div{
            text-shadow: 0 0 2px #FFF;
            padding: 5px;
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

            -webkit-transition: opacity 0.3s ease-in-out;
            -moz-transition: opacity 0.3s ease-in-out;
            -ms-transition: opacity 0.3s ease-in-out;
            -o-transition: opacity 0.3s ease-in-out;
            transition: opacity 0.3s ease-in-out;
            opacity: 1;
            cursor: w-resize;

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

        #socket_info_container .socket_connected, #socket_info_container .socket_disconnected{
            font-weight: bold;
            min-width: 200px;
            text-align: center;
            background-color: #FFF;
        }
        #socket_info_container .socket_connected{
            display: block;
            color: #FFF;
            animation: glow 1s infinite alternate;
        }
        @keyframes glow {
            from {
                color: #DFD;
                text-shadow: 
                    0 0 1px #050, 
                    0 0 2px #070, 
                    0 0 3px #670, 
                    0 0 4px #670;
            }
            to {
                color: #FFF;
                text-shadow: 
                    0 0 2px #020,
                    0 0 5px #090, 
                    0 0 10px #0F0, 
                    0 0 15px #BF0, 
                    0 0 20px #B6FF00;
            }
        }
        #socket_info_container .socket_disconnected{
            display: none;
            color: #777;
        }
        #socket_info_container.disconnected .socket_connected{
            display: none;
        }
        #socket_info_container.disconnected .socket_disconnected{
            display: block;
        }
    </style>

    <div id="socket_info_container" class="disconnected" >
        <div class="socket_disconnected" data-toggle="tooltip" title="Your User ID" data-placement="right">
            <i class="fas fa-times"></i> Socket Disconnected<br>
        </div>
        <div class="socket_connected">
            <i class="fas fa-check"></i> Socket Connected
        </div>
        <div class="socketItem" ><i class="fas fa-user"></i> Your User ID <span class="socket_users_id">0</span></div>
        <div class="socketItem" ><i class="fas fa-id-card"></i> Socket ResourceId <span class="socket_resourceId">0</span></div>
        <div class="socketItem" ><i class="fas fa-network-wired"></i> Total Different Devices <span class="total_devices_online">0</span></div>
        <div class="socketItem" ><i class="fas fa-users"></i> Total Users Online <span class="total_users_online">0</span></div>
        <div class="socketItem" ><i class="far fa-play-circle"></i> Users online on same video as you <span class="total_on_same_video">0</span></div>
        <div class="socketItem" ><i class="fas fa-podcast"></i> Users online on same live as you <span class="total_on_same_live">0</span></div>
    </div>
    <script>
        if (typeof $("#socket_info_container").draggable === 'function') {
            $("#socket_info_container").draggable({axis: "x"});
        }
        var oldSocketStatus = '';
        var socketStatusTimeout;
        setInterval(function () {
            if (typeof conn != 'undefined') {
                var newSocketStatus;
                if (avideoSocketIsActive()) {
                    $("#socket_info_container").removeClass('disconnected');
                    newSocketStatus = 'connected';
                } else {
                    $("#socket_info_container").addClass('disconnected');
                    newSocketStatus = 'disconnected';
                }
                if (oldSocketStatus != newSocketStatus) {
                    avideoToast('Socket ' + newSocketStatus);
                    if (newSocketStatus == 'disconnected') {
                        socketStatusTimeout = setTimeout(function(){avideoAlertInfo('<?php echo $help; ?>');},3000);
                    } else {
                        clearTimeout(socketStatusTimeout);
                    }
                }
                oldSocketStatus = newSocketStatus;
            }

        }, 1000);
    </script>
    <?php
}
?>
<script>
    var webSocketToken = '<?php echo getEncryptedInfo(0); ?>';
    var webSocketURL = '<?php echo Socket::getWebSocketURL(); ?>';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js" type="text/javascript"></script>
