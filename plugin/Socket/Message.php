<?php

namespace Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Socket/functions.php';

class Message implements MessageComponentInterface {

    protected $clients, $clients_users_id;

    public function __construct() {
        //$this->clients = new \SplObjectStorage;
        $this->clients = [];
        _log_message("Construct");
    }

    public function onOpen(ConnectionInterface $conn) {
        _log_message("New connection! ({$conn->resourceId})");
        // Store the new connection to send messages to later
        //$this->clients->attach($conn);
        if (!isset($this->clients[$conn->resourceId])) {
            $this->clients[$conn->resourceId] = array();
        }
        $this->clients[$conn->resourceId]['conn'] = $conn;
        
        self::msgToResourceId("Connection opened", $conn->resourceId);
    }

    public function msgToResourceId($msg, $resourceId) {
        if(!is_array($msg)){
            $this->msgToArray($msg);
        }
        $msg['ResourceId'] = $resourceId;
        $msg = json_encode($msg);       
        _log_message("msgToUser: resourceId=({$resourceId}) {$msg}"); 
        $this->clients[$resourceId]['conn']->send($msg);
    }

    public function msgToUsers_id($msg, $users_id) {
        if(empty($users_id)){
            return false;
        }
        $this->clients[$from->resourceId]['users_id'];
        $count = 0;
        foreach ($this->clients as $resourceId => $value) {
            if($value['users_id'] == $users_id){
                $count++;
                $this->msgToResourceId($msg, $resourceId);
            }
        }
        _log_message("msgToUsers_id: sent to ($count) clients users_id={$users_id}");
        
    }

    public function msgToAll(ConnectionInterface $from, $msg) {
        _log_message("msgToAll");
        $numRecv = count($this->clients) - 1;
        //_log_message(sprintf('Connection %d sending message "%s" to %d other connection%s' . PHP_EOL , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's'));
        
        foreach ($this->clients as $key => $client) {
            if ($from !== $client['conn']) {
                $this->msgToResourceId($msg, $key);
            }
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        _log_message("onMessage: {$msg}");
        $json = json_decode($msg);
        if (empty($json)) {
            _log_message("onMessage ERROR: JSON is empty ");
            return false;
        }
        if (empty($json->webSocketToken)) {
            _log_message("onMessage ERROR: webSocketToken is empty ");
            return false;
        }
        if (!$msgObj = getDecryptedInfo($json->webSocketToken)) {
            _log_message("onMessage ERROR: could not decrypt webSocketToken");
            return false;
        }

        switch ($json->msg) {
            case "webSocketToken":
                if (empty($this->clients[$from->resourceId]['users_id'])) {
                    _log_message("onMessage: set users_id {$msgObj->users_id}");
                    $this->clients[$from->resourceId]['users_id'] = $msgObj->users_id;
                }
                break;
            default:
                $this->msgToArray($json);
                if (!empty($json['to_users_id'])) {
                    $this->msgToUsers_id($json['msg'], $json['to_users_id']);
                } else {
                    $this->msgToAll($from, $json['msg']);
                }
                break;
        }
    }
    
    private function msgToArray(&$json){
        $json = $this->makeSureIsArray($json);
        $msg = $this->makeSureIsArray(@$json['msg']);
        $msg['callback'] = @$json['callback'];
        $msg['uniqid'] = uniqid();
        $json['msg'] = $msg;
        return true;
    }
    
    private function makeSureIsArray($msg){
        if(empty($msg)){
            return array();
        }
        if(is_string($msg)){
            $decoded = json_decode($msg);
        }else{
            $decoded = object_to_array($msg);
        }
        if(is_string($msg) && !$decoded){
            return array($msg);
        }else if(is_string($msg)){
            return object_to_array($decoded);
        }
        return object_to_array($msg);
    }

    public function onClose(ConnectionInterface $conn) {
        _log_message("Connection {$conn->resourceId} has disconnected");
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach($conn);
        unset($this->clients[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        _log_message("An error has occurred: {$e->getMessage()}");
        $conn->close();
    }

    public function getTags() {
        return array('free', 'live');
    }

}

function _log_message($msg) {
    _error_log($msg);
    echo $msg . PHP_EOL;
}
