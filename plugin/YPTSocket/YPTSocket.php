<?php

/**
 * to stop
 * find who is using the port 
 * * lsof -i :25
 * Kill it
 * * kill -9 PID
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

class YPTSocket extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = '<span class="socket_info" style="float: right; margin:0 10px;">' . getSocketConnectionLabel() . '</span><script>if(isSocketActive()){setSocketIconStatus(\'connected\');}</script> ';
        $desc .= "Socket Plugin, WebSockets allow for a higher amount of efficiency compared to REST because they do not require the HTTP request/response overhead for each message sent and received<br>";
        $desc .= "<br>To start it on server now <code>sudo nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &</code>";
        $desc .= "<br>To test use <code>php {$global['systemRootPath']}plugin/YPTSocket/test.php</code>";
        $desc .= "<br>To start it on server reboot add it on your crontab (Ubuntu 18+) <code>sudo crontab -eu root</code> than add this code on the last line <code>@reboot sleep 60;nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &</code>";
        $desc .= "<br>If you use Certbot to renew your SSL use (Ubuntu 18+) <code>sudo crontab -eu root</code> than add this code on the last line <code>0 1 * * * nohup php {$global['systemRootPath']}plugin/YPTSocket/serverCertbot.php &</code>";
        $help = "<br>run this command start the server <small><a href='https://github.com/WWBN/AVideo/wiki/Socket-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc . $help;
    }

    public function getName() {
        return "YPTSocket";
    }

    public function getUUID() {
        return "YPTSocket-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "2.2";
    }

    public static function getServerVersion() {
        return "5.2";
    }

    public function updateScript() {
        global $global;
        /*
          if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
          sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
          }
         * 
         */
        return true;
    }
    
    

    public static function getDataObjectAdvanced() {
        return array(
            'forceNonSecure',
            'uri',
            'debugSocket',
            'debugAllUsersSocket',
            'allow_self_signed',
            'forceNonSecure',
            'showTotalOnlineUsersPerVideo',
            'showTotalOnlineUsersPerLive',
            'showTotalOnlineUsersPerLiveLink',
            );
    }
    

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        $server_crt_file = "/etc/letsencrypt/live/{$host}/fullchain.pem";
        $server_key_file = "/etc/letsencrypt/live/{$host}/privkey.pem";

        $host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);

        $obj->forceNonSecure = false;
        self::addDataObjectHelper('forceNonSecure', 'Force not to use wss (non secure)', 'This is good if a reverse proxy is giving you a SSL');
        $obj->port = "2053";
        self::addDataObjectHelper('port', 'Server Port', 'You also MUST open this port on the firewall');
        $obj->uri = "0.0.0.0";
        self::addDataObjectHelper('uri', 'Server URI', 'You should not change it, only if you know what you are doing');
        $obj->host = $host;
        self::addDataObjectHelper('host', 'Server host', 'If your site is HTTPS make sure this host also handle the SSL connection');
        $obj->debugSocket = true;
        self::addDataObjectHelper('debugSocket', 'Show server debugger to admin', 'This will show a panel with some socket informations to the ADMIN user only');
        $obj->debugAllUsersSocket = false;
        self::addDataObjectHelper('debugAllUsersSocket', 'Show server debugger to all', 'Same as above but will show the panel to all users');
        $obj->server_crt_file = $server_crt_file;
        self::addDataObjectHelper('server_crt_file', 'SSL Certificate File', 'If your site use HTTPS, you MUST provide one');
        $obj->server_key_file = $server_key_file;
        self::addDataObjectHelper('server_key_file', 'SSL Certificate Key File', 'If your site use HTTPS, you MUST provide one');
        $obj->allow_self_signed = true;
        self::addDataObjectHelper('allow_self_signed', 'Allow self signed certs', 'Should be unchecked in production');

        $obj->showTotalOnlineUsersPerVideo = true;
        self::addDataObjectHelper('showTotalOnlineUsersPerVideo', 'Show Total Online Users Per Video');
        $obj->showTotalOnlineUsersPerLive = true;
        self::addDataObjectHelper('showTotalOnlineUsersPerLive', 'Show Total Online Users Per Live');
        $obj->showTotalOnlineUsersPerLiveLink = true;
        self::addDataObjectHelper('showTotalOnlineUsersPerLiveLink', 'Show Total Online Users Per LiveLink');
        $obj->enableCalls = false;
        self::addDataObjectHelper('enableCalls', 'Enable Meeting Calls', 'This feature requires the meet plugin enabled');

        return $obj;
    }

    public function getFooterCode() {
        self::getSocketJS();
        self::getCallerJS();
    }

    public static function getSocketJS() {
        global $global;
        include $global['systemRootPath'] . 'plugin/YPTSocket/footer.php';
    }

    public static function getCallerJS() {
        global $global;
        include $global['systemRootPath'] . 'plugin/YPTSocket/footerCaller.php';
    }

    public static function sendAsync($msg, $callbackJSFunction = "", $users_id = "", $send_to_uri_pattern = "") {
        global $global;
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        $command = get_php() . " {$global['systemRootPath']}plugin/YPTSocket/send.json.php '$msg' '$callbackJSFunction' '$users_id' '$send_to_uri_pattern'";
        execAsync($command);
    }

    public static function send($msg, $callbackJSFunction = "", $users_id = "", $send_to_uri_pattern = "") {
        global $global, $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj, $SocketURL;
        _mysql_close();
        @_session_write_close();
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        $SocketSendUsers_id = $users_id;
        if (!is_array($SocketSendUsers_id)) {
            $SocketSendUsers_id = array($SocketSendUsers_id);
        }

        $SocketSendObj = new stdClass();
        $SocketSendObj->msg = $msg;
        $SocketSendObj->isCommandLine = isCommandLineInterface();
        $SocketSendObj->json = _json_decode($msg);

        $SocketSendObj->webSocketToken = getEncryptedInfo(0, $send_to_uri_pattern);
        $SocketSendObj->callback = $callbackJSFunction;

        $SocketSendResponseObj = new stdClass();
        $SocketSendResponseObj->error = true;
        $SocketSendResponseObj->msg = "";
        $SocketSendResponseObj->msgObj = $SocketSendObj;
        $SocketSendResponseObj->callbackJSFunction = $callbackJSFunction;

        require_once $global['systemRootPath'] . 'objects/autoload.php';

        $SocketURL = self::getWebSocketURL(true, $SocketSendObj->webSocketToken, isDocker());
        //_error_log("Socket Send: {$SocketURL}");
        \Ratchet\Client\connect($SocketURL)->then(function ($conn) {
            global $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj;
            $conn->on('message', function ($msg) use ($conn, $SocketSendResponseObj) {
                //echo "Received: {$msg}".PHP_EOL;
                $SocketSendResponseObj->error = false;
                $SocketSendResponseObj->msg = $msg;
                //$conn->close();
            });

            foreach ($SocketSendUsers_id as $users_id) {
                $SocketSendObj->to_users_id = $users_id;
                $conn->send(json_encode($SocketSendObj));
                //echo "send: {$users_id}".PHP_EOL;
            }

            $conn->close();
            //echo "close".PHP_EOL;

            //$SocketSendResponseObj->error = false;
        }, function ($e) {
                    global $SocketURL;
                    _error_log("Could not connect: {$e->getMessage()} {$SocketURL}", AVideoLog::$ERROR);
                });

        return $SocketSendResponseObj;
    }

    public static function getWebSocketURL($isCommandLine = false, $webSocketToken = '', $internalDocker = false) {
        global $global;
        $socketobj = AVideoPlugin::getDataObject("YPTSocket");
        $address = $socketobj->host;
        $port = $socketobj->port;
        $protocol = "ws";
        $scheme = parse_url($global['webSiteRootURL'], PHP_URL_SCHEME);
        if(isDocker()){
            $protocol = "wss";
            $dockerVars = getDockerVars();
            $port = $dockerVars->SOCKET_PORT;
            $address = $dockerVars->SERVER_NAME;
        }else if (strtolower($scheme) === 'https') {
            $protocol = "wss";
        }
        if (empty($webSocketToken)) {
            $webSocketToken = getEncryptedInfo(0);
        }
        return "{$protocol}://{$address}:{$port}?webSocketToken={$webSocketToken}&isCommandLine=" . intval($isCommandLine);
    }

    public function onUserSocketConnect() {
        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
        if (!empty($obj->enableCalls)) {
            echo 'callerNewConnection(response);';
        }
        echo 'socketNewConnection(response);';
        return '';
    }
    
    public function onUserSocketDisconnect() {
        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
        if (!empty($obj->enableCalls)) {
            echo 'callerDisconnection(response);';
        }
        echo 'socketDisconnection(response);';
        return '';
    }
    
    public static function getUserOnlineLabel($users_id, $class='', $style='') {
        global $global;
        $users_id = intval($users_id);
        $varsArray = array('users_id' => $users_id, 'class'=>$class, 'style'=>$style);
        $filePath = $global['systemRootPath'] . 'plugin/YPTSocket/userOnlineLabel.php';
        return getIncludeFileContent($filePath, $varsArray);
    }
    
    
    public static function shouldShowCaller() {
        global $_YPTSocketshouldShowCaller;
        if(!isset($_YPTSocketshouldShowCaller)){
            $obj = new stdClass();
            $obj->show = false;
            $obj->reason = '';
            if (!User::isLogged()) {
                $obj->reason = 'Not logged';
            } else {
                $objSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
                if (empty($objSocket->enableCalls)) {
                    $obj->reason = 'YPTSocket enableCalls = false';
                }else{
                    $obj->show = true;
                }
            }
            $_YPTSocketshouldShowCaller = $obj;
        }
        return $_YPTSocketshouldShowCaller;
    }
    
    static public function scheduleRestart()
    {
        $scheduler_commands_id = Scheduler::add(strtotime('+5 seconds'), 'none', array('users_id'=>User::getId()), 'SocketRestart');
        return $scheduler_commands_id;
    }

    public function getPluginMenu()
    {
        global $global;
        $btn = '<button onclick="avideoAjax(webSiteRootURL+\'plugin/YPTSocket/restart.json.php\', {});" class="btn btn-danger btn-sm btn-xs btn-block"><i class="fas fa-power-off"></i> Restart</button>';
        return $btn;
    }
    
    static public function restart()
    {
        global $global;
        exec("php {$global['systemRootPath']}plugin/YPTSocket/stopServer.php");
        exec("sleep 1");
        execAsync("nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &");
        return true;
    }
    
}
