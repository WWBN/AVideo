<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistoryLog.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';

$getStatsObject = [];
$_getStats = [];

User::loginFromRequest();

class Live extends PluginAbstract {

    public static $public_server_http = 'http';
    public static $public_server_port = 8080;
    public static $public_server_domain = 'live.ypt.me';
    public static $posterType_regular = 0;
    public static $posterType_preroll = 1;
    public static $posterType_postroll = 2;

    public function getTags() {
        return [
            PluginTags::$LIVE,
            PluginTags::$FREE,
            PluginTags::$RECOMMENDED,
            PluginTags::$PLAYER,
        ];
    }

    public function getDescription() {
        global $global;
        $desc = "Broadcast a RTMP video from your computer<br> and receive HLS streaming from servers";
        $lu = AVideoPlugin::loadPlugin("LiveUsers");
        if (!empty($lu)) {
            if (version_compare($lu->getPluginVersion(), "2.0") < 0) {
                $desc .= "<div class='alert alert-danger'>You MUST update your LiveUsers plugin to version 2.0 or greater</div>";
            }
        }
        $desc .= "<br><strong>Start Self hosted WebRTC server:</strong> <code>php {$global['systemRootPath']}plugin/Live/standAloneFiles/WebRTCServer/server.php</code> ";
        $desc .= "<br><small><a href='https://github.com/WWBN/AVideo/wiki/WebRTC-Server' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc;
    }

