<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistoryLog.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';

$getStatsObject = array();
$_getStats = array();

class Live extends PluginAbstract {

    public function getDescription() {
        return "Broadcast a RTMP video from your computer<br> and receive HLS streaming from servers";
    }

    public function getName() {
        return "Live";
    }

    public function getHTMLMenuRight() {
        global $global;
        $buttonTitle = $this->getButtonTitle();
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
    }

    public function getUUID() {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }

    public function getPluginVersion() {
        return "4.0";
    }

    public function updateScript() {
        global $global;
        //update version 2.0
        $sql = "SELECT 1 FROM live_transmitions_history LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch = sqlDAL::fetchAssoc($res);
        if (!$fetch) {
            sqlDal::writeSql(file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV2.0.sql'));
        }
        //update version 3.0
        $sql = "SELECT 1 FROM live_transmition_history_log LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch = sqlDAL::fetchAssoc($res);
        if (!$fetch) {
            sqlDal::writeSql(file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV3.0.sql'));
        }
        //update version 4.0
        $sql = "SELECT 1 FROM live_servers LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch = sqlDAL::fetchAssoc($res);
        if (!$fetch) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV4.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        return true;
    }

    public function getEmptyDataObject() {
        global $global;
        $server = parse_url($global['webSiteRootURL']);

        $scheme = "http";
        $port = "8080";
        if (strtolower($server["scheme"]) == "https") {
            $scheme = "https";
            $port = "8443";
        }

        $obj = new stdClass();
        $obj->button_title = "LIVE";
        $obj->server = "rtmp://{$server['host']}/live";
        $obj->playerServer = "{$scheme}://{$server['host']}:{$port}/live";
        $obj->stats = "{$scheme}://{$server['host']}:{$port}/stat";
        $obj->disableDVR = false;
        $obj->disableGifThumbs = false;
        $obj->useAadaptiveMode = false;
        $obj->protectLive = false;
        $obj->experimentalWebcam = false;
        $obj->doNotShowLiveOnVideosList = false;
        $obj->doNotShowGoLiveButton = false;
        $obj->doNotProcessNotifications = false;
        $obj->useLiveServers = false;
        $obj->hls_path = "/HLS/live";
        $obj->requestStatsTimout = 4; // if the server does not respond we stop wait
        $obj->cacheStatsTimout = 15; // we will cache the result
        $obj->requestStatsInterval = 15; // how many seconds untill request the stats again
        return $obj;
    }

    public function getButtonTitle() {
        $o = $this->getDataObject();
        return $o->button_title;
    }

    public function getKey() {
        $o = $this->getDataObject();
        return $o->key;
    }

    static function getServer() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getCurrentLiveServersId());
            if (!empty($ls->getRtmp_server())) {
                return $ls->getRtmp_server();
            }
        }
        return $obj->server;
    }

    static function getPlayerServer() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getCurrentLiveServersId());
            if (!empty($ls->getPlayerServer())) {
                return $ls->getPlayerServer();
            }
        }
        return $obj->playerServer;
    }

    static function getUseAadaptiveMode() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getCurrentLiveServersId());
            return $ls->getUseAadaptiveMode();
        }
        return $obj->useAadaptiveMode;
    }

    static function getRemoteFile() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getCurrentLiveServersId());
            return $ls->getGetRemoteFile();
        }
        return false;
    }

    static function getRemoteFileFromRTMPHost($rtmpHostURI) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            $live_servers_id = Live_servers::getServerIdFromRTMPHost($rtmpHostURI);
            if ($live_servers_id) {
                $ls = new Live_servers($live_servers_id);
                return $ls->getGetRemoteFile();
            }
        }
        return false;
    }

    static function getLiveServersIdRequest() {
        if (empty($_REQUEST['live_servers_id'])) {
            return 0;
        }
        return intval($_REQUEST['live_servers_id']);
    }

    static function getM3U8File($uuid) {
        global $global;
        $o = AVideoPlugin::getObjectData("Live");
        $playerServer = self::getPlayerServer();
        $live_servers_id = self::getLiveServersIdRequest();
        $liveServer = new Live_servers($live_servers_id);
        if ($liveServer->getStats_url()) {
            $o->protectLive = $liveServer->getProtectLive();
            $o->useAadaptiveMode = $liveServer->getUseAadaptiveMode();
        }
        if ($o->protectLive) {
            return "{$global['webSiteRootURL']}plugin/Live/m3u8.php?live_servers_id={$live_servers_id}&uuid=" . encryptString($uuid);
        } else if ($o->useAadaptiveMode) {
            return $playerServer . "/{$uuid}.m3u8";
        } else {
            return $playerServer . "/{$uuid}/index.m3u8";
        }
    }

    public function getDisableGifThumbs() {
        $o = $this->getDataObject();
        return $o->disableGifThumbs;
    }

    public function getStatsURL($live_servers_id = 0) {
        global $global;
        $o = $this->getDataObject();
        if (!empty($live_servers_id)) {
            $liveServer = new Live_servers($live_servers_id);
            if ($liveServer->getStats_url()) {
                return $liveServer->getStats_url();
            }
        }
        return $o->stats;
    }

    public function getChat($uuid) {
        global $global;
        //check if LiveChat Plugin is available
        $filename = $global['systemRootPath'] . 'plugin/LiveChat/LiveChat.php';
        if (file_exists($filename)) {
            require_once $filename;
            LiveChat::includeChatPanel($uuid);
        }
    }

    function getStatsObject($live_servers_id = 0) {
        global $getStatsObject;
        if (!empty($getStatsObject[$live_servers_id])) {
            return $getStatsObject[$live_servers_id];
        }
        $o = $this->getDataObject();
        if ($o->doNotProcessNotifications) {
            $xml = new stdClass();
            $xml->server = new stdClass();
            $xml->server->application = array();
            return $xml;
        }
        if (empty($o->requestStatsTimout)) {
            $o->requestStatsTimout = 2;
        }
        ini_set('allow_url_fopen ', 'ON');
        $url = $this->getStatsURL($live_servers_id);
        if (!empty($_SESSION['getStatsObjectRequestStatsTimout'][$url])) {
            _error_log("Live::getStatsObject RTMP Server ($url) is NOT responding we will wait less from now on => live_servers_id = ($live_servers_id) ");
            // if the server already fail, do not wait mutch for it next time, just wait 0.5 seconds
            $o->requestStatsTimout = $_SESSION['getStatsObjectRequestStatsTimout'][$url];
        }
        $data = $this->get_data($url, $o->requestStatsTimout);
        if (empty($data)) {
            if (empty($_SESSION['getStatsObjectRequestStatsTimout'][$url])) {
                // the server fail to respont, just wait 0.5 seconds until it respond again
                _session_start();
                if (empty($_SESSION['getStatsObjectRequestStatsTimout'])) {
                    $_SESSION['getStatsObjectRequestStatsTimout'] = array();
                }
                $_SESSION['getStatsObjectRequestStatsTimout'][$url] = 0.5;
            }
            _error_log("Live::getStatsObject RTMP Server ($url) is OFFLINE, we could not connect on it => live_servers_id = ($live_servers_id) ", AVideoLog::$ERROR);
            $data = '<?xml version="1.0" encoding="utf-8" ?><?xml-stylesheet type="text/xsl" href="stat.xsl" ?><rtmp><server><application><name>The RTMP Server is Unavailable</name><live><nclients>0</nclients></live></application></server></rtmp>';
        } else {
            if (!empty($_SESSION['getStatsObjectRequestStatsTimout'][$url])) {
                _error_log("Live::getStatsObject RTMP Server ($url) is respond again => live_servers_id = ($live_servers_id) ");
                // the server respont again, wait the default time
                $_SESSION['getStatsObjectRequestStatsTimout'][$url] = 0;
                unset($_SESSION['getStatsObjectRequestStatsTimout'][$url]);
            }
        }
        $xml = simplexml_load_string($data);
        $getStatsObject[$live_servers_id] = $xml;
        return $xml;
    }

    function get_data($url, $timeout) {
        try {
            return @url_get_contents($url, "", $timeout);
        } catch (Exception $exc) {
            _error_log($exc->getTraceAsString());
        }
        return false;
    }

    public function getTags() {
        return array('free', 'live', 'streaming', 'live stream');
    }

    public function getChartTabs() {
        return '<li><a data-toggle="tab" id="liveVideos" href="#liveVideosMenu"><i class="fas fa-play-circle"></i> Live videos</a></li>';
    }

    public function getChartContent() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/report.php';
    }

    static public function saveHistoryLog($key) {
        // get the latest history for this key
        $latest = LiveTransmitionHistory::getLatest($key);

        if (!empty($latest)) {
            LiveTransmitionHistoryLog::addLog($latest['id']);
        }
    }

    public function dataSetup() {
        $obj = $this->getDataObject();
        if (!isLive() || $obj->disableDVR) {
            return "";
        }
        return "liveui: true";
    }

    static function stopLive($users_id) {
        if (!User::isAdmin() && User::getId() != $users_id) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj)) {
            $server = str_replace("stats", "", $obj->stats);
            $lt = new LiveTransmition(0);
            $lt->loadByUser($users_id);
            $key = $lt->getKey();
            $appName = self::getApplicationName();
            $url = "{$server}control/drop/publisher?app={$appName}&name=$key";
            url_get_contents($url);
            $dir = $obj->hls_path . "/$key";
            if (is_dir($dir)) {
                exec("rm -fR $dir");
                rrmdir($dir);
            }
        }
    }

    // not implemented yet
    static function startRecording($users_id) {
        if (!User::isAdmin() && User::getId() != $users_id) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj)) {
            $server = str_replace("stats", "", $obj->stats);
            $lt = new LiveTransmition(0);
            $lt->loadByUser($users_id);
            $key = $lt->getKey();
            $appName = self::getApplicationName();
            $url = "{$server}control/record/start?app={$appName}&name=$key";
            url_get_contents($url);
        }
    }

    static function getApplicationName() {
        $obj = AVideoPlugin::getObjectData('Live');
        $parts = explode("/", $obj->playerServer);
        $live = end($parts);

        if (empty($live)) {
            $live = "live";
        }
        return $live;
    }

    // not implemented yet
    static function stopRecording($users_id) {
        if (!User::isAdmin() && User::getId() != $users_id) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj)) {
            $server = str_replace("stats", "", $obj->stats);
            $lt = new LiveTransmition(0);
            $lt->loadByUser($users_id);
            $key = $lt->getKey();
            $appName = self::getApplicationName();
            $url = "{$server}control/record/stop?app={$appName}&name=$key";
            url_get_contents($url);
        }
    }

    static function getLinkToLiveFromUsers_id($users_id) {
        if (empty($users_id)) {
            return false;
        }
        global $global;
        $user = new User($users_id);
        if (empty($user)) {
            return false;
        }
        $ls = self::getCurrentLiveServersId();
        return "{$global['webSiteRootURL']}plugin/Live/?live_servers_id={$ls}&c=" . urlencode($user->getChannelName());
    }

    static function getAvailableLiveServersId() {
        $ls = self::getAvailableLiveServer();
        if (empty($ls)) {
            return 0;
        } else {
            return intval($ls->live_servers_id);
        }
    }

    static function getCurrentLiveServersId() {
        $live_servers_id = self::getLiveServersIdRequest();
        if ($live_servers_id) {
            return $live_servers_id;
        } else {
            return self::getAvailableLiveServersId();
        }
    }

    public function getVideosManagerListButtonTitle() {
        global $global;
        if (!User::isAdmin()) {
            return "";
        }
        $btn = '<br><button type="button" class="btn btn-default btn-light btn-sm btn-xs" onclick="document.location = \\\'' . $global['webSiteRootURL'] . 'plugin/Live/?users_id=\' + row.users_id + \'\\\';" data-row-id="right" ><i class="fa fa-circle"></i> Live Info</button>';
        return $btn;
    }

    public function getPluginMenu() {
        global $global;
        return '<a href="plugin/Live/view/editor.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit Live Servers</a>';
    }

    static function getStats() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            return self::_getStats(0);
        } else if (!empty(Live::getLiveServersIdRequest())) {
            $ls = new Live_servers(Live::getLiveServersIdRequest());
            if (!empty($ls->getPlayerServer())) {
                $server = self::_getStats($ls->getId());
                $server->live_servers_id = $ls->getId();
                $server->playerServer = $ls->getPlayerServer();
                return $server;
            }
        }
        $ls = Live_servers::getAllActive();
        $liveServers = array();
        $getLiveServersIdRequest = self::getLiveServersIdRequest();
        foreach ($ls as $value) {
            $server = Live_servers::getStatsFromId($value['id']);
            if(!empty($server) && is_object($server)){
                $server->live_servers_id = $value['id'];
                $server->playerServer = $value['playerServer'];

                foreach ($server->applications as $key => $app) {
                    $_REQUEST['live_servers_id'] = $value['id'];
                    $server->applications[$key]['m3u8'] = self::getM3U8File($app['key']);
                }

                $liveServers[] = $server;
            }else{
                _error_log("Live::getStats Live Server NOT found {$value['id']} " . json_encode($server)." " . json_encode($value));
            }
        }
        $_REQUEST['live_servers_id'] = $getLiveServersIdRequest;
        return $liveServers;
    }

    static function getAllServers() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            return array("id" => 0, "name" => __("Default"), "status" => "a", "rtmp_server" => $obj->server, 'playerServer' => $obj->playerServer, "stats_url" => $obj->stats, "disableDVR" => $obj->disableDVR, "disableGifThumbs" => $obj->disableGifThumbs, "useAadaptiveMode" => $obj->useAadaptiveMode, "protectLive" => $obj->protectLive, "getRemoteFile" => "");
        } else {
            return Live_servers::getAllActive();
        }
    }

    static function getAvailableLiveServer() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            return false;
        } else {
            $liveServers = self::getStats();
            usort($liveServers, function($a, $b) {
                if ($a->countLiveStream == $b->countLiveStream) {
                    return 0;
                }
                return ($a->countLiveStream < $b->countLiveStream) ? -1 : 1;
            });
            return $liveServers[0];
        }
    }

    static function _getStats($live_servers_id = 0) {
        global $global, $_getStats;
        if (empty($_REQUEST['name'])) {
            _error_log("Live::_getStats {$live_servers_id} GET " . json_encode($_GET));
            _error_log("Live::_getStats {$live_servers_id} POST " . json_encode($_POST));
            _error_log("Live::_getStats {$live_servers_id} REQUEST " . json_encode($_REQUEST));
            $_REQUEST['name'] = "undefined";
        }
        if (!empty($_getStats[$live_servers_id][$_REQUEST['name']]) && is_object($_getStats[$live_servers_id][$_REQUEST['name']])) {
            $_getStats[$live_servers_id][$_REQUEST['name']] = $_REQUEST['name'];
            //_error_log("Live::_getStats cached result {$_REQUEST['name']} " . json_encode($_getStats[$live_servers_id][$_REQUEST['name']]));
            return $_getStats[$live_servers_id][$_REQUEST['name']];
        }
        session_write_close();
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "OFFLINE";
        $obj->nclients = 0;
        $obj->applications = array();
        $obj->name = $_REQUEST['name'];
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $p = AVideoPlugin::loadPlugin("Live");
        $xml = $p->getStatsObject($live_servers_id);
        $xml = json_encode($xml);
        $xml = json_decode($xml);

        $stream = false;
        $lifeStream = array();
        //$obj->server = $xml->server;
        if (!empty($xml->server->application) && !is_array($xml->server->application)) {
            $application = $xml->server->application;
            $xml->server->application = array();
            $xml->server->application[] = $application;
        }
        foreach ($xml->server->application as $key => $application) {
            if (!empty($application->live->stream)) {
                if (empty($lifeStream)) {
                    $lifeStream = array();
                }

                $stream = $application->live->stream;
                if (empty($application->live->stream->name) && !empty($application->live->stream[0]->name)) {
                    foreach ($application->live->stream as $stream) {
                        $lifeStream[] = $stream;
                    }
                } else {
                    $lifeStream[] = $application->live->stream;
                }
            }
        }

        $obj->disableGif = $p->getDisableGifThumbs();
        $obj->countLiveStream = count($lifeStream);
        foreach ($lifeStream as $value) {
            if (!empty($value->name)) {
                $row = LiveTransmition::keyExists($value->name);
                if (!empty($row) && $value->name === $obj->name) {
                    $obj->msg = "ONLINE";
                }
                if (empty($row) || empty($row['public'])) {
                    continue;
                }

                $users = false;
                if ($liveUsersEnabled) {
                    $filename = $global['systemRootPath'] . 'plugin/LiveUsers/Objects/LiveOnlineUsers.php';
                    if (file_exists($filename)) {
                        require_once $filename;
                        $liveUsers = new LiveOnlineUsers(0);
                        $users = $liveUsers->getUsersFromTransmitionKey($value->name, $live_servers_id);
                    }
                }

                $u = new User($row['users_id']);
                if ($u->getStatus() !== 'a') {
                    continue;
                }

                $userName = $u->getNameIdentificationBd();
                $user = $u->getUser();
                $channelName = $u->getChannelName();
                $photo = $u->getPhotoDB();
                $UserPhoto = $u->getPhoto();
                $obj->applications[] = array("key" => $value->name, "users" => $users, "name" => $userName, "user" => $user, "photo" => $photo, "UserPhoto" => $UserPhoto, "title" => $row['title'], 'channelName' => $channelName);
                if ($value->name === $obj->name) {
                    $obj->error = property_exists($value, 'publishing') ? false : true;
                    $obj->msg = (!$obj->error) ? "ONLINE" : "Waiting for Streamer";
                    $obj->stream = $value;
                    $obj->nclients = intval($value->nclients);
                    break;
                }
            }
        }

        $appArray = AVideoPlugin::getLiveApplicationArray();
        $obj->applications = array_merge($obj->applications, $appArray);
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        //_error_log("Live::_getStats NON cached result {$_REQUEST['name']} " . json_encode($obj));
        return $obj;
    }

}
