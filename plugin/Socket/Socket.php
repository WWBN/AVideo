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

    public static function send($msg, $callbackJSFunction="", $users_id="") {
        global $global, $SocketSendObj;
        $socketobj = AVideoPlugin::getDataObject("Socket");
        $address = "localhost";
        $port = $socketobj->port;
        
        if(!is_string($msg)){
            $msg = json_encode($msg);
        }
        
        $SocketSendObj = new stdClass();
        $SocketSendObj->webSocketToken = getEncryptedInfo();
        $SocketSendObj->msg = $msg;
        $SocketSendObj->json = json_decode($msg);
        $SocketSendObj->users_id = $users_id;
        $SocketSendObj->callback = $callbackJSFunction;
        
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "";
        $obj->msgObj = $SocketSendObj;
        $obj->callbackJSFunction = $callbackJSFunction;

        
        
        require_once $global['systemRootPath'] . 'objects/autoload.php';

        \Ratchet\Client\connect("ws://{$address}:{$port}")->then(function($conn) {
            global $SocketSendObj;
            $conn->on('message', function($msg) use ($conn) {
                //echo "Received: {$msg}\n";
                $conn->close();
            });

            $conn->send(json_encode($SocketSendObj));
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
        
        return $obj;
    }
    

}
