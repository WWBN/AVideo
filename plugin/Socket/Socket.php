<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Socket/functions.php';

class Socket extends PluginAbstract {

    public function getDescription() {
        $desc = "Socket Plugin";
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
        $obj = new stdClass();
        $obj->port = "8888";
        $obj->debugSocket = false;
        $obj->useHTTPS = true;
        $obj->server_crt_file = "";
        $obj->server_key_file = "";
        $obj->allow_self_signed = true;// Allow self signed certs (should be false in production)
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
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
        global $global, $SocketSendObj, $SocketSendUsers_id, $SocketSendResponseObj;
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

        \Ratchet\Client\connect(self::getWebSocketURL(true, true))->then(function($conn) {
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
            echo "Could not connect: {$e->getMessage()}\n";
        });
        
        return $SocketSendResponseObj;
    }

    public static function getWebSocketURL($useLocalHost = true, $isCommandLine=false) {
        global $global;
        $socketobj = AVideoPlugin::getDataObject("Socket");
        $address = "localhost";
        if (empty($useLocalHost)) {
            $address = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
        $port = $socketobj->port;
        $protocol = "ws";
        if($socketobj->useHTTPS){
            $protocol = "wss";
        }
        return "{$protocol}://{$address}:{$port}?webSocketToken=".getEncryptedInfo(0)."&isCommandLine=".intval($isCommandLine);
    }

}
