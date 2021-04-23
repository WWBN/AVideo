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
        $desc = getSocketConnectionLabel();
        $desc .= "Socket Plugin, WebSockets allow for a higher amount of efficiency compared to REST because they do not require the HTTP request/response overhead for each message sent and received<br>";
        $desc .= "<code>sudo nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &</code>";
        $desc .= "<br>To test use <code>php {$global['systemRootPath']}plugin/YPTSocket/test.php</code>";
        $desc .= "<br>To start it on server reboot add it on your crontab (Ubuntu 18+) <code>sudo crontab -eu root</code> than add this code on the last line <code>@reboot sleep 60;nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &</code>";
        $help = "<br>run this command start the server <small><a href='https://github.com/WWBN/AVideo/wiki/Socket-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc.$help;
    }

    public function getName() {
        return "YPTSocket";
    }

    public function getUUID() {
        return "YPTSocket-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.1";
    }
    
    public static function getServerVersion() {
        return "2.5";
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
        $obj->host = $host;
        self::addDataObjectHelper('host', 'Server host', 'If your site is HTTPS make sure this host also handle the SSL connection');
        $obj->debugSocket = false;
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
        
        return $obj;
    }
    
    public function getFooterCode() {
        self::getSocketJS();
    }

    public static function getSocketJS() {
        global $global;
        include $global['systemRootPath'] . 'plugin/YPTSocket/footer.php';
    }
    
    public static function sendAsync($msg, $callbackJSFunction = "", $users_id = "", $send_to_uri_pattern = "") {
        global $global;
        if(!is_string($msg)){
            $msg = json_encode($msg);
        }
        $command = "php {$global['systemRootPath']}plugin/YPTSocket/send.json.php '$msg' '$callbackJSFunction' '$users_id' '$send_to_uri_pattern'";
        execAsync($command);
    }

    public static function send($msg, $callbackJSFunction = "", $users_id = "", $send_to_uri_pattern = "") {
        global $global, $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj, $SocketURL;
        if(!is_string($msg)){
            $msg = json_encode($msg);
        }
        $SocketSendUsers_id = $users_id;
        if(!is_array($SocketSendUsers_id)){
            $SocketSendUsers_id = array($SocketSendUsers_id);
        }
        
        $SocketSendObj = new stdClass();
        $SocketSendObj->msg = $msg;
        $SocketSendObj->json = _json_decode($msg);
        
        $SocketSendObj->webSocketToken = getEncryptedInfo(0,$send_to_uri_pattern);
        $SocketSendObj->callback = $callbackJSFunction;
        
        $SocketSendResponseObj = new stdClass();
        $SocketSendResponseObj->error = true;
        $SocketSendResponseObj->msg = "";
        $SocketSendResponseObj->msgObj = $SocketSendObj;
        $SocketSendResponseObj->callbackJSFunction = $callbackJSFunction;  
        
        require_once $global['systemRootPath'] . 'objects/autoload.php';

        $SocketURL = self::getWebSocketURL(true, $SocketSendObj->webSocketToken);
        _error_log("Socket Send: {$SocketURL}");
        \Ratchet\Client\connect($SocketURL)->then(function($conn) {
            global $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj;
            $conn->on('message', function($msg) use ($conn, $SocketSendResponseObj) {
                //echo "Received: {$msg}\n";
                //$conn->close();
                $SocketSendResponseObj->error = false;
                $SocketSendResponseObj->msg = $msg;
            });

            foreach ($SocketSendUsers_id as $users_id) {
                $SocketSendObj->to_users_id = $users_id;
                $conn->send(json_encode($SocketSendObj));
            }
        
            $conn->close();
            
            //$SocketSendResponseObj->error = false;
        }, function ($e) {
            global $SocketURL;
            _error_log("Could not connect: {$e->getMessage()} {$SocketURL}", AVideoLog::$ERROR);
        });
        
        return $SocketSendResponseObj;
    }

    public static function getWebSocketURL($isCommandLine=false, $webSocketToken='') {
        global $global;
        $socketobj = AVideoPlugin::getDataObject("YPTSocket");
        $address = $socketobj->host;
        $port = $socketobj->port;
        $protocol = "ws";
        $scheme = parse_url($global['webSiteRootURL'], PHP_URL_SCHEME);
        if(strtolower($scheme)==='https'){
            $protocol = "wss";
        }
        if(empty($webSocketToken)){
            $webSocketToken = getEncryptedInfo(0);
        }
        return "{$protocol}://{$address}:{$port}?webSocketToken={$webSocketToken}&isCommandLine=".intval($isCommandLine);
    }

}
