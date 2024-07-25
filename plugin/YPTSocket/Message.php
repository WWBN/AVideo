<?php

namespace Socket;

use React\EventLoop\Loop;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

class Message implements MessageComponentInterface {

    protected $clients;
    protected $totalUsersOnLives;
    protected $clientsWatchinLive;
    protected $clientsWatchVideosId;
    protected $clientsUsersId;
    protected $clientsChatRoom;
    protected $loop;
    protected $timeout;

    public function __construct($loop) {
        $this->clients = [];
        $this->clientsWatchinLive = [];
        $this->clientsWatchVideosId = [];
        $this->clientsUsersId = [];
        $this->clientsChatRoom = [];
        $this->loop = $loop;
        $this->timeout = 600; // 10 minutes timeout
        _log_message("Construct");
    }

    public function onOpen(ConnectionInterface $conn) {
        global $onMessageSentTo, $SocketGetTotals;
        global $global;
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
            _log_message("Invalid websocket token [{$global['webSiteRootURL']}] [{$wsocketGetVars['webSocketToken']}]");
            return false;
        }

        $client = array();
        $client['conn'] = $conn;
        $client['resourceId'] = $conn->resourceId;
        $client['users_id'] = $json->from_users_id;
        $client['isAdmin'] = $json->isAdmin;
        $client['user_name'] = $json->user_name;
        $client['browser'] = $json->browser;
        $client['yptDeviceId'] = $json->yptDeviceId;
        $client['client'] = deviceIdToObject($json->yptDeviceId);
        $client['selfURI'] = !empty($wsocketGetVars['webSocketSelfURI']) ? $wsocketGetVars['webSocketSelfURI'] : $json->selfURI;
        $client['isCommandLine'] = @$wsocketGetVars['isCommandLine'];
        $client['page_title'] = @utf8_encode(@$wsocketGetVars['page_title']);
        $client['videos_id'] = $json->videos_id;
        $client['live_key'] = object_to_array(@$json->live_key);
        $client['ip'] = $json->ip;
        $client['location'] = $json->location;

        if (!empty($client['live_key']['key'])) {
            $this->clientsWatchinLive[$client['live_key']['key']][$client['resourceId']] = $client['users_id'];
        } else if (!empty($client['live_key']['liveLink'])) {
            $this->clientsWatchinLive[$client['live_key']['liveLink']][$client['resourceId']] = $client['users_id'];
        } else if (!empty($client['videos_id'])) {
            $this->clientsWatchVideosId[$client['videos_id']][$client['resourceId']] = $client['users_id'];
        }
        if (!empty($client['users_id'])) {
            if (!isset($this->clientsUsersId[$client['users_id']])) {
                $this->clientsUsersId[$client['users_id']] = array(
                    "users_id" => $client['users_id'],
                    "isAdmin" => $client['isAdmin'],
                    "user_name" => $client['user_name']
                );
                $this->clientsUsersId[$client['users_id']]['resourceId'] = array();
            }

            if (!in_array($client['resourceId'], $this->clientsUsersId[$client['users_id']]['resourceId'])) {
                $this->clientsUsersId[$client['users_id']]['resourceId'][$client['resourceId']] = $client['resourceId'];
            }
        }

        _log_message("New connection ($conn->resourceId) {$json->yptDeviceId} {$client['selfURI']} {$client['browser']}");

        $this->clients[$conn->resourceId] = $client;

        if ($client['browser'] == \SocketMessageType::TESTING) {
            _log_message("Test detected and received from ($conn->resourceId) " . PHP_EOL . "\e[1;32;40m*** SUCCESS TEST CONNECION {$json->test_msg} ***\e[0m");
            $this->msgToResourceId($json, $conn->resourceId, \SocketMessageType::TESTING);
        } else if ($this->shouldPropagateInfo($client)) {
            $this->msgToAll($conn, array('users_id' => $client['users_id'], 'user_name' => $client['user_name'], 'yptDeviceId' => $client['yptDeviceId']), \SocketMessageType::NEW_CONNECTION, true);
        }

        if (!empty($json->videos_id)) {
            $this->msgToAllSameVideo($json->videos_id, "");
        }

        if (!empty($json->live_key)) {
            if ($this->isLiveUsersEnabled()) {
                $live_key = object_to_array($json->live_key);
                if (!empty($live_key['key'])) {
                    \_mysql_connect(true);
                    $lt = \LiveTransmitionHistory::getLatest($live_key['key']);
                    if (!empty($lt['id'])) {
                        $l = new \LiveTransmitionHistory($lt['id']);
                        $total_viewers = \LiveUsers::getTotalUsers($lt['key'], $lt['live_servers_id']);
                        $max_viewers_sametime = $l->getMax_viewers_sametime();
                        $viewers_now = !empty($live_key['key']) ? count($this->clientsWatchinLive[$live_key['key']]) : count($this->clientsWatchinLive[$live_key['liveLink']]);
                        if ($viewers_now > $max_viewers_sametime) {
                            $l->setMax_viewers_sametime($viewers_now);
                        }
                        $l->setTotal_viewers($total_viewers);
                        _log_message("onOpen Connection viewers_now = {$viewers_now} => total_viewers = {$total_viewers}");
                        $l->save();
                    }
                }
            }
            $this->msgToAllSameLive($json->live_key, "");
        }

