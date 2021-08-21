<?php
if (isBot()) {
    return false;
}
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
if (!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))) {
    $socket_info_container_class = '';
    $socket_info_container_top = 60;
    $socket_info_container_left = 50;
    if (isset($_COOKIE['socketInfoMinimized'])) {
        $socket_info_container_class = 'socketMinimized';
    }
    if (isset($_COOKIE['socketInfoPositionTop'])) {
        $socket_info_container_top = $_COOKIE['socketInfoPositionTop'];
    }
    if (isset($_COOKIE['socketInfoPositionLeft'])) {
        $socket_info_container_left = $_COOKIE['socketInfoPositionLeft'];
    }
    $command = "sudo nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &";
    ?>
    <style>
        #socket_info_container>div{
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
            border: 2px solid transparent;
            position: fixed; 
            top: <?php echo $socket_info_container_top; ?>px; 
            left: <?php echo $socket_info_container_left; ?>px; 

            background-color: rgba(255,255,255,0);
            color: #000;

            -webkit-transition: background-color  0.5s linear;
            -moz-transition: background-color  0.5s linear;
            -ms-transition: background-color  0.5s linear;
            -o-transition: background-color  0.5s linear;
            transition: background-color  0.5s linear;
            transition: box-shadow 0.5s ease-in-out;
            z-index: 1000;
            -moz-box-shadow:    0 0 0 #00000000;
            -webkit-box-shadow: 0 0 0 #00000000;
            box-shadow:         0 0 0 #00000000;

        }
        #socket_info_container:hover{
            background-color: rgba(255,255,255,1);
            -moz-box-shadow:    0 0 10px #000000;
            -webkit-box-shadow: 0 0 10px #000000;
            box-shadow:         0 0 10px #000000;
            border: 2px solid #777;
        }

        #socket_info_container div{
            color: #00000077;
        }
        #socket_info_container .socketItem span{
            opacity: 0.5;
        }

        #socket_info_container.socketMinimized .socketItem{
            display: none;
        }

        #socket_info_container .socketItem {
            background-color: rgba(255,255,255,0.8);
        }
        .socketTitle, .socketTitle span{
            text-align: center;
            font-size: 14px;
            width: 100%;
            cursor: move;
        }
        #socket_info_container > div.socketHeader{
            padding: 2px 15px 2px 5px;
        }
        .socketHeader, .socketUserName{
            cursor: pointer;
        }
        #socket_info_container > div.clearfix{
            cursor: move;
        }
        #socketUsersURI{
            max-height: 300px;
            overflow: auto;
        }
        #socketUsersURI a{
            text-overflow: ellipsis;
            overflow: hidden; 
            max-width: 300px;
        }
        .socketItem img{
            height: 20px;
            width: 20px;
            margin: 2px 5px 2px 0;
            display: inline;
        }
        #socket_info_container > div:last-child{
            margin-top: 5px;
            border-top: solid 1px #000;
        }
        .hideNotConected, .hideNotDisconnected,
        .socketUserDiv .socketUserPages,
        .socketUserDiv .fa-caret-up,
        .socketUserDiv.visible .fa-caret-down{
            display: none;
        }
        .socket_connected .hideNotConected,
        .socketUserDiv.visible .socketUserPages,
        .socket_disconnected .hideNotDisconnected{
            display: block;
        }
        
        .socketUserDiv.visible .fa-caret-up{
            display: inline-block;
        }
    </style>
    <div id="socket_info_container" class="socket_info <?php echo $socket_info_container_class; ?>" >
        <div class="socketHeader">
            <?php
            echo getSocketConnectionLabel();
            ?>
        </div>
        <div class="socketItem hideNotDisconnected" ><button class="btn btn-xs btn-block btn-default" onclick="copyToClipboard('<?php echo addcslashes($command,'\\'); ?>')">Copy code to run on terminal</button></div>
        <div class="socketItem hideNotConected" ><i class="fas fa-user"></i> Your User ID <span class="socket_users_id">0</span></div>
        <div class="socketItem hideNotConected" ><i class="fas fa-id-card"></i> Socket ResourceId <span class="socket_resourceId">0</span></div>
        <div class="socketItem hideNotConected" ><i class="fas fa-network-wired"></i> Total Different Devices <span class="total_devices_online">0</span></div>
        <div class="socketItem hideNotConected" ><i class="fas fa-users"></i> Total Users Online <span class="total_users_online">0</span></div>
        <div class="socketItem hideNotConected" id="socketUsersURI">    
        </div>
    </div>
    <script>
        var socket_info_container_draging = false;
        $(document).ready(function () {
            if (typeof $("#socket_info_container").draggable === 'function') {
                $("#socket_info_container").draggable({
                    start: function (event, ui) {
                        socket_info_container_draging = true;
                    },
                    stop: function (event, ui) {
                        setTimeout(function(){socket_info_container_draging = false;},100);
                        var currentPos = $(this).position();
                        Cookies.set('socketInfoPositionTop', currentPos.top, {
                            path: '/',
                            expires: 365
                        });
                        Cookies.set('socketInfoPositionLeft', currentPos.left, {
                            path: '/',
                            expires: 365
                        });
                    }
                });
            } else {
                $("#socket_info_container").hide();
            }
            $(".socketHeader").click(function () {
                socketInfoToogle()
            });
            checkSocketInfoPosition();
        });

        function socketInfoMinimize() {
            $("#socket_info_container").addClass('socketMinimized');

            Cookies.set('socketInfoMinimized', 1, {
                path: '/',
                expires: 365
            });
        }
        function socketInfoMaximize() {
            $("#socket_info_container").removeClass('socketMinimized');
            Cookies.set('socketInfoMinimized', 0, {
                path: '/',
                expires: 365
            });
        }
        function socketInfoToogle() {
            if(socket_info_container_draging){
                return false;
            }
            if ($("#socket_info_container").hasClass('socketMinimized')) {
                socketInfoMaximize();
            } else {
                socketInfoMinimize();
            }
        }

        function checkSocketInfoPosition() {
            var currentPos = $('#socket_info_container').position();
            var maxLeft = $(window).width() - $('#socket_info_container').width();
            var maxTop = $(window).height() - $('#socket_info_container').height();

            if (currentPos.top < 60 || currentPos.left < 60 || currentPos.top > maxTop || currentPos.left > maxLeft) {
                $('#socket_info_container').css('top', '60px');
                $('#socket_info_container').css('left', '60px');
            }
        }

        function socketUserNameToggle(socketUserDivID) {
            var isVisible = $(socketUserDivID).find('.socketUserPages').is(":visible");
            if (isVisible) {
                $(socketUserDivID).removeClass('visible');
            } else {
                $(socketUserDivID).addClass('visible');
            }
            Cookies.set(socketUserDivID, !isVisible, {
                path: '/',
                expires: 365
            });
        }

    </script>
    <?php
}
?>
<script>
    var webSocketSelfURI = '<?php echo getSelfURI(); ?>';
    var webSocketVideos_id = '<?php echo getVideos_id(); ?>';
    var webSocketLiveKey = '<?php echo json_encode(isLive()); ?>';
    var webSocketServerVersion = '<?php echo YPTSocket::getServerVersion(); ?>';
    var webSocketToken = '';
    var webSocketURL = '';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;


    function onUserSocketConnect(response) {
        try {
<?php echo AVideoPlugin::onUserSocketConnect(); ?>
        } catch (e) {
            console.log('onUserSocketConnect:error', e.message);
        }
    }

    function onUserSocketDisconnect(response) {
        try {
<?php echo AVideoPlugin::onUserSocketDisconnect(); ?>
        } catch (e) {
            console.log('onUserSocketConnect:error', e.message);
        }
    }
</script>
<script src="<?php echo getCDN(); ?>plugin/YPTSocket/script.js?<?php echo filectime($global['systemRootPath'] . 'plugin/YPTSocket/script.js'); ?>" type="text/javascript"></script>
