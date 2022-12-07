<?php

namespace Socket;

use React\EventLoop\Loop;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

class Message implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        //$this->loop->ad
        $this->clients = array();
        _log_message("Construct");
    }

    public function onOpen(ConnectionInterface $conn) {
        $start = microtime(true);
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $wsocketGetVars);
        foreach ($wsocketGetVars as $key => $value) {
            $wsocketGetVars[$key] = urldecode($value);
        }
        if (empty($wsocketGetVars['webSocketToken'])) {
            _log_message("Empty websocket token ");
            return false;
        }
        $json = getDecryptedInfo($wsocketGetVars['webSocketToken']);
        if (empty($json)) {
            _log_message("Invalid websocket token ");
            return false;
        }
        $this->clients[$conn->resourceId] = $conn;
        $live_key = object_to_array(@$json->live_key);
        if(empty($live_key)){
            $live_key = new \stdClass();
            $live_key->key = '';
            $live_key->live_servers_id = 0;
            $live_key->liveLink = '';
        }
        
        $client = array();
        $client['resourceId'] = intval($conn->resourceId);
        $client['users_id'] = intval($json->from_users_id);
        $client['room_users_id'] = intval(@$json->room_users_id);
        $client['videos_id'] = intval($json->videos_id);
        $client['live_key_servers_id'] = "{$live_key->key}_{$live_key->live_servers_id}";
        $client['liveLink'] = $live_key->liveLink;
        $client['isAdmin'] = $json->isAdmin;
        $client['live_key'] = $live_key->key;
        $client['live_servers_id'] = intval($live_key->live_servers_id);
        $client['user_name'] = $json->user_name;
        $client['browser'] = $json->browser;
        $client['yptDeviceId'] = $json->yptDeviceId;
        $client['client'] = deviceIdToObject($json->yptDeviceId);
        if (!empty($wsocketGetVars['webSocketSelfURI'])) {
            $client['selfURI'] = $wsocketGetVars['webSocketSelfURI'];
        } else {
            $client['selfURI'] = $json->selfURI;
        }
        $client['isCommandLine'] = @$wsocketGetVars['isCommandLine'];
        $client['pageTitle'] = @utf8_encode(@$wsocketGetVars['page_title']);
        $client['ip'] = $json->ip;
        $client['location'] = $json->location;
        $client['data'] = $json;

        dbInsertConnection($client);

        if ($client['browser'] == \SocketMessageType::TESTING) {
            _log_message("Test detected and received from ($conn->resourceId) " . PHP_EOL . "\e[1;32;40m*** SUCCESS TEST CONNECION {$json->test_msg} ***\e[0m");
            $this->msgToResourceId($json, $conn->resourceId, \SocketMessageType::TESTING);
        } else if ($this->shouldPropagateInfo($client)) {
            //_log_message("shouldPropagateInfo {$json->yptDeviceId}");
            $this->msgToAll($conn, array('users_id' => $client['users_id'], 'user_name' => $client['user_name'], 'yptDeviceId' => $client['yptDeviceId']), \SocketMessageType::NEW_CONNECTION, true);
        } else {
            //_log_message("NOT shouldPropagateInfo ");
        }
        $end = number_format(microtime(true)-$start, 4);
        _log_message("Connection opened in {$end} seconds");
    }

    public function onClose(ConnectionInterface $conn) {
        global $onMessageSentTo, $SocketGetTotals;
        $client = dbGetRowFromResourcesId($conn->resourceId);
        dbDeleteConnection($conn->resourceId);
        unset($this->clients[$conn->resourceId]);
        if ($this->shouldPropagateInfo($client)) {
            $this->msgToAll($conn, array('users_id' => $client['users_id']), \SocketMessageType::NEW_DISCONNECTION);
        }
        _log_message("Connection {$conn->resourceId} has disconnected");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();
        //_log_message("onMessage: {$msg}");
        $json = _json_decode($msg);
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
            case \SocketMessageType::TESTING:
                $this->msgToResourceId($json, $from->resourceId, \SocketMessageType::TESTING);
                break;
            default:
                $this->msgToArray($json);
                //_log_message("onMessage:msgObj: " . json_encode($json));
                if (!empty($msgObj->send_to_uri_pattern)) {
                    $this->msgToSelfURI($json, $msgObj->send_to_uri_pattern);
                } else if (!empty($json['resourceId'])) {
                    $this->msgToResourceId($json, $json['resourceId']);
                } else if (!empty($json['to_users_id'])) {
                    $this->msgToUsers_id($json, $json['to_users_id']);
                } else {
                    $this->msgToAll($from, $json);
                }
                break;
        }
    }

    private function shouldPropagateInfo($row) {
        if (preg_match('/^unknowDevice.*/', $row['yptDeviceId'])) {
            return false;
        }
        if (!empty($row['isCommandLine'])) {
            return false;
        }
        return true;
    }

    public function msgToResourceId($msg, $resourceId, $type = "") {
        global $onMessageSentTo, $SocketDataObj;
        if(empty($resourceId)){
            return false;
        }
        
        $row = dbGetRowFromResourcesId($resourceId);

        if (empty($row)) {
            _log_message("msgToResourceId: resourceId=({$resourceId}) NOT found");
        }

        if (!$this->shouldPropagateInfo($row)) {
            _log_message("msgToResourceId: we wil NOT send the message to resourceId=({$resourceId}) {$type}");
        }

        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        if (!empty($msg['webSocketToken'])) {
            unset($msg['webSocketToken']);
        }
        if (empty($type)) {
            $type = \SocketMessageType::DEFAULT_MESSAGE;
        }

        $videos_id = $row['videos_id'];
        $users_id = $row['users_id'];
        $live_key = $row['live_key'];

        $obj = array();
        $obj['ResourceId'] = $resourceId;
        $obj['type'] = $type;

        if (isset($msg['callback'])) {
            $obj['callback'] = $msg['callback'];
            unset($msg['callback']);
        } else {
            $obj['callback'] = "";
        }

        if (!empty($msg['json'])) {
            $obj['msg'] = $msg['json'];
        } else if (!empty($msg['msg'])) {
            $obj['msg'] = $msg['msg'];
        } else {
            $obj['msg'] = $msg;
        }

        $obj['uniqid'] = uniqid();
        $obj['users_id'] = $users_id;
        $obj['videos_id'] = $videos_id;
        $obj['live_key'] = $live_key;
        $obj['webSocketServerVersion'] = $SocketDataObj->serverVersion;
        $obj['isAdmin'] = $row['isAdmin'];

        $return = $this->getTotals();

        $info = array(
            'webSocketServerVersion' => $SocketDataObj->serverVersion,
            'socket_users_id' => $users_id,
            'socket_resourceId' => $resourceId,
        );

        $obj['autoUpdateOnHTML'] = array_merge($info, $return);

        $obj['users_uri'] = $return['users_uri'];
        $obj['resourceId'] = $resourceId;
        $obj['users_id_online'] = dbGetUniqueUsers();

        $msgToSend = json_encode($obj);
        _log_message("msgToResourceId: resourceId=({$resourceId}) {$type}");
        $this->clients[$resourceId]->send($msgToSend);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        dbDeleteConnection($conn->resourceId);
        $conn->close();
    }

    public function msgToUsers_id($msg, $users_id, $type = "") {
        if (empty($users_id)) {
            return false;
        }
        try {
            $count = 0;
            if (!is_array($users_id)) {
                $users_id = array($users_id);
            }
            foreach ($users_id as $user_id) {
                $user_id = intval($user_id);
                if (empty($user_id)) {
                    continue;
                }

                $rows = dbGetAllResourcesIdFromUsersId($user_id);
                foreach ($rows as $row) {
                    $count++;
                    $this->msgToResourceId($msg, $row['resourceId'], $type);
                }
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        _log_message("msgToUsers_id: sent to ($count) clients users_id=" . json_encode($users_id));
    }

    public function msgToSelfURI($msg, $pattern, $type = "") {
        if (empty($pattern)) {
            return false;
        }
        $count = 0;
        $rows = dbGetAllResourceIdFromSelfURI($pattern);
        foreach ($rows as $row) {
            $count++;
            $this->msgToResourceId($msg, $row['resourceId'], $type);
        }
        _log_message("msgToSelfURI: sent to ($count) clients pattern={$pattern} {$type}");
    }

    public function getTotals() {
        $totals = array();
        $totals['total_devices_online'] = dbGetTotalUniqueDevices();
        $totals['total_users_online'] = dbGetTotalUniqueUsers();
        $totals['class_to_update'] = dbGetTotalUniqueDevices();
        $totals['users_uri'] = dbGetTotalUniqueDevices();
        $totals['LivesTotals'] = $this->getLivesTotal();
        return $totals;
    }

    function getLivesTotal() {
        $this->totalUsersOnLives = array('updated' => time());
        $this->totalUsersOnLives['statsList'] = array();
        $rows = dbGetTotalInLive();
        foreach ($rows as $value) {
            $total_viewers = 0;
            if ($this->isLiveUsersEnabled()) {
                $total_viewers = \LiveUsers::getTotalUsers($value['live_key'], $value['live_servers_id']);
            }
            $this->totalUsersOnLives['statsList'][$value['live_key']] = array(
                'total_viewers' => $total_viewers,
                'watching_now' => intval($value['total']),
            );
        }

        return $this->totalUsersOnLives;
    }

    private function isLiveUsersEnabled() {
        global $_isLiveUsersEnabled;
        if (!isset($_isLiveUsersEnabled)) {
            $_isLiveUsersEnabled = \AVideoPlugin::isEnabledByName('LiveUsers') && method_exists('LiveUsers', 'getTotalUsers');
        }
        return $_isLiveUsersEnabled;
    }

    public function msgToAll(ConnectionInterface $from, $msg, $type = "", $includeMe = false) {
        $start = microtime(true);
        $rows = dbGetAll();
        foreach ($rows as $key => $client) {
            $this->msgToResourceId($msg, $client['resourceId'], $type);
        }
        $end = number_format(microtime(true)-$start, 4);
        _log_message("msgToAll FROM ({$from->resourceId}) {$type} Total Clients: " . count($rows)." in {$end} seconds");
    }

    public function msgToAllSameVideo($videos_id, $msg) {
        if (empty($videos_id)) {
            return false;
        }
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        _log_message("msgToAllSameVideo: {$videos_id}");
        foreach (dbGetAllResourcesIdFromVideosId($videos_id) as $client) {
            $this->msgToResourceId($msg, $client['resourceId'], \SocketMessageType::ON_VIDEO_MSG);
        }
    }

    public function msgToAllSameLive($live_key, $live_servers_id, $msg) {
        if (empty($live_key)) {
            return false;
        }
                
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        
        $rows = dbGetAllResourcesIdFromLive($live_key, $live_servers_id);
        foreach ($rows as $value) {
            $this->msgToResourceId($msg, $value['resourceId'], \SocketMessageType::ON_LIVE_MSG);
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
            $decoded = _json_decode($msg);
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

    public function getTags() {
        return array('free', 'live');
    }

    public function isUserLive($users_id) {
        return dbIsUserOnLine($users_id);
    }

}

function _log_message($msg, $type = "") {
    global $SocketDataObj;
    if (!empty($SocketDataObj->debugAllUsersSocket) || !empty($SocketDataObj->debugSocket)) {
        //_error_log($msg, \AVideoLog::$SOCKET);
        $mem_usage = memory_get_usage();
        $mem = humanFileSize($mem_usage);
        echo date('Y-m-d H:i:s') . " Using: {$mem} RAM " . $msg . PHP_EOL;
    } else if ($type == \AVideoLog::$ERROR) {
        _error_log($msg, \AVideoLog::$SOCKET);
        echo "\e[1;31;40m" . date('Y-m-d H:i:s') . ' ' . $msg . "\e[0m" . PHP_EOL;
    }
}