    public function getName() {
        return "Live";
    }

    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
    }

    public function getUUID() {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }

    public function getPluginVersion() {
        return "10.5";
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
        if (AVideoPlugin::compareVersion($this->getName(), "7.2") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV7.2.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        if (AVideoPlugin::compareVersion($this->getName(), "8.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV8.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        if (AVideoPlugin::compareVersion($this->getName(), "9.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV9.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.1") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.1.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.2") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.2.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.3") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.3.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.4") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.4.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        if (AVideoPlugin::compareVersion($this->getName(), "10.5") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV10.5.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
            LiveTransmitionHistory::finishALL();
        }
        return true;
    }

    public function getLivePanel() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Live/view/panel.php';
        include $filename;
    }

    public function getLiveApplicationArray() {
        global $global;
        $_playlists_id_live = @$_REQUEST['playlists_id_live'];
        unset($_REQUEST['playlists_id_live']);

        $obj = $this->getDataObject();

        $rows = Live_schedule::getAllActiveLimit();
        //var_dump($rows);exit;
        $array = [];
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        foreach ($rows as $value) {
            unset($_REQUEST['playlists_id_live']);
            $isLive = LiveTransmitionHistory::getActiveLiveFromUser($value['users_id'], $value['live_servers_id'], $value['key']);
            if ($isLive) {
                //var_dump(__LINE__, $isLive);
                continue;
            }

            $timestamp = getTimestampFromTimezone($value['scheduled_time'], $value['timezone']);
            // live is already expired
            if ($timestamp < time()) {
                //var_dump(__LINE__, $timestamp);
                continue;
            }

            $callback = '';
            $link = Live::getLinkToLiveFromUsers_idAndLiveServer($value['users_id'], $value['live_servers_id']);
            // AVOID image POG
            if (preg_match('/\.jpg/', $link)) {
                //var_dump(__LINE__, $link);
                continue;
            }
            $link = addQueryStringParameter($link, 'live_schedule', intval($value['id']));
            $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($value['key'], $value['live_servers_id']) : '');

            $title = self::getTitleFromKey($value['key'], $value['title']);

            $users_id = Live_schedule::getUsers_idOrCompany($value['id']);

            $app = self::getLiveApplicationModelArray($users_id, $title, $link, Live_schedule::getPosterURL($value['id']), '', 'scheduleLive', $LiveUsersLabelLive, 'LiveSchedule_' . $value['id'], $callback, date('Y-m-d H:i:s', $timestamp), 'live_' . $value['key']);
            $app['live_servers_id'] = $value['live_servers_id'];
            $app['key'] = $value['key'];
            $app['isPrivate'] = false;
            $app['method'] = 'Live::getLiveApplicationArray::Live_schedule';
            $array[] = $app;
        }
        //var_dump($rows);exit;

        $rows = LiveTransmitionHistory::getActiveLives();
        $currentLives = array();
        foreach ($rows as $value) {
            unset($_REQUEST['playlists_id_live']);
            // if key is from schedule, skipp it
            if (!LiveTransmition::keyExists($value['key'], false) && Live_schedule::keyExists($value['key'])) {
                //if (Live_schedule::keyExists($value['key'])) {
                continue;
            }

            $link = LiveTransmitionHistory::getLinkToLive($value['id']);

            if (empty($link)) {
                $link = Live::getLinkToLiveFromUsers_idAndLiveServer($value['users_id'], $value['live_servers_id']);
            }

            if (in_array($link, $currentLives)) {
                _error_log("Live::getLiveApplicationArray LiveTransmitionHistory::finishFromTransmitionHistoryId({$value['id']}) {$value['users_id']}, {$value['live_servers_id']} [{$link}]");
                LiveTransmitionHistory::finishFromTransmitionHistoryId($value['id']);
                continue;
            }
            $currentLives[] = $link;
            $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($value['key'], $value['live_servers_id']) : '');

            $title = self::getTitleFromKey($value['key'], $value['title']);

            $users_id = LiveTransmitionHistory::getUsers_idOrCompany($value['id']);

            $app = self::getLiveApplicationModelArray($users_id, $title, $link, self::getPoster($value['users_id'], $value['live_servers_id']), '', 'LiveDB', $LiveUsersLabelLive, 'LiveObject_' . $value['id'], '', '', "live_{$value['key']}");
            $app['live_servers_id'] = $value['live_servers_id'];
            $app['key'] = $value['key'];
            $app['live_transmitions_history_id'] = $value['id'];
            $app['isPrivate'] = LiveTransmitionHistory::isPrivate($value['id']);
            $app['isPasswordProtected'] = LiveTransmitionHistory::isPasswordProtected($value['id']);
            $app['method'] = 'Live::getLiveApplicationArray::LiveTransmitionHistory';

            $array[] = $app;
        }

        $_REQUEST['playlists_id_live'] = $_playlists_id_live;
        return $array;
    }

    public static function getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, $type, $LiveUsersLabelLive = '', $uid = '', $callback = '', $startsOnDate = '', $class = '') {
        global $global, $_getLiveApplicationModelArray_counter, $_getLiveApplicationModelArray;

        if (!isset($_getLiveApplicationModelArray)) {
            $_getLiveApplicationModelArray = [];
        }

        if (!empty($_getLiveApplicationModelArray[$uid])) {
            return $_getLiveApplicationModelArray[$uid];
        }

        if (empty($_getLiveApplicationModelArray_counter)) {
            $_getLiveApplicationModelArray_counter = 0;
        }

        $uid = str_replace(['&', '='], '', $uid);

        $_getLiveApplicationModelArray_counter++;

        $search = [
            '_unique_id_',
            '_user_photo_',
            '_title_',
            '_user_identification_',
            '_link_',
            '_imgJPG_',
            '_imgGIF_',
            '_total_on_live_links_id_',
            '_class_',
        ];

        if (empty($global['getLiveApplicationModelArray']['content'])) {
            $filenameExtra = $global['systemRootPath'] . 'plugin/Live/view/extraitems_templates/extraItem.html';
            $filenameExtraVideoPage = $global['systemRootPath'] . 'plugin/Live/view/extraitems_templates/extraItemVideoPage.html';
            $filename = $filenameListItem = $global['systemRootPath'] . 'plugin/Live/view/extraitems_templates/videoListItem.html';
            $global['getLiveApplicationModelArray']['content'] = file_get_contents($filename);
            $global['getLiveApplicationModelArray']['contentExtra'] = file_get_contents($filenameExtra);
            $global['getLiveApplicationModelArray']['contentExtraVideoPage'] = file_get_contents($filenameExtraVideoPage);
            $global['getLiveApplicationModelArray']['contentListem'] = file_get_contents($filenameListItem);
        }

        $u = new User($users_id);
        $lt = LiveTransmition::getFromDbByUser($users_id);
        $UserPhoto = User::getPhoto($users_id);
        $name = User::getNameIdentificationById($users_id);
        $comingsoon = false;
        if (!empty($startsOnDate)) {
            if (strtotime($startsOnDate) > time()) {
                $callback .= ';' . '$(\'.' . $uid . ' .liveNow\').attr(\'class\', \'liveNow label label-primary\');'
                        . '$(\'.' . $uid . ' .liveNow\').text(\'' . $startsOnDate . '\');'
                        . 'startTimerToDate(\'' . $startsOnDate . '\', \'.' . $uid . ' .liveNow\', false);';
                $comingsoon = true;
            }
        }
        if (empty($imgJPG)) {
            $imgJPG = getURL(Live::getPosterThumbsImage($users_id, 0, $comingsoon));
        }
        $replace = [
            $uid,
            $UserPhoto,
            $title,
            $u->getUser(),
            $link,
            (!empty($imgJPG) ? '<img src="' . getURL('view/img/loading-gif.png') . '" data-src="' . $imgJPG . '" class="thumbsJPG img-responsive" height="130">' : ''),
            (!empty($imgGIF) ? ('<img src="' . getURL('view/img/loading-gif.png') . '" data-src="' . $imgGIF . '" style="position: absolute; top: 0px; height: 0px; width: 0px; display: none;" class="thumbsGIF img-responsive" height="130">') : ''),
            $LiveUsersLabelLive,
            $class,
        ];

        $newContent = str_replace($search, $replace, $global['getLiveApplicationModelArray']['content']);
        $newContentExtra = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentExtra']);
        $newContentExtraVideoPage = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentExtraVideoPage']);
        $newContentVideoListItem = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentListem']);

        $array = [
            "html" => $newContent,
            "htmlExtra" => $newContentExtra,
            "htmlExtraVideoPage" => $newContentExtraVideoPage,
            "htmlExtraVideoListItem" => $newContentVideoListItem,
            "type" => $type,
            "photo" => $UserPhoto,
            "UserPhoto" => $UserPhoto,
            "title" => $title,
            "users_id" => $users_id,
            "name" => $name,
            "href" => $link,
            "link" => $link,
            "callback" => $callback,
            'poster' => $imgJPG,
            'imgGif' => $imgGIF,
            'categories_id' => intval($lt['categories_id']),
            'className' => $uid,
            'comingsoon' => $comingsoon,
        ];

        $_getLiveApplicationModelArray[$uid] = $array;
        return $array;
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
        $obj->topCopyKeysButtonTitle = "Copy Live Keys";
        $obj->hideTopCopyKeysButton = true;
        self::addDataObjectHelper('hideTopCopyKeysButton', 'Hide Top Copy Live Keys Button', 'This will hide the "Copy Live Keys" button on the top menu bar');

        $obj->button_title = "LIVE";
        self::addDataObjectHelper('button_title', 'Button Title', 'This is the title that will appear in your button to enter in the Live panel');
        $o = new stdClass();
        $o->type = [0 => __('Public') . ' UNDER DEVELOPMENT', 1 => __('Self Hosted')];
        $o->value = 1;
        $obj->server_type = $o;
        self::addDataObjectHelper('server_type', 'Server type', "If is set to public you do not need to configure anything");
        $obj->server = "rtmp://{$server['host']}/live";
        self::addDataObjectHelper('server', 'RTMP Server URL', 'Usually it is ' . "rtmp://{$server['host']}/live");
        $obj->playerServer = "{$scheme}://{$server['host']}:{$port}/live";
        self::addDataObjectHelper('playerServer', 'Player URL', 'This is a URL to your NGINX server, this URL will be used by the HTML5 player, If your site is HTTPS your player URL MUST be HTTPS as well, usually it is ' . "{$scheme}://{$server['host']}:{$port}/live");
        $obj->stats = "{$scheme}://{$server['host']}:{$port}/stat";
        self::addDataObjectHelper('stats', 'Stats Page URL', 'When you installed the NGINX you also install the stat.xsl, we will use it to grab the information when you have livestreams running, usually it is ' . "{$scheme}://{$server['host']}:{$port}/stat");
        $obj->restreamerURL = "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/restreamer.json.php";
        self::addDataObjectHelper('restreamerURL', 'Restreamer URL', 'https://github.com/WWBN/AVideo/wiki/Restream');
        $obj->controlURL = "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/control.json.php";
        self::addDataObjectHelper('controlURL', 'Control URL');
        $obj->controlServer = "http://localhost:8080/";
        self::addDataObjectHelper('controlServer', 'Control Server');
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
        $obj->hideUserGroups = false;
        $obj->hideShare = false;
        $obj->hideAdvancedStreamKeys = false;
        $obj->hidePublicListedOption = false;
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
        $obj->doNotShowGoLiveButton = false;
        self::addDataObjectHelper('doNotShowGoLiveButton', 'Hide Top Go live Button', 'This will hide the "Go Live" button on the top menu bar');
        $obj->doNotShowGoLiveButtonOnUploadMenu = false;
        self::addDataObjectHelper('doNotShowGoLiveButtonOnUploadMenu', 'Hide Go live Button on Upload Menu', 'This will hide the "Go Live" button on the right upload menu bar');
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
        $obj->requestStatsInterval = 15; // how many seconds until requesting the stats again
        self::addDataObjectHelper('requestStatsInterval', 'Stats Request Interval', 'how many seconds until request the stats again');
        $obj->streamDeniedMsg = "You can not stream live videos";
        self::addDataObjectHelper('streamDeniedMsg', 'Denied Message', 'We will show this message when a user is not allowed so watch a livestream');
        $obj->allowMultipleLivesPerUser = true;
        self::addDataObjectHelper('allowMultipleLivesPerUser', 'Allow Multiple Lives Per User', 'Your users will be able to make unlimited livestreams');

        $obj->controllButtonsShowOnlyToAdmin_record_start = false;
        self::addDataObjectHelper('controllButtonsShowOnlyToAdmin_record_start', 'Show Record Start Button Only to Admin', 'Regular users will not able to see this button');
        $obj->controllButtonsShowOnlyToAdmin_record_stop = false;
        self::addDataObjectHelper('controllButtonsShowOnlyToAdmin_record_stop', 'Show Record Stop Button Only to Admin', 'Regular users will not able to see this button');
        $obj->controllButtonsShowOnlyToAdmin_drop_publisher = false;
        self::addDataObjectHelper('controllButtonsShowOnlyToAdmin_drop_publisher', 'Show Drop Publisher Button Only to Admin', 'Regular users will not able to see this button');
        $obj->controllButtonsShowOnlyToAdmin_drop_publisher_reset_key = false;
        self::addDataObjectHelper('controllButtonsShowOnlyToAdmin_drop_publisher_reset_key', 'Show Drop Publisher and Reset Key Button Only to Admin', 'Regular users will not able to see this button');
        $obj->controllButtonsShowOnlyToAdmin_save_dvr = false;
        self::addDataObjectHelper('controllButtonsShowOnlyToAdmin_save_dvr', 'Show Save DVR Button Only to Admin', 'Regular users will not able to see this button');

        $obj->disable_live_schedule = false;
        self::addDataObjectHelper('disable_live_schedule', 'Disable Live Schedule');

        $obj->live_schedule_label = 'Upcoming Events';
        self::addDataObjectHelper('live_schedule_label', 'Label for Schedule');

        $obj->webRTC_isDisabled = false;
        self::addDataObjectHelper('webRTC_isDisabled', 'Disable WebRTC camera', 'https://github.com/WWBN/AVideo/wiki/WebRTC-Server');

        $o = new stdClass();
        $o->type = [0 => __('Public'), 1 => __('Self Hosted')];
        $o->value = 0;
        $obj->webRTC_server = $o;
        self::addDataObjectHelper('webRTC_server', 'WebRTC Server', 'https://github.com/WWBN/AVideo/wiki/WebRTC-Server');

        $ServerHost = getHostOnlyFromURL($global['webSiteRootURL']);

        $obj->webRTC_SelfHostedURL = $ServerHost;
        self::addDataObjectHelper('webRTC_SelfHostedURL', 'Self Hosted URL', 'Self Hosted only');

        $obj->webRTC_CertPath = '/etc/letsencrypt/live/' . $ServerHost . '/cert.pem';
        self::addDataObjectHelper('webRTC_CertPath', 'SSL Certificate path', 'Self Hosted only');

        $obj->webRTC_KeyPath = '/etc/letsencrypt/live/' . $ServerHost . '/privkey.pem';
        self::addDataObjectHelper('webRTC_KeyPath', 'SSL Key path', 'Self Hosted only');

        $obj->webRTC_ChainCertPath = '/etc/letsencrypt/live/' . $ServerHost . '/chain.pem';
        self::addDataObjectHelper('webRTC_ChainCertPath', 'SSL Certificate Chain path', 'Self Hosted only');

        $obj->webRTC_PushRTMP = false;
        self::addDataObjectHelper('webRTC_PushRTMP', 'PushRTMP', 'Self Hosted only If it is unchecked we will restream the Webcam instead of pushing it');

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
                $css .= '<link href="' . getURL('plugin/YouPHPFlix2/view/css/fullscreen.css') . '" rel="stylesheet" type="text/css"/>';
                $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
            }
            $js .= '<script>var playLiveInFullScreen = true</script>';
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        
        if($live = isLive()){
            $prerollPoster = 'false';
            $postrollPoster = 'false';
            if (self::prerollPosterExists()) {
                $prerollPoster = "'" . getURL(self::getPrerollPosterImage()) . "'";
            }
            if (self::postrollPosterExists()) {
                $postrollPoster = "'" . getURL(self::getPostrollPosterImage()) . "'";
            }
            $liveImageBGTemplate = '';
            if($prerollPoster || $postrollPoster){
                $liveImageBGTemplate = file_get_contents($global['systemRootPath'].'plugin/Live/view/imagebg.template.html');
            }
            $js .= '<script>'
                    . 'var prerollPoster_'.$live['cleanKey'].' = ' . $prerollPoster . ';'
                    . 'var postrollPoster_'.$live['cleanKey'].' = ' . $postrollPoster . ';'
                    . 'var liveImageBGTemplate = ' . json_encode($liveImageBGTemplate) . ';'
                    . '</script>';
            
            
        }
        
        return $js . $css;
    }

    public static function getWebRTCPlayer($live_servers_id = -1) {
        $player = self::getWebRTCServerURL($live_servers_id);
        return "{$player}player/";
    }

    public static function getWebRTCIframeURL($users_id) {
        global $global;
        $obj = AVideoPlugin::getObjectData("Live");
        $iframeURL = Live::getWebRTCPlayer();
        $iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
        $iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash($users_id));
        $iframeURL = addQueryStringParameter($iframeURL, 'server_type', $obj->server_type->value);
        return $iframeURL;
    }

    public static function getWebRTCServerURL($live_servers_id = -1) {
        global $global;
        $obj = AVideoPlugin::getObjectData("Live");

        if (empty($obj->webRTC_server->value)) {
            return 'https://webrtc.ypt.me/';
        }

        if (!empty($obj->useLiveServers)) {
            if ($live_servers_id < 0) {
                $live_servers_id = self::getCurrentLiveServersId();
            }
            $ls = new Live_servers($live_servers_id);
            if (!empty($ls->getwebRTC_server())) {
                return $ls->getwebRTC_server();
            }
        }

        return "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/WebRTCServer/";
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        $js = '';
        if (!empty($obj->playLiveInFullScreen)) {
            $js = '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'.liveVideo a.galleryLink\'); } });</script>';
        } elseif (!empty($obj->playLiveInFullScreenOnIframe)) {
            $js = '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
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

    public static function getDestinationApplicationName() {
        $app = self::getAPPName();
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$app}?p=" . User::getUserPass();
    }

    public static function getDestinationHost() {
        $server = self::getServer();
        $host = parse_url($server, PHP_URL_HOST);
        return $host;
    }

    public static function getDestinationPort() {
        $server = self::getServer();
        $port = parse_url($server, PHP_URL_PORT);
        if (empty($port)) {
            $port = 1935;
        }
        return $port;
    }

    public static function getServer($live_servers_id = -1) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            return 'rtmp://' . self::$public_server_domain . '/live';
        }
        if (!empty($obj->useLiveServers)) {
            if ($live_servers_id < 0) {
                $live_servers_id = self::getCurrentLiveServersId();
            }
            $ls = new Live_servers($live_servers_id);
            if (!empty($ls->getRtmp_server())) {
                return $ls->getRtmp_server();
            }
        }
        return trim($obj->server);
    }

    public static function getControlOrPublic($key, $live_servers_id = 0) {
        global $global;
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            $row = LiveTransmitionHistory::getLatest($key, $live_servers_id);
            if (!empty($row['domain'])) {
                $url = "{$row['domain']}control.json.php";
                return addQueryStringParameter($url, 'webSiteRootURL', $global['webSiteRootURL']);
            }
        }
        $domain = self::getControl($live_servers_id);
        return $domain;
    }

    public static function getAPPName() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            return 'live';
        } else {
            $server = self::getPlayerServer();
            if (preg_match('/.cdn.ypt.me/', $server)) {
                return 'live';
            } else {
                $server = rtrim($server, "/");
                $parts = explode("/", $server);
                $app = array_pop($parts);
            }
        }
        return $app;
    }

    public static function getDropURL($key, $live_servers_id = 0) {
        $obj = AVideoPlugin::getObjectData("Live");

        $app = self::getAPPName();
        $domain = self::getControlOrPublic($key, $live_servers_id);
        $domain = addQueryStringParameter($domain, 'command', 'drop_publisher');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getIsRecording($key, $live_servers_id = 0) {
        $app = self::getAPPName();
        $domain = self::getControlOrPublic($key, $live_servers_id);
        $domain = addQueryStringParameter($domain, 'command', 'is_recording');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getStartRecordURL($key, $live_servers_id = 0) {
        $app = self::getAPPName();
        $domain = self::getControlOrPublic($key, $live_servers_id);
        $domain = addQueryStringParameter($domain, 'command', 'record_start');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getStopRecordURL($key, $live_servers_id = 0) {
        $app = self::getAPPName();
        $domain = self::getControlOrPublic($key, $live_servers_id);
        $domain = addQueryStringParameter($domain, 'command', 'record_stop');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function controlRecording($key, $live_servers_id, $start = true, $try = 0) {
        if ($start) {
            $url = self::getStartRecordURL($key, $live_servers_id);
        } else {
            $url = self::getStopRecordURL($key, $live_servers_id);
        }
        $response = url_get_contents($url, '', 5);
        _error_log("Live:controlRecording {$url} {$live_servers_id} - [{$response}]");
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "";
        $obj->remoteResponse = false;
        if (!empty($response)) {
            $json = json_decode($response);
            if (!empty($json)) {
                if ($start && empty($json->error) && empty($json->response) && $try < 4) {
                    _error_log("Live:controlRecording start record is not ready trying again in 5 seconds " . (isCommandLineInterface() ? 'From Command Line' : 'Not Command Line'));
                    _error_log("Live:controlRecording " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

                    sleep(5);
                    return self::controlRecording($key, $live_servers_id, $start, $try + 1);
                }
                _error_log("Live:controlRecording start record is ready {$json->response}");
                $obj->error = $json->error;
                $obj->msg = $json->msg;
                $obj->remoteResponse = true;
            } else {
                $obj->msg = "JSON response fail";
            }
        } else {
            $obj->msg = "Control response fail";
        }
        if ($obj->error) {
            _error_log("Live::controlRecording: [$key], [$live_servers_id], [$start] " . json_encode($obj));
        }
        return $obj;
    }

    public static function controlRecordingAsync($key, $live_servers_id, $start = true) {
        global $global;
        outputAndContinueInBackground();
        $command = get_php() . " {$global['systemRootPath']}plugin/Live/controlRecording.php '$key' '$live_servers_id' '$start'";

        _error_log("NGINX Live::controlRecordingAsync start  ($command)");
        $pid = execAsync($command);
        _error_log("NGINX Live::controlRecordingAsync end {$pid}");
        return $pid;
    }

    public static function userCanRecordLive($users_id) {
        if (!AVideoPlugin::isEnabledByName('SendRecordedToEncoder')) {
            return false;
        }
        return SendRecordedToEncoder::canRecord($users_id);
    }

    public static function getButton($command, $key, $live_servers_id = 0, $iconsOnly = false, $label = "", $class = "", $tooltip = "") {
        if (!User::canStream()) {
            return '';
        }
        global $global;
        $id = "getButton" . uniqid();
        $afterLabel = "";
        $obj = AVideoPlugin::getDataObject('Live');
        switch ($command) {
            case "record_start":
                if ($obj->controllButtonsShowOnlyToAdmin_record_start && !User::isAdmin()) {
                    return '';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                $buttonClass = "btn btn-success";
                $iconClass = "fas fa-video";
                if (empty($label)) {
                    $label = __("Start Record");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Start Record");
                }
                $afterLabel = '<span class="fas fa-circle isRecordingIcon isRecordingIcon' . $key . '" ></span>';
                break;
            case "record_stop":
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_record_stop && !User::isAdmin()) {
                    return '';
                }
                $buttonClass = "btn btn-danger";
                $iconClass = "fas fa-video-slash";
                if (empty($label)) {
                    $label = __("Stop Record");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Stop Record");
                }
                break;
            case "drop_publisher":
                if ($obj->controllButtonsShowOnlyToAdmin_drop_publisher && !User::isAdmin()) {
                    return '';
                }
                $buttonClass = "btn btn-default";
                $iconClass = "fas fa-wifi";
                if (empty($label)) {
                    $label = __("Disconnect Livestream");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Disconnect Livestream");
                }
                break;
            case "drop_publisher_reset_key":
                if ($obj->controllButtonsShowOnlyToAdmin_drop_publisher_reset_key && !User::isAdmin()) {
                    return '';
                }
                $buttonClass = "btn btn-default";
                $iconClass = "fas fa-key";
                if (empty($label)) {
                    $label = __("Disconnect Livestream");
                }
                if (empty($tooltip)) {
                    $tooltip = __("Disconnect Livestream") . __(" and also reset the stream name/key");
                }
                break;
            case "save_dvr":
                $obj2 = AVideoPlugin::getDataObjectIfEnabled('SendRecordedToEncoder');
                if (empty($obj2) || empty($obj2->saveDVREnable)) {
                    return '<!-- SendRecordedToEncoder saveDVREnable is not present -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_save_dvr && !User::isAdmin()) {
                    return '<!-- User Cannot save DVR controllButtonsShowOnlyToAdmin_save_dvr -->';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                return '<!-- SendRecordedToEncoder::getSaveDVRButton -->' . SendRecordedToEncoder::getSaveDVRButton($key, $live_servers_id, $class);
                break;
            case "save_the_momment":
                $obj2 = AVideoPlugin::getDataObjectIfEnabled('SendRecordedToEncoder');
                if (empty($obj2) || empty($obj2->saveTheMommentEnable)) {
                    return '<!-- SendRecordedToEncoder saveDVREnable is not present -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_save_dvr && !User::isAdmin()) {
                    return '<!-- User Cannot save DVR controllButtonsShowOnlyToAdmin_save_dvr -->';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                $class .= 'btn btn-warning ';
                return '<!-- SendRecordedToEncoder::getSaveTheMommentButton -->' . SendRecordedToEncoder::getSaveTheMommentButton($key, $live_servers_id, $class);
                break;
            case "download_the_momment":
                $obj2 = AVideoPlugin::getDataObjectIfEnabled('SendRecordedToEncoder');
                if (empty($obj2) || empty($obj2->downloadTheMommentEnable)) {
                    return '<!-- SendRecordedToEncoder saveDVREnable is not present -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_save_dvr && !User::isAdmin()) {
                    return '<!-- User Cannot save DVR controllButtonsShowOnlyToAdmin_save_dvr -->';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                $class .= 'btn btn-info ';
                return '<!-- SendRecordedToEncoder::getSaveTheMommentButton -->' . SendRecordedToEncoder::getDownloadTheMommentButton($key, $live_servers_id, $class);
                break;
            default:
                return '';
        }
        if ($iconsOnly) {
            $label = "";
        }

        $html = "<button class='{$buttonClass} {$class}' id='{$id}'  data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{$tooltip}\"><i class='{$iconClass}'></i> <span class='hidden-sm hidden-xs'>{$label}</span> {$afterLabel}";
        $html .= "<script>$(document).ready(function () {
            $('#{$id}').click(function(){
        modal.showPleaseWait();
                $.ajax({
                    url: '{$global['webSiteRootURL']}plugin/Live/control.json.php?command=$command&key={$key}&live_servers_id={$live_servers_id}',
                    success: function (response) {
                        console.log('getDropButton called');
                        console.log(response);

                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoToastError('" . __('Error') . " '+response.msg);
                        } else{
                            $('#streamkey, .streamkey').val(response.newkey);
                            avideoToastSuccess('" . __('Success') . " '+response.msg);
                        }
                    }
                });
            });
    });</script>";
        $html .= "</button>";
        return $html;
    }

    public static function getRecordControlls($key, $live_servers_id = 0, $iconsOnly = false) {
        if (!User::canStream()) {
            return "";
        }

        $btn = "<div class=\"btn-group justified\">";
        $btn .= self::getButton("record_start", $key, $live_servers_id, $iconsOnly);
        $btn .= self::getButton("record_stop", $key, $live_servers_id, $iconsOnly);
        $btn .= "</div>";

        return $btn;
    }

    public static function getAllControlls($key, $live_servers_id = 0, $iconsOnly = false, $btnClass = '') {
        if (!Live::canManageLiveFromLiveKey($key, User::getId())) {
            return '';
        }

        $btn = "<div class=\"btn-group justified recordLiveControlsDiv\" style=\"display: none;\" id=\"liveControls\">";
        //$btn .= self::getButton("drop_publisher", $live_transmition_id, $live_servers_id);
        $btn .= self::getButton("save_dvr", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("save_the_momment", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("download_the_momment", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("drop_publisher_reset_key", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("record_start", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("record_stop", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= "</div>";
        $btn .= "<script>
                $(document).ready(function () {
                    setInterval(function () {
                        if (isOnlineLabel || $('.liveOnlineLabel.label-success').length) {
                            $('#liveControls').slideDown();
                        } else {
                            $('#liveControls').slideUp();
                        }
                    }, 1000);

                });
            </script>";

        return $btn;
    }

    public static function getRestreamer($live_servers_id = -1) {
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

    public static function getControl($live_servers_id = -1) {
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj->useLiveServers) && !empty($live_servers_id)) {
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

    public static function getRTMPLink($users_id, $forceIndex = false) {
        $key = self::getKeyFromUser($users_id);
        return self::getRTMPLinkFromKey($key, $forceIndex);
    }

    public static function getRTMPLinkFromKey($key, $forceIndex = false) {
        $lso = new LiveStreamObject($key);

        return $lso->getRTMPLink($forceIndex);
    }

    public static function getRTMPLinkWithOutKey($users_id, $short = true) {
        $lso = new LiveStreamObject(self::getKeyFromUser($users_id));

        return $lso->getRTMPLinkWithOutKey($short);
    }

    public static function getRTMPLinkWithOutKeyFromKey($key) {
        $lso = new LiveStreamObject($key);

        return $lso->getRTMPLinkWithOutKey();
    }

    public static function getKeyFromUser($users_id) {
        if (!User::isLogged() || ($users_id !== User::getId() && !User::isAdmin())) {
            return false;
        }
        $user = new User($users_id);
        $trasnmition = LiveTransmition::createTransmitionIfNeed($users_id);
        return $trasnmition['key'];
    }

    public static function getDynamicKey($key) {
        $objLive = AVideoPlugin::getDataObject("Live");
        if ($objLive->allowMultipleLivesPerUser) {
            $key .= '-' . date('His');
        }
        return $key;
    }

    public static function getPlayerServer($ignoreCDN = false) {
        $obj = AVideoPlugin::getObjectData("Live");

        $url = $obj->playerServer;
        if (empty($ignoreCDN)) {
            $url = getCDNOrURL($url, 'CDN_Live');
        }
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getLiveServersIdRequest());
            if (!empty($ls->getPlayerServer())) {
                $url = $ls->getPlayerServer();
                if (empty($ignoreCDN)) {
                    $url = getCDNOrURL($url, 'CDN_LiveServers', $ls->getId());
                }
            }
        }
        //$url = str_replace("encoder.gdrive.local", "192.168.1.18", $url);
        return $url;
    }

    public static function getUseAadaptiveMode() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            return true;
        }
        if (!empty($obj->useLiveServers)) {
            $ls = new Live_servers(self::getCurrentLiveServersId());
            return $ls->getUseAadaptiveMode();
        }
        return $obj->useAadaptiveMode;
    }

    public static function getRemoteFile() {
        return self::getRemoteFileFromLiveServersID(self::getCurrentLiveServersId());
    }

    public static function getRemoteFileFromLiveServersID($live_servers_id) {
        global $global;
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($live_servers_id) || !empty($obj->useLiveServers)) {
            $ls = new Live_servers($live_servers_id);
            $url = $ls->getGetRemoteFile();
            if (IsValidURL($url)) {
                return $url;
            }
        }
        return "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/getRecordedFile.php";
    }

    public static function getRemoteFileFromRTMPHost($rtmpHostURI) {
        $live_servers_id = Live_servers::getServerIdFromRTMPHost($rtmpHostURI);
        return self::getRemoteFileFromLiveServersID($live_servers_id);
    }

    public static function getLiveServersIdRequest() {
        if (empty($_REQUEST['live_servers_id'])) {
            return 0;
        }
        return intval($_REQUEST['live_servers_id']);
    }

    public static function getLiveScheduleIdRequest() {
        if (empty($_REQUEST['live_schedule_id'])) {
            return 0;
        }
        return intval($_REQUEST['live_schedule_id']);
    }

    public static function getM3U8File($uuid, $doNotProtect = false, $ignoreCDN = false) {
        $live_servers_id = self::getLiveServersIdRequest();
        $lso = new LiveStreamObject($uuid, $live_servers_id, false, false);
        $parts = self::getLiveParametersFromKey($uuid);
        if (!empty($parts['live_index'])) {
            $allowOnlineIndex = $parts['live_index'];
        } elseif (!empty($_REQUEST['live_index'])) {
            $allowOnlineIndex = false;
        }
        //_error_log("Live:getM3U8File($uuid) ". json_encode($parts));
        return $lso->getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN);
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

    public function getStatsObject($live_servers_id = 0, $force_recreate = false, $tries = 0) {
        global $global;

        if (!empty($global['disableGetStatsObject'])) {
            return [];
        }

        if (!function_exists('simplexml_load_file')) {
            _error_log("Live::getStatsObject: You need to install the simplexml_load_file function to be able to see the Live stats", AVideoLog::$ERROR);
            return false;
        }

        $name = "getStats" . DIRECTORY_SEPARATOR . "live_servers_id_{$live_servers_id}" . DIRECTORY_SEPARATOR . "getStatsObject";

        global $getStatsObject;
        if (!isset($getStatsObject)) {
            $getStatsObject = [];
        }
        if (empty($force_recreate)) {
            //_error_log("Live::getStatsObject[$live_servers_id] 1: searching for cache");
            if (isset($getStatsObject[$live_servers_id])) {
                //_error_log("Live::getStatsObject[$live_servers_id] 2: return cached result");
                return $getStatsObject[$live_servers_id];
            }

            $result = ObjectYPT::getCache($name, maxLifetime() + 60, true);

            if (!empty($result)) {
                //_error_log("Live::getStatsObject[$live_servers_id] 3: return cached result $name [lifetime=" . (maxLifetime() + 60) . "]");
                return _json_decode($result);
            }
            _error_log("Live::getStatsObject[$live_servers_id] 4: cache not found");
        } else {
            _error_log("Live::getStatsObject[$live_servers_id] 5: forced to be recreated");
        }

        $o = $this->getDataObject();
        if ($o->doNotProcessNotifications) {
            _error_log("Live::getStatsObject[$live_servers_id]: will not show notifications because you select the option doNotProcessNotifications on the live plugin ");
            $xml = new stdClass();
            $xml->server = new stdClass();
            $xml->server->application = [];
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
        //_error_log("Live::getStatsObject[$live_servers_id]: Creating a waitfile {$waitFile}");
        file_put_contents($waitFile, time());
        $data = $this->get_data($url, $o->requestStatsTimout);
        unlink($waitFile);
        if (empty($data)) {
            _session_start();
            if (empty($_SESSION['getStatsObjectRequestStatsTimout'])) {
                $_SESSION['getStatsObjectRequestStatsTimout'] = [];
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

    public function get_data($url, $timeout) {
        global $global;
        if (!IsValidURL($url)) {
            return false;
        }

        //_error_log_debug("Live::get_data($url, $timeout)");
        return url_get_contents($url, '', $timeout);
    }

    public function getChartTabs() {
        return '<li><a data-toggle="tab" id="liveVideos" href="#liveVideosMenu"><i class="fas fa-play-circle"></i> ' . __('Live videos') . '</a></li>';
    }

    public function getChartContent() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/report.php';
    }

    public static function saveHistoryLog($key) {
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

    public static function stopLive($users_id) {
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
                rrmdir($dir);
            }
        }
    }

    // not implemented yet
    public static function startRecording($users_id) {
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

    public static function getApplicationName() {
        $rtmpServer = self::getServer();
        $parts = explode('/', $rtmpServer);
        $live = end($parts);

        if (!preg_match('/^live/i', $live)) {
            $live = 'live';
        }
        return trim($live);
    }

    // not implemented yet
    public static function stopRecording($users_id) {
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

    public static function getLinkToLiveFromUsers_id($users_id) {
        $live_servers_id = self::getCurrentLiveServersId();
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id);
    }

    public static function getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $live_index = null) {
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

    public static function getLinkToLiveFromChannelNameAndLiveServer($channelName, $live_servers_id, $live_index = null, $live_schedule_id = 0) {
        global $global;
        $live_servers_id = intval($live_servers_id);
        $channelName = trim($channelName);
        if (empty($channelName)) {
            return false;
        }

        $url = "{$global['webSiteRootURL']}live/{$live_servers_id}/" . urlencode($channelName);

        if (!empty($live_index)) {
            $url .= '/' . urlencode($live_index);
        } elseif (!isset($live_index) && !empty($_REQUEST['live_index'])) {
            $url .= '/' . urlencode($_REQUEST['live_index']);
        }

        if (!empty($_REQUEST['playlists_id_live'])) {
            $url = addQueryStringParameter($url, 'playlists_id_live', $_REQUEST['playlists_id_live']);
        }
        $live_schedule_id = intval($live_schedule_id);
        if (!empty($live_schedule_id)) {
            $url = addQueryStringParameter($url, 'live_schedule', $live_schedule_id);
        }

        //return "{$global['webSiteRootURL']}plugin/Live/?live_servers_id={$live_servers_id}&c=" . urlencode($channelName);
        return $url;
    }

    public static function getAvailableLiveServersId() {
        $ls = self::getAvailableLiveServer();
        if (empty($ls)) {
            return 0;
        } else {
            return intval($ls->live_servers_id);
        }
    }

    public static function getLastServersIdFromUser($users_id) {
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($last)) {
            return 0;
        } else {
            return intval($last['live_servers_id']);
        }
    }

    public static function getLastsLiveHistoriesFromUser($users_id, $count = 10) {
        return LiveTransmitionHistory::getLastsLiveHistoriesFromUser($users_id, $count);
    }

    public static function getLinkToLiveFromUsers_idWithLastServersId($users_id) {
        $live_servers_id = self::getLastServersIdFromUser($users_id);
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id);
    }

    public static function getCurrentLiveServersId() {
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
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/Live/view/finishAll.json.php\', {});" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-ban"></i> ' . __('Mark all as finished') . '</button>';
        if ($obj->server_type->value) {
            if ($obj->useLiveServers) {
                $servers = Live_servers::getAll();
                foreach ($servers as $value) {
                    $btn .= '<button onclick="avideoModalIframeSmall(\'' . $global['webSiteRootURL'] . 'plugin/Live/test.php?statsURL=' . urlencode($value['stats_url']) . '\');" class="btn btn-primary btn-sm btn-xs btn-block"> ' . __('Test Server') . ' ' . $value['id'] . '</button>';
                }
            } else {
                $btn .= '<button onclick="avideoModalIframeSmall(\'' . $global['webSiteRootURL'] . 'plugin/Live/test.php?statsURL=' . urlencode($obj->stats) . '\');" class="btn btn-primary btn-sm btn-xs btn-block"> ' . __('Test Stats') . '</button>';
            }
        }
        return $btn;
    }

    public static function getStats($force_recreate = false) {
        global $getStatsLive, $_getStats, $getStatsObject;
        if (empty($force_recreate)) {
            if (isset($getStatsLive)) {
                //_error_log('Live::getStats: return cached result');
                return $getStatsLive;
            }
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            $rows = LiveTransmitionHistory::getActiveLiveFromUser(0, '', '', 50);
            $servers = [];
            $servers['applications'] = [];
            foreach ($rows as $value) {
                if (!is_array($value)) {
                    continue;
                }
                $servers['applications'][] = LiveTransmitionHistory::getApplicationObject($value['id']);
            }
            return $servers;
        } elseif (empty($obj->useLiveServers)) {
            //_error_log('getStats getStats 1: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));
            $getStatsLive = self::_getStats(0, $force_recreate);
            //_error_log('Live::getStats(0) 1');
            return $getStatsLive;
        } else {
            $rows = Live_servers::getAllActive();
            $liveServers = [];
            foreach ($rows as $key => $row) {
                $ls = new Live_servers(Live::getLiveServersIdRequest());
                if (!empty($row['playerServer'])) {
                    //_error_log('getStats getStats 2: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));
                    $server = self::_getStats($row['id'], $force_recreate);
                    $server->live_servers_id = $row['id'];
                    $server->playerServer = $row['playerServer'];
                    $getStatsLive = $server;
                    $liveServers[] = $server;
                }
            }
            if (!empty($liveServers)) {
                return $liveServers;
            }
        }
        $ls = Live_servers::getAllActive();
        $liveServers = [];
        $getLiveServersIdRequest = self::getLiveServersIdRequest();
        foreach ($ls as $value) {
            $server = Live_servers::getStatsFromId($value['id'], $force_recreate);
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
        //_error_log("Live::getStats return " . json_encode($liveServers));
        $_REQUEST['live_servers_id'] = $getLiveServersIdRequest;
        $getStatsLive = $liveServers;
        return $liveServers;
    }

    public static function isAdaptive($key) {
        if (preg_match('/_(hi|low|mid)$/i', $key)) {
            return true;
        }
        return false;
    }

    public static function getAllServers() {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            return ["id" => 0, "name" => __("Default"), "status" => "a", "rtmp_server" => $obj->server, 'playerServer' => $obj->playerServer, "stats_url" => $obj->stats, "disableDVR" => $obj->disableDVR, "disableGifThumbs" => $obj->disableGifThumbs, "useAadaptiveMode" => $obj->useAadaptiveMode, "protectLive" => $obj->protectLive, "getRemoteFile" => ""];
        } else {
            return Live_servers::getAllActive();
        }
    }

    public static function getAvailableLiveServer() {
        global $_getAvailableLiveServer__Live;
        if (isset($_getAvailableLiveServer__Live)) {
            return $_getAvailableLiveServer__Live;
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
                $liveServers = [];
                $servers = Live_servers::getAllActive();
                foreach ($servers as $value) {
                    $obj = new stdClass();
                    $obj->live_servers_id = $value['id'];
                    $obj->countLiveStream = 0;
                    $liveServers[$value['id']] = $obj;
                }
                /*
                  foreach ($stats['applications'] as $value) {
                  if (!empty($value['live_servers_id'])) {
                  $liveServers[$value['live_servers_id']]->countLiveStream++;
                  }
                  }
                 * 
                 */

                usort($liveServers, function ($a, $b) {
                    if ($a->countLiveStream == $b->countLiveStream) {
                        $_getAvailableLiveServer = 0;
                        return 0;
                    }
                    $_getAvailableLiveServer = ($a->countLiveStream < $b->countLiveStream) ? -1 : 1;
                    return $_getAvailableLiveServer;
                });
                if (empty($liveServers[0])) {
                    _error_log("Live::getAvailableLiveServer we could not get server status, try to uncheck useLiveServers parameter from the Live plugin");
                    $_getAvailableLiveServer__Live = [];
                    return [];
                }
                $return = $liveServers[0];
                ObjectYPT::setCache($name, $return);
            }
        }
        $_getAvailableLiveServer__Live = $return;
        return $return;
    }

    public static function canSeeLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->userCanSeeTransmition();
    }

    public static function isPasswordProtected($key) {
        global $_isPasswordProtected;
        if (empty($key)) {
            return false;
        }
        if (!isset($_isPasswordProtected)) {
            $_isPasswordProtected = array();
        }
        if (!isset($_isPasswordProtected[$key])) {
            $lt = self::getLiveTransmitionObjectFromKey($key);
            if (empty($lt)) {
                $_isPasswordProtected[$key] = false;
            } else {
                $password = $lt->getPassword();
                if (!empty($password)) {
                    $_isPasswordProtected[$key] = true;
                } else {
                    $_isPasswordProtected[$key] = false;
                }
            }
        }
        //var_dump($key, $_isPasswordProtected[$key]);
        return $_isPasswordProtected[$key];
    }

    public static function canManageLiveFromLiveKey($key, $users_id) {
        if (empty($users_id)) {
            return false;
        }
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        $user = new User($users_id);
        if ($user->getIsAdmin()) {
            return true;
        }
        $u_id = $lt->getUsers_id();
        return $u_id == $users_id;
    }

    public static function isAPrivateLiveFromLiveKey($key) {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->isAPrivateLive();
    }

    public static function getTitleFromUsers_Id($users_id) {
        if (empty($users_id)) {
            return '';
        }
        $lt = self::getLiveTransmitionObjectFromUsers_id($users_id);
        if (empty($lt)) {
            return '';
        }
        return self::getTitleFromKey($lt->getKey(), $lt->getTitle());
    }

    public static function getLiveTransmitionObjectFromUsers_id($users_id) {
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (!empty($latest)) {
            $key = $latest['key'];
        } else {
            $key = self::getLiveKey($users_id);
        }
        return self::getLiveTransmitionObjectFromKey($key);
    }

    public static function getLiveTransmitionObjectFromKey($key) {
        global $getLiveTransmitionObjectFromKey;
        if (empty($getLiveTransmitionObjectFromKey)) {
            $getLiveTransmitionObjectFromKey = [];
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

    /*
      static function cleanUpApplication($application){
      if(!is_object($application) || empty($application->name)) {
      return $application;
      }
      $parts = explode('&', $application->name);
      if (!empty($parts[0])) {
      $application->name = $parts[0];
      }
      $parts = explode('&', $application->{$application->name}->stream->name);
      if (!empty($parts[0])) {
      $application->{$application->name}->stream->name = $parts[0];
      }
      //var_dump($application, $parts);exit;
      return $application;
      }
     *
     */

    public static function _getStats($live_servers_id = 0, $force_recreate = false) {
        global $global, $_getStats;
        if (empty($_REQUEST['name'])) {
            //_error_log("Live::_getStats {$live_servers_id} GET " . json_encode($_GET));
            //_error_log("Live::_getStats {$live_servers_id} POST " . json_encode($_POST));
            //_error_log("Live::_getStats {$live_servers_id} REQUEST " . json_encode($_REQUEST));
            $_REQUEST['name'] = "undefined";
        }
        //_error_log('_getStats: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));
        $cacheName = "getStats" . DIRECTORY_SEPARATOR . "live_servers_id_{$live_servers_id}" . DIRECTORY_SEPARATOR . "{$_REQUEST['name']}_" . User::getId();
        //$force_recreate = true;
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
        $obj->applications = [];
        $obj->hidden_applications = [];
        $obj->name = $_REQUEST['name'];
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $p = AVideoPlugin::loadPlugin("Live");
        $xml = $p->getStatsObject($live_servers_id, $force_recreate);
        $xml = json_encode($xml);
        $xml = _json_decode($xml);
        $stream = false;
        $lifeStream = [];
        $applicationName = self::getApplicationName();
        if (empty($xml) || !is_object($xml)) {
            _error_log("_getStats XML is not an object live_servers_id=$live_servers_id");
        } else {
            //$obj->server = $xml->server;
            if (!empty($xml->server->application) && !is_array($xml->server->application)) {
                $application = $xml->server->application;
                $xml->server->application = [];
                $xml->server->application[] = $application;
            }
            foreach ($xml->server->application as $key => $application) {
                //$application = self::cleanUpApplication($application);
                if ($application->name !== $applicationName && $application->name !== 'adaptive') {
                    continue;
                }
                if (!empty($application->live->stream)) {
                    if (empty($lifeStream)) {
                        $lifeStream = [];
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
            unset($_REQUEST['playlists_id_live']);
            if (!empty($value->name)) {
                $_playlists_id_live = @$_REQUEST['playlists_id_live'];
                $row = LiveTransmition::keyExists($value->name);
                //var_dump($row);exit;
                if (empty($row['users_id'])) {
                    continue;
                }
                if (!empty($row) && $value->name === $obj->name) {
                    $obj->msg = "ONLINE";
                }
                $title = self::getTitleFromKey($row['key'], $row['title']);
                self::getTitleFromUsers_Id($users_id);
                $u = new User($row['users_id']);
                $hiddenName = preg_replace('/^(.{5})/', '*****', $value->name);

                //_error_log('Live::isLiveFromKey:_getStats '. json_encode($_SERVER));
                if (!self::canSeeLiveFromLiveKey($value->name)) {
                    $obj->hidden_applications[] = [
                        "key" => $value->name,
                        "name" => $row['channelName'],
                        "user" => $row['channelName'],
                        "title" => "{$row['channelName']} ($hiddenName} is a private live",
                    ];
                } elseif ($u->getStatus() !== 'a') {
                    $obj->hidden_applications[] = [
                        "key" => $value->name,
                        "name" => $row['channelName'],
                        "user" => $row['channelName'],
                        "title" => "{$row['channelName']} {$hiddenName} " . __("the user is inactive"),
                    ];
                    if (!User::isAdmin()) {
                        continue;
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
                if (!empty($live_index)) {
                    $_REQUEST['live_index'] = $live_index;
                }

                $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($value->name, $live_servers_id) : '');
                $uid = "live_{$live_servers_id}_{$value->name}";
                $live_schedule_id = 0;
                if (!empty($row['scheduled'])) {
                    $live_schedule_id = intval($row['id']);
                }

                $link = Live::getLinkToLiveFromChannelNameAndLiveServer($u->getChannelName(), $live_servers_id, $live_index, $live_schedule_id);
                $imgJPG = $p->getLivePosterImage($row['users_id'], $live_servers_id, $playlists_id_live, $live_index, 'jpg', $live_schedule_id);
                if ($obj->disableGif) {
                    $imgGIF = '';
                } else {
                    $imgGIF = $p->getLivePosterImage($row['users_id'], $live_servers_id, $playlists_id_live, $live_index, 'webp', $live_schedule_id);
                }

                $users_id = LiveTransmition::getUsers_idOrCompanyFromKey($value->name);

                $app = self::getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, 'live', $LiveUsersLabelLive, $uid, '', $uid, 'live_' . $value->name);
                $app['live_servers_id'] = $live_servers_id;
                $app['key'] = $value->name;
                $app['isPrivate'] = self::isPrivate($app['key']);
                $app['isPasswordProtected'] = self::isPasswordProtected($app['key']);
                $app['method'] = 'Live::_getStats';
                //var_dump($app['isPrivate'],$app['key']);exit;
                if (!self::isApplicationListed($app['key'])) {
                    $obj->hidden_applications[] = $app;
                } else {
                    $obj->applications[] = $app;
                }

                if ($value->name === $obj->name) {
                    $obj->error = property_exists($value, 'publishing') ? false : true;
                    $obj->msg = (!$obj->error) ? "ONLINE" : "Waiting for Streamer";
                    $obj->stream = $value;
                    $obj->nclients = intval($value->nclients);
                    break;
                }


                $_REQUEST['playlists_id_live'] = $_playlists_id_live;
            }
        }

        $obj->countLiveStream = count($obj->applications);
        $obj->error = false;
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        //_error_log("Live::_getStats NON cached result {$_REQUEST['name']} " . json_encode($obj));
        ObjectYPT::setCache($cacheName, json_encode($obj));
        return $obj;
    }

    static function getTitleFromKey($key, $title = '') {
        if (empty($key)) {
            return $title;
        }
        $row = LiveTransmition::keyExists($key);
        if (empty($row)) {
            return $title;
        }
        if (empty($title)) {
            $title = $row['title'];
        }
        $Char = "&zwnj;";
        if (str_contains($title, $Char)) {
            return $title;
        }
        $title = "{$Char}{$title}";
        //var_dump($title);
        if (self::isPrivate($row['key'])) {
            $title = " <i class=\"fas fa-eye-slash\"></i> {$title}";
        }
        if (self::isPasswordProtected($row['key'])) {
            $title = " <i class=\"fas fa-lock\"></i> {$title}";
        }

        $u = new User($row['users_id']);
        if ($u->getStatus() !== 'a') {
            $title = " <i class=\"fas fa-user-alt-slash\"></i> {$title}";
        }

        $parameters = self::getLiveParametersFromKey($key);
        $playlists_id_live = $parameters['playlists_id_live'];
        $live_index = $parameters['live_index'];
        if (!empty($live_index) && $live_index !== 'false') {
            $title .= " ({$live_index})";
        }

        return $title;
    }

    public static function isApplicationListed($key, $listItIfIsAdminOrOwner = true) {
        global $_isApplicationListed;
        if (empty($key)) {
            return __LINE__;
        }
        if ($listItIfIsAdminOrOwner && User::isAdmin()) {
            return __LINE__;
        }
        if (!isset($_isApplicationListed)) {
            $_isApplicationListed = array();
        }
        if (!isset($_isApplicationListed[$key])) {
            $row = LiveTransmition::keyExists($key);
            if (empty($row)) {
                $_isApplicationListed[$key] = __LINE__;
            } else if (!empty($row['scheduled'])) {
                $_isApplicationListed[$key] = __LINE__;
            } else if (!empty($row['public'])) {
                $_isApplicationListed[$key] = __LINE__;
            } else if ($listItIfIsAdminOrOwner && User::getId() == $row['users_id']) {
                $_isApplicationListed[$key] = __LINE__;
            } else {
                $_isApplicationListed[$key] = false;
            }
        }
        return $_isApplicationListed[$key];
    }

    public static function isPrivate($key) {
        if (!empty($key)) {
            $lt = LiveTransmition::getFromKey($key);
            if (empty($lt['public'])) {
                return true;
            }
        }
        return false;
    }

    public static function byPass() {
        if (preg_match('/socket_notification/', $_SERVER['SCRIPT_FILENAME'])) {
            return true;
        }

        return false;
    }

    public static function getLiveParametersFromKey($key) {
        $key = preg_replace('/[^a-z0-9_-]/i', '', $key);
        //$obj = AVideoPlugin::getObjectData('Live');
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
        return ['key' => $key, 'cleanKey' => $cleanKey, 'live_index' => $live_index, 'playlists_id_live' => $playlists_id_live];
    }

    public static function getLiveIndexFromKey($key) {
        $parameters = self::getLiveParametersFromKey($key);
        return $parameters['live_index'];
    }

    public static function cleanUpKey($key) {
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

    public static function isAdaptiveTransmition($key) {
        // check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            $adaptive = ['hi', 'low', 'mid'];
            if (in_array($parts[1], $adaptive)) {
                return $parts[0];
                ;
            }
        }
        return false;
    }

    public static function isPlayListTransmition($key) {
        // check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    public static function isSubTransmition($key) {
        // check if is a subtransmition
        $parts = explode("-", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    public static function getImage($users_id, $live_servers_id, $playlists_id_live = 0, $live_index = '') {
        $p = AVideoPlugin::loadPlugin("Live");
        if (self::isLive($users_id, $live_servers_id, $live_index)) {
            $url = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $live_index);
            $url = addQueryStringParameter($url, "playlists_id_live", $playlists_id_live);
        } else {
            $url = self::getOfflineImage(false);
        }
        return $url;
    }

    public static function getLatestKeyFromUser($users_id) {
        if (empty($users_id)) {
            return false;
        }
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($latest)) {
            return false;
        }
        return $latest['key'];
    }

    public static function isLive($users_id, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_live_is_live;
        if (empty($users_id)) {
            return false;
        }
        if (!isset($_live_is_live)) {
            $_live_is_live = [];
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
        if (self::isLiveAndIsReadyFromKey($key, $live_servers_id, $live_index, $force_recreate)) {
            $_live_is_live[$name] = $key;
        } else {
            $_live_is_live[$name] = false;
        }
        return $_live_is_live[$name];
    }

    public static function isKeyLiveInStats($key, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_isLiveFromKey;
        if (empty($key) || $key == '-1') {
            _error_log('Live::isKeyLiveInStats key is empty');
            return false;
        }
        $index = "$key, $live_servers_id,$live_index";
        if (!isset($_isLiveFromKey)) {
            $_isLiveFromKey = [];
        }

        if (isset($_isLiveFromKey[$index])) {
            _error_log('Live::isKeyLiveInStats key is already set');
            return $_isLiveFromKey[$index];
        }

        $o = AVideoPlugin::getObjectData("Live");
        if (empty($o->server_type->value) || !empty($live_servers_id)) {
            return LiveTransmitionHistory::isLive($key);
        }


        //_error_log('getStats execute getStats: ' . __LINE__ . ' ' . __FILE__);
        //$json = getStatsNotifications($force_recreate);
        //_error_log('getStats execute getStats: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));

        $json = self::getStats($force_recreate);
        //_error_log('Live::isKeyLiveInStats:self::getStats ' . json_encode($json));
        $_isLiveFromKey[$index] = false;
        if (!empty($json)) {
            //_error_log("Live::isLiveFromKey {$key} JSON was not empty");
            if (!is_array($json)) {
                $json = [$json];
            }
            $namesFound = [];
            foreach ($json as $ki => $item) {
                //_error_log("Live::isLiveFromKey json [$ki] " . json_encode($item));
                $applications = [];
                if (empty($item->applications) && is_array($item)) {
                    $applications = $item;
                } elseif (is_object($item) && !empty($item->applications)) {
                    $applications = $item->applications;
                }

                foreach ($applications as $k => $value) {
                    $value = object_to_array($value);
                    //_error_log("Live::isLiveFromKey applications [$k] ". json_encode($value));
                    if (!is_array($value) || empty($value) || empty($value['key'])) {
                        continue;
                    }
                    $namesFound[] = "({$value['key']})";
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
                        $namesFound[] = "({$value['key']})";
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
            _error_log("Live::isLiveFromKey namesFound " . json_encode($namesFound));
        }
        if (empty($_isLiveFromKey[$index])) {
            _error_log("Live::isLiveFromKey is NOT online [{$key}]");
        } else {
            _error_log("Live::isLiveFromKey is online [{$key}]");
        }
        return $_isLiveFromKey[$index];
    }

    public static function isLiveAndIsReadyFromKey($key, $live_servers_id = 0, $live_index = '', $force_recreate = false) {
        global $_isLiveAndIsReadyFromKey;

        if (!isset($_isLiveAndIsReadyFromKey)) {
            $_isLiveAndIsReadyFromKey = [];
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
                $ls = @$_REQUEST['live_servers_id'];
                $_REQUEST['live_servers_id'] = $live_servers_id;
                $m3u8 = self::getM3U8File($key, false, true);
                $_REQUEST['live_servers_id'] = $ls;
                //_error_log('getStats execute isURL200: ' . __LINE__ . ' ' . __FILE__);
                $is200 = isValidM3U8Link($m3u8);
                if (empty($is200)) {
                    _error_log("isLiveAndIsReadyFromKey the m3u8 file is not present {$m3u8} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
                    $_isLiveAndIsReadyFromKey[$name] = false;
                }
            }
            $json->result = $_isLiveAndIsReadyFromKey[$name];
            ObjectYPT::setCache($name, json_encode($json));
        }

        return $_isLiveAndIsReadyFromKey[$name];
    }

    public static function getOnlineLivesFromUser($users_id) {
        $key = self::getLiveKey($users_id);
        return self::getOnlineLivesFromKey($key);
    }

    public static function getOnlineLivesFromKey($key) {
        $json = getStatsNotifications();
        $lives = [];
        if (!empty($json) && is_object($json) && !empty($json->applications)) {
            foreach ($json->applications as $value) {
                if (preg_match("/{$key}.*/", $value['key'])) {
                    $lives[] = $value;
                }
            }
        }
        return $lives;
    }

    public static function keyIsFromPlaylist($key) {
        $parts = explode("_", $key);
        if (empty($parts[1])) {
            return false;
        }
        return ['key' => $parts[0], 'playlists_id' => $parts[1]];
    }

    public static function getLiveKey($users_id) {
        $lt = new LiveTransmition(0);
        $lt->loadByUser($users_id);
        return $lt->getKey();
    }

    public static function getLiveKeyFromUser($users_id, $live_index = '', $playlists_id_live = '') {
        $key = self::getLiveKey($users_id);
        return self::getLiveKeyFromRequest($key, $live_index, $playlists_id_live);
    }

    public static function getLiveKeyFromRequest($key, $live_index = '', $playlists_id_live = '') {
        if (strpos($key, '-') === false) {
            if (!empty($live_index)) {
                $key .= '-' . preg_replace('/[^0-9a-z]/i', '', $live_index);
            } elseif (!empty($_REQUEST['live_index'])) {
                $key .= '-' . preg_replace('/[^0-9a-z]/i', '', $_REQUEST['live_index']);
            }
        }
        if (strpos($key, '_') === false) {
            if (!empty($playlists_id_live)) {
                $key .= '_' . preg_replace('/[^0-9]/', '', $playlists_id_live);
            } elseif (!empty($_REQUEST['playlists_id_live'])) {
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

    public static function getPosterImage($users_id, $live_servers_id, $live_schedule_id = 0, $posterType = 0) {
        global $global;
        if (empty($users_id)) {
            $isLive = isLive();
            if (!empty($isLive)) {
                $lt = self::getLiveTransmitionObjectFromKey($isLive['key']);
                if (empty($lt)) {
                    return false;
                }
                $users_id = $lt->getUsers_id();
                if (empty($live_servers_id)) {
                    $live_servers_id = self::getLiveServersIdRequest();
                }
                if (empty($live_schedule_id)) {
                    $live_schedule_id = self::getLiveScheduleIdRequest();
                }
            }
        }

        if (empty($users_id)) {
            return false;
        }
        $file = self::_getPosterImage($users_id, $live_servers_id, $live_schedule_id, $posterType);
        //var_dump($file);
        if (!file_exists($global['systemRootPath'] . $file)) {
            if (!empty($live_schedule_id)) {
                if (Live_schedule::isLive($live_schedule_id)) {
                    $file = self::getOnAirImage(false);
                } else {
                    $file = self::getComingSoonImage(false);
                }
            } else {
                if (LiveTransmitionHistory::isLive($live_schedule_id)) {
                    $file = self::getOnAirImage(false);
                } else {
                    $file = self::getOfflineImage(false);
                }
            }
        }
        //var_dump($file);exit;
        return $file;
    }

    public static function getPrerollPosterImage($users_id = 0, $live_servers_id = 0, $live_schedule_id = 0) {
        return self::getPosterImage($users_id, $live_servers_id, $live_schedule_id, self::$posterType_preroll);
    }

    public static function getPostrollPosterImage($users_id = 0, $live_servers_id = 0, $live_schedule_id = 0) {
        return self::getPosterImage($users_id, $live_servers_id, $live_schedule_id, self::$posterType_postroll);
    }

    public static function posterExists($users_id = 0, $live_servers_id = 0, $live_schedule_id = 0, $posterType = 0) {
        global $global;

        if (empty($users_id)) {
            $isLive = isLive();
            if (!empty($isLive)) {
                $lt = self::getLiveTransmitionObjectFromKey($isLive['key']);
                if (empty($lt)) {
                    return false;
                }
                $users_id = $lt->getUsers_id();
                if (empty($live_servers_id)) {
                    $live_servers_id = self::getLiveServersIdRequest();
                }
                if (empty($live_schedule_id)) {
                    $live_schedule_id = self::getLiveScheduleIdRequest();
                }
            }
        }

        if (empty($users_id)) {
            return false;
        }

        $file = self::_getPosterImage($users_id, $live_servers_id, $live_schedule_id, $posterType);
        return file_exists("{$global['systemRootPath']}{$file}");
    }

    public static function prerollPosterExists($users_id = 0, $live_servers_id = 0, $live_schedule_id = 0) {
        return self::posterExists($users_id, $live_servers_id, $live_schedule_id, self::$posterType_preroll);
    }

    public static function postrollPosterExists($users_id = 0, $live_servers_id = 0, $live_schedule_id = 0) {
        return self::posterExists($users_id, $live_servers_id, $live_schedule_id, self::$posterType_postroll);
    }

    public static function getPosterImageOrFalse($users_id, $live_servers_id) {
        $poster = self::getPosterImage($users_id, $live_servers_id);
        if (preg_match('/OnAir.jpg$/', $poster)) {
            return false;
        }

        return $poster;
    }

    public function getLivePosterImage($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '', $format = 'jpg', $live_schedule_id = 0) {
        global $global;

        return self::getLivePosterImageRelativePath($users_id, $live_servers_id, $playlists_id_live, $live_index, $format, $live_schedule_id, true);
    }

    public static function getLivePosterImageRelativePath($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '', $format = 'jpg', $live_schedule_id = 0, $returnURL = false) {
        global $global;
        if (empty($live_servers_id)) {
            $live_servers_id = self::getCurrentLiveServersId();
        }
        $live_schedule_id = intval($live_schedule_id);
        if (self::isLiveThumbsDisabled()) {
            if ($format !== 'jpg') {
                return false;
            }
            $file = self::_getPosterImage($users_id, $live_servers_id, $live_schedule_id);

            if (!file_exists($global['systemRootPath'] . $file)) {
                $file = self::getOnAirImage(false);
            }
            if ($returnURL) {
                $file = getURL($file);
            }
        } else {
            $u = new User($users_id);
            $username = $u->getUser();
            $file = "plugin/Live/getImage.php?live_servers_id={$live_servers_id}&playlists_id_live={$playlists_id_live}&live_index={$live_index}&u={$username}&format={$format}";
            if (!empty($live_schedule_id)) {
                $file .= "&live_schedule={$live_schedule_id}";
            }
            if ($returnURL) {
                $file = $global['webSiteRootURL'] . $file;
            }
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

    public static function getPosterThumbsImage($users_id, $live_servers_id, $cominsoon = false) {
        global $global;
        if (empty($_REQUEST['live_schedule'])) {
            $file = self::_getPosterThumbsImage($users_id, $live_servers_id);
        } else {
            $array = Live_schedule::getPosterPaths($_REQUEST['live_schedule']);
            $file = $array['relative_path'];
        }

        if (empty($file) || !file_exists($global['systemRootPath'] . $file)) {
            $file = self::_getPosterThumbsImage($users_id, $live_servers_id);
            if (empty($file) || !file_exists($global['systemRootPath'] . $file)) {
                if ($cominsoon) {
                    $file = self::getComingSoonImage(false);
                } else {
                    $file = self::getOnAirImage(false);
                }
            }
        }
        return $file;
    }

    public static function getPoster($users_id, $live_servers_id, $key = '') {
        global $_getPoster;
        if (!isset($_getPoster)) {
            $_getPoster = array();
        }
        $index = "$users_id, $live_servers_id, $key";
        if (isset($_getPoster[$index])) {
            return $_getPoster[$index];
        }
        //_error_log("getPoster($users_id, $live_servers_id, $key)");
        $lh = LiveTransmitionHistory::getActiveLiveFromUser($users_id, $live_servers_id, $key);
        $live_index = self::getLiveIndexFromKey($lh['key']);
        $poster = self::getPosterImageOrFalse($users_id, $live_servers_id, $live_index);
        if (empty($poster)) {
            $poster = self::getOfflineImage(false);
        }
        if (empty($lh)) {
            _error_log("getPoster empty activity");
            $_getPoster[$index] = $poster;
            return $_getPoster[$index];
        }
        $parameters = self::getLiveParametersFromKey($lh['key']);
        $live_index = $parameters['live_index'];
        $playlists_id_live = $parameters['playlists_id_live'];
        if (self::isLiveAndIsReadyFromKey($lh['key'], $lh['live_servers_id'])) {
            $_getPoster[$index] = self::getLivePosterImageRelativePath($users_id, $live_servers_id, $playlists_id_live, $live_index);
            //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' is live and ready');
            return $_getPoster[$index];
        } else {
            if (self::isKeyLiveInStats($lh['key'], $lh['live_servers_id'])) {
                //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is in the stats');
                $_getPoster[$index] = self::getPosterImage($users_id, $live_servers_id, $live_index);
            } else {
                //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is NOT in the stats');
                $_getPoster[$index] = $poster;
            }
            return $_getPoster[$index];
        }
    }

    public static function getPosterFromKey($key, $live_servers_id, $live_index = '') {
        $key = self::getLatestKeyFromUser($users_id);
    }

    public static function getOfflineImage($includeURL = true) {
        global $global;
        $img = "plugin/Live/view/Offline.jpg";
        if ($includeURL) {
            $img = getCDN() . $img;
        }
        return $img;
    }

    public static function getOnAirImage($includeURL = true) {
        global $global;
        $img = "plugin/Live/view/OnAir.jpg";
        if ($includeURL) {
            $img = getCDN() . $img;
        }
        return $img;
    }

    public static function getComingSoonImage($includeURL = true) {
        global $global;
        $img = "plugin/Live/view/ComingSoon.jpg";
        if ($includeURL) {
            $img = getCDN() . $img;
        }
        return $img;
    }

    public static function _getPosterImage($users_id, $live_servers_id, $live_schedule_id = 0, $posterType = 0) {

        $users_id = intval($users_id);
        $live_servers_id = intval($live_servers_id);
        $live_schedule_id = intval($live_schedule_id);
        $posterType = intval($posterType);

        if (!empty($live_schedule_id)) {
            $paths = Live_schedule::getPosterPaths($live_schedule_id, $posterType);
            return $paths['relative_path'];
        }
        $type = '';
        if (!empty($posterType)) {
            $type = "_{$posterType}_";
        }
        $file = "videos/userPhoto/Live/user_{$users_id}_bg_{$live_servers_id}{$type}.jpg";
        return $file;
    }

    public static function _getPosterThumbsImage($users_id, $live_servers_id, $posterType = 0) {
        $posterType = intval($posterType);
        $type = '';
        if (!empty($posterType)) {
            $type = "_{$posterType}_";
        }
        $file = "videos/userPhoto/Live/user_{$users_id}_thumbs_{$live_servers_id}{$type}.jpg";
        return $file;
    }

    public static function on_publish($liveTransmitionHistory_id) {
        $obj = AVideoPlugin::getDataObject("Live");
        if (empty($obj->disableRestream)) {
            self::restream($liveTransmitionHistory_id);
        }
        $lt = new LiveTransmitionHistory($liveTransmitionHistory_id);

        AVideoPlugin::onLiveStream($lt->getUsers_id(), $lt->getLive_servers_id());
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
        _error_log("deleteStatsCache: {$cacheDir} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        rrmdir($cacheDir);
        if ($clearFirstPage) {
            clearCache(true);
        }
        // temporary solution to when you go online
        ObjectYPT::deleteALLCache();
        //isURL200Clear();
        unset($__getAVideoCache);
        unset($getStatsLive);
        unset($getStatsObject);
        unset($_getStats);
        unset($_getStatsNotifications);
        unset($_isLiveFromKey);
        unset($_isLiveAndIsReadyFromKey);
    }

    public static function getReverseRestreamObject($m3u8, $users_id, $live_servers_id = -1, $forceIndex = false) {
        if (!isValidURL($m3u8)) {
            return false;
        }
        $obj = new stdClass();
        $obj->m3u8 = $m3u8;
        $obj->restreamerURL = self::getRestreamer($live_servers_id);
        $obj->restreamsDestinations = [Live::getRTMPLink($users_id, $forceIndex)];
        $obj->token = getToken(60);
        $obj->users_id = $users_id;
        return $obj;
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
        $obj->restreamsDestinations = [];
        $obj->token = getToken(60);
        $obj->users_id = $lth->getUsers_id();
        $obj->liveTransmitionHistory_id = $liveTransmitionHistory_id;
        $obj->key = $lth->getKey();

        $rows = Live_restreams::getAllFromUser($lth->getUsers_id());
        foreach ($rows as $value) {
            $value['stream_url'] = rtrim($value['stream_url'], "/") . '/';
            $obj->restreamsDestinations[] = "{$value['stream_url']}{$value['stream_key']}";
        }
        return $obj;
    }

    public static function reverseRestream($m3u8, $users_id, $live_servers_id = -1, $forceIndex = false) {
        _error_log("Live:reverseRestream start");
        $obj = self::getReverseRestreamObject($m3u8, $users_id, $live_servers_id, $forceIndex);
        _error_log("Live:reverseRestream obj " . _json_encode($obj));
        return self::sendRestream($obj);
    }

    public static function restream($liveTransmitionHistory_id) {
        outputAndContinueInBackground();
        $obj = self::getRestreamObject($liveTransmitionHistory_id);
        return self::sendRestream($obj);
    }

    private static function sendRestream($obj) {
        _error_log("Live:sendRestream start");
        try {
            if (empty($obj)) {
                _error_log("Live:sendRestream object is empty");
                return false;
            }
            $data_string = json_encode($obj);
            _error_log("Live:sendRestream ({$obj->restreamerURL}) {$data_string} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $obj->restreamerURL);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_POSTREDIR, 3);
            curl_setopt($ch, CURLOPT_POST, 1);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string),]
            );
            $output = curl_exec($ch);
            if (empty($output)) {
                _error_log('Live:sendRestream ERROR ' . curl_error($ch));
                curl_close($ch);
                return false;
            }
            curl_close($ch);
            $json = _json_decode($output);
            if (empty($output)) {
                _error_log('Live:sendRestream JSON ERROR ' . $output);
                return false;
            }
            _error_log('Live:sendRestream complete ' . $output);
            return $json;
        } catch (Exception $exc) {
            _error_log("Live:sendRestream " . $exc->getTraceAsString());
            return false;
        }
        return false;
    }

    public static function canStreamWithWebRTC() {
        if (!User::canStream()) {
            return false;
        }

        $obj = AVideoPlugin::getObjectDataIfEnabled("Live");
        if (!empty($obj->webRTC_isDisabled)) {
            return false;
        }

        return true;
    }

    public static function canScheduleLive() {
        if (!User::canStream()) {
            return false;
        }

        $obj = AVideoPlugin::getObjectDataIfEnabled("Live");
        if (!empty($obj->disable_live_schedule)) {
            return false;
        }

        return true;
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
        if ($obj->doNotShowGoLiveButtonOnUploadMenu) {
            return '';
        }
        if (!empty(!User::canStream())) {
            return '';
        }
        $buttonTitle = $this->getButtonTitle();
        include $global['systemRootPath'] . 'plugin/Live/getUploadMenuButton.php';
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
                    _error_log("getAllVideos without limit " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
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
        $videos = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);

                $row['live_servers_id'] = self::getLastServersIdFromUser($row['users_id']);

                if (empty($otherInfo)) {
                    $otherInfo = [];
                    $otherInfo['category'] = xss_esc_back($row['category']);
                    $otherInfo['groups'] = UserGroups::getVideosAndCategoriesUserGroups($row['id']);
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

    public static function finishLive($key) {
        $lh = LiveTransmitionHistory::finish($key);
    }

    public static function updateVideosUserGroup($videos_id, $key) {
        $lt = LiveTransmition::keyExists($key);
        if (!empty($lt)) {
            $lt = new LiveTransmition($lt['id']);
            $groups = $lt->getGroups();
            if (!empty($groups)) {
                UserGroups::updateVideoGroups($videos_id, $groups);
            }
        }
    }

    public static function notifySocketStats($callBack = 'socketLiveONCallback', $array = []) {
        clearAllUsersSessionCache();
        if (empty($array['stats'])) {
            $array['stats'] = getStatsNotifications();
        }
        _error_log("NGINX Live::on_publish_socket_notification sendSocketMessageToAll Start");
        $socketObj = sendSocketMessageToAll($array, $callBack);
        _error_log("NGINX Live::on_publish_socket_notification SocketMessageToAll END");
        return $socketObj;
    }

    public static function getImageType($content) {
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
        if ($contentLen >= 2095335 && $contentLen <= 2095350) {
            return LiveImageType::$DEFAULTGIF;
        }
        if ($contentLen >= 70805 && $contentLen <= 70810) {
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

    public static function isLiveImage($content) {
        return self::getImageType($content) === LiveImageType::$LIVE;
    }

    public static function isDefaultImage($content) {
        $type = self::getImageType($content);
        return $type === LiveImageType::$ONAIRENCODER || $type === LiveImageType::$ONAIR || $type === LiveImageType::$OFFLINE || $type === LiveImageType::$DEFAULTGIF;
    }

    public static function iskeyOnline($key) {
        $stats = getStatsNotifications();
        foreach ($stats["applications"] as $value) {
            if (empty($value['key'])) {
                continue;
            }
            if (preg_match('/' . $key . '/', $value['key'])) {
                return true;
            }
        }
        return false;
    }

    public static function getValidNotOnlineLiveIndex($key, $live_index) {
        if (empty($live_index)) {
            return 1;
        }
        if (!Live::iskeyOnline("{$key}-{$live_index}")) {
            return $live_index;
        } else {
            if (is_numeric($live_index)) {
                return self::getValidNotOnlineLiveIndex($key, ++$live_index);
            } else {
                return self::getValidNotOnlineLiveIndex($key, $live_index . 'New');
            }
        }
    }

    public static function getLatestValidNotOnlineLiveIndex($key) {
        $live_index = LiveTransmitionHistory::getLatestIndexFromKey($key);
        $live_index = self::getValidNotOnlineLiveIndex($key, $live_index);
        return $live_index;
    }

    public static function getLivesOnlineFromKey($key) {
        global $_getLivesOnlineFromKey;
        if (!isset($_getLivesOnlineFromKey)) {
            $_getLivesOnlineFromKey = [];
        }
        if (!isset($_getLivesOnlineFromKey[$key])) {
            $stats = getStatsNotifications();
            $_getLivesOnlineFromKey[$key] = [];
            foreach ($stats["applications"] as $value) {
                if (empty($value['key'])) {
                    continue;
                }
                if (preg_match('/' . $key . '/', $value['key'])) {
                    $_getLivesOnlineFromKey[$key][] = $value;
                }
            }
        }
        return $_getLivesOnlineFromKey[$key];
    }

    public static function getFirstLiveOnlineFromKey($key) {
        $onliveApplications = self::getLivesOnlineFromKey($key);
        if (!empty($onliveApplications[0])) {
            return $onliveApplications[0];
        }
        return false;
    }

    public static function getUserHash($users_id) {
        return encryptString(_json_encode(['users_id' => $users_id, 'time' => time()]));
    }

    public static function decryptHash($hash) {
        $string = decryptString($hash);
        $json = _json_decode($string);
        return object_to_array($json);
    }

    public static function getServerURL($key, $users_id, $short = true) {
        global $global;
        if (empty($short)) {
            $obj = new stdClass();
            $obj->users_id = $users_id;
            $obj->key = $key;
            $encrypt = encryptString($obj);

            $url = Live::getServer();
            $url = addQueryStringParameter($url, 'e', base64_encode($encrypt));
        } else {
            $str = "{$key}";
            $encrypt = encryptString($str);

            $url = Live::getServer();
            $url = addQueryStringParameter($url, 's', $encrypt);
        }
        $url = addQueryStringParameter($url, 'webSiteRootURL', base64_encode($global['webSiteRootURL']));
        $o = AVideoPlugin::getObjectDataIfEnabled("Live");
        if (empty($o->server_type->value)) {
            $url = addQueryStringParameter($url, 'webSiteRootURL', base64_encode($global['webSiteRootURL']));
        }
        $url = str_replace('%3D', '', $url);
        return $url;
    }

    public static function passwordIsGood($key) {
        $row = LiveTransmition::getFromKey($key, true);
        if (empty($row) || empty($row['id']) || empty($row['users_id'])) {
            return false;
        }

        $password = @$row['live_password'];

        if (!empty($row['scheduled_password'])) {
            $password = $row['scheduled_password'];
        }

        //var_dump($key,$password, $_REQUEST, $_SESSION['live_password'], $row);exit;
        if (empty($password)) {
            return true;
        }
        if (empty($_SESSION['live_password'][$key]) || $password !== $_SESSION['live_password'][$key]) {
            if (!empty($_POST['live_password']) && $_REQUEST['live_password'] == $password) {
                _session_start();
                $_SESSION['live_password'][$key] = $_REQUEST['live_password'];
                return true;
            }
            return false;
        }
        return true;
    }

    public static function checkIfPasswordIsGood($key) {
        global $global;
        if (!self::passwordIsGood($key)) {
            $_REQUEST['key'] = $key;
            include $global['systemRootPath'] . 'plugin/Live/confirmLivePassword.php';
            exit;
        }
        return true;
    }

}

class LiveImageType {

    public static $UNKNOWN = 'unknown';
    public static $OFFLINE = 'offline';
    public static $ONAIR = 'onair';
    public static $ONAIRENCODER = 'onair_encoder';
    public static $DEFAULTGIF = 'defaultgif';
    public static $LIVE = 'live';

}

class LiveStreamObject {

    private $key;
    private $live_servers_id;
    private $live_index;
    private $playlists_id_live;

    public function __construct($key, $live_servers_id = 0, $live_index = 0, $playlists_id_live = 0) {
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
            } elseif (!empty($_REQUEST['live_index'])) {
                $this->live_index = $_REQUEST['live_index'];
            }
        }
        if (empty($this->playlists_id_live)) {
            // check if the index is on the key already
            if (!empty($parts['playlists_id_live'])) {
                $this->playlists_id_live = $parts['playlists_id_live'];
            } elseif (!empty($_REQUEST['playlists_id_live'])) {
                $this->playlists_id_live = $_REQUEST['playlists_id_live'];
            }
        }
        $this->key = $parts['cleanKey'];
        $this->live_index = preg_replace('/[^0-9a-z]/i', '', $this->live_index);
    }

    public function getKey() {
        return $this->key;
    }

    public function getKeyWithIndex($forceIndexIfEnabled = false, $allowOnlineIndex = false) {
        if (!empty($forceIndexIfEnabled)) {
            if (is_string($forceIndexIfEnabled) || is_int($forceIndexIfEnabled)) {
                $this->live_index = $forceIndexIfEnabled;
            } else {
                $objLive = AVideoPlugin::getDataObject("Live");
                if (!empty($objLive->allowMultipleLivesPerUser)) {
                    if (empty($allowOnlineIndex)) {
                        $this->live_index = Live::getLatestValidNotOnlineLiveIndex($this->key);
                    } else {
                        $this->live_index = LiveTransmitionHistory::getLatestIndexFromKey($this->key);
                    }
                }
            }
        }
        return Live::getLiveKeyFromRequest($this->key, $this->live_index, $this->playlists_id_live);
    }

    public function getLive_servers_id() {
        return $this->live_servers_id;
    }

    public function getLive_index() {
        return $this->live_index;
    }

    public function getPlaylists_id_live() {
        return $this->playlists_id_live;
    }

    public function getURL() {
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

    public function getURLEmbed() {
        $url = $this->getURL();
        return addQueryStringParameter($url, 'embed', 1);
    }

    public function getM3U8($doNotProtect = false, $allowOnlineIndex = false, $ignoreCDN = false) {
        global $global;
        $o = AVideoPlugin::getObjectData("Live");

        $uuid = $this->getKeyWithIndex($allowOnlineIndex, $allowOnlineIndex);
        //_error_log("Live:getM3U8($doNotProtect , $allowOnlineIndex e, $ignoreCDN) $uuid ($allowOnlineIndex");
        if (empty($o->server_type->value)) {
            $row = LiveTransmitionHistory::getLatest($this->key, $this->live_servers_id);
            if (!empty($row['domain'])) {
                return "{$row['domain']}live/{$uuid}.m3u8";
            }
        }

        $playerServer = Live::getPlayerServer($ignoreCDN);
        if (!empty($this->live_servers_id)) {
            $liveServer = new Live_servers($this->live_servers_id);
            if ($liveServer->getStats_url()) {
                $o->protectLive = $liveServer->getProtectLive();
                $o->useAadaptiveMode = $liveServer->getUseAadaptiveMode();
            }
        }

        $playerServer = addLastSlash($playerServer);
        if ($o->protectLive && empty($doNotProtect)) {
            return "{$global['webSiteRootURL']}plugin/Live/m3u8.php?live_servers_id={$this->live_servers_id}&uuid=" . encryptString($uuid);
        } elseif ($o->useAadaptiveMode) {
            return $playerServer . "{$uuid}.m3u8";
        } else {
            return $playerServer . "{$uuid}/index.m3u8";
        }
    }

    public function getOnlineM3U8($users_id, $doNotProtect = false) {
        $li = $this->live_index;
        if (empty($this->live_index)) {
            $online = Live::getFirstLiveOnlineFromKey($this->key);
            if (!empty($online)) {
                $parameters = Live::getLiveParametersFromKey($online['key']);
                //var_dump($parameters, $this->live_index, $li, $online);exit;
            } else {
                $key = Live::getLatestKeyFromUser($users_id);
                $parameters = Live::getLiveParametersFromKey($key);
            }
            $this->live_index = $parameters['live_index'];
        }
        $m3u8 = $this->getM3U8($doNotProtect, true);
        $this->live_index = $li;
        return $m3u8;
    }

    public function getRTMPLink($forceIndex = false) {
        $key = $this->getKeyWithIndex(true);
        if (!empty($forceIndex)) {
            // make sure the key is unique
            $parts = explode('-', $key);
            $key = $parts[0] . "-{$forceIndex}";
        }
        $url = addLastSlash($this->getRTMPLinkWithOutKey()) . $key;
        _error_log("getRTMPLink: {$url}");
        return $url;
    }

    public function getRTMPLinkWithOutKey($short = true) {
        $lt = LiveTransmition::getFromKey($this->key);
        return Live::getServerURL($this->key, $lt['users_id'], $short);
    }

}
