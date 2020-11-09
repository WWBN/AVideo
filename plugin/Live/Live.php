<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistoryLog.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';

$getStatsObject = array();
$_getStats = array();

User::loginFromRequest();

class Live extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$LIVE,
            PluginTags::$FREE,
            PluginTags::$RECOMMENDED,
            PluginTags::$PLAYER,
        );
    }

    public function getDescription() {
        $desc = "Broadcast a RTMP video from your computer<br> and receive HLS streaming from servers";
        $lu = AVideoPlugin::loadPlugin("LiveUsers");
        if (!empty($lu)) {
            if (version_compare($lu->getPluginVersion(), "2.0") < 0) {
                $desc .= "<div class='alert alert-danger'>You MUST update your LiveUsers plugin to version 2.0 or greater</div>";
            }
        }
        return $desc;
    }

    public function getName() {
        return "Live";
    }

    public function getHTMLMenuRight() {
        global $global;
        $buttonTitle = $this->getButtonTitle();
        $obj = $this->getDataObject();
        if (!empty($obj->hideTopButton)) {
            return '';
        }
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
    }

    public function getUUID() {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }

    public function getPluginVersion() {
        return "5.2";
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
        //update version 5.0
        $sql = "SELECT 1 FROM live_restreams LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch = sqlDAL::fetchAssoc($res);
        if (!$fetch) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV5.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        //update version 5.1
        if (AVideoPlugin::compareVersion($this->getName(), "5.1") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV5.1.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        //update version 5.2
        if (AVideoPlugin::compareVersion($this->getName(), "5.2") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV5.2.sql');
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
        self::addDataObjectHelper('button_title', 'Button Title', 'This is the title that will appear in your button to enter in the Live panel');
        $obj->server = "rtmp://{$server['host']}/live";
        self::addDataObjectHelper('server', 'RTMP Server URL', 'Usually it is '."rtmp://{$server['host']}/live");
        $obj->playerServer = "{$scheme}://{$server['host']}:{$port}/live";
        self::addDataObjectHelper('playerServer', 'Player URL', 'This is a URL to your NGINX server, this URL will be used by the HTML5 player, If your site is HTTPS your player URL MUST be HTTPS as well, usually it is '."{$scheme}://{$server['host']}:{$port}/live");
        $obj->stats = "{$scheme}://{$server['host']}:{$port}/stat";
        self::addDataObjectHelper('stats', 'Stats Page URL', 'When you installed the NGINX you also install the stat.xsl, we will use it to grab the information when you have livestreams running, usually it is '."{$scheme}://{$server['host']}:{$port}/stat");
        $obj->restreamerURL = "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/restreamer.json.php";
        self::addDataObjectHelper('restreamerURL', 'Restreamer URL', 'https://github.com/WWBN/AVideo/wiki/Restream');
        $obj->controlURL = "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/control.json.php";
        self::addDataObjectHelper('controlURL', 'Control URL', 'Still under development');
        $obj->disableRestream = false;
        self::addDataObjectHelper('disableRestream', 'Disable Restream', 'If you check this, we will not send requests to your Restreamer URL');
        $obj->disableDVR = false;
        self::addDataObjectHelper('disableDVR', 'Disable DVR', 'Enable or disable the DVR Feature, you can control the DVR length in your nginx.conf on the parameter hls_playlist_length');
        $obj->disableGifThumbs = false;
        self::addDataObjectHelper('disableGifThumbs', 'Disable Gif Thumbs', 'This option will disable the Animated Gif render, it will save some hardware capacity from your encoder and may speedup your page');
        $obj->disableLiveThumbs = false;
        self::addDataObjectHelper('disableLiveThumbs', 'Disable Live thumbnails', 'This option will disable the çive image extraction and will use the user static image, it will save some hardware capacity from your encoder and may speedup your page');
        $obj->hideTopButton = false;
        self::addDataObjectHelper('hideTopButton', 'Hide Top Button', 'This will hide the "Go Live" button on the top menu bar');
        $obj->useAadaptiveMode = false;
        self::addDataObjectHelper('useAadaptiveMode', 'Adaptive mode', 'https://github.com/WWBN/AVideo/wiki/Adaptive-Bitrates-on-Livestream');
        $obj->protectLive = false;
        self::addDataObjectHelper('protectLive', 'Live Protection', 'With this your encryption key will be protected, and only your site player will be able to play your videos, download tools will not be able to download your video. if you want to share your live externally you can use the embed and you will still be protected. but if you want to use the m3u8 file you must disable this');
        $obj->experimentalWebcam = false;
        self::addDataObjectHelper('experimentalWebcam', 'Experimental Webcam', 'Requires flash and it is deprecated, will be removed. not recommend to enable it.');
        $obj->doNotShowLiveOnVideosList = false;
        self::addDataObjectHelper('doNotShowLiveOnVideosList', 'Do not show live on videos list', 'We will not show the live thumbs on the main Gallery page');
        $obj->limitLiveOnVideosList = 12;
        self::addDataObjectHelper('limitLiveOnVideosList', 'Videos List Limit', 'This will limit the maximum of videos that you will see in the Videos page');
        //$obj->doNotShowGoLiveButton = false;
        //self::addDataObjectHelper('doNotShowGoLiveButton', 'Hide Top Button', 'This will hide the "Go Live" button on the top menu bar');
        $obj->doNotProcessNotifications = false;
        self::addDataObjectHelper('doNotProcessNotifications', 'Do not show notifications', 'Do not show the notification on the top bar');
        $obj->useLiveServers = false;
        self::addDataObjectHelper('useLiveServers', 'Use Live Servers', 'Check this if you will use External Live Servers https://github.com/WWBN/AVideo/wiki/Live-Plugin#livestream-server-balance ');
        $obj->disableMeetCamera = false;
        self::addDataObjectHelper('disableMeetCamera', 'Disable Meet camera', 'This requires out Meet Server, with the Meet camera you can use your PC webcam directly in the webpage or mobile to make livestreams');
        $obj->hls_path = "/HLS/live";
        self::addDataObjectHelper('hls_path', 'HLS Path URL', 'Used only when we stop a Live, we use this path to delete the files');
        $obj->requestStatsTimout = 4; // if the server does not respond we stop wait
        self::addDataObjectHelper('requestStatsTimout', 'Stats Timout', 'If a remote server (stats page) does not respond we stop waiting after this timeout');
        $obj->cacheStatsTimout = 15; // we will cache the result
        self::addDataObjectHelper('cacheStatsTimout', 'Stats Cache Timeout', 'we will cache the result, this will save some resources');
        $obj->requestStatsInterval = 15; // how many seconds untill request the stats again
        self::addDataObjectHelper('requestStatsInterval', 'Stats Request Interval', 'how many seconds until request the stats again');
        $obj->streamDeniedMsg = "You can not stream live videos";
        self::addDataObjectHelper('streamDeniedMsg', 'Denied Message', 'We will show this message when a user is not allowed so watch a livestream');
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

    static function getDestinationApplicationName() {
        $server = self::getPlayerServer();
        $server = rtrim($server, "/");
        $parts = explode("/", $server);
        $app = array_pop($parts);
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$app}?p=" . User::getUserPass();
    }

    static function getDestinationHost() {
        $server = self::getServer();
        $host = parse_url($server, PHP_URL_HOST);
        return $host;
    }

    static function getDestinationPort() {
        $server = self::getServer();
        $port = parse_url($server, PHP_URL_PORT);
        if (empty($port)) {
            $port = 1935;
        }
        return $port;
    }

    static function getServer($live_servers_id = -1) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            if ($live_servers_id < 0) {
                $live_servers_id = self::getCurrentLiveServersId();
            }
            $ls = new Live_servers($live_servers_id);
            if (!empty($ls->getRtmp_server())) {
                return $ls->getRtmp_server();
            }
        }
        return $obj->server;
    }

    static function getDropURL($key) {
        $server = self::getPlayerServer();
        $server = rtrim($server, "/");
        $parts = explode("/", $server);
        $app = array_pop($parts);
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$domain}?command=drop_publisher&app={$app}&name={$key}&token=" . getToken(60);
    }

    static function getIsRecording($key) {
        $server = self::getPlayerServer();
        $server = rtrim($server, "/");
        $parts = explode("/", $server);
        $app = array_pop($parts);
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$domain}?command=is_recording&app={$app}&name={$key}&token=" . getToken(60);
    }

    static function getStartRecordURL($key) {
        $server = self::getPlayerServer();
        $server = rtrim($server, "/");
        $parts = explode("/", $server);
        $app = array_pop($parts);
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$domain}?command=record_start&app={$app}&name={$key}&token=" . getToken(60);
    }

    static function getStopRecordURL($key) {
        $server = self::getPlayerServer();
        $server = rtrim($server, "/");
        $parts = explode("/", $server);
        $app = array_pop($parts);
        $domain = self::getControl();

        return "{$domain}?command=record_stop&app={$app}&name={$key}&token=" . getToken(60);
    }

    static function getButton($command, $live_transmition_id, $live_servers_id = 0, $iconsOnly = false, $label = "", $class = "", $tooltip = "") {
        if (!User::canStream()) {
            return "";
        }
        global $global;
        $id = "getButton" . uniqid();
        if (empty($live_servers_id)) {
            $live_servers_id = self::getLiveServersIdRequest();
        }

        switch ($command) {
            case "record_start":
                $buttonClass = "btn btn-default btn-sm";
                $iconClass = "fas fa-video";
                if (empty($label)) {
                    $label = __("Start Record");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Start Record");
                }
                break;
            case "record_stop":
                $buttonClass = "btn btn-default btn-sm";
                $iconClass = "fas fa-video-slash";
                if (empty($label)) {
                    $label = __("Stop Record");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Stop Record");
                }
                break;
            case "drop_publisher":
                $buttonClass = "btn btn-default btn-sm";
                $iconClass = "fas fa-wifi";
                if (empty($label)) {
                    $label = __("Disconnect Livestream");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Disconnect Livestream");
                }
                break;
            case "drop_publisher_reset_key":
                $buttonClass = "btn btn-default btn-sm";
                $iconClass = "fas fa-key";
                if (empty($label)) {
                    $label = __("Disconnect Livestream");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Disconnect Livestream") . __(" and also reset the stream name/key");
                }
                break;
            default:
                return '';
        }
        if ($iconsOnly) {
            $label = "";
        }
        $html = "<button class='{$buttonClass} {$class}' id='{$id}'  data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{$tooltip}\"><i class='{$iconClass}'></i> {$label}";
        $html .= "<script>$(document).ready(function () {
            $('#{$id}').click(function(){
        modal.showPleaseWait();
                $.ajax({
                    url: '{$global['webSiteRootURL']}plugin/Live/control.json.php?command=$command&live_transmition_id={$live_transmition_id}&live_servers_id={$live_servers_id}',
                    success: function (response) {
                        console.log('getDropButton called');
                        console.log(response);
                        
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert('" . __("Sorry!") . "', response.msg, 'error');
                        } else{
                            if(response.newkey != response.key){
                                avideoAlert('" . __("Congratulations!") . "', '" . __("New Key") . ": '+response.newkey, 'success');
                            }
                            $('#streamkey, .streamkey').val(response.newkey);
                        }
                    }
                });
            });
    });</script>";
        $html .= "</button>";
        return $html;
    }

    static function getRecordControlls($live_transmition_id, $live_servers_id = 0, $iconsOnly = false) {
        if (!User::canStream()) {
            return "";
        }

        $btn = "<div class=\"btn-group justified\">";
        $btn .= self::getButton("record_start", $live_transmition_id, $live_servers_id, $iconsOnly);
        $btn .= self::getButton("record_stop", $live_transmition_id, $live_servers_id, $iconsOnly);
        $btn .= "</div>";

        return $btn;
    }

    static function getAllControlls($live_transmition_id, $live_servers_id = 0, $iconsOnly = false) {
        if (!User::canStream()) {
            return "";
        }

        $btn = "<div class=\"btn-group justified\">";
        //$btn .= self::getButton("drop_publisher", $live_transmition_id, $live_servers_id);
        $btn .= self::getButton("drop_publisher_reset_key", $live_transmition_id, $live_servers_id, $iconsOnly);
        $btn .= self::getButton("record_start", $live_transmition_id, $live_servers_id, $iconsOnly);
        $btn .= self::getButton("record_stop", $live_transmition_id, $live_servers_id, $iconsOnly);
        $btn .= "</div>";

        return $btn;
    }

    static function getRestreamer($live_servers_id = -1) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers)) {
            if ($live_servers_id < 0) {
                $live_servers_id = self::getCurrentLiveServersId();
            }
            $ls = new Live_servers($live_servers_id);
            if (!empty($ls->getRestreamerURL())) {
                return $ls->getRestreamerURL();
            }
        }
        return $obj->restreamerURL;
    }

    static function getControl($live_servers_id = -1) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->controlURL)) {
            if ($live_servers_id < 0) {
                $live_servers_id = self::getCurrentLiveServersId();
            }
            $ls = new Live_servers($live_servers_id);
            if (!empty($ls->getControlURL())) {
                return $ls->getControlURL();
            }
        }
        return $obj->controlURL;
    }

    static function getRTMPLink($users_id) {
        if (!User::isLogged() || ($users_id !== User::getId() && !User::isAdmin())) {
            return false;
        }
        $user = new User($users_id);
        $trasnmition = LiveTransmition::createTransmitionIfNeed($users_id);
        return self::getServer() . "?p=" . $user->getPassword() . "/" . $trasnmition['key'];
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

    static function getM3U8File($uuid, $doNotProtect = false) {
        global $global;
        $o = AVideoPlugin::getObjectData("Live");
        $playerServer = self::getPlayerServer();
        $live_servers_id = self::getLiveServersIdRequest();
        $liveServer = new Live_servers($live_servers_id);
        if ($liveServer->getStats_url()) {
            $o->protectLive = $liveServer->getProtectLive();
            $o->useAadaptiveMode = $liveServer->getUseAadaptiveMode();
        }
        
        $uuid = LiveTransmition::keyNameFix($uuid);
        
        if ($o->protectLive && empty($doNotProtect)) {
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
        if (!function_exists('simplexml_load_file')) {
            _error_log("Live::getStatsObject: You need to install the simplexml_load_file function to be able to see the Live stats", AVideoLog::$ERROR);
            return false;
        }

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
                _session_start();
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
            $content = @url_get_contents($url, "", $timeout);
            return $content;
        } catch (Exception $exc) {
            _error_log($exc->getTraceAsString());
        }
        return false;
    }

    public function getChartTabs() {
        return '<li><a data-toggle="tab" id="liveVideos" href="#liveVideosMenu"><i class="fas fa-play-circle"></i> ' . __('Live videos') . '</a></li>';
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
        $live_servers_id = self::getCurrentLiveServersId();
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id);
    }

    static function getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id) {
        if (empty($users_id)) {
            return false;
        }
        global $global;
        $user = new User($users_id);
        if (empty($user->getChannelName())) {
            return false;
        }
        return self::getLinkToLiveFromChannelNameAndLiveServer($user->getChannelName(), $live_servers_id);
    }

    static function getLinkToLiveFromChannelNameAndLiveServer($channelName, $live_servers_id) {
        global $global;
        $live_servers_id = intval($live_servers_id);
        $channelName = trim($channelName);
        if (empty($channelName)) {
            return false;
        }
        $playlists_id_live = "";
        if(!empty($_REQUEST['playlists_id_live'])){
            $playlists_id_live = "?playlists_id_live=".$_REQUEST['playlists_id_live'];
        }
        //return "{$global['webSiteRootURL']}plugin/Live/?live_servers_id={$live_servers_id}&c=" . urlencode($channelName);
        return "{$global['webSiteRootURL']}live/{$live_servers_id}/" . urlencode($channelName).$playlists_id_live;
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
        $btn = '<div class="clearfix"></div><button type="button" class="btn btn-default btn-light btn-sm btn-xs" onclick="document.location = \\\'' . $global['webSiteRootURL'] . 'plugin/Live/?users_id=\' + row.users_id + \'\\\';" data-row-id="right" ><i class="fa fa-circle"></i> ' . __("Live Info") . '</button>';
        return $btn;
    }

    public function getPluginMenu() {
        global $global;
        return '<a href="plugin/Live/view/editor.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> ' . __('Edit Live Servers') . '</a>';
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
            if (!empty($server) && is_object($server)) {
                $server->live_servers_id = $value['id'];
                $server->playerServer = $value['playerServer'];
                foreach ($server->applications as $key => $app) {
                    $_REQUEST['live_servers_id'] = $value['id'];
                    if (empty($app['key'])) {
                        $app['key'] = "";
                    }
                    $server->applications[$key]['m3u8'] = self::getM3U8File($app['key']);
                }

                $liveServers[] = $server;
            } else {
                _error_log("Live::getStats Live Server NOT found {$value['id']} " . json_encode($server) . " " . json_encode($value));
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
        // create 1 min cache
        $name = "Live::getAvailableLiveServer";
        $return = ObjectYPT::getCache($name, 60);
        if (empty($return)) {
            $obj = AVideoPlugin::getObjectData("Live");
            if (empty($obj->useLiveServers)) {
                $return = false;
            } else {
                $liveServers = self::getStats();
                usort($liveServers, function($a, $b) {
                    if ($a->countLiveStream == $b->countLiveStream) {
                        return 0;
                    }
                    return ($a->countLiveStream < $b->countLiveStream) ? -1 : 1;
                });
                if (empty($liveServers[0])) {
                    _error_log("Live::getAvailableLiveServer we could not get server status, try to uncheck useLiveServers parameter from the Live plugin");
                    return array();
                }
                $return = $liveServers[0];
                ObjectYPT::setCache($name, $return);
            }
        }
        return $return;
    }

    static function canSeeLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if(empty($lt)){
            return false;
        }
        return $lt->userCanSeeTransmition();
    }
    
    
    static function isAPrivateLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if(empty($lt)){
            return false;
        }
        return $lt->isAPrivateLive();
    }
    
    static function getLiveTransmitionObjectFromKey($key) {
        global $getLiveTransmitionObjectFromKey;
        if(empty($getLiveTransmitionObjectFromKey)){
            $getLiveTransmitionObjectFromKey = array();
        }
        $parts = explode("_", $key);
        if (empty($parts[0])) {
            return false;
        }
        if(!isset($getLiveTransmitionObjectFromKey[$parts[0]])){
            $livet = LiveTransmition::keyExists($parts[0]);
            if (empty($livet)) {
                $getLiveTransmitionObjectFromKey[$parts[0]] = false;
            } else {
                $lt = new LiveTransmition($livet['id']);
                $getLiveTransmitionObjectFromKey[$parts[0]] = $lt;
            }
        }
        return $getLiveTransmitionObjectFromKey[$parts[0]];
        
    }

    static function _getStats($live_servers_id = 0) {
        global $global, $_getStats;
        if (empty($_REQUEST['name'])) {
            //_error_log("Live::_getStats {$live_servers_id} GET " . json_encode($_GET));
            //_error_log("Live::_getStats {$live_servers_id} POST " . json_encode($_POST));
            //_error_log("Live::_getStats {$live_servers_id} REQUEST " . json_encode($_REQUEST));
            $_REQUEST['name'] = "undefined";
        }
        if (!empty($_getStats[$live_servers_id][$_REQUEST['name']]) && is_object($_getStats[$live_servers_id][$_REQUEST['name']])) {
            //_error_log("Live::_getStats cached result {$_REQUEST['name']} " . json_encode($_getStats[$live_servers_id][$_REQUEST['name']]));
            return $_getStats[$live_servers_id][$_REQUEST['name']];
        }
        session_write_close();
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "OFFLINE";
        $obj->nclients = 0;
        $obj->applications = array();
        $obj->hidden_applications = array();
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
                if (empty($row['users_id'])) {
                    continue;
                }
                if (!empty($row) && $value->name === $obj->name) {
                    $obj->msg = "ONLINE";
                }
                $title = $row['title'];
                $u = new User($row['users_id']);
                $hiddenName = preg_replace('/^(.{5})/', '*****', $value->name);
                                
                if (!self::canSeeLiveFromLiveKey($value->name)) {
                    $obj->hidden_applications[] = "{$row['channelName']} ($hiddenName} is a private live";
                    if (!User::isAdmin()) {
                        continue;
                    } else {
                        $title .= " (private live)";
                    }
                } else
                if (empty($row) || empty($row['public'])) {
                    $obj->hidden_applications[] = "{$row['channelName']} ($hiddenName} is set to not be listed";
                    if (!User::isAdmin()) {
                        continue;
                    } else {
                        $title .= " (set to not be listed)";
                    }
                } else
                if ($u->getStatus() !== 'a') {
                    $obj->hidden_applications[] = "{$row['channelName']} {$hiddenName} the user is inactive";
                    if (!User::isAdmin()) {
                        continue;
                    } else {
                        $title .= " (user is inactive)";
                    }
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

                $userName = $u->getNameIdentificationBd();
                $user = $u->getUser();
                $channelName = $u->getChannelName();
                $photo = $u->getPhotoDB();
                $poster = $global['webSiteRootURL'] . $p->getPosterImage($row['users_id'], $live_servers_id);
                
                $playlists_id_live = 0;
                if(preg_match("/.*_([0-9]+)/", $value->name, $matches)){
                    if(!empty($matches[1])){
                        $_REQUEST['playlists_id_live'] = intval($matches[1]);
                        $playlists_id_live = $_REQUEST['playlists_id_live'];
                        $pl = new PlayList($_REQUEST['playlists_id_live']);
                        $title = $pl->getName();
                    }
                }
                
                $link = Live::getLinkToLiveFromChannelNameAndLiveServer($u->getChannelName(), $live_servers_id);
                // this variable is to keep it compatible for Mobile app
                $UserPhoto = $photo;
                $obj->applications[] = array(
                    "key" => LiveTransmition::keyNameFix($value->name),
                    "isPrivate" => self::isAPrivateLiveFromLiveKey($value->name),
                    "users" => $users,
                    "name" => $userName,
                    "user" => $user,
                    "photo" => $photo,
                    "UserPhoto" => $UserPhoto,
                    "title" => $title,
                    'channelName' => $channelName,
                    'poster' => $poster,
                    'link' => $link . (strpos($link, '?') !== false ? "&embed=1" : "?embed=1"),
                    'href' => $link,
                    'playlists_id_live' => $playlists_id_live
                );
                if ($value->name === $obj->name) {
                    $obj->error = property_exists($value, 'publishing') ? false : true;
                    $obj->msg = (!$obj->error) ? "ONLINE" : "Waiting for Streamer";
                    $obj->stream = $value;
                    $obj->nclients = intval($value->nclients);
                    break;
                }
            }
        }
        $obj->error = false;
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        //_error_log("Live::_getStats NON cached result {$_REQUEST['name']} " . json_encode($obj));
        return $obj;
    }

    public function getPosterImage($users_id, $live_servers_id) {
        global $global;
        $file = self::_getPosterImage($users_id, $live_servers_id);

        if (!file_exists($global['systemRootPath'] . $file)) {
            $file = "plugin/Live/view/OnAir.jpg";
        }

        return $file;
    }

    public function getLivePosterImage($users_id, $live_servers_id = 0) {
        global $global;

        return $global['webSiteRootURL'] . self::getLivePosterImageRelativePath($users_id, $live_servers_id);
    }

    public function getLivePosterImageRelativePath($users_id, $live_servers_id = 0) {
        global $global;
        if (empty($live_servers_id)) {
            $live_servers_id = self::getCurrentLiveServersId();
        }
        if (self::isLiveThumbsDisabled()) {
            $file = self::_getPosterImage($users_id, $live_servers_id);

            if (!file_exists($global['systemRootPath'] . $file)) {
                $file = "plugin/Live/view/OnAir.jpg";
            }
        } else {
            $u = new User($users_id);
            $username = $u->getUserName();
            $file = "plugin/Live/getImage.php?live_servers_id={$live_servers_id}&u={$username}&format=jpg";
        }

        return $file;
    }

    public static function isLiveThumbsDisabled() {
        $obj = AVideoPlugin::getDataObject("Live");
        if (!empty($obj->disableLiveThumbs)) {
            return true;
        }
        return false;
    }

    public function getPosterThumbsImage($users_id, $live_servers_id) {
        global $global;
        $file = self::_getPosterThumbsImage($users_id, $live_servers_id);

        if (!file_exists($global['systemRootPath'] . $file)) {
            $file = "plugin/Live/view/OnAir.jpg";
        }

        return $file;
    }

    public function _getPosterImage($users_id, $live_servers_id) {
        $file = "videos/userPhoto/Live/user_{$users_id}_bg_{$live_servers_id}.jpg";
        return $file;
    }

    public function _getPosterThumbsImage($users_id, $live_servers_id) {
        $file = "videos/userPhoto/Live/user_{$users_id}_thumbs_{$live_servers_id}.jpg";
        return $file;
    }

    public static function on_publish($liveTransmitionHistory_id) {
        $obj = AVideoPlugin::getDataObject("Live");
        if (empty($obj->disableRestream)) {
            self::restream($liveTransmitionHistory_id);
        }
    }

    public static function getRestreamObject($liveTransmitionHistory_id) {

        if (empty($liveTransmitionHistory_id)) {
            return false;
        }
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        if (empty($lth->getKey())) {
            return false;
        }
        $_REQUEST['live_servers_id'] = $lth->getLive_servers_id();
        $obj = new stdClass();
        $obj->m3u8 = self::getM3U8File($lth->getKey(), true);
        $obj->restreamerURL = self::getRestreamer($lth->getLive_servers_id());
        $obj->restreamsDestinations = array();
        $obj->token = getToken(60);
        $obj->users_id = $lth->getUsers_id();

        $rows = Live_restreams::getAllFromUser($lth->getUsers_id());
        foreach ($rows as $value) {
            $value['stream_url'] = rtrim($value['stream_url'], "/") . '/';
            $obj->restreamsDestinations[] = "{$value['stream_url']}{$value['stream_key']}";
        }
        return $obj;
    }

    public static function restream($liveTransmitionHistory_id) {
        ignore_user_abort(true);
        ob_start();
        header("Connection: close");
        @header("Content-Length: " . ob_get_length());
        ob_end_flush();
        flush();
        try {
            $obj = self::getRestreamObject($liveTransmitionHistory_id);
            if (empty($obj)) {
                return false;
            }
            $data_string = json_encode($obj);
            _error_log("Live:restream ({$obj->restreamerURL}) {$data_string}");
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $obj->restreamerURL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );
            $output = curl_exec($ch);
            curl_close($ch);
            return json_decode($output);
        } catch (Exception $exc) {
            _error_log("Live:restream " . $exc->getTraceAsString());
        }
        return false;
    }

    public static function canStreamWithMeet() {
        if (!User::canStream()) {
            return false;
        }

        if (!User::canCreateMeet()) {
            return false;
        }

        $mobj = AVideoPlugin::getObjectDataIfEnabled("Meet");

        if (empty($mobj)) {
            return false;
        }

        $obj = AVideoPlugin::getObjectDataIfEnabled("Live");
        if (!empty($obj->disableMeetCamera)) {
            return false;
        }

        return true;
    }

    public function getUploadMenuButton() {
        global $global;
        $obj = $this->getDataObject();
        if (!empty($obj->doNotShowGoLiveButton) || !User::canStream()) {
            return '';
        }
        $buttonTitle = $this->getButtonTitle();
        //include $global['systemRootPath'] . 'plugin/Live/getUploadMenuButton.php';
    }

}
