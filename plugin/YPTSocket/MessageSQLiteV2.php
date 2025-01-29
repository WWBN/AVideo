<?php

namespace Socket;

use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use User;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

class Message implements MessageComponentInterface
{
    const MSG_TO_ALL_TIMEOUT = 5;
    static $lastMessageToAllDuration = 0;
    static $lastMessageToAllDurationMessages = [];
    static $msgToAll = array();
    static $isSendingToAll = false;
    static $mem_usage;
    static $mem;
    protected $clients;
    protected $clientsLoggedConnections = 0;
    protected $disconnectAfter = 14400; //4 hours
    protected $clientsInVideos = array();
    protected $clientsInLives = array();
    protected $clientsInLivesLinks = array();
    protected $clientsInChatsRooms = array();
    protected $itemsToCheck = array(
        array('parameter' => 'clientsLoggedConnections', 'index' => 'users_id', 'class_prefix' => ''),
        array('parameter' => 'clientsInVideos', 'index' => 'videos_id', 'class_prefix' => 'total_on_videos_id_'),
        array('parameter' => 'clientsInLives', 'index' => 'live_key_servers_id', 'class_prefix' => 'total_on_live_'),
        array('parameter' => 'clientsInLivesLinks', 'index' => 'liveLink', 'class_prefix' => 'total_on_live_links_id_'),
        array('parameter' => 'clientsInChatsRooms', 'index' => 'room_users_id', 'class_prefix' => '')
    );

    public function __construct()
    {
        //$this->loop->ad
        $this->clients = array();
        _log_message("Construct");
        $this->initPeriodicTask();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        global $global;
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
            _log_message("Invalid websocket token [{$global['webSiteRootURL']}] [{$wsocketGetVars['webSocketToken']}]");
            return false;
        }

        $live_key = object_to_array(@$json->live_key);
        if (empty($live_key)) {
            $live_key = array();
            $live_key['key'] = '';
            $live_key['live_servers_id'] = 0;
            $live_key['liveLink'] = '';
        }

        //_error_log(json_encode(array($json, $wsocketGetVars)), \AVideoLog::$SOCKET);
        //var_dump($live_key);
        $client = array();
        $client['time'] = time();
        $client['resourceId'] = intval($conn->resourceId);
        $client['users_id'] = intval($json->from_users_id);
        $client['room_users_id'] = intval(@$json->room_users_id);
        $client['videos_id'] = intval($json->videos_id);
        $client['live_key_servers_id'] = "{$live_key['key']}_{$live_key['live_servers_id']}";
        $client['liveLink'] = $live_key['liveLink'];
        $client['isAdmin'] = $json->isAdmin;
        $client['live_key'] = $live_key['key'];
        $client['live_servers_id'] = intval($live_key['live_servers_id']);
        $client['user_name'] = $json->user_name;
        $client['identification'] = User::getNameIdentificationById($client['users_id']);
        $client['browser'] = $json->browser;
        $client['yptDeviceId'] = $json->yptDeviceId;
        $client['client'] = deviceIdToObject($json->yptDeviceId);
        if (!empty($wsocketGetVars['webSocketSelfURI'])) {
            $client['selfURI'] = $wsocketGetVars['webSocketSelfURI'];
        } else {
            $client['selfURI'] = $json->selfURI;
        }
        $client['isCommandLine'] = @$wsocketGetVars['isCommandLine'];
        $client['page_title'] = @utf8_encode(@$wsocketGetVars['page_title']);
        $client['ip'] = $json->ip;
        if (!empty($json->location)) {
            $client['location'] = $json->location->country_name;
            $client['country_name'] = $json->location->country_name;
            $client['country_code'] = $json->location->country_code;
        } else {
            $client['location'] = 0;
            $client['country_name'] = 0;
            $client['country_code'] = 0;
        }
        $client['browser'] = $client['client']->browser;
        $client['os'] = $client['client']->os;
        $client['data'] = $json;

