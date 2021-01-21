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

require_once $global['systemRootPath'] . 'plugin/Socket/functions.php';

class Socket extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Socket Plugin, run the command below to start the server<br>";
        $desc .= "<code>nohup php {$global['systemRootPath']}plugin/Socket/server.php &</code>";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    public function getName() {
        return "Socket";
    }

    public function getUUID() {
        return "Socket-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
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
        
        if(!file_exists($server_crt_file)){
            $server_crt_file = "";
        }
        if(!file_exists($server_key_file)){
            $server_key_file = "";
        }
        
        $host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        
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
        
        return $obj;
    }

    public function getFooterCode() {
        self::getSocketJS();
    }

    public static function getSocketJS() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Socket/footer.php';
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
        $SocketSendObj->webSocketToken = getEncryptedInfo(0,$send_to_uri_pattern);
        $SocketSendObj->msg = $msg;
        $SocketSendObj->json = json_decode($msg);
        $SocketSendObj->callback = $callbackJSFunction;
        
        $SocketSendResponseObj = new stdClass();
        $SocketSendResponseObj->error = true;
        $SocketSendResponseObj->msg = "";
        $SocketSendResponseObj->msgObj = $SocketSendObj;
        $SocketSendResponseObj->callbackJSFunction = $callbackJSFunction;        
        
        require_once $global['systemRootPath'] . 'objects/autoload.php';

        $SocketURL = self::getWebSocketURL(true);
        
        \Ratchet\Client\connect($SocketURL)->then(function($conn) {
            global $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj;
            $conn->on('message', function($msg) use ($conn) {
                //echo "Received: {$msg}\n";
                //$conn->close();
                $SocketSendResponseObj->error = false;
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

    public static function getWebSocketURL($isCommandLine=false) {
        global $global;
        $socketobj = AVideoPlugin::getDataObject("Socket");
        $address = $socketobj->host;
        $port = $socketobj->port;
        $protocol = "ws";
        $scheme = parse_url($global['webSiteRootURL'], PHP_URL_SCHEME);
        if(strtolower($scheme)==='https'){
            $protocol = "wss";
        }
        return "{$protocol}://{$address}:{$port}?webSocketToken=".getEncryptedInfo(0)."&isCommandLine=".intval($isCommandLine);
    }

}
