<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once dirname(__FILE__) . '/Objects/LiveChatObj.php';
require_once dirname(__FILE__) . '/../YouPHPTubePlugin.php';

$p = \YouPHPTubePlugin::loadPlugin("LiveChat");
$canSendMessage = $p->canSendMessage();

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $canSendMessage;
        if(empty($canSendMessage)){
            echo "Cant Send message\n";
            //return false;
        }
        //var_dump($msg);
        echo "Saving message\n";
        $lc = new \LiveChatObj(0);
        $object = json_decode($msg);
        $lc->setLive_stream_code($object->chatId);
        $lc->setStatus('a');
        $lc->setText($object->text);
        $lc->setUsers_id($object->userId);
        $lc->save();
        
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }    
    
    public function getTags() {
        return array('free', 'chat', 'live');
    }
}