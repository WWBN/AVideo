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
            border: 2px solid #777;
            position: fixed; 
            top: <?php echo $socket_info_container_top; ?>px; 
            left: <?php echo $socket_info_container_left; ?>px; 

            background-color: rgba(255,255,255,0.7);
            color: #000;

            -webkit-transition: background  0.5s ease-in-out;
            -moz-transition: background  0.5s ease-in-out;
            -ms-transition: background  0.5s ease-in-out;
            -o-transition: background  0.5s ease-in-out;
            transition: background  0.5s ease-in-out;
            opacity: 1;

            -moz-box-shadow:    0 0 10px #000000;
            -webkit-box-shadow: 0 0 10px #000000;
            box-shadow:         0 0 10px #000000;
            z-index: 1000;

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

        #socketBtnMaximize{
            display: none;
        }
        #socket_info_container.socketMinimized .socketItem, #socket_info_container.socketMinimized #socketBtnMinimize{
            display: none;
        }
        #socket_info_container.socketMinimized #socketBtnMaximize{
            display: block;
        }
        .socketTitle, .socketTitle span{
            text-align: center;
            font-size: 14px;
            width: 100%;
            cursor: move;
        }
        .socketUserName{
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

        .socketUserDiv .fa-caret-up{
            display: none;
        }

        .socketUserDiv.visible .fa-caret-up{
            display: inline-block;
        }
        .socketUserDiv.visible .fa-caret-down{
            display: none;
        }
        .socketUserDiv .socketUserPages{
            display: none;
        }
        .socketUserDiv.visible .socketUserPages{
            display: block;
        }
        .socketButtons{
            margin-left: 10px;
        }
        .socket_disconnected{
            display: none;
        }
        .disconnected .socket_connected{
            display: none;
        }
        .disconnected .socket_disconnected{
            display: block;
        }
        .socket_connected, .socket_disconnected{
            font-weight: bold;
        }
        .socket_connected{
            color: #FFF;
            animation: socketGlow 1s infinite alternate;
        }
        @keyframes socketGlow {
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
    </style>
    <div id="socket_info_container" class="socketStatus disconnected <?php echo $socket_info_container_class; ?>" >
        <div class=" ">
            <div class="pull-left">
                <?php
                echo getSocketConnectionLabel();
                ?>
            </div>
            <div class="pull-right socketButtons">
                <button class="btn btn-default btn-xs" id="socketBtnMinimize">
                    <i class="fas fa-window-minimize"></i>
                </button>
                <button class="btn btn-default btn-xs maximize" id="socketBtnMaximize">
                    <i class="far fa-window-maximize"></i>
                </button>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="socketItem" ><i class="fas fa-user"></i> Your User ID <span class="socket_users_id">0</span></div>
        <div class="socketItem" ><i class="fas fa-id-card"></i> Socket ResourceId <span class="socket_resourceId">0</span></div>
        <div class="socketItem" ><i class="fas fa-network-wired"></i> Total Different Devices <span class="total_devices_online">0</span></div>
        <div class="socketItem" ><i class="fas fa-users"></i> Total Users Online <span class="total_users_online">0</span></div>
        <div class="socketItem" id="socketUsersURI">    
        </div>
    </div>
    <script>
        $(document).ready(function () {
            if (typeof $("#socket_info_container").draggable === 'function') {
                $("#socket_info_container").draggable({
                    stop: function (event, ui) {
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
            }else{
                $("#socket_info_container").hide();
            }
            $("#socketBtnMinimize").click(function () {
                socketInfoMinimize();
            });
            $("#socketBtnMaximize").click(function () {
                socketInfoMaximize();
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
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/YPTSocket/script.js?<?php echo filectime($global['systemRootPath'] . 'plugin/YPTSocket/script.js'); ?>" type="text/javascript"></script>