        // Set a timeout to close inactive connections
        $this->setTimeout($conn);
    }

    protected function setTimeout(ConnectionInterface $conn) {
        if (isset($conn->timeout)) {
            $this->loop->cancelTimer($conn->timeout);
        }

        $conn->timeout = $this->loop->addTimer($this->timeout, function() use ($conn) {
            _log_message("Closing inactive connection ({$conn->resourceId}) due to timeout\n");
            $conn->close();
        });
    }

    public function onClose(ConnectionInterface $conn) {
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();

        if (empty($this->clients[$conn->resourceId])) {
            _log_message("onClose Connection {$conn->resourceId} not found");
            return false;
        }

        $client = $this->clients[$conn->resourceId];

        if (!empty($client['live_key'])) {
            if (!empty($client['live_key']['key'])) {
                unset($this->clientsWatchinLive[$client['live_key']['key']][$conn->resourceId]);
            }
            if (!empty($client['live_key']['liveLink'])) {
                unset($this->clientsWatchinLive[$client['live_key']['liveLink']][$conn->resourceId]);
            }
        }
        if (!empty($client['users_id'])) {
            unset($this->clientsUsersId[$client['users_id']]['resourceId'][$conn->resourceId]);
            if (empty($this->clientsUsersId[$client['users_id']]['resourceId'])) {
                unset($this->clientsUsersId[$client['users_id']]);
            }
        }

        unset($this->clients[$conn->resourceId]);
        $users_id = $client['users_id'];
        $videos_id = $client['videos_id'];
        $live_key = $client['live_key'];

        if ($this->shouldPropagateInfo($client)) {
            $this->msgToAll($conn, array('users_id' => $users_id), \SocketMessageType::NEW_DISCONNECTION);
            if (!empty($videos_id)) {
                $this->msgToAllSameVideo($videos_id, "");
            }
            if (!empty($live_key)) {
                $this->msgToAllSameLive($live_key, "");
            }
        }
        $conn->close();
        _log_message("Connection {$conn->resourceId} has disconnected");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();

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
            case "getClientsList":
                if (empty($this->clientsUsersId)) {
                    return false;
                }
                $this->msgToResourceId(array('json' => $this->clientsUsersId, 'callback' => 'loadCallerPanel'), $from->resourceId);
                break;
            case \SocketMessageType::TESTING:
                $this->msgToResourceId($json, $from->resourceId, \SocketMessageType::TESTING);
                break;
            default:
                $this->msgToArray($json);
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

        // Reset the timeout on new message
        $this->setTimeout($from);
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

        // do not send duplicated messages
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
        $obj['resourceId'] = $resourceId;
        $obj['users_id_online'] = $this->clientsUsersId;

        $msgToSend = json_encode($obj);
        _log_message("msgToResourceId: resourceId=({$resourceId}) {$type}");
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
        if (empty($users_id) || empty($this->clientsUsersId)) {
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
                if ($this->isUserLive($user_id)) {
                    foreach ($this->clientsUsersId[$user_id]['resourceId'] as $resourceId) {
                        $count++;
                        $this->msgToResourceId($msg, $resourceId, $type);
                    }
                }
            }
        } catch (\Exception $exc) {
            echo $exc->getTraceAsString();
            var_dump($users_id, $this->clientsUsersId);
        }

        _log_message("msgToUsers_id: sent to ($count) clients users_id=" . json_encode($users_id));
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
        $return['LivesTotals'] = $this->getLivesTotal();
        if (!$isAdmin) {
            $SocketGetTotals = $return;
        }
        return $return;
    }

    function getLivesTotal() {
        if (!isset($this->totalUsersOnLives) || $this->totalUsersOnLives['updated'] < strtotime('- 1 minute')) {

            $stats = getStatsNotifications();

            $statsList = array();

            foreach ($stats as $server) {
                if (is_array($server) || is_object($server)) {
                    foreach ($server as $lt) {
                        if (!empty($lt['key'])) {
                            $statsList[$lt['key']] = ['total_viewers' => $this->isLiveUsersEnabled() ? \LiveUsers::getTotalUsers($lt['key'], $lt['live_servers_id']) : 0];
                        }
                    }
                }
            }

            $this->totalUsersOnLives = array('updated' => time(), 'statsList' => $statsList);
        }

        foreach ($this->totalUsersOnLives['statsList'] as $key => $lt) {
            if(!empty($lt['key']) && $lt['key'] !== 'key' && isset($this->clientsWatchinLive[$lt['key']]) && is_array($this->clientsWatchinLive[$lt['key']])){
                $this->totalUsersOnLives['statsList'][$key]['watching_now'] = count($this->clientsWatchinLive[$lt['key']]);
            }else{
                $this->totalUsersOnLives['statsList'][$key]['watching_now'] = 0;
            }
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
        _log_message("msgToAll FROM ({$from->resourceId}) {$type} Total Clients: " . count($this->clients));
        foreach ($this->clients as $key => $client) {
            $this->msgToResourceId($msg, $key, $type);
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
        \_mysql_connect(true);
        $msg['is_live'] = \Live::isLiveAndIsReadyFromKey($live_key['key'], $live_key['live_servers_id'], true);
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

    public function isUserLive($users_id) {
        return !empty($this->clientsUsersId[$users_id]) && !empty($this->clientsUsersId[$users_id]['resourceId']);
    }

}

function _log_message($msg, $type = "") {
    global $SocketDataObj;
    if (!empty($SocketDataObj->debugAllUsersSocket) || !empty($SocketDataObj->debugSocket)) {
        echo date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL;
    } else if ($type == \AVideoLog::$ERROR) {
        _error_log($msg, \AVideoLog::$SOCKET);
        echo "\e[1;31;40m" . date('Y-m-d H:i:s') . ' ' . $msg . "\e[0m" . PHP_EOL;
    }
}
