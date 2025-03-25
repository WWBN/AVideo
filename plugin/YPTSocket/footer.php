<?php
global $global;
if (isConfirmationPage()) {
    echo '<!-- isConfirmationPage socket_info_container -->';
    return false;
}
if (isBot()) {
    echo '<!-- isBot socket_info_container -->';
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
    $command = "sudo " . YPTSocket::getStartServerCommand();
?>
    <style>
        #socket_info_container>div {
            padding: 5px;
            font-size: 11px;
        }

        #socket_info_container div.socketItem span {
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

        #socket_info_container {
            border-radius: 5px;
            border: 2px solid transparent;
            position: fixed;
            top: <?php echo $socket_info_container_top; ?>px;
            left: <?php echo $socket_info_container_left; ?>px;

            background-color: rgba(255, 255, 255, 0);
            color: #000;

            -webkit-transition: background-color 0.5s linear;
            -moz-transition: background-color 0.5s linear;
            -ms-transition: background-color 0.5s linear;
            -o-transition: background-color 0.5s linear;
            transition: background-color 0.5s linear;
            transition: box-shadow 0.5s ease-in-out;
            z-index: 1050;
            -moz-box-shadow: 0 0 0 #00000000;
            -webkit-box-shadow: 0 0 0 #00000000;
            box-shadow: 0 0 0 #00000000;

        }

        #socket_info_container:hover {
            background-color: rgba(255, 255, 255, 1);
            -moz-box-shadow: 0 0 10px #000000;
            -webkit-box-shadow: 0 0 10px #000000;
            box-shadow: 0 0 10px #000000;
            border: 2px solid #777;
        }

        #socket_info_container div {
            color: #00000077;
        }

        #socket_info_container.socketMinimized .socketItem {
            display: none;
        }

        .socketTitle,
        .socketTitle span {
            text-align: center;
            font-size: 14px;
            width: 100%;
            cursor: move;
        }

        #socket_info_container>div.socketHeader {
            padding: 2px 15px 2px 5px;
        }

        .socketHeader,
        .socketUserName {
            cursor: pointer;
        }

        #socket_info_container>div.clearfix {
            cursor: move;
        }

        #socketUsersURI {
            max-height: 300px;
            overflow: auto;
        }

        #socketUsersURI a {
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 300px;
        }

        .socketItem img {
            height: 20px;
            width: 20px;
            margin: 2px 5px 2px 0;
            display: inline;
        }

        #socket_info_container>div:last-child {
            margin-top: 5px;
            border-top: solid 1px #000;
        }

        .hideNotConected,
        .hideNotDisconnected,
        .socketUserDiv .socketUserPages,
        .socketUserDiv .fa-caret-up,
        .socketUserDiv.visible .fa-caret-down {
            display: none;
        }

        .socket_connected .hideNotConected,
        .socketUserDiv.visible .socketUserPages,
        .socket_disconnected .hideNotDisconnected {
            display: block;
        }

        .socketUserDiv.visible .fa-caret-up {
            display: inline-block;
        }

        #socket_info_container {
            border: solid 2px #CCCCCC11;
        }
    </style>
    <div id="socket_info_container" class="socket_info blur-background <?php echo $socket_info_container_class; ?> <?php echo getCSSAnimationClassAndStyle('animate__bounceIn', 'socket_info'); ?>" style="display: none;">
        <div class="socketHeader ">
            <?php
            echo getSocketConnectionLabel();
            ?>
        </div>
        <div class="socketItem hideNotDisconnected <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'socket'); ?>">
            <button class="btn btn-xs btn-block btn-default" onclick="copyToClipboard('<?php echo addcslashes($command, '\\'); ?>')">Copy code to run on terminal</button>
            <button class="btn btn-xs btn-block btn-primary" onclick="socketConnect()">Try again</button>
        </div>

        <div class="socketItem hideNotConected <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'socket'); ?>">
            <i class="fa-solid fa-code-compare"></i> Version <span class="webSocketServerVersion"></span>
        </div>

        <div class="socketItem hideNotConected <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'socket'); ?>">
            <i class="fa-solid fa-memory"></i> Memory <span class="socket_mem">0 bytes</span>
        </div>

        <div class="socketItem hideNotConected <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'socket'); ?>"  data-toggle="tooltip"
            title="Number of unique users with active WebSocket connections. One connection per real device. Iframes in the same browser are not counted twice.">
            <i class="fas fa-network-wired"></i> Unique Devices Online <span class="total_devices_online">0</span>
        </div>

        <div class="socketItem hideNotConected <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'socket'); ?>"  data-toggle="tooltip"
            title="Total number of active WebSocket connections. A user with multiple devices or multiple tabs/iframes will be counted more than once.">
            <i class="fas fa-users"></i> Total Connections <span class="total_users_online">0</span>
        </div>

        <div class="socketItem hideNotConected <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'socket'); ?>" id="socketUsersURI">
        </div>


        <button onclick="avideoAjax(webSiteRootURL+'plugin/YPTSocket/restart.json.php', {});" class="socketItem btn btn-danger btn-sm btn-xs btn-block"><i class="fas fa-power-off"></i> Restart</button>
    </div>
    <script>
        var socket_info_container_draging = false;
        $(document).ready(function() {
            if (typeof $("#socket_info_container").draggable === 'function') {
                $("#socket_info_container").draggable({
                    start: function(event, ui) {
                        socket_info_container_draging = true;
                    },
                    stop: function(event, ui) {
                        setTimeout(function() {
                            socket_info_container_draging = false;
                        }, 100);
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
            $(".socketHeader").click(function() {
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
            if (socket_info_container_draging) {
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
    var schedulerIsActive = <?php echo class_exists('Scheduler') && Scheduler::isActive() ? 1 : 0; ?>;
    var webSocketToken = '';
    var webSocketURL = '';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;

    $(document).ready(function() {
        <?php
        if (!isEmbed()) {
        ?>
            if (!inIframe()) {
                $('#socket_info_container').fadeIn();
            }
        <?php
        }
        ?>
    });

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
            console.log('onUserSocketDisconnect:error', e.message);
        }
    }
</script>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/YPTSocket/script.js'); ?>" type="text/javascript"></script>
