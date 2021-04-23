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
        return "7.1";
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
        if (AVideoPlugin::compareVersion($this->getName(), "6.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV6.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        if (AVideoPlugin::compareVersion($this->getName(), "7.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV7.0.sql');
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
        self::addDataObjectHelper('server', 'RTMP Server URL', 'Usually it is ' . "rtmp://{$server['host']}/live");
        $obj->playerServer = "{$scheme}://{$server['host']}:{$port}/live";
        self::addDataObjectHelper('playerServer', 'Player URL', 'This is a URL to your NGINX server, this URL will be used by the HTML5 player, If your site is HTTPS your player URL MUST be HTTPS as well, usually it is ' . "{$scheme}://{$server['host']}:{$port}/live");
        $obj->stats = "{$scheme}://{$server['host']}:{$port}/stat";
        self::addDataObjectHelper('stats', 'Stats Page URL', 'When you installed the NGINX you also install the stat.xsl, we will use it to grab the information when you have livestreams running, usually it is ' . "{$scheme}://{$server['host']}:{$port}/stat");
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
        $obj->doNotShowOnlineOfflineLabel = false;
        self::addDataObjectHelper('doNotShowOnlineOfflineLabel', 'Hide the Online/Offline Badge on live streams');
        $obj->doNotShowLiveOnCategoryList = false;
        self::addDataObjectHelper('doNotShowLiveOnCategoryList', 'Do not show live on site category list', 'We will not show the live thumbs on the main Gallery page');
        $obj->doNotShowOfflineLiveOnCategoryList = false;
        self::addDataObjectHelper('doNotShowOfflineLiveOnCategoryList', 'Do not show offline lives on site category list', 'We will not show the live thumbs on the main Gallery page if it is offline');
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
        $obj->playLiveInFullScreen = false;
        self::addDataObjectHelper('playLiveInFullScreen', 'Play Livestream in Full Screen');
        $obj->playLiveInFullScreenOnIframe = false;
        self::addDataObjectHelper('playLiveInFullScreenOnIframe', 'Play Livestream in Full Screen on IFrame');
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
        $obj->allowMultipleLivesPerUser = true;
        self::addDataObjectHelper('allowMultipleLivesPerUser', 'Allow Multiple Lives Per User', 'Your users will be able to make unlimited livestreams');

        return $obj;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
// preload image
        $js = "";
        $css = '';

        if (!empty($obj->playLiveInFullScreen)) {
            if ((isLive() || isEmbed()) && canFullScreen()) {
                $css .= '<link href="' . getCDN() . 'plugin/YouPHPFlix2/view/css/fullscreen.css" rel="stylesheet" type="text/css"/>';
                $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
            }
            $js .= '<script>var playLiveInFullScreen = true</script>';
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        return $js . $css;
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        $js = '';
        if (!empty($obj->playLiveInFullScreen)) {
            $js = '<script src="' . getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'.liveVideo a.galleryLink\'); } });</script>';
        } else
        if (!empty($obj->playLiveInFullScreenOnIframe)) {
            $js = '<script src="' . getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
            $js .= '<script>$(function () { if(typeof linksToFullscreen === \'function\'){ linksToFullscreen(\'.liveVideo a.galleryLink\'); } });</script>';
        }
        include $global['systemRootPath'] . 'plugin/Live/view/footer.php';
        return $js;
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

        return self::getServer() . "?p=" . $user->getPassword() . "/" . self::getDynamicKey($trasnmition['key']);
    }

    static function getDynamicKey($key) {
        $objLive = AVideoPlugin::getDataObject("Live");
        if ($objLive->allowMultipleLivesPerUser) {
            $key .= '-' . date('His');
        }
        return $key;
    }

    static function getPlayerServer() {
        $obj = AVideoPlugin::getObjectData("Live");
        $url = $obj->playerServer;
        $url = getCDNOrURL($url, 'CDN_Live');
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getLiveServersIdRequest());
            if (!empty($ls->getPlayerServer())) {
                $url = $ls->getPlayerServer();
                $url = getCDNOrURL($url, 'CDN_LiveServers', $ls->getId());
            }
        }
        $url = str_replace("encoder.gdrive.local", "192.168.1.18", $url);
        return $url;
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
        $live_servers_id = self::getLiveServersIdRequest();
        $lso = new LiveStreamObject($uuid, $live_servers_id, false, false);
        return $lso->getM3U8($doNotProtect);
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

    function getStatsObject($live_servers_id = 0, $force_recreate = false, $tries = 0) {
        if (!function_exists('simplexml_load_file')) {
            _error_log("Live::getStatsObject: You need to install the simplexml_load_file function to be able to see the Live stats", AVideoLog::$ERROR);
            return false;
        }

        $name = "getStats" . DIRECTORY_SEPARATOR . "live_servers_id_{$live_servers_id}" . DIRECTORY_SEPARATOR . "getStatsObject";

        global $getStatsObject;
        if (!isset($getStatsObject)) {
            $getStatsObject = array();
        }
        if (empty($force_recreate)) {
//_error_log("Live::getStatsObject[$live_servers_id] 1: searching for cache");
            if (isset($getStatsObject[$live_servers_id])) {
                _error_log("Live::getStatsObject[$live_servers_id] 2: return cached result");
                return $getStatsObject[$live_servers_id];
            }

            $result = ObjectYPT::getCache($name, maxLifetime() + 60, true);

            if (!empty($result)) {
                _error_log("Live::getStatsObject[$live_servers_id] 3: return cached result $name [lifetime=" . (maxLifetime() + 60) . "]");
                return _json_decode($result);
            }
            _error_log("Live::getStatsObject[$live_servers_id] 4: cache not found");
        } else {
            _error_log("Live::getStatsObject[$live_servers_id] 5: forced to be recreated");
        }

        $o = $this->getDataObject();
        if ($o->doNotProcessNotifications) {
            $xml = new stdClass();
            $xml->server = new stdClass();
            $xml->server->application = array();
            $getStatsObject[$live_servers_id] = $xml;
            ObjectYPT::setCache($name, json_encode($xml));
            return $xml;
        }
        if (empty($o->requestStatsTimout)) {
            $o->requestStatsTimout = 2;
        }
        ini_set('allow_url_fopen ', 'ON');
        $url = $this->getStatsURL($live_servers_id);
        if (!empty($_SESSION['getStatsObjectRequestStatsTimout'][$url])) {
            _error_log("Live::getStatsObject[$live_servers_id] RTMP Server ($url) is NOT responding we will wait less from now on => live_servers_id = ($live_servers_id) ");
// if the server already fail, do not wait mutch for it next time, just wait 0.5 seconds
            $o->requestStatsTimout = $_SESSION['getStatsObjectRequestStatsTimout'][$url];
        }
//_error_log_debug("Live::getStatsObject ($url) ({$o->requestStatsTimout}) ");


        $waitFile = getTmpDir() . md5($name);
        if (file_exists($waitFile) && filemtime($waitFile) > time() - 10 && $tries < 10) {
            _error_log("Live::getStatsObject[$live_servers_id]: there is a request in progeress, please wait {$waitFile}");
            sleep(1);
            return self::getStatsObject($live_servers_id, $force_recreate, $tries + 1);
        }
        _error_log("Live::getStatsObject[$live_servers_id]: Creating a waitfile {$waitFile}");
        file_put_contents($waitFile, time());
        $data = $this->get_data($url, $o->requestStatsTimout);
        unlink($waitFile);
        if (empty($data)) {
            _session_start();
            if (empty($_SESSION['getStatsObjectRequestStatsTimout'])) {
                $_SESSION['getStatsObjectRequestStatsTimout'] = array();
            }
            $_SESSION['getStatsObjectRequestStatsTimout'][$url] = $o->requestStatsTimout - 1;
            if ($_SESSION['getStatsObjectRequestStatsTimout'][$url] < 1) {
                $_SESSION['getStatsObjectRequestStatsTimout'][$url] = 2;
            }
            _error_log("Live::getStatsObject RTMP Server ($url) is OFFLINE, timeout=({$o->requestStatsTimout}) we could not connect on it => live_servers_id = ($live_servers_id) ", AVideoLog::$ERROR);
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
        ObjectYPT::setCache($name, json_encode($xml));
        return $xml;
    }

    function get_data($url, $timeout) {
        global $global;
        if (!IsValidURL($url)) {
            return false;
        }

        _error_log_debug("Live::get_data($url, $timeout)");
        return url_get_contents($url, '', $timeout);
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

    static function getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $live_index = null) {
        if (empty($users_id)) {
            return false;
        }
        global $global;
        $user = new User($users_id);
        if (empty($user->getChannelName())) {
            return false;
        }
        return self::getLinkToLiveFromChannelNameAndLiveServer($user->getChannelName(), $live_servers_id, $live_index);
    }

    static function getLinkToLiveFromChannelNameAndLiveServer($channelName, $live_servers_id, $live_index = null) {
        global $global;
        $live_servers_id = intval($live_servers_id);
        $channelName = trim($channelName);
        if (empty($channelName)) {
            return false;
        }

        $url = "{$global['webSiteRootURL']}live/{$live_servers_id}/" . urlencode($channelName);

        if (!empty($live_index)) {
            $url .= '/' . urlencode($live_index);
        } else if (!isset($live_index) && !empty($_REQUEST['live_index'])) {
            $url .= '/' . urlencode($_REQUEST['live_index']);
        }

        if (!empty($_REQUEST['playlists_id_live'])) {
            $url = addQueryStringParameter($url, 'playlists_id_live', $_REQUEST['playlists_id_live']);
        }


//return "{$global['webSiteRootURL']}plugin/Live/?live_servers_id={$live_servers_id}&c=" . urlencode($channelName);
        return $url;
    }

    static function getAvailableLiveServersId() {
        $ls = self::getAvailableLiveServer();
        if (empty($ls)) {
            return 0;
        } else {
            return intval($ls->live_servers_id);
        }
    }

    static function getLastServersIdFromUser($users_id) {
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($last)) {
            return 0;
        } else {
            return intval($last['live_servers_id']);
        }
    }

    static function getLastsLiveHistoriesFromUser($users_id, $count = 10) {
        return LiveTransmitionHistory::getLastsLiveHistoriesFromUser($users_id, $count);
    }

    static function getLinkToLiveFromUsers_idWithLastServersId($users_id) {
        $live_servers_id = self::getLastServersIdFromUser($users_id);
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id);
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

        $obj = $this->getDataObject();

        $btn = '<button onclick="avideoModalIframeLarge(\'' . $global['webSiteRootURL'] . 'plugin/Live/view/editor.php\');" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> ' . __('Edit Live Servers') . '</button>';

        if ($obj->useLiveServers) {
            $servers = Live_servers::getAll();
            foreach ($servers as $value) {
                $btn .= '<button onclick="avideoModalIframeSmall(\'' . $global['webSiteRootURL'] . 'plugin/Live/test.php?statsURL=' . urlencode($value['stats_url']) . '\');" class="btn btn-primary btn-sm btn-xs btn-block"> ' . __('Test Server') . ' ' . $value['id'] . '</button>';
            }
        } else {
            $btn .= '<button onclick="avideoModalIframeSmall(\'' . $global['webSiteRootURL'] . 'plugin/Live/test.php?statsURL=' . urlencode($obj->stats) . '\');" class="btn btn-primary btn-sm btn-xs btn-block"> ' . __('Test Stats') . '</button>';
        }

        return $btn;
    }

    static function getStats($force_recreate = false) {
        global $getStatsLive, $_getStats, $getStatsObject;
        if (empty($force_recreate)) {
            if (isset($getStatsLive)) {
                _error_log('Live::getStats: return cached result');
                return $getStatsLive;
            }
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            $getStatsLive = self::_getStats(0, $force_recreate);
//_error_log('Live::getStats(0) 1');
            return $getStatsLive;
        } else {
            $rows = Live_servers::getAllActive();
            foreach ($rows as $key => $value) {
                $ls = new Live_servers(Live::getLiveServersIdRequest());
                if (!empty($row['playerServer'])) {
                    $server = self::_getStats($row['id'], $force_recreate);
                    $server->live_servers_id = $row['id'];
                    $server->playerServer = $row['playerServer'];
                    $getStatsLive = $server;
                    return $server;
                }
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
                $server->applications = object_to_array($server->applications);
                foreach ($server->applications as $key => $app) {
                    if (self::isAdaptive($app['key'])) {
                        continue;
                    }
                    $_REQUEST['live_servers_id'] = $value['id'];
                    if (empty($app['key'])) {
                        $app['key'] = "";
                    }
                    $server->applications[$key]['m3u8'] = self::getM3U8File($app['key']);
                    $server->applications[$key]['isURL200'] = isURL200($server->applications[$key]['m3u8']);
                }

                $liveServers[] = $server;
            } else {
                _error_log("Live::getStats Live Server NOT found {$value['id']} " . json_encode($server) . " " . json_encode($value));
            }
        }
        _error_log("Live::getStats return " . json_encode($liveServers));
        $_REQUEST['live_servers_id'] = $getLiveServersIdRequest;
        $getStatsLive = $liveServers;
        return $liveServers;
    }

    static function isAdaptive($key) {
        if (preg_match('/_(hi|low|mid)$/i', $key)) {
            return true;
        }
        return false;
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
        global $_getAvailableLiveServer;
        if (isset($_getAvailableLiveServer)) {
            return $_getAvailableLiveServer;
        }
// create 1 min cache
        $name = "Live::getAvailableLiveServer";
        $return = ObjectYPT::getCache($name, 60, true);
        if (empty($return)) {
            $obj = AVideoPlugin::getObjectData("Live");
            if (empty($obj->useLiveServers)) {
                $return = false;
            } else {
                $stats = getStatsNotifications();
                $liveServers = array();
                $servers = Live_servers::getAllActive();
                foreach ($servers as $value) {
                    $obj = new stdClass();
                    $obj->live_servers_id = $value['id'];
                    $obj->countLiveStream = 0;
                    $liveServers[$value['id']] = $obj;
                }

                foreach ($stats['applications'] as $value) {
                    if (!empty($value['live_servers_id'])) {
                        $liveServers[$value['live_servers_id']]->countLiveStream++;
                    }
                }

                usort($liveServers, function($a, $b) {
                    if ($a->countLiveStream == $b->countLiveStream) {
                        $_getAvailableLiveServer = 0;
                        return 0;
                    }
                    $_getAvailableLiveServer = ($a->countLiveStream < $b->countLiveStream) ? -1 : 1;
                    return $_getAvailableLiveServer;
                });
                if (empty($liveServers[0])) {
                    _error_log("Live::getAvailableLiveServer we could not get server status, try to uncheck useLiveServers parameter from the Live plugin");
                    $_getAvailableLiveServer = array();
                    return array();
                }
                $return = $liveServers[0];
                ObjectYPT::setCache($name, $return);
            }
        }
        $_getAvailableLiveServer = $return;
        return $return;
    }

    static function canSeeLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->userCanSeeTransmition();
    }

    static function isAPrivateLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->isAPrivateLive();
    }

    static function getTitleFromUsers_Id($users_id) {
        $lt = self::getLiveTransmitionObjectFromUsers_id($users_id);
        return $lt->getTitle();
    }

    static function getLiveTransmitionObjectFromUsers_id($users_id) {
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (!empty($latest)) {
            $key = $latest['key'];
        } else {
            $key = self::getLiveKey($users_id);
        }
        return self::getLiveTransmitionObjectFromKey($key);
    }

    static function getLiveTransmitionObjectFromKey($key) {
        global $getLiveTransmitionObjectFromKey;
        if (empty($getLiveTransmitionObjectFromKey)) {
            $getLiveTransmitionObjectFromKey = array();
        }
        $parts = explode("_", $key);
        if (empty($parts[0])) {
            return false;
        }
        if (!isset($getLiveTransmitionObjectFromKey[$parts[0]])) {
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

    static function _getStats($live_servers_id = 0, $force_recreate = false) {
        global $global, $_getStats;
        if (empty($_REQUEST['name'])) {
//_error_log("Live::_getStats {$live_servers_id} GET " . json_encode($_GET));
//_error_log("Live::_getStats {$live_servers_id} POST " . json_encode($_POST));
//_error_log("Live::_getStats {$live_servers_id} REQUEST " . json_encode($_REQUEST));
            $_REQUEST['name'] = "undefined";
        }
        $cacheName = "getStats" . DIRECTORY_SEPARATOR . "live_servers_id_{$live_servers_id}" . DIRECTORY_SEPARATOR . "{$_REQUEST['name']}_" . User::getId();
        if (empty($force_recreate)) {
            if (!empty($_getStats[$live_servers_id][$_REQUEST['name']]) && is_object($_getStats[$live_servers_id][$_REQUEST['name']])) {
                _error_log("Live::_getStats cached result 1 {$_REQUEST['name']} ");
                return $_getStats[$live_servers_id][$_REQUEST['name']];
            }
            $result = ObjectYPT::getCache($cacheName, maxLifetime() + 60, true);
            if (!empty($result)) {
                _error_log("Live::_getStats cached result 2 {$_REQUEST['name']} {$cacheName}");
                return _json_decode($result);
            }
        }
        session_write_close();
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "OFFLINE";
        $obj->nclients = 0;
        $obj->applications = array();
        $obj->hidden_applications = array();
        $obj->name = $_REQUEST['name'];
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $p = AVideoPlugin::loadPlugin("Live");
        $xml = $p->getStatsObject($live_servers_id, $force_recreate);
        $xml = json_encode($xml);
        $xml = _json_decode($xml);
        $stream = false;
        $lifeStream = array();

        if (empty($xml) || !is_object($xml)) {
            _error_log("_getStats XML is not an object live_servers_id=$live_servers_id");
        } else {
//$obj->server = $xml->server;
            if (!empty($xml->server->application) && !is_array($xml->server->application)) {
                $application = $xml->server->application;
                $xml->server->application = array();
                $xml->server->application[] = $application;
            }
            foreach ($xml->server->application as $key => $application) {
                if ($application->name !== 'live' && $application->name !== 'adaptive') {
                    continue;
                }
                if (!empty($application->live->stream)) {
                    if (empty($lifeStream)) {
                        $lifeStream = array();
                    }

                    $stream = $application->live->stream;
                    if (empty($application->live->stream->name) && !empty($application->live->stream[0]->name)) {
                        foreach ($application->live->stream as $stream) {
                            if (Live::isAdaptive($stream->name)) {
                                continue;
                            }
                            $lifeStream[] = $stream;
                        }
                    } else {
                        if (Live::isAdaptive($stream->name)) {
                            continue;
                        }
                        $lifeStream[] = $application->live->stream;
                    }
                }
            }
        }
        $obj->disableGif = $p->getDisableGifThumbs();

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
                    $obj->hidden_applications[] = "{$row['channelName']} ($hiddenName} " . __("is set to not be listed");
                    if (!User::isAdmin()) {
                        continue;
                    } else {
                        $title .= __(" (set to not be listed)");
                    }
                } else
                if ($u->getStatus() !== 'a') {
                    $obj->hidden_applications[] = "{$row['channelName']} {$hiddenName} " . __("the user is inactive");
                    if (!User::isAdmin()) {
                        continue;
                    } else {
                        $title .= __(" (user is inactive)");
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

//return array('key'=>$key, 'cleanKey'=>$cleanKey, 'live_index'=>$live_index, 'playlists_id_live'=>$playlists_id_live);
                $parameters = self::getLiveParametersFromKey($value->name);
                $playlists_id_live = $parameters['playlists_id_live'];
                $live_index = $parameters['live_index'];
                if (!empty($playlists_id_live)) {
                    $_REQUEST['playlists_id_live'] = $playlists_id_live;
                    $playlists_id_live = $_REQUEST['playlists_id_live'];
                    $photo = PlayLists::getImage($_REQUEST['playlists_id_live']);
                    $title = PlayLists::getNameOrSerieTitle($_REQUEST['playlists_id_live']);
                }
                $poster = $p->getLivePosterImage($row['users_id'], $live_servers_id, $playlists_id_live, $live_index);
                if (!empty($live_index)) {
                    $_REQUEST['live_index'] = $live_index;
                }

                if (!empty($live_index) || $live_index === 'false') {
                    $title .= " ({$live_index})";
                }

// this variable is to keep it compatible for Mobile app
                $UserPhoto = $photo;
                $key = LiveTransmition::keyNameFix($value->name);
                $link = Live::getLinkToLiveFromChannelNameAndLiveServer($u->getChannelName(), $live_servers_id, $live_index);
                $m3u8 = self::getM3U8File($key);
                $obj->applications[] = array(
                    "live_index" => $live_index,
                    "live_cleanKey" => $parameters['cleanKey'],
                    "key" => $value->name,
                    "isAdaptive" => self::isAdaptive($value->name),
                    "isPrivate" => self::isAPrivateLiveFromLiveKey($value->name),
                    "users" => $users,
                    "name" => $userName,
                    "user" => $user,
                    "photo" => $photo,
                    "UserPhoto" => $UserPhoto,
                    "title" => $title,
                    'channelName' => $channelName,
                    'poster' => $poster,
                    'imgGif' => $p->getLivePosterImage($row['users_id'], $live_servers_id, $playlists_id_live, $live_index, 'gif'),
                    'link' => addQueryStringParameter($link, 'embed', 1),
                    'href' => $link,
                    'playlists_id_live' => $playlists_id_live,
                    'live_index' => $live_index,
                    'm3u8' => $m3u8,
                    'isURL200' => isURL200($m3u8),
                    'users_id' => $row['users_id'],
                    'live_servers_id' => $live_servers_id,
                    'categories_id' => intval($row['categories_id']),
                    'className' => "live_{$live_servers_id}_{$value->name}"
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

        $obj->countLiveStream = count($obj->applications);
        $obj->error = false;
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
//_error_log("Live::_getStats NON cached result {$_REQUEST['name']} " . json_encode($obj));
        ObjectYPT::setCache($cacheName, json_encode($obj));
        return $obj;
    }

    static function getLiveParametersFromKey($key) {
        $obj = AVideoPlugin::getObjectData('Live');
        $playlists_id_live = false;
        if (preg_match("/.*_([0-9]+)/", $key, $matches)) {
            if (!empty($matches[1])) {
                $playlists_id_live = intval($matches[1]);
            }
        }
        $live_index = '';

        if (preg_match("/.*-([0-9a-zA-Z]+)/", $key, $matches)) {
            if (!empty($matches[1])) {
                $live_index = strip_tags($matches[1]);
                if ($live_index === 'false') {
                    $live_index = '';
                }
            }
        }
        $cleanKey = self::cleanUpKey($key);
        return array('key' => $key, 'cleanKey' => $cleanKey, 'live_index' => $live_index, 'playlists_id_live' => $playlists_id_live);
    }

    static function getLiveIndexFromKey($key) {
        $parameters = self::getLiveParametersFromKey($key);
        return $parameters['live_index'];
    }

    static function cleanUpKey($key) {
        if ($adapKey = self::isAdaptiveTransmition($key)) {
            $key = $adapKey;
        }
        if ($plKey = self::isPlayListTransmition($key)) {
            $key = $plKey;
        }
        if ($subKey = self::isSubTransmition($key)) {
            $key = $subKey;
        }
        return $key;
    }

    static function isAdaptiveTransmition($key) {
// check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            $adaptive = array('hi', 'low', 'mid');
            if (in_array($parts[1], $adaptive)) {
                return $parts[0];
                ;
            }
        }
        return false;
    }

    static function isPlayListTransmition($key) {
// check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    static function isSubTransmition($key) {
// check if is a subtransmition
        $parts = explode("-", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    static function getImage($users_id, $live_servers_id, $playlists_id_live = 0, $live_index = '') {
        $p = AVideoPlugin::loadPlugin("Live");
        if (self::isLive($users_id, $live_servers_id, $live_index)) {
            $url = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $live_index);
            $url = addQueryStringParameter($url, "playlists_id_live", $playlists_id_live);
        } else {
            $url = self::getOfflineImage(false);
        }
        return $url;
    }

    static function getLatestKeyFromUser($users_id) {
        if (empty($users_id)) {
            return false;
        }
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($latest)) {
            return false;
        }
        return $latest['key'];
    }

    static function isLive($users_id, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_live_is_live;
        if (empty($users_id)) {
            return false;
        }
        if (!isset($_live_is_live)) {
            $_live_is_live = array();
        }
        $name = "{$users_id}_{$live_servers_id}";
        if (!empty($_live_is_live[$name])) {
            return $_live_is_live[$name];
        }
        $lh = LiveTransmitionHistory::getActiveLiveFromUser($users_id, $live_servers_id, '');
        if (empty($lh)) {
            _error_log("Live::isLive we could not found any active livestream for user $users_id, $live_servers_id");
            return false;
        }
        $key = $lh['key'];
        $_live_is_live[$name] = self::isLiveAndIsReadyFromKey($key, $live_servers_id, $live_index, $force_recreate);
        return $_live_is_live[$name];
    }

    static function isKeyLiveInStats($key, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_isLiveFromKey;
        if (empty($key) || $key == '-1') {
            _error_log('Live::isKeyLiveInStats key is empty');
            return false;
        }
        $index = "$key, $live_servers_id,$live_index";
        if (!isset($_isLiveFromKey)) {
            $_isLiveFromKey = array();
        }

        if (empty($force_recreate) && isset($_isLiveFromKey[$index])) {
            _error_log('Live::isKeyLiveInStats key is already set');
            return $_isLiveFromKey[$index];
        }

//_error_log('getStats execute getStats: ' . __LINE__ . ' ' . __FILE__);
//$json = getStatsNotifications($force_recreate);
        $json = self::getStats($force_recreate);
        $_isLiveFromKey[$index] = false;
        if (!empty($json)) {
            _error_log("Live::isLiveFromKey {$key} JSON was not empty");
            if (!is_array($json)) {
                $json = array($json);
            }
            foreach ($json as $item) {
                $applications = array();
                if (empty($item->applications) && is_array($item)) {
                    $applications = $item;
                } else if (is_object($item) && !empty($item->applications)) {
                    $applications = $item->applications;
                }

                foreach ($applications as $value) {
                    $value = object_to_array($value);
                    if (!is_array($value) || empty($value) || empty($value['key'])) {
                        continue;
                    }
                    if (preg_match("/{$key}.*/", $value['key'])) {
                        if (empty($live_servers_id)) {
                            $_isLiveFromKey[$index] = true;
                            $_isLiveFromKey[$index] = $_isLiveFromKey[$index];
                            break 2;
                        } else {
                            if (intval(@$value['live_servers_id']) == $live_servers_id) {
                                $_isLiveFromKey[$index] = true;
                                $_isLiveFromKey[$index] = $_isLiveFromKey[$index];
                                break 2;
                            }
                        }
                    }
                }

                if (!empty($item->hidden_applications)) {
                    $applications = $item->hidden_applications;
                    foreach ($applications as $value) {
                        $value = object_to_array($value);
                        if (!is_array($value) || empty($value) || empty($value['key'])) {
                            continue;
                        }
                        if (preg_match("/{$key}.*/", $value['key'])) {
                            if (empty($live_servers_id)) {
                                $_isLiveFromKey[$index] = true;
                                $_isLiveFromKey[$index] = $_isLiveFromKey[$index];
                                break 2;
                            } else {
                                if (intval(@$value['live_servers_id']) == $live_servers_id) {
                                    $_isLiveFromKey[$index] = true;
                                    $_isLiveFromKey[$index] = $_isLiveFromKey[$index];
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (empty($_isLiveFromKey[$index])) {
            _error_log("Live::isLiveFromKey is NOT online [{$key}]");
        } else {
            _error_log("Live::isLiveFromKey is online [{$key}]");
        }
        return $_isLiveFromKey[$index];
    }

    static function isLiveAndIsReadyFromKey($key, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_isLiveAndIsReadyFromKey;

        if (!isset($_isLiveAndIsReadyFromKey)) {
            $_isLiveAndIsReadyFromKey = array();
        }
        $name = "getStats" . DIRECTORY_SEPARATOR . "isLiveAndIsReadyFromKey{$key}_{$live_servers_id}";
        if (empty($force_recreate)) {
            if (isset($_isLiveAndIsReadyFromKey[$name])) {
                return $_isLiveAndIsReadyFromKey[$name];
            }
            $cache = ObjectYPT::getCache($name, 60, true);
        }
        if (!empty($cache)) {
            $json = _json_decode($cache);
        }

        if (!empty($json) && is_object($json)) {
            $_isLiveAndIsReadyFromKey[$name] = $json->result;
        } else {
            $json = new stdClass();
            $key = self::getLiveKeyFromRequest($key, $live_index);
//_error_log('getStats execute isKeyLiveInStats: ' . __LINE__ . ' ' . __FILE__);
//_error_log("isLiveAndIsReadyFromKey::key: {$key}");
            $isLiveFromKey = self::isKeyLiveInStats($key, $live_servers_id, $live_index, $force_recreate);
            $_isLiveAndIsReadyFromKey[$name] = true;
            if (empty($isLiveFromKey)) {
                _error_log("isLiveAndIsReadyFromKey the key {$key} is not present on the stats");
                $_isLiveAndIsReadyFromKey[$name] = false;
            } else {
                $ls = $_REQUEST['live_servers_id'];
                $_REQUEST['live_servers_id'] = $live_servers_id;
                $m3u8 = self::getM3U8File($key);
                $_REQUEST['live_servers_id'] = $ls;
//_error_log('getStats execute isURL200: ' . __LINE__ . ' ' . __FILE__);
                $is200 = isURL200($m3u8, $force_recreate);
                if (empty($is200)) {
                    _error_log("isLiveAndIsReadyFromKey the m3u8 file is not present {$m3u8}");
                    $_isLiveAndIsReadyFromKey[$name] = false;
                }
            }
            $json->result = $_isLiveAndIsReadyFromKey[$name];
            ObjectYPT::setCache($name, json_encode($json));
        }

        return $_isLiveAndIsReadyFromKey[$name];
    }

    static function getOnlineLivesFromUser($users_id) {
        $key = self::getLiveKey($users_id);
        return self::getOnlineLivesFromKey($key);
    }

    static function getOnlineLivesFromKey($key) {
        $json = getStatsNotifications();
        $lives = array();
        if (!empty($json) && is_object($json) && !empty($json->applications)) {
            foreach ($json->applications as $value) {
                if (preg_match("/{$key}.*/", $value['key'])) {
                    $lives[] = $value;
                }
            }
        }
        return $lives;
    }

    static function keyIsFromPlaylist($key) {
        $parts = explode("_", $key);
        if (empty($parts[1])) {
            return false;
        }
        return array('key' => $parts[0], 'playlists_id' => $parts[1]);
    }

    static function getLiveKey($users_id) {
        $lt = new LiveTransmition(0);
        $lt->loadByUser($users_id);
        return $lt->getKey();
    }

    static function getLiveKeyFromUser($users_id, $live_index = '', $playlists_id_live = '') {
        $key = self::getLiveKey($users_id);
        return self::getLiveKeyFromRequest($key, $live_index, $playlists_id_live);
    }

    static function getLiveKeyFromRequest($key, $live_index = '', $playlists_id_live = '') {
        if (strpos($key, '-') === false) {
            if (!empty($live_index)) {
                $key .= '-' . preg_replace('/[^0-9a-z]/i', '', $live_index);
            } else
            if (!empty($_REQUEST['live_index'])) {
                $key .= '-' . preg_replace('/[^0-9a-z]/i', '', $_REQUEST['live_index']);
            }
        }
        if (strpos($key, '_') === false) {
            if (!empty($playlists_id_live)) {
                $key .= '_' . preg_replace('/[^0-9]/', '', $_REQUEST['playlists_id_live']);
            } else if (!empty($_REQUEST['playlists_id_live'])) {
                $key .= '_' . preg_replace('/[^0-9]/', '', $_REQUEST['playlists_id_live']);
            }
        }
        return $key;
    }

    public function getImageGif($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '') {
        global $global;
        if (empty($live_servers_id)) {
            $live_servers_id = self::getCurrentLiveServersId();
        }
        if ($live_index === 'false') {
            $live_index = '';
        }
        $u = new User($users_id);
        $username = $u->getUser();
        $file = "plugin/Live/getImage.php";
        $url = $global['webSiteRootURL'] . $file;
        $url = addQueryStringParameter($url, "live_servers_id", $live_servers_id);
        $url = addQueryStringParameter($url, "playlists_id_live", $playlists_id_live);
        $url = addQueryStringParameter($url, "live_index", $live_index);
        $url = addQueryStringParameter($url, "u", $username);
        $url = addQueryStringParameter($url, "format", 'gif');
        return $url;
    }

    public static function getPosterImage($users_id, $live_servers_id) {
        global $global;
        $file = self::_getPosterImage($users_id, $live_servers_id);

        if (!file_exists($global['systemRootPath'] . $file)) {
            $file = self::getOnAirImage(false);
        }

        return $file;
    }
    
    public static function getPosterImageOrFalse($users_id, $live_servers_id) {
        $poster = self::getPosterImage($users_id, $live_servers_id); 
        if(preg_match('/OnAir.jpg$/', $poster)){
            return false;
        }

        return $poster;
    }

    public function getLivePosterImage($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '', $format = 'jpg') {
        global $global;

        return $global['webSiteRootURL'] . self::getLivePosterImageRelativePath($users_id, $live_servers_id, $playlists_id_live, $live_index, $format);
    }

    public function getLivePosterImageRelativePath($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '', $format = 'jpg') {
        global $global;
        if (empty($live_servers_id)) {
            $live_servers_id = self::getCurrentLiveServersId();
        }
        if (self::isLiveThumbsDisabled()) {
            $file = self::_getPosterImage($users_id, $live_servers_id);

            if (!file_exists($global['systemRootPath'] . $file)) {
                $file = self::getOnAirImage(false);
            }
        } else {
            $u = new User($users_id);
            $username = $u->getUser();
            $file = "plugin/Live/getImage.php?live_servers_id={$live_servers_id}&playlists_id_live={$playlists_id_live}&live_index={$live_index}&u={$username}&format={$format}";
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

    public static function getPosterThumbsImage($users_id, $live_servers_id) {
        global $global;
        $file = self::_getPosterThumbsImage($users_id, $live_servers_id);

        if (!file_exists($global['systemRootPath'] . $file)) {
            $file = self::getOnAirImage(false);
        }

        return $file;
    }

    public static function getPoster($users_id, $live_servers_id, $key = '') {
        _error_log("getPoster($users_id, $live_servers_id, $key)");
        $lh = LiveTransmitionHistory::getActiveLiveFromUser($users_id, $live_servers_id, $key);
        $live_index = self::getLiveIndexFromKey($lh['key']);
        $poster = self::getPosterImageOrFalse($users_id, $live_servers_id, $live_index);
        if(empty($poster)){
            $poster = self::getOfflineImage(false);
        }
        if (empty($lh)) {
            _error_log("getPoster empty activity");
            return $poster;
        }
        $parameters = self::getLiveParametersFromKey($lh['key']);
        $live_index = $parameters['live_index'];
        $playlists_id_live = $parameters['playlists_id_live'];
        if (self::isLiveAndIsReadyFromKey($lh['key'], $lh['live_servers_id'])) {
            return self::getLivePosterImageRelativePath($users_id, $live_servers_id, $playlists_id_live, $live_index);
            _error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' is live and ready');
        } else {
            if (self::isKeyLiveInStats($lh['key'], $lh['live_servers_id'])) {
                _error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is in the stats');
                return self::getPosterImage($users_id, $live_servers_id, $live_index);
            } else {
                _error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is NOT in the stats');
                return $poster;
            }
        }
    }

    public static function getPosterFromKey($key, $live_servers_id, $live_index = '') {
        $key = self::getLatestKeyFromUser($users_id);
    }

    static function getOfflineImage($includeURL = true) {
        global $global;
        $img = "plugin/Live/view/Offline.jpg";
        if ($includeURL) {
            $img = "{$global['webSiteRootURL']}{$img}";
        }
        return $img;
    }

    static function getOnAirImage($includeURL = true) {
        global $global;
        $img = "plugin/Live/view/OnAir.jpg";
        if ($includeURL) {
            $img = "{$global['webSiteRootURL']}{$img}";
        }
        return $img;
    }

    public static function _getPosterImage($users_id, $live_servers_id) {
        $file = "videos/userPhoto/Live/user_{$users_id}_bg_{$live_servers_id}.jpg";
        return $file;
    }

    public static function _getPosterThumbsImage($users_id, $live_servers_id) {
        $file = "videos/userPhoto/Live/user_{$users_id}_thumbs_{$live_servers_id}.jpg";
        return $file;
    }

    public static function on_publish($liveTransmitionHistory_id) {
        $obj = AVideoPlugin::getDataObject("Live");
        if (empty($obj->disableRestream)) {
            self::restream($liveTransmitionHistory_id);
        }
    }

    public static function deleteStatsCache($clearFirstPage = false) {
        global $getStatsLive, $_getStats, $getStatsObject, $_getStatsNotifications, $__getAVideoCache, $_isLiveFromKey, $_isLiveAndIsReadyFromKey;

        _error_log_debug("Live::deleteStatsCache");
        $tmpDir = ObjectYPT::getCacheDir();
        $cacheDir = $tmpDir . "getstats" . DIRECTORY_SEPARATOR;
        if (isset($live_servers_id)) {
            $cacheDir .= "live_servers_id_{$live_servers_id}";
            $pattern = "/.getStats.{$live_servers_id}.*/i";
            ObjectYPT::deleteCachePattern($pattern);
        }
//_error_log("Live::deleteStatsCache [{$cacheDir}]");
        rrmdir($cacheDir);
        exec('rm -R ' . $cacheDir);
        if (is_dir($cacheDir)) {
//_error_log("Live::deleteStatsCache [{$cacheDir}] looks like the cache was not deleted", AVideoLog::$ERROR);
            exec('rm -R ' . $cacheDir);
        } else {
//_error_log("Live::deleteStatsCache [{$cacheDir}] Success");
        }
        if ($clearFirstPage) {
            clearCache(true);
        }
        isURL200Clear();
        unset($__getAVideoCache);
        unset($getStatsLive);
        unset($getStatsObject);
        unset($_getStats);
        unset($_getStatsNotifications);
        unset($_isLiveFromKey);
        unset($_isLiveAndIsReadyFromKey);
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
        outputAndContinueInBackground();
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
            return _json_decode($output);
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

    public static function getAllVideos($status = "", $showOnlyLoggedUserVideos = false, $activeUsersOnly = true) {
        global $global, $config, $advancedCustom;
        if (AVideoPlugin::isEnabledByName("VideoTags")) {
            if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
            }
        }
        $status = str_replace("'", "", $status);

        $sql = "SELECT u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, v.modified as videoModified "
                . " FROM live_transmitions as v "
                . " LEFT JOIN categories c ON categories_id = c.id "
                . " LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 1=1 ";

        if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
            $uid = intval(User::getId());
            $sql .= " AND v.users_id = '{$uid}'";
        } elseif (!empty($showOnlyLoggedUserVideos)) {
            $uid = intval($showOnlyLoggedUserVideos);
            $sql .= " AND v.users_id = '{$uid}'";
        } elseif (!empty($_GET['channelName'])) {
            $user = User::getChannelOwner($_GET['channelName']);
            $uid = intval($user['id']);
            $sql .= " AND v.users_id = '{$uid}' ";
        }

        if ($activeUsersOnly) {
            $sql .= " AND u.status = 'a' ";
        }

        if ($status == "publicOnly") {
            $sql .= " AND v.public = 1 ";
        } elseif (!empty($status)) {
            $sql .= " AND v.`public` = '{$status}'";
        }

        if (!empty($_GET['catName'])) {
            $catName = $global['mysqli']->real_escape_string($_GET['catName']);
            $sql .= " AND (c.clean_name = '{$catName}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name =  '{$catName}' ))";
        }

        if (!empty($_GET['modified'])) {
            $_GET['modified'] = str_replace("'", "", $_GET['modified']);
            $sql .= " AND v.modified >= '{$_GET['modified']}'";
        }

        $sql .= AVideoPlugin::getVideoWhereClause();

        if (strpos(strtolower($sql), 'limit') === false) {
            if (!empty($_GET['limitOnceToOne'])) {
                $sql .= " LIMIT 1";
                unset($_GET['limitOnceToOne']);
            } else {
                $_REQUEST['rowCount'] = getRowCount();
                if (!empty($_REQUEST['rowCount'])) {
                    $sql .= " LIMIT {$_REQUEST['rowCount']}";
                } else {
                    _error_log("getAllVideos without limit " . json_encode(debug_backtrace()));
                    if (empty($global['limitForUnlimitedVideos'])) {
                        $global['limitForUnlimitedVideos'] = 100;
                    }
                    if ($global['limitForUnlimitedVideos'] > 0) {
                        $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                    }
                }
            }
        }

//echo $sql;exit;
//_error_log("getAllVideos($status, $showOnlyLoggedUserVideos , $ignoreGroup , ". json_encode($videosArrayId).")" . $sql);
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);
        $videos = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);

                $row['live_servers_id'] = self::getLastServersIdFromUser($row['users_id']);

                if (empty($otherInfo)) {
                    $otherInfo = array();
                    $otherInfo['category'] = xss_esc_back($row['category']);
                    $otherInfo['groups'] = UserGroups::getVideoGroups($row['id']);
//$otherInfo['title'] = UTF8encode($row['title']);
                    $otherInfo['description'] = UTF8encode($row['description']);
                    $otherInfo['descriptionHTML'] = Video::htmlDescription($otherInfo['description']);
                    $otherInfo['filesize'] = 0;
                }

                foreach ($otherInfo as $key => $value) {
                    $row[$key] = $value;
                }

                $row['rotation'] = 0;
                $row['filename'] = '';
                $row['type'] = 'live';
                $row['duration'] = '';
                $row['isWatchLater'] = 0;
                $row['isFavorite'] = 0;
                $row['views_count'] = 0;

                $videos[] = $row;
            }
//$videos = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $videos = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    static function finishLive($key) {
        $lh = LiveTransmitionHistory::finish($key);
    }

    static function updateVideosUserGroup($videos_id, $key) {
        $lt = LiveTransmition::keyExists($key);
        if (!empty($lt)) {
            $lt = new LiveTransmition($lt['id']);
            $groups = $lt->getGroups();
            if (!empty($groups)) {
                UserGroups::updateVideoGroups($videos_id, $groups);
            }
        }
    }

    static function notifySocketStats($callBack = 'socketLiveONCallback', $array = array()) {
        if (empty($array['stats'])) {
            $array['stats'] = getStatsNotifications();
        }
        _error_log("NGINX Live::on_publish_socket_notification sendSocketMessageToAll Start");
        $socketObj = sendSocketMessageToAll($array, $callBack);
        _error_log("NGINX Live::on_publish_socket_notification SocketMessageToAll END");
        return $socketObj;
    }

    static public function getImageType($content) {
        global $global;
        if (empty($content)) {
            return LiveImageType::$UNKNOWN;
        }
        $contentLen = strlen($content);
        if ($contentLen < 255) {
// check if it is a file
            if (file_exists($content)) {
                $contentLen = strlen(file_get_contents($content));
            }
        }
        if ($contentLen === 2095341) {
            return LiveImageType::$DEFAULTGIF;
        }
        if ($contentLen === 70808) {
            return LiveImageType::$ONAIRENCODER;
        }
        $filesize = file_get_contents($global['systemRootPath'] . self::getOnAirImage(false));
        if ($contentLen === $filesize) {
            return LiveImageType::$ONAIR;
        }
        $filesize = file_get_contents($global['systemRootPath'] . self::getOfflineImage(false));
        if ($contentLen === $filesize) {
            return LiveImageType::$OFFLINE;
        }
//_error_log('getImageType: is not defined yet ('.$contentLen.')');
        return LiveImageType::$LIVE;
    }

    static function isLiveImage($content) {
        return self::getImageType($content) === LiveImageType::$LIVE;
    }

    static function isDefaultImage($content) {
        $type = self::getImageType($content);
        return $type === LiveImageType::$ONAIRENCODER || $type === LiveImageType::$ONAIR || $type === LiveImageType::$OFFLINE || $type === LiveImageType::$DEFAULTGIF;
    }

}

class LiveImageType {

    static $UNKNOWN = 'unknown';
    static $OFFLINE = 'offline';
    static $ONAIR = 'onair';
    static $ONAIRENCODER = 'onair_encoder';
    static $DEFAULTGIF = 'defaultgif';
    static $LIVE = 'live';

}

class LiveStreamObject {

    private $key, $live_servers_id, $live_index, $playlists_id_live;

    function __construct($key, $live_servers_id, $live_index, $playlists_id_live) {
        $this->key = $key;
        $this->live_servers_id = intval($live_servers_id);
        $this->live_index = $live_index;
        $this->playlists_id_live = intval($playlists_id_live);
        $parts = Live::getLiveParametersFromKey($this->key);
        $objLive = AVideoPlugin::getDataObject("Live");
        if (empty($live_servers_id) && !empty($objLive->useLiveServers)) {
            $live_servers_id = Live::getLiveServersIdRequest();
        }
        if (empty($this->live_index)) {
            // check if the index is on the key already
            if (!empty($parts['live_index'])) {
                $this->live_index = $parts['live_index'];
            } else if (!empty($_REQUEST['live_index'])) {
                $this->live_index = $_REQUEST['live_index'];
            }
        }
        $this->key = $parts['cleanKey'];
        $this->live_index = preg_replace('/[^0-9a-z]/i', '', $this->live_index);
    }

    function getKey() {
        return $this->key;
    }

    function getKeyWithIndex($forceIndexIfEnabled = false) {
        if ($forceIndexIfEnabled) {
            $objLive = AVideoPlugin::getDataObject("Live");
            if (empty($this->live_index) && !empty($objLive->allowMultipleLivesPerUser)) {
                $this->live_index = date('His');
            }
        }
        return Live::getLiveKeyFromRequest($this->key, $this->live_index, $this->playlists_id_live);
    }

    function getLive_servers_id() {
        return $this->live_servers_id;
    }

    function getLive_index() {
        return $this->live_index;
    }

    function getPlaylists_id_live() {
        return $this->playlists_id_live;
    }

    function getURL() {
        global $global;
        $lt = LiveTransmition::getFromKey($this->key);
        if (empty($lt)) {
            return false;
        }
        $user = new User($lt['users_id']);
        $channelName = $user->getChannelName();
        if (empty($channelName)) {
            return false;
        }

        $url = "{$global['webSiteRootURL']}live/{$this->live_servers_id}/" . urlencode($channelName);

        if (!empty($this->live_index)) {
            $url .= '/' . urlencode($this->live_index);
        }

        if (!empty($this->playlists_id_live)) {
            $url = addQueryStringParameter($url, 'playlists_id_live', $this->playlists_id_live);
        }

        return $url;
    }

    function getURLEmbed() {
        $url = $this->getURL();
        return addQueryStringParameter($url, 'embed', 1);
    }

    function getM3U8($doNotProtect = false) {
        global $global;
        $o = AVideoPlugin::getObjectData("Live");
        $playerServer = Live::getPlayerServer();
        $live_servers_id = Live::getLiveServersIdRequest();
        if (!empty($this->live_servers_id)) {
            $liveServer = new Live_servers($this->live_servers_id);
            if ($liveServer->getStats_url()) {
                $o->protectLive = $liveServer->getProtectLive();
                $o->useAadaptiveMode = $liveServer->getUseAadaptiveMode();
            }
        }

        $uuid = $this->getKeyWithIndex();
        $playerServer = addLastSlash($playerServer);
        if ($o->protectLive && empty($doNotProtect)) {
            return "{$global['webSiteRootURL']}plugin/Live/m3u8.php?live_servers_id={$this->live_servers_id}&uuid=" . encryptString($uuid);
        } else if ($o->useAadaptiveMode) {
            return $playerServer . "{$uuid}.m3u8";
        } else {
            return $playerServer . "{$uuid}/index.m3u8";
        }
    }

}
