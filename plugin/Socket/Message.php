<?php

namespace Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Socket/functions.php';

class Message implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        //$this->clients = new \SplObjectStorage;
        $this->clients = [];
        _log_message("Construct");
    }

    public function onOpen(ConnectionInterface $conn) {
        global $onMessageSentTo;
        $onMessageSentTo = array();
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $wsocketToken);
        if (empty($wsocketToken['webSocketToken'])) {
            _log_message("Empty websocket token ");
            return false;
        }
        $json = getDecryptedInfo($wsocketToken['webSocketToken']);
        if (empty($json)) {
            _log_message("Invalid websocket token ");
            return false;
        }
        // Store the new connection to send messages to later
        //$this->clients->attach($conn);
        if (!isset($this->clients[$conn->resourceId])) {
            $this->clients[$conn->resourceId] = array();
        }
        $this->clients[$conn->resourceId]['conn'] = $conn;
        $this->clients[$conn->resourceId]['users_id'] = $json->from_users_id;
        $this->clients[$conn->resourceId]['yptDeviceId'] = $json->yptDeviceId;
        $this->clients[$conn->resourceId]['selfURI'] = $json->selfURI;
        $this->clients[$conn->resourceId]['isCommandLine'] = $wsocketToken['isCommandLine'];
        $this->clients[$conn->resourceId]['videos_id'] = $json->videos_id;        
        $this->clients[$conn->resourceId]['live_key'] = object_to_array(@$json->live_key);
        $this->clients[$conn->resourceId]['autoEvalCodeOnHTML'] = $json->autoEvalCodeOnHTML;
        $this->clients[$conn->resourceId]['ip'] = $json->ip;
        $this->clients[$conn->resourceId]['location'] = $json->location;
        
        _log_message("New connection ($conn->resourceId) {$json->yptDeviceId}");
        if ($this->shouldPropagateInfo($this->clients[$conn->resourceId])) {
            //_log_message("shouldPropagateInfo {$json->yptDeviceId}");
            $this->msgToAll($conn, array(), \SocketMessageType::NEW_CONNECTION, true);
            \AVideoPlugin::onUserSocketConnect($json->from_users_id, $this->clients[$conn->resourceId]);
        } else {
            //_log_message("NOT shouldPropagateInfo ");
        }
        if (!empty($json->videos_id)) {
            //_log_message("msgToAllSameVideo ");
            $this->msgToAllSameVideo($json->videos_id, "");
        } else {
            //_log_message("NOT msgToAllSameVideo ");
        }
        if (!empty($json->live_key)) {
            //_log_message("msgToAllSameLive ");
            $this->msgToAllSameLive($json->live_key, "");
        } else {
            //_log_message("NOT msgToAllSameLive ");
        }
    }

    public function onClose(ConnectionInterface $conn) {
        global $onMessageSentTo;
        $onMessageSentTo = array();

        unset($getStatsLive);
        unset($_getStats);
        _log_message("Connection {$conn->resourceId} has disconnected");
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach($conn);
        $users_id = $this->clients[$conn->resourceId]['users_id'];
        $videos_id = $this->clients[$conn->resourceId]['videos_id'];
        $live_key = $this->clients[$conn->resourceId]['live_key'];
        if ($this->shouldPropagateInfo($this->clients[$conn->resourceId])) {
            $this->msgToAll($conn, array(), \SocketMessageType::NEW_DISCONNECTION, true);
            \AVideoPlugin::onUserSocketDisconnect($users_id, $this->clients[$conn->resourceId]);
        }

        unset($this->clients[$conn->resourceId]);
        if (!empty($videos_id)) {
            $this->msgToAllSameVideo($videos_id, "");
        }
        if (!empty($live_key)) {
            $this->msgToAllSameLive($live_key, "");
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $onMessageSentTo;
        $onMessageSentTo = array();
        //_log_message("onMessage: {$msg}");
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
                    _log_message("onMessage:webSocketToken");
                    $this->clients[$from->resourceId]['users_id'] = $msgObj->from_users_id;
                    $this->clients[$from->resourceId]['yptDeviceId'] = $msgObj->yptDeviceId;
                }
                break;
            default:
                $this->msgToArray($json);
                _log_message("onMessage:msgObj: " . json_encode($json));
                if (!empty($msgObj->send_to_uri_pattern)) {
                    $this->msgToSelfURI($json, $msgObj->send_to_uri_pattern);
                } else if (!empty($json['to_users_id'])) {
                    $this->msgToUsers_id($json, $json['to_users_id']);
                } else {
                    $this->msgToAll($from, $json);
                }
                break;
        }
    }

    private function shouldPropagateInfo($connection) {
        if (preg_match('/^unknowDevice.*/', $connection['yptDeviceId'])) {
            return false;
        }
        if (!empty($connection['isCommandLine'])) {
            return false;
        }
        return true;
    }

    public function msgToResourceId($msg, $resourceId, $type = "") {
        global $onMessageSentTo;
        if (in_array($resourceId, $onMessageSentTo)) {
            return false;
        }
        // do not sent duplicated messages
        $onMessageSentTo[] = $resourceId;

        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        if(!empty($msg['webSocketToken'])){
            unset($msg['webSocketToken']);
        }
        if (empty($type)) {
            $type = \SocketMessageType::DEFAULT_MESSAGE;
        }
        
        $videos_id = $this->clients[$resourceId]['videos_id'];
        $users_id = $this->clients[$resourceId]['users_id'];
        $live_key = $this->clients[$resourceId]['live_key'];
        
        $obj = array();
        $obj['ResourceId'] = $resourceId;
        $obj['type'] = $type;

        if(isset($msg['callback'])){
            $obj['callback'] = $msg['callback'];
            unset($msg['callback']);
        }else{
            $obj['callback'] = "";
        }
        
        if(!empty($msg['json'])){
            $obj['msg'] = $msg['json'];
        }else if(!empty($msg['msg'])){
            $obj['msg'] = $msg['msg'];
        }else{
            $obj['msg'] = $msg;
        }
        
        $obj['uniqid'] = uniqid();
        $obj['users_id'] = $users_id;
        $obj['videos_id'] = $videos_id;
        $obj['live_key'] = $live_key;
        $obj['autoUpdateOnHTML'] = array(
            'socket_users_id' => $users_id,
            'socket_resourceId' => $resourceId,
            'total_devices_online' => count($this->getUniqueDevices()),
            'total_users_online' => count($this->getUsersIdFromDevicesOnline()),
            'usersonline_per_video' => $this->getTotalPerVideo(),
            'total_on_same_video' => $this->getTotalOnVideos_id($videos_id),
            'total_on_same_live' => $this->getTotalOnlineOnLive_key($live_key)
        );
        $obj['autoEvalCodeOnHTML'] = $this->clients[$resourceId]['autoEvalCodeOnHTML'];
        
        $msgToSend = json_encode($obj);
        _log_message("msgToResourceId: resourceId=({$resourceId}) {$type}");
        $this->clients[$resourceId]['conn']->send($msgToSend);
        //sleep(0.1);
    }

    public function msgToUsers_id($msg, $users_id, $type = "") {
        if (empty($users_id)) {
            return false;
        }
        $count = 0;
        foreach ($this->clients as $resourceId => $value) {
            if ($value['users_id'] == $users_id) {
                $count++;
                $this->msgToResourceId($msg, $resourceId, $type);
            }
        }
        _log_message("msgToUsers_id: sent to ($count) clients users_id={$users_id}");
    }

    public function msgToSelfURI($msg, $pattern, $type = "") {
        if (empty($pattern)) {
            return false;
        }
        $count = 0;
        foreach ($this->clients as $resourceId => $value) {
            if (empty($value['selfURI'])) {
                continue;
            }
            if (preg_match($pattern, $value['selfURI'])) {
                $count++;
                $this->msgToResourceId($msg, $resourceId, $type);
            }
        }
        _log_message("msgToSelfURI: sent to ($count) clients pattern={$pattern} {$type}");
    }

    public function getTotalSelfURI($pattern) {
        if (empty($videos_id)) {
            return false;
        }
        $count = 0;
        foreach ($this->clients as $key => $client) {
            if (empty($client['selfURI'])) {
                continue;
            }
            if (preg_match($pattern, $client['selfURI'])) {
                $count++;
            }
        }
        _log_message("getTotalSelfURI: total ($count) clients pattern={$pattern} {$type}");
        return $count;
    }

    public function getTotalPerVideo() {
        $videos = array();
        foreach ($this->clients as $key => $client) {
            if (empty($client['videos_id'])) {
                continue;
            }
            if (!isset($videos[$client['videos_id']])) {
                $videos[$client['videos_id']] = array('videos_id' => $client['videos_id'], 'total' => 1);
            } else {
                $videos[$client['videos_id']]['total']++;
            }
        }
        return $videos;
    }

    public function msgToDevice_id($msg, $yptDeviceId) {
        if (empty($yptDeviceId)) {
            return false;
        }
        $count = 0;
        foreach ($this->clients as $resourceId => $value) {
            if ($value['yptDeviceId'] == $yptDeviceId) {
                $count++;
                $this->msgToResourceId($msg, $resourceId);
            }
        }
        _log_message("msgToDevice_id: sent to ($count) clients yptDeviceId={$yptDeviceId} ");
    }

    public function getUsersIdFromDevicesOnline() {
        $users_id = array();
        foreach ($this->clients as $value) {
            if (empty($value['yptDeviceId']) || empty($value['users_id'])) {
                continue;
            }
            if(in_array($value['users_id'], $users_id)){
                continue;
            }
            if ($this->shouldPropagateInfo($value)) {
                $users_id[] = $value['users_id'];
            }
        }
        return $users_id;
    }

    public function getUniqueDevices() {
        $devices = array();
        foreach ($this->clients as $value) {
            if (empty($value['yptDeviceId'])) {
                continue;
            }
            if(!in_array($value['yptDeviceId'], $devices)){
                $devices[] = $value['yptDeviceId'];
            }
        }
        return $devices;
    }

    public function msgToAll(ConnectionInterface $from, $msg, $type = "", $includeMe = false) {
        _log_message("msgToAll ({$from->resourceId}) {$type}");
        foreach ($this->clients as $key => $client) {
            if (!empty($includeMe) || $from !== $client['conn']) {
                $this->msgToResourceId($msg, $key, $type);
            }
        }
    }

    public function getTotalOnVideos_id($videos_id) {
        if (empty($videos_id)) {
            return false;
        }
        //_log_message("getTotalOnVideos_id: {$videos_id}");
        $count = 0;
        foreach ($this->clients as $key => $client) {
            if (empty($client['videos_id'])) {
                continue;
            }
            if ($client['videos_id'] == $videos_id) {
                $count++;
            }
        }
        return $count;
    }
    

    public function getTotalOnlineOnLive_key($live_key) {
        if (empty($live_key)) {
            return false;
        }
        
        $live_key = object_to_array($live_key);
        //_log_message("getTotalOnlineOnLive_key: key={$live_key['key']} live_servers_id={$live_key['live_servers_id']}");
        $count = 0;
        foreach ($this->clients as $key => $client) {
            if (empty($client['live_key'])) {
                continue;
            }
            if ($client['live_key']['key'] == $live_key['key'] && $client['live_key']['live_servers_id'] == $live_key['live_servers_id']) {
                $count++;
            }
        }

        return $count;
    }

    public function msgToAllSameVideo($videos_id, $msg) {
        if (empty($videos_id)) {
            return false;
        }
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        $msg['total'] = $this->getTotalOnVideos_id($videos_id);
        $msg['videos_id'] = $videos_id;
        _log_message("msgToAllSameVideo: {$videos_id}");
        foreach ($this->clients as $key => $client) {
            if (empty($client['videos_id'])) {
                continue;
            }
            if ($client['videos_id'] == $videos_id) {
                $this->msgToResourceId($msg, $key, \SocketMessageType::ON_VIDEO_MSG);
            }
        }
    }

    public function msgToAllSameLive($live_key, $msg) {
        if (empty($live_key)) {
            return false;
        }
        $live_key = object_to_array($live_key);
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        _mysql_connect();
        $msg['is_live'] = \Live::isLiveAndIsReadyFromKey($live_key['key'], $live_key['live_servers_id'], true);
        _mysql_close();
        _log_message("msgToAllSameLive: key={$live_key['key']} live_servers_id={$live_key['live_servers_id']}");
        foreach ($this->clients as $key => $client) {
            if (empty($client['live_key'])) {
                continue;
            }
            if ($client['live_key']['key'] == $live_key['key'] && $client['live_key']['live_servers_id'] == $live_key['live_servers_id']) {
                $this->msgToResourceId($msg, $key, \SocketMessageType::ON_LIVE_MSG);
            }
        }
    }

    private function msgToArray(&$json) {
        $json = $this->makeSureIsArray($json);
        return true;
    }

    private function makeSureIsArray($msg) {
        if (empty($msg)) {
            return array();
        }
        if (is_string($msg)) {
            $decoded = json_decode($msg);
        } else {
            $decoded = object_to_array($msg);
        }
        if (is_string($msg) && !$decoded) {
            return array($msg);
        } else if (is_string($msg)) {
            return object_to_array($decoded);
        }
        return object_to_array($msg);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        _log_message("An error has occurred: ($conn->resourceId) {$e->getMessage()} ", \AVideoLog::$ERROR);
        $conn->close();
    }

    public function getTags() {
        return array('free', 'live');
    }

}

function _log_message($msg, $type="") {
    global $SocketDataObj;
    if (!empty($SocketDataObj->debugAllUsersSocket) || !empty($SocketDataObj->debugSocket)) {
        _error_log($msg, \AVideoLog::$SOCKET);
        echo $msg . PHP_EOL;
    }else if($type==\AVideoLog::$ERROR){
        _error_log($msg, \AVideoLog::$SOCKET);
        echo $msg . PHP_EOL;
    }
}