        if (empty($client['room_users_id'])) {
            $queryString = parse_url($client['selfURI'], PHP_URL_QUERY);
            parse_str($queryString, $params);
            if (!empty($params['room_users_id'])) {
                $client['room_users_id'] = intval($params['room_users_id']);
            }
        }
        $client['chat_is_banned'] = 0;
        if (!empty($client['room_users_id']) && !empty($client['users_id'])) {
            require_once $global['systemRootPath'] . 'plugin/Chat2/Objects/ChatBan.php';
            if (\ChatBan::isUserBanned($client['room_users_id'], $client['users_id'])) {
                $client['chat_is_banned'] = 1;
            }
        }
        //var_dump($client, $json, $wsocketGetVars);
        //var_dump($client['liveLink'], $live_key);

        $this->setClient($conn, $client);
        dbInsertConnection($client);

        if ($client['browser'] == \SocketMessageType::TESTING) {
            _log_message("Test detected and received from ($conn->resourceId) " . PHP_EOL . "\e[1;32;40m*** SUCCESS TEST CONNECION {$json->test_msg} ***\e[0m");
            $this->msgToResourceId($json, $conn->resourceId, \SocketMessageType::TESTING);
        } else if ($this->shouldPropagateInfo($client) && !$this->isDeviceCommandLine($client['yptDeviceId'])) {
            $this->msgToAll(
                array(
                    'users_id' => $client['users_id'],
                    'yptDeviceId' => $client['yptDeviceId'],
                    'live_key_servers_id' => $client['live_key_servers_id'],
                    'identification' => $client['identification'],
                    'videos_id' => $client['videos_id'],
                    'room_users_id' => $client['room_users_id'],
                    'chat_is_banned' => $client['chat_is_banned'],
                    'resourceId' => $client['resourceId']
                ),
                \SocketMessageType::NEW_CONNECTION
            );
        } else {
            global $_shouldPropagateInfoLastMessage;
            _log_message("NOT shouldPropagateInfo {$_shouldPropagateInfoLastMessage}");
            if($this->isDeviceCommandLine($client['yptDeviceId'])){
                //var_dump($json,$wsocketGetVars);
            }
        }
        $end = number_format(microtime(true) - $start, 4);
        //_log_message("Connection opened in {$end} seconds users_id={$client['users_id']} selfURI={$client['selfURI']} isCommandLine={$client['isCommandLine']} page_title={$client['page_title']} browser={$client['browser']} ");
        //if(!empty($client['isCommandLine'])){
        //_error_log("isCommandLine close it {$client['browser']} {$client['selfURI']}", \AVideoLog::$SOCKET);
        //$conn->close();
        //}
    }

    public function onClose(ConnectionInterface $conn)
    {
        global $onMessageSentTo, $SocketGetTotals;
        if (empty($conn)) {
            return false;
        }
        $client = dbGetRowFromResourcesId($conn->resourceId);
        if (empty($client)) {
            $client = array('users_id' => 0);
        }
        //_log_message("onClose {$conn->resourceId} before deleted");
        dbDeleteConnection($conn->resourceId);
        //_log_message("onClose {$conn->resourceId} has deleted");
        $this->unsetClient($conn, $client);
        if ($this->shouldPropagateInfo($client) && !empty($client['yptDeviceId']) && !$this->isDeviceCommandLine($client['yptDeviceId'])) {
            $this->msgToAll(array('users_id' => $client['users_id'], 'disconnected' => $conn->resourceId), \SocketMessageType::NEW_DISCONNECTION);
        }
        //_log_message("Connection {$conn->resourceId} has disconnected");
    }

    protected function setClient(ConnectionInterface $conn, $client)
    {
        $this->clients[$conn->resourceId] = $conn;
        foreach ($this->itemsToCheck as $value) {
            if (!empty($client[$value['index']])) {
                if (!is_array($this->{$value['parameter']})) {
                    $this->{$value['parameter']} = array();
                }
                if (empty($this->{$value['parameter']}[$client[$value['index']]])) {
                    $this->{$value['parameter']}[$client[$value['index']]] = 1;
                } else {
                    $this->{$value['parameter']}[$client[$value['index']]]++;
                }
            }
        }
    }

    protected function unsetClient(ConnectionInterface $conn, $client)
    {
        unset($this->clients[$conn->resourceId]);

        foreach ($this->itemsToCheck as $value) {
            if (!empty($client[$value['index']])) {
                if (!is_array($this->{$value['parameter']})) {
                    $this->{$value['parameter']} = array();
                } else {
                    $this->{$value['parameter']}[$client[$value['index']]]--;
                    if ($this->{$value['parameter']}[$client[$value['index']]] <= 0) {
                        unset($this->{$value['parameter']}[$client[$value['index']]]);
                    }
                }
            }
        }
    }

    function getTotalFromVars()
    {
        $totals = array();

        foreach ($this->itemsToCheck as $value) {
            if (!empty($value['class_prefix'])) {
                foreach ($this->{$value['parameter']} as $key2 => $value2) {
                    if (empty($key2) || $key2 === '_0' || $key2 === '_') {
                        continue;
                    }
                    $index = "{$value['class_prefix']}{$key2}";
                    $totals[$index] = $value2;
                }
            }
        }

        return $totals;
    }

    public function getTotals()
    {
        //$getTotals = dbGetDBTotals();
        $totals = array();
        $totals['total_devices_online'] = dbGetTotalUniqueDevices();
        $totals['total_users_online'] = dbGetTotalConnections();
        $totals['total_users_unique_users'] = dbGetTotalUniqueUsers();
        //$totals['class_to_update'] = dbGetTotalUniqueDevices();
        //$totals['users_uri'] = $getTotals['users_uri'];
        $totals['LivesTotals'] = $this->getLivesTotal();

        //$getTotals = dbGetDBTotals();
        $getTotalFromVars = $this->getTotalFromVars();

        $totals = array_merge($totals, $getTotalFromVars);
        //var_dump($totals);
        return $totals;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        global $onMessageSentTo, $SocketGetTotals;
        $SocketGetTotals = null;
        $onMessageSentTo = array();
        //_log_message("onMessage: {$msg}");
        $json = _json_decode($msg);
        if (empty($json)) {
            //_log_message("onMessage ERROR: JSON is empty ");
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
                } else if (!empty($json['json']['redirectLive'])) {
                    _log_message("onMessage:msgToAllSameLive: " . json_encode($json));
                    $this->msgToAllSameLive($json['json']['redirectLive']['live_key'], $json['json']['redirectLive']['live_servers_id'], $json);
                } else {
                    $this->msgToAll($json);
                }
                break;
        }
    }

    private function isDeviceCommandLine($yptDeviceId)
    {
        return preg_match('/^commandLine-0$/',$yptDeviceId);
    }

    private function shouldPropagateInfo($row)
    {
        global $_shouldPropagateInfoLastMessage;
        if (!empty($row['yptDeviceId']) && preg_match('/^unknowDevice.*/', $row['yptDeviceId'])) {
            $_shouldPropagateInfoLastMessage = 'unknowDevice '.$row['yptDeviceId'];
            return false;
        }
        if (!empty($row['yptDeviceId']) && $this->isDeviceCommandLine($row['yptDeviceId'])) {
            $_shouldPropagateInfoLastMessage = 'commandLine '.$row['yptDeviceId'];
            return true;
        }
        if (!empty($row['selfURI']) && preg_match('/.*getConfiguration.json.php$/', $row['selfURI'])) {
            $_shouldPropagateInfoLastMessage = 'getConfiguration';
            return false;
        }
        return true;
    }

    private function getShouldPropagateInfoLastMessage()
    {
        global $_shouldPropagateInfoLastMessage;
        return $_shouldPropagateInfoLastMessage;
    }

    public function msgToResourceId($msg, $resourceId, $type = "", $totals = array())
    {
        global $onMessageSentTo, $SocketDataObj;
        if (empty($resourceId)) {
            return false;
        }

        if (empty($this->clients[$resourceId])) {
            _log_message("msgToResourceId: resourceId=({$resourceId}) is empty");
            return false;
        }
        $startTime = microtime(true);

        $row = dbGetRowFromResourcesId($resourceId);

        if (!self::isValidSelfURI($row['selfURI'])) {
            _log_message("msgToResourceId: resourceId=({$resourceId}) selfURI is invalid {$row['selfURI']}");
            return false;
        }

        if (empty($row)) {
            _log_message("msgToResourceId: resourceId=({$resourceId}) NOT found");
            return false;
        }

        if (!$this->shouldPropagateInfo($row)) {
            _log_message("msgToResourceId: we wil NOT send the message to resourceId=({$resourceId}) [{$type}] " . $this->getShouldPropagateInfoLastMessage());
            return false;
        }

        //_log_message("msgToResourceId: go ".json_encode($msg));
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        if (!empty($msg['webSocketToken'])) {
            unset($msg['webSocketToken']);
        }
        if (empty($type)) {
            _log_message("msgToResourceId: empty message type ");
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

        if (empty($totals)) {
            $totals = $this->getTotals();
        }
        $return = $totals;

        $info = array(
            'webSocketServerVersion' => $SocketDataObj->serverVersion,
            'socket_mem' => Message::$mem,
            'socket_users_id' => $users_id,
            'socket_resourceId' => $resourceId,
        );

        $autoUpdateOnHTML = array_merge($info, $return);
        $obj['autoUpdateOnHTML'] = $autoUpdateOnHTML;
        $obj['lastMessageToAllDuration'] = self::$lastMessageToAllDuration;
        $obj['lastMessageToAllDurationMessages'] = self::$lastMessageToAllDurationMessages;

        //$obj['users_uri'] = $return['users_uri'];
        $obj['resourceId'] = $resourceId;
        $obj['users_id_online'] = dbGetUniqueUsers();
        $obj['mem'] = Message::$mem;

        $msgToSend = json_encode($obj);
        $this->clients[$resourceId]->send($msgToSend);
        // End timing and calculate the duration
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        //_log_message("msgToResourceId: resourceId=({$resourceId}) {$type} users_id={$obj['users_id']} duration=$duration");
        return true;
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $client = dbGetRowFromResourcesId($conn->resourceId);
        $debug = array(
            'resourceId' => $conn->resourceId,
            'client' => $client['client'],
            'ip' => $client['ip'],
            'selfURI' => $client['selfURI'],
            //'debug_backtrace'=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            //'trace'=>$e->getTrace(),
        );
        dbDeleteConnection($conn->resourceId);
        _error_log("onError " . json_encode($debug), \AVideoLog::$SOCKET);
        $conn->close();
    }

    public function msgToUsers_id($msg, $users_id, $type = "")
    {
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
        } catch (\Throwable $th) {
            echo $th->getTraceAsString();
        }

        _log_message("msgToUsers_id: sent to ($count) clients users_id=" . json_encode($users_id));
    }

    public function msgToSelfURI($msg, $pattern, $type = "")
    {
        if (empty($pattern)) {
            return false;
        }
        $count = 0;
        $rows = dbGetAllResourceIdFromSelfURI($pattern);
        $totals = $this->getTotals();
        foreach ($rows as $row) {
            $count++;
            $this->msgToResourceId($msg, $row['resourceId'], $type, $totals);
        }
        _log_message("msgToSelfURI: sent to ($count) clients pattern={$pattern} {$type}");
    }

    function getLivesTotal()
    {
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

    private function isLiveUsersEnabled()
    {
        global $_isLiveUsersEnabled;
        if (!isset($_isLiveUsersEnabled)) {
            $_isLiveUsersEnabled = \AVideoPlugin::isEnabledByName('LiveUsers') && method_exists('LiveUsers', 'getTotalUsers');
        }
        return $_isLiveUsersEnabled;
    }

    public function msgToAll($msg, $type = \SocketMessageType::UNDEFINED)
    {
        self::$msgToAll[] = array('msg' => $msg, 'type' => $type);
    }

    public function initPeriodicTask()
    {
        $loop = Loop::get();
        $loop->addPeriodicTimer(Message::MSG_TO_ALL_TIMEOUT, function (TimerInterface $timer) {
            // Your code to execute every 5 seconds
            //echo "Task executed at " . date('Y-m-d H:i:s') . ' '.json_encode(self::$msgToAll).PHP_EOL;
            // You can call other methods or perform any periodic action here
            if (!empty(self::$msgToAll)) {
                if (!Message::$isSendingToAll) {
                    //_error_log("initPeriodicTask _msgToAll start ", \AVideoLog::$SOCKET);
                    $this->_msgToAll(self::$msgToAll, \SocketMessageType::MSG_TO_ALL);
                    self::$msgToAll = array();
                    //_error_log("initPeriodicTask _msgToAll end ", \AVideoLog::$SOCKET);
                }
            }
        });
    }

    public function _msgToAll($msg, $type = "")
    {
        $loop = Loop::get(); // Get the ReactPHP event loop
        $rows = dbGetAll(); // Get all clients
        $totals = $this->getTotals(); // Get totals for the message
        $time = time();
        $delay = self::MSG_TO_ALL_TIMEOUT/100; // Delay in seconds between iterations
        $iterationsMaxDuration = self::MSG_TO_ALL_TIMEOUT/50; // Delay in seconds between iterations
        Message::$isSendingToAll = true;

        self::$lastMessageToAllDurationMessages = [];
        $clients = $rows; // Copy all clients
        $totalMessages = 0; // Track total messages sent
        $processStartTime = microtime(true); // Track total processing time

        // Process clients in timed chunks
        $loop->addPeriodicTimer($delay, function (TimerInterface $timer) use (
            &$clients,
            $msg,
            $type,
            $totals,
            $time,
            $loop,
            $iterationsMaxDuration,
            &$totalMessages,
            $processStartTime
        ) {
            $start = microtime(true); // Start timer for this iteration
            $countMessages = 0; // Track messages sent in this iteration

            while (!empty($clients)) {
                // Take the first client
                $client = array_shift($clients);
                $startTime = microtime(true);

                // Disconnect old clients
                if ($client['time'] + $this->disconnectAfter < $time) {
                    if (!empty($this->clients[$client['resourceId']])) {
                        _error_log("resourceId={$client['resourceId']} close on line " . __LINE__, \AVideoLog::$SOCKET);
                        $this->clients[$client['resourceId']]->close();
                    }
                    unset($this->clients[$client['resourceId']]);
                    continue;
                }

                // Disconnect command line clients after 1 min
                if ($client['isCommandLine']) {
                    if ($client['time'] + 60 < $time && !empty($this->clients[$client['resourceId']])) {
                        _error_log("resourceId={$client['resourceId']} disconnect commandline after 1 min", \AVideoLog::$SOCKET);
                        $this->clients[$client['resourceId']]->close();
                        unset($this->clients[$client['resourceId']]);
                    }
                    continue;
                }

                // Send the message
                $this->msgToResourceId($msg, $client['resourceId'], $type, $totals);
                $countMessages++;
                $totalMessages++;

                // Measure message duration
                $endTime = microtime(true);
                $duration = $endTime - $startTime;

                $maxDuration = 0.5;
                if (self::$lastMessageToAllDuration > (self::MSG_TO_ALL_TIMEOUT / 3)) {
                    $maxDuration = 0.4;
                }
                if (self::$lastMessageToAllDuration > (self::MSG_TO_ALL_TIMEOUT / 2)) {
                    $maxDuration = 0.3;
                }
                if (self::$lastMessageToAllDuration > self::MSG_TO_ALL_TIMEOUT) {
                    $maxDuration = 0.2;
                }

                // Log and close client if duration exceeds max
                if ($duration > $maxDuration) {
                    $msg = "resourceId={$client['resourceId']} took {$duration} seconds to send the message  maxDuration=$maxDuration clients=" . count($this->clients);
                    _log_message($msg);
                    _error_log($msg, \AVideoLog::$SOCKET);
                    self::$lastMessageToAllDurationMessages[] = [
                        'resourceId' => $client['resourceId'],
                        'client' => _json_decode($client['client']),
                        'ip' => $client['ip'],
                        'selfURI' => $client['selfURI'],
                        'duration' => $duration
                    ];
                    $this->clients[$client['resourceId']]->close();
                }

                // Check elapsed time for this iteration
                $elapsedTime = microtime(true) - $start;
                if ($elapsedTime > $iterationsMaxDuration) { // Interrupt if processing takes longer than the delay
                    _log_message("Interrupting processing after {$elapsedTime} seconds, {$countMessages} messages processed, resuming in the next tick.");
                    break;
                }
            }

            // Stop the timer when all clients are processed
            if (empty($clients)) {
                $loop->cancelTimer($timer);
                $processEndTime = microtime(true);
                self::$lastMessageToAllDuration = $processEndTime - $processStartTime;
                $totalProcessTime = number_format(self::$lastMessageToAllDuration, 4);
                _log_message("All clients processed. Total messages sent: {$totalMessages}. Total process time: {$totalProcessTime} seconds.");
                Message::$isSendingToAll = false;
            }
        });
    }


    public function msgToAllSameVideo($videos_id, $msg)
    {
        if (empty($videos_id)) {
            return false;
        }
        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }
        _log_message("msgToAllSameVideo: {$videos_id}");
        $totals = $this->getTotals();
        foreach (dbGetAllResourcesIdFromVideosId($videos_id) as $client) {
            if ($client['isCommandLine']) {
                _error_log("msgToAllSameVideo continue", \AVideoLog::$SOCKET);
                continue;
            }
            $this->msgToResourceId($msg, $client['resourceId'], \SocketMessageType::ON_VIDEO_MSG, $totals);
        }
    }

    public function msgToAllSameLive($live_key, $live_servers_id, $msg)
    {
        if (empty($live_key)) {
            return false;
        }

        if (!is_array($msg)) {
            $this->msgToArray($msg);
        }

        $live_servers_id = intval($live_servers_id);

        $rows = dbGetAllResourcesIdFromLive($live_key, $live_servers_id);
        $totals = $this->getTotals();

        _log_message("msgToAllSameLive: {$live_key}_{$live_servers_id} total=" . count($rows) . ' ' . json_encode($msg));
        foreach ($rows as $value) {
            if (!empty($value['isCommandLine'])) {
                continue;
            }
            _log_message("msgToAllSameLive: {$value['resourceId']} ");
            $this->msgToResourceId($msg, $value['resourceId'], \SocketMessageType::ON_LIVE_MSG, $totals);
        }
    }

    private function msgToArray(&$json)
    {
        $json = $this->makeSureIsArray($json);
        return true;
    }

    private function makeSureIsArray($msg)
    {
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

    public function getTags()
    {
        return array('free', 'live');
    }

    public function isUserLive($users_id)
    {
        return dbIsUserOnLine($users_id);
    }

    static function isValidSelfURI($selfURI)
    {
        if (preg_match('/MobileYPT\/getConfiguration\.json\.php/', $selfURI)) {
            return true;
        }
        if (preg_match('/\.json/i', $selfURI)) {
            return false;
        }
        if (preg_match('/plugin\/Live\/on_/i', $selfURI)) {
            return false;
        }
        return true;
    }
}

function _log_message($msg, $type = "")
{
    global $SocketDataObj;
    if (!empty($SocketDataObj->debugAllUsersSocket) || !empty($SocketDataObj->debugSocket)) {
        //_error_log($msg, \AVideoLog::$SOCKET);
        Message::$mem_usage = memory_get_usage();
        Message::$mem = humanFileSize(Message::$mem_usage);
        echo date('Y-m-d H:i:s') . " Using: " . Message::$mem . " RAM " . $msg . PHP_EOL;
    } else if ($type == \AVideoLog::$ERROR) {
        _error_log($msg, \AVideoLog::$SOCKET);
        echo "\e[1;31;40m" . date('Y-m-d H:i:s') . ' ' . $msg . "\e[0m" . PHP_EOL;
    }
}
