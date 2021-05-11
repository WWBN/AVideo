<?php

namespace Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

class Message implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        //$this->clients = new \SplObjectStorage;
        $this->clients = [];
        _log_message("Construct");
    }

    public function onOpen(ConnectionInterface $conn) {
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();
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
        // Store the new connection to send messages to later
        //$this->clients->attach($conn);
        $client = array();
        $client['conn'] = $conn;
        $client['resourceId'] = $conn->resourceId;
        $client['users_id'] = $json->from_users_id;
        $client['isAdmin'] = $json->isAdmin;
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
        $client['page_title'] = utf8_encode(@$wsocketGetVars['page_title']);
        $client['videos_id'] = $json->videos_id;
        $client['live_key'] = object_to_array(@$json->live_key);
        $client['ip'] = $json->ip;
        $client['location'] = $json->location;

        _log_message("New connection ($conn->resourceId) {$json->yptDeviceId} {$client['selfURI']} {$client['browser']}");

        $this->clients[$conn->resourceId] = $client;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $limit = 250;
        } else {
            $limit = 900;
        }
        
        if(count($this->clients)>$limit){
            $resourceId = array_key_first($this->clients);
            _log_message("\e[1;32;40m*** Closing connection {$resourceId} ***\e[0m");
            //$this->clients[$resourceId]->close();
            //$this->clients->detach($this->clients[$resourceId]['conn']);
            $this->clients[$resourceId]['conn']->close();
            unset($resourceId);
        }
        
        if ($client['browser'] == \SocketMessageType::TESTING) {
            _log_message("Test detected and received from ($conn->resourceId) " . PHP_EOL . "\e[1;32;40m*** SUCCESS TEST CONNECION {$json->test_msg} ***\e[0m");
            $this->msgToResourceId($json, $conn->resourceId, \SocketMessageType::TESTING);
        } else if ($this->shouldPropagateInfo($client)) {
            //_log_message("shouldPropagateInfo {$json->yptDeviceId}");
            $this->msgToAll($conn, array('users_id' => $client['users_id'], 'yptDeviceId' => $client['yptDeviceId']), \SocketMessageType::NEW_CONNECTION, true);
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
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();

        unset($getStatsLive);
        unset($_getStats);
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach($conn);
        if (empty($this->clients[$conn->resourceId])) {
            _log_message("onClose Connection {$conn->resourceId} not found");
            return false;
        }
        $client = $this->clients[$conn->resourceId];
        unset($this->clients[$conn->resourceId]);
        $users_id = $client['users_id'];
        $videos_id = $client['videos_id'];
        $live_key = $client['live_key'];
        if ($this->shouldPropagateInfo($client)) {
            $this->msgToAll($conn, array('users_id' => $client['users_id']), \SocketMessageType::NEW_DISCONNECTION);
            //\AVideoPlugin::onUserSocketDisconnect($users_id, $this->clients[$conn->resourceId]);
            if (!empty($videos_id)) {
                $this->msgToAllSameVideo($videos_id, "");
            }
            if (!empty($live_key)) {
                $this->msgToAllSameLive($live_key, "");
            }
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
            case "webSocketToken":
                if (empty($this->clients[$from->resourceId]['users_id'])) {
                    _log_message("onMessage:webSocketToken");
                    $this->clients[$from->resourceId]['users_id'] = $msgObj->from_users_id;
                    $this->clients[$from->resourceId]['yptDeviceId'] = $msgObj->yptDeviceId;
                }
                break;
            case \SocketMessageType::TESTING:
                $this->msgToResourceId($json, $from->resourceId, \SocketMessageType::TESTING);
                break;
            default:
                $this->msgToArray($json);
                //_log_message("onMessage:msgObj: " . json_encode($json));
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

    private function shouldPropagateInfo($client) {
        if (preg_match('/^unknowDevice.*/', $client['yptDeviceId'])) {
            return false;
        }
        if (!empty($client['isCommandLine'])) {
            return false;
        }
        return true;
    }

    public function msgToResourceId($msg, $resourceId, $type = "") {
        global $onMessageSentTo, $SocketDataObj;
        if (in_array($resourceId, $onMessageSentTo)) {
            return false;
        }
        if (empty($this->clients[$resourceId]) || empty($this->clients[$resourceId]['conn'])) {
            _log_message("msgToResourceId: we wil NOT send the message to resourceId=({$resourceId}) {$type} because it does not exists anymore");
            return false;
        }

        // do not sent duplicated messages
        $onMessageSentTo[] = $resourceId;

        if (!$this->shouldPropagateInfo($this->clients[$resourceId])) {
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

        $videos_id = $this->clients[$resourceId]['videos_id'];
        $users_id = $this->clients[$resourceId]['users_id'];
        $live_key = $this->clients[$resourceId]['live_key'];

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
        $obj['isAdmin'] = $this->clients[$resourceId]['isAdmin'];

        $return = $this->getTotals($this->clients[$resourceId]);

        $totals = array(
            'webSocketServerVersion' => $SocketDataObj->serverVersion,
            'socket_users_id' => $users_id,
            'socket_resourceId' => $resourceId,
            'total_devices_online' => count($return['users_id']),
            'total_users_online' => count($return['devices'])
        );

        $obj['autoUpdateOnHTML'] = array_merge($totals, $return['class_to_update']);

        $obj['users_uri'] = $return['users_uri'];

        $msgToSend = json_encode($obj);
        //_log_message("msgToResourceId: resourceId=({$resourceId}) {$type}");
        $this->clients[$resourceId]['conn']->send($msgToSend);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        if (!preg_match('/protocol is shutdown/i', $e->getMessage())) { // this may be the iframe that reloads the page
            $debug = $this->clients[$conn->resourceId];
            unset($debug['conn']);
            var_dump($debug);
            _log_message("ERROR: ($conn->resourceId) {$e->getMessage()} ", \AVideoLog::$ERROR);
        }
        $conn->close();
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

    public function getTotals($_client) {

        $isAdmin = $_client['isAdmin'];
        $selfURI = $_client['selfURI'];
        $videos_id = $_client['videos_id'];
        $users_id = $_client['users_id'];
        $live_key = object_to_array($_client['live_key']);
        global $SocketDataObj, $SocketGetTotals;

        if (!$isAdmin && isset($SocketGetTotals)) {
            return $SocketGetTotals;
        }

        $return = array(
            'users_id' => array(),
            'devices' => array(),
            'class_to_update' => array(),
            'users_uri' => array()
        );

        $users_id_array = $devices = $list = array();

        foreach ($this->clients as $key => $client) {
            if (empty($client['yptDeviceId'])) {
                continue;
                _log_message("getTotals: yptDeviceId is empty ");
            }
            unset($client['conn']);

            if ($isAdmin) {
                $index = md5($client['selfURI']);
                if (!isset($return['users_uri'][$index])) {
                    $return['users_uri'][$index] = array();
                }
                if (!isset($return['users_uri'][$index][$client['yptDeviceId']])) {
                    $return['users_uri'][$index][$client['yptDeviceId']] = array();
                }
                if (empty($client['users_id'])) {
                    $return['users_uri'][$index][$client['yptDeviceId']][uniqid()] = $client;
                } else
                if (!isset($return['users_uri'][$index][$client['yptDeviceId']][$client['users_id']])) {
                    $return['users_uri'][$index][$client['yptDeviceId']][$client['users_id']] = $client;
                }
            }

            //total_devices_online
            if (!in_array($client['yptDeviceId'], $return['devices'])) {
                $return['devices'][] = $client['yptDeviceId'];
            }
            //total_users_online
            if (!empty($client['users_id']) && !in_array($client['users_id'], $return['users_id'])) {
                if ($this->shouldPropagateInfo($client)) {
                    $return['users_id'][] = $client['users_id'];
                }
            }

            $keyName = "";
            if (!empty($SocketDataObj->showTotalOnlineUsersPerVideo) && !empty($client['videos_id'])) {
                $keyName = getSocketVideoClassName($client['videos_id']);
            } else if (!empty($SocketDataObj->showTotalOnlineUsersPerLive) && !empty($client['live_key']['key'])) {
                $keyName = getSocketLiveClassName($client['live_key']['key'], $client['live_key']['live_servers_id']);
            } else if (!empty($SocketDataObj->showTotalOnlineUsersPerLiveLink) && !empty($client['live_key']['liveLink'])) {
                $keyName = getSocketLiveLinksClassName($client['live_key']['liveLink']);
            }

            if (!empty($keyName)) {
                if (!isset($return['class_to_update'][$keyName])) {
                    $return['class_to_update'][$keyName] = 1;
                } else {
                    $return['class_to_update'][$keyName]++;
                }
            }
        }
        if (!$isAdmin) {
            $SocketGetTotals = $return;
        }
        return $return;
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

    public function msgToAll(ConnectionInterface $from, $msg, $type = "", $includeMe = false) {
        _log_message("msgToAll FROM ({$from->resourceId}) {$type}");
        foreach ($this->clients as $key => $client) {
            if (!empty($includeMe) || $from !== $client['conn']) {
                //_log_message("msgToAll FROM ({$from->resourceId}) TO {$key} {$type}");
                $this->msgToResourceId($msg, $key, $type);
            }
        }
    }

    public function msgToAllSameVideo($videos_id, $msg) {
        if (empty($videos_id)) {
            return false;
        }
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
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
        _log_message("msgToAllSameLive: key={$live_key['key']} live_servers_id={$live_key['live_servers_id']} liveLink={$live_key['liveLink']}");
        foreach ($this->clients as $key => $client) {
            if (empty($client['live_key']) || (empty($client['live_key']['key']) && empty($client['live_key']['liveLink']))) {
                continue;
            }
            if ($client['live_key']['key'] == $live_key['key'] && $client['live_key']['live_servers_id'] == $live_key['live_servers_id']) {
                $this->msgToResourceId($msg, $key, \SocketMessageType::ON_LIVE_MSG);
            } else if ($client['live_key']['liveLink'] == $live_key['liveLink']) {
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

}

function _log_message($msg, $type = "") {
    global $SocketDataObj;
    if (!empty($SocketDataObj->debugAllUsersSocket) || !empty($SocketDataObj->debugSocket)) {
        //_error_log($msg, \AVideoLog::$SOCKET);
        echo date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL;
    } else if ($type == \AVideoLog::$ERROR) {
        _error_log($msg, \AVideoLog::$SOCKET);
        echo "\e[1;31;40m" . date('Y-m-d H:i:s') . ' ' . $msg . "\e[0m" . PHP_EOL;
    }
}
