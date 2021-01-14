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
        if(!isset($this->clients[$conn->resourceId] )){
            $this->clients[$conn->resourceId]  = array();
        }
        $this->clients[$conn->resourceId]['conn'] = $conn;
        self::msgToUser($conn->resourceId, $conn->resourceId);
    }
    
    public function msgToUser($msg, $id) {
        $this->clients[$id]['conn']->send($msg);
    }
    
    public function msgToUsers_id($msg, $users_id) {
        if(!empty($this->clients_users_id[$users_id])){
            $this->msgToUser($msg, $this->clients_users_id[$users_id]);
        }
    }

    public function msgToAll(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        _log_message(sprintf('Connection %d sending message "%s" to %d other connection%s'.PHP_EOL
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's'));

        foreach ($this->clients as $client) {
            if ($from !== $client['conn']) {
                // The sender is not the receiver, send to each client connected
                $client['conn']->send($msg);
            }
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        _log_message("onMessage: {$msg}");
        $json = json_decode($msg);
        if(empty($json)){
            _log_message("onMessage ERROR: JSON is empty ");
            return false;
        }
        if(empty($json->webSocketToken)){
            _log_message("onMessage ERROR: webSocketToken is empty ");
            return false;
        }
        if(!$msgObj = getDecryptedInfo($json->webSocketToken)){
            _log_message("onMessage ERROR: could not decrypt webSocketToken");
            return false;
        }
        if($msg == "webSocketToken" && empty($this->clients[$from->resourceId]['users_id'])){
            _log_message("onMessage: set users_id {$msgObj->users_id}");
            $this->clients[$from->resourceId]['users_id'] = $msgObj->users_id;
            if(!isset($this->clients_users_id)){
                $this->clients_users_id = array();
            }
            $this->clients_users_id[$msgObj->users_id] = $from->resourceId;
        }else{  
            if(!empty($json->users_id)){
                $this->msgToUsers_id($from, $json->users_id);
            }else{
                $this->msgToAll($from, $msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        _log_message("Connection {$conn->resourceId} has disconnected");
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach($conn);
        if(!empty($this->clients[$conn->resourceId]) && !empty($this->clients[$conn->resourceId]['users_id'])){
            unset($this->clients_users_id[$this->clients[$conn->resourceId]['users_id']]);
        }
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

function _log_message($msg){    
    _error_log($msg);
    echo $msg.PHP_EOL;
}