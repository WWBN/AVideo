<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistoryLog.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';

$getStatsObject = [];
$_getStats = [];

User::loginFromRequestIfNotLogged();

class Live extends PluginAbstract
{

    public static $public_server_http = 'http';
    public static $public_server_port = 8080;
    public static $public_server_domain = 'live.ypt.me';
    public static $posterType_regular = 0;
    public static $posterType_preroll = 1;
    public static $posterType_postroll = 2;

    const PERMISSION_CAN_RESTREAM = 0;
    const CAN_RESTREAM_All_USERS = 0;
    const CAN_RESTREAM_ONLY_SELECTED_USERGROUPS = 1;

    public function getTags()
    {
        return [
            PluginTags::$LIVE,
            PluginTags::$FREE,
            PluginTags::$RECOMMENDED,
            PluginTags::$PLAYER,
        ];
    }

    public function getDescription()
    {
        global $global;
        $desc = "Broadcast a RTMP video from your computer<br> and receive HLS streaming from servers";
        $lu = AVideoPlugin::loadPlugin("LiveUsers");
        if (!empty($lu)) {
            if (version_compare($lu->getPluginVersion(), "2.0") < 0) {
                $desc .= "<div class='alert alert-danger'>You MUST update your LiveUsers plugin to version 2.0 or greater</div>";
            }
        }
        return $desc;
    }

    public function getName()
    {
        return "Live";
    }

    public function getHTMLMenuRight()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
    }

    public function getUUID()
    {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }

    public function getPluginVersion()
    {
        return "14.0";
    }

    public function getLivePanel()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Live/view/panel.php';
        include $filename;
    }

    public function getLiveApplicationArray()
    {
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
            //$link = addQueryStringParameter($link, 'live_schedule', intval($value['id']));
            $link = $link . '/ls/' . intval($value['id']) . '/';
            $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($value['key'], $value['live_servers_id']) : '');

            $title = self::getTitleFromKey($value['key'], $value['title']);

            $users_id = Live_schedule::getUsers_idOrCompany($value['id']);

            $_array = array(
                'users_id' => $users_id,
                'title' => $title,
                'link' => $link,
                'imgJPG' => Live_schedule::getPosterURL($value['id'], 0),
                'imgGIF' => '',
                'type' => 'scheduleLive',
                'LiveUsersLabelLive' => $LiveUsersLabelLive,
                'uid' => 'LiveSchedule_' . $value['id'],
                'callback' => $callback,
                'startsOnDate' => "{$value['scheduled_time']} {$value['timezone']}",
                'class' => 'live_' . $value['key'],
                'description' => $value['description']
            );

            $app = self::getLiveApplicationModelArray($_array);
            $app['live_servers_id'] = $value['live_servers_id'];
            $app['key'] = $value['key'];
            $app['isPrivate'] = self::isPrivate($value['key']);
            $app['method'] = 'Live::getLiveApplicationArray::Live_schedule';
            $app['Live_schedule_timezone'] = date_default_timezone_get();
            $app['scheduled_time_timezone'] = $value['timezone'];
            $app['scheduled_time'] = $value['scheduled_time'];
            $app['live_schedule_id'] = $value['id'];
            //var_dump($app);exit;
            $array[] = $app;
        }
        //var_dump(count($array), $rows);exit;

        $rows = LiveTransmitionHistory::getActiveLives();
        $currentLives = array();

        $isLiveAndIsReadyFromKey = false;
        $isStatsAccessible = false;

        foreach ($rows as $value) {
            unset($_REQUEST['playlists_id_live']);
            // if key is from schedule, skipp it
            if (!empty($value['key']) && strtotime($value['modified']) > strtotime('-5 minures')) {
                $isLiveAndIsReadyFromKey = Live::isLiveAndIsReadyFromKey($value['key'], $value['live_servers_id']);
                $isStatsAccessible = self::isStatsAccessible($value['live_servers_id']);
                if (empty($isLiveAndIsReadyFromKey) && $isStatsAccessible) {
                    //_error_log("Live::getLiveApplicationArray LiveTransmitionHistory::finishFromTransmitionHistoryId({$value['id']}) isLiveAndIsReadyFromKey({$value['key']}, {$value['live_servers_id']})");
                    LiveTransmitionHistory::finishFromTransmitionHistoryId($value['id']);
                    continue;
                } else if (!empty($isLiveAndIsReadyFromKey)) {
                    LiveTransmitionHistory::updateModifiedTime($value['id']);
                }
            }

            if ($obj->useLiveServers && empty($value['live_servers_id'])) {
                continue;
            }

            if (!LiveTransmition::keyExists($value['key'], false) && Live_schedule::keyExists($value['key'])) {
                //if (Live_schedule::keyExists($value['key'])) {
                continue;
            }

            $link = LiveTransmitionHistory::getLinkToLive($value['id']);

            if (empty($link)) {
                $link = Live::getLinkToLiveFromUsers_idAndLiveServer($value['users_id'], $value['live_servers_id']);
            }

            if (in_array($link, $currentLives)) {
                //_error_log("Live::getLiveApplicationArray LiveTransmitionHistory::finishFromTransmitionHistoryId({$value['id']}) {$value['users_id']}, {$value['live_servers_id']} [{$link}]");
                //LiveTransmitionHistory::finishFromTransmitionHistoryId($value['id']);
                continue;
            }
            $currentLives[] = $link;
            $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($value['key'], $value['live_servers_id']) : '');

            $title = self::getTitleFromKey($value['key'], $value['title']);

            $users_id = LiveTransmitionHistory::getUsers_idOrCompany($value['id']);
            $value['live_servers_id'] = intval($value['live_servers_id']);
            $_array = array(
                'users_id' => $users_id,
                'title' => $title,
                'link' => $link,
                'imgJPG' => self::getPoster($value['users_id'], $value['live_servers_id'], ''),
                'imgGIF' => '',
                'type' => 'live',
                'LiveUsersLabelLive' => $LiveUsersLabelLive,
                'uid' => "live_{$value['live_servers_id']}_{$value['key']}",
                'callback' => '',
                'startsOnDate' => '',
                'class' => "live_{$value['key']}",
                'description' => $value['description']
            );

            $app = self::getLiveApplicationModelArray($_array);
            $app['live_servers_id'] = intval($value['live_servers_id']);
            $app['key'] = $value['key'];
            $app['live_transmitions_history_id'] = $value['id'];
            $app['isPrivate'] = LiveTransmitionHistory::isPrivate($value['id']);
            $app['isPasswordProtected'] = LiveTransmitionHistory::isPasswordProtected($value['id']);
            $app['isRebroadcast'] = LiveTransmitionHistory::isRebroadcast($value['id']);
            $app['method'] = 'Live::getLiveApplicationArray::LiveTransmitionHistory';
            $app['isLiveAndIsReadyFromKey'] = $isLiveAndIsReadyFromKey;
            $app['isStatsAccessible'] = $isStatsAccessible;
            $app['modified'] = $value['modified'];
            $app['now'] = date('Y-m-d H:i:s');

            $array[] = $app;
        }

        $_REQUEST['playlists_id_live'] = $_playlists_id_live;
        return $array;
    }

    public static function getLiveApplicationModelArray($array)
    {
        global $global, $_getLiveApplicationModelArray_counter, $_getLiveApplicationModelArray;

        if (!isset($_getLiveApplicationModelArray)) {
            $_getLiveApplicationModelArray = [];
        }

        if (empty($_getLiveApplicationModelArray_counter)) {
            $_getLiveApplicationModelArray_counter = 0;
        }

        $users_id = '';
        $title = '';
        $link = '';
        $imgJPG = '';
        $imgGIF = '';
        $type = '';
        $LiveUsersLabelLive = '';
        $uid = '';
        $callback = '';
        $startsOnDate = '';
        $class = '';
        $description = '';

        $expectedValues = array(
            'users_id',
            'title',
            'link',
            'imgJPG',
            'imgGIF',
            'type',
            'LiveUsersLabelLive',
            'uid',
            'callback',
            'startsOnDate',
            'class',
            'description'
        );

        $argsArray = array();

        $arg_list = func_get_args();
        if (count($arg_list) > 1) {
            foreach ($arg_list as $key => $value) {
                $argsArray[$expectedValues[$key]] = $value;
            }
        } else {
            $argsArray = $array;
        }

        foreach ($expectedValues as $value) {
            if (isset($argsArray[$value])) {
                eval('$' . $value . ' = $argsArray[$value];');
            } else {
                eval('$' . $value . ' = false;');
            }
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

            preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})(.*)/', $startsOnDate, $matches);

            if (empty($matches) || empty($matches[2])) {
                $datetime = "'$startsOnDate'";
            } else {
                $datetime = "convertDateFromTimezoneToLocal('{$matches[1]}', '{$matches[2]}')";
            }

            $startsOnDateTime = strtotime($startsOnDate);
            if ($startsOnDateTime > time()) {
                $callback .= ';' . '$(\'.' . $uid . ' .liveNow\').attr(\'class\', \'liveNow label label-primary\');'
                    . '$(\'.' . $uid . ' .liveNow\').text(' . $datetime . ');'
                    . 'startTimerToDate(' . $datetime . ', \'.' . $uid . ' .liveNow\', false);';
                $comingsoon = $startsOnDateTime;
            }
        }
        if (empty($imgJPG)) {
            $imgJPG = getURL(Live::getPosterThumbsImage($users_id, 0, $comingsoon));
        }
        $replace = [
            $uid,
            $UserPhoto,
            $title,
            $u->getNameIdentificationBd(),
            $link,
            (!empty($imgJPG) ? '<img src="' . ImagesPlaceHolders::getVideoAnimationLandscape(ImagesPlaceHolders::$RETURN_URL) . '" data-src="' . $imgJPG . '" class="thumbsJPG img-responsive" height="130">' : ''),
            (!empty($imgGIF) ? ('<img src="' . ImagesPlaceHolders::getVideoAnimationLandscape(ImagesPlaceHolders::$RETURN_URL) . '" data-src="' . $imgGIF . '" style="position: absolute; top: 0px; height: 0px; width: 0px; display: none;" class="thumbsGIF img-responsive" height="130">') : ''),
            $LiveUsersLabelLive,
            $class,
        ];

        $newContent = str_replace($search, $replace, $global['getLiveApplicationModelArray']['content']);
        $newContentExtra = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentExtra']);
        $newContentExtraVideoPage = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentExtraVideoPage']);
        $newContentVideoListItem = str_replace($search, $replace, $global['getLiveApplicationModelArray']['contentListem']);

        $hasPPVLive = false;
        if (AVideoPlugin::isEnabledByName('PayPerViewLive')) {
            $plans = PayPerViewLive::getAllPlansFromUser($users_id);
            $hasPPVLive = !empty($plans);
        }
        $array = [
            "hasPPVLive" => $hasPPVLive,
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
            'description' => $description,
            'timezone' => date_default_timezone_get(),
            "html" => $newContent,
            "htmlExtra" => $newContentExtra,
            "htmlExtraVideoPage" => $newContentExtraVideoPage,
            "htmlExtraVideoListItem" => $newContentVideoListItem,
        ];

        $_getLiveApplicationModelArray[$uid] = $array;
        return $array;
    }

    public static function getDataObjectAdvanced()
    {
        return array(
            'topCopyKeysButtonTitle',
            'hideTopCopyKeysButton',
            'button_title',
            'restreamerURL',
            'controlURL',
            'controlServer',
            'disableRestream',
            'disableDVR',
            'disableGifThumbs',
            'disableLiveThumbs',
            'hideTopButton',
            'hideUserGroups',
            'hideShare',
            'hideAdvancedStreamKeys',
            'hidePublicListedOption',
            'useAadaptiveMode',
            'protectLive',
            'doNotShowLiveOnVideosList',
            'doNotShowOnlineOfflineLabel',
            'doNotShowLiveOnCategoryList',
            'doNotShowOfflineLiveOnCategoryList',
            'limitLiveOnVideosList',
            'doNotShowGoLiveButton',
            'doNotShowGoLiveButtonOnUploadMenu',
            'useLiveServers',
            'streamDeniedMsg',
            'allowMultipleLivesPerUser',
            'controllButtonsShowOnlyToAdmin_record_start',
            'controllButtonsShowOnlyToAdmin_record_stop',
            'controllButtonsShowOnlyToAdmin_drop_publisher',
            'controllButtonsShowOnlyToAdmin_drop_publisher_reset_key',
            'controllButtonsShowOnlyToAdmin_save_dvr',
            'disable_live_schedule',
            'live_schedule_label',
            'hls_path',
            'autoFishLiveEveryHour',
        );
    }

    public static function getDataObjectDeprecated()
    {
        return array(
            'server_type',
            'requestStatsTimout',
            'cacheStatsTimout',
            'requestStatsInterval',
            'webRTC_isDisabled',
            'webRTC_server',
            'webRTC_SelfHostedURL',
            'webRTC_CertPath',
            'webRTC_KeyPath',
            'webRTC_ChainCertPath',
            'webRTC_PushRTMP',
            'webRTC_PushRTMP',
            'webRTC_PushRTMP',
            'experimentalWebcam',
        );
    }

    public static function getDataObjectExperimental()
    {
        return array(
            'playLiveInFullScreen',
            'playLiveInFullScreenOnIframe',
        );
    }

    public function getEmptyDataObject()
    {
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

        $o = new stdClass();
        $o->type = array(self::CAN_RESTREAM_All_USERS => ('All Users'), self::CAN_RESTREAM_ONLY_SELECTED_USERGROUPS => ('Selected user groups'));
        $o->value = self::CAN_RESTREAM_All_USERS;
        $obj->whoCanRestream = $o;
        self::addDataObjectHelper('whoCanRestream', 'Who can Restream');

        $obj->disableDVR = false;
        self::addDataObjectHelper('disableDVR', 'Disable DVR', 'Enable or disable the DVR Feature, you can control the DVR length in your nginx.conf on the parameter hls_playlist_length');
        $obj->disableGifThumbs = false;
        self::addDataObjectHelper('disableGifThumbs', 'Disable Gif Thumbs', 'This option will disable the Animated Gif render, it will save some hardware capacity from your encoder and may speedup your page');
        $obj->disableLiveThumbs = false;
        self::addDataObjectHelper('disableLiveThumbs', 'Disable Live thumbnails', 'This option will disable the çive image extraction and will use the user static image, it will save some hardware capacity from your encoder and may speedup your page');
        $obj->hideTopButton = false;
        self::addDataObjectHelper('hideTopButton', 'Hide Top Button', 'This will hide the "Live Settings" button on the top menu bar');
        $obj->hideUserGroups = false;
        $obj->hideShare = false;
        $obj->hideAdvancedStreamKeys = false;
        $obj->hidePublicListedOption = false;
        $obj->publicListedIsTheDefault = true;
        $obj->hideIsRebroadcastOption = true;
        $obj->saveLiveIsTheDefault = true;
        self::addDataObjectHelper('saveLiveIsTheDefault', 'Save Live is the Default', 'https://github.com/WWBN/AVideo/wiki/Record-Live-Stream');
        $obj->useAadaptiveMode = false;
        self::addDataObjectHelper('useAadaptiveMode', 'Adaptive mode', 'https://github.com/WWBN/AVideo/wiki/Adaptive-Bitrates-on-Livestream');
        $obj->protectLive = false;
        self::addDataObjectHelper('protectLive', 'Live Protection', 'With this your encryption key will be protected, and only your site player will be able to play your videos, download tools will not be able to download your video. if you want to share your live externally you can use the embed and you will still be protected. but if you want to use the m3u8 file you must disable this');
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
        self::addDataObjectHelper('doNotShowGoLiveButton', 'Hide Top Live Settings Button', 'This will hide the "Go Live" button on the top menu bar');
        $obj->doNotShowGoLiveButtonOnUploadMenu = false;
        self::addDataObjectHelper('doNotShowGoLiveButtonOnUploadMenu', 'Hide Live Settings Button on Upload Menu', 'This will hide the "Live Settings" button on the right upload menu bar');
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
        $obj->cacheStatsTimout = 60; // we will cache the result
        self::addDataObjectHelper('cacheStatsTimout', 'Stats Cache Timeout', 'we will cache the result, this will save some resources');
        $obj->requestStatsInterval = 60; // how many seconds until requesting the stats again
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

        $obj->live_rebroadcasts_label = 'Rebroadcasts';
        self::addDataObjectHelper('live_rebroadcasts_label', 'Label for Rebroadcasts');

        $ServerHost = getHostOnlyFromURL($global['webSiteRootURL']);

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "Hi {UserIdentification},

This is a friendly reminder that the live <strong>{liveTitle}</strong> will start in <strong>{timeToStart}</strong>.

Click <a href=\"{link}\">here</a> to join our live.";
        $obj->reminderText = $o;
        self::addDataObjectHelper('reminderText', 'Scheduled reminder text', 'If you setup the live scheduler properly this text will be sent to your subscribers');

        $obj->autoFishLiveEveryHour = false;
        self::addDataObjectHelper('autoFishLiveEveryHour', 'Automatically end offline live sessions every hour', 'The server will verify if can access the m3u8 file, and finish the live if cannot');

        return $obj;
    }

    public function getHeadCode()
    {
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
            $js .= '<script>var playLiveInFullScreen = true;</script>';
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }

        if ($live = isLive()) {
            $prerollPoster = 'false';
            $postrollPoster = 'false';
            $liveImgCloseTimeInSecondsPreroll = 'false';
            $liveImgTimeInSecondsPreroll = 'false';
            $liveImgCloseTimeInSecondsPostroll = 'false';
            $liveImgTimeInSecondsPostroll = 'false';
            //var_dump('',$live, self::prerollPosterExists($live['users_id'], $live['live_servers_id'], $live['live_schedule']));exit;
            if (self::prerollPosterExists($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0)) {

                $path = self::getPrerollPosterImage($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0);
                $prerollPoster = "'" . getURL($path) . "'";

                $times = self::getPrerollPosterImageTimes($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0);
                $liveImgCloseTimeInSecondsPreroll = $times->liveImgCloseTimeInSeconds;
                $liveImgTimeInSecondsPreroll = $times->liveImgTimeInSeconds;
                //var_dump($times);
            }
            if (self::postrollPosterExists($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0)) {
                $postrollPoster = "'" . getURL(self::getPostrollPosterImage($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0)) . "'";

                $times = self::getPostrollPosterImageTimes($live['users_id'], $live['live_servers_id'], $live['live_schedule'], 0);
                $liveImgCloseTimeInSecondsPostroll = $times->liveImgCloseTimeInSeconds;
                $liveImgTimeInSecondsPostroll = $times->liveImgTimeInSeconds;
                //var_dump($times);
            }
            $liveImageBGTemplate = '';
            if ($prerollPoster || $postrollPoster) {
                $liveImageBGTemplate = file_get_contents($global['systemRootPath'] . 'plugin/Live/view/imagebg.template.html');
            }
            //var_dump($liveImgCloseTimeInSecondsPreroll ,$liveImgTimeInSecondsPreroll,$liveImgCloseTimeInSecondsPostroll ,$liveImgTimeInSecondsPostroll);exit;
            $js .= '<script>'
                . 'var prerollPoster_' . $live['cleanKey'] . ' = ' . $prerollPoster . ';'
                . 'var postrollPoster_' . $live['cleanKey'] . ' = ' . $postrollPoster . ';'
                . 'var liveImgCloseTimeInSecondsPreroll_' . $live['cleanKey'] . ' = ' . $liveImgCloseTimeInSecondsPreroll . ';'
                . 'var liveImgTimeInSecondsPreroll_' . $live['cleanKey'] . ' = ' . $liveImgTimeInSecondsPreroll . ';'
                . 'var liveImgCloseTimeInSecondsPostroll_' . $live['cleanKey'] . ' = ' . $liveImgCloseTimeInSecondsPostroll . ';'
                . 'var liveImgTimeInSecondsPostroll_' . $live['cleanKey'] . ' = ' . $liveImgTimeInSecondsPostroll . ';'
                . 'var liveImageBGTemplate = ' . json_encode($liveImageBGTemplate) . ';'
                . 'var isLive = ' . json_encode(isLive()) . ';'
                . '</script>';
        }
        $js .= '<link href="' . getURL('plugin/Live/view/live.css') . '" rel="stylesheet" type="text/css"/>';

        return $js . $css;
    }

    public function getFooterCode()
    {
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

    public function getButtonTitle()
    {
        $o = $this->getDataObject();
        return $o->button_title;
    }

    public function getKey()
    {
        $o = $this->getDataObject();
        return $o->key;
    }

    public static function getDestinationApplicationName()
    {
        $app = self::getAPPName();
        $domain = self::getControl();
        //return "{$domain}/control/drop/publisher?app={$app}&name={$key}";
        return "{$app}?p=" . User::getUserPass();
    }

    public static function getDestinationHost()
    {
        $server = self::getServer();
        $host = parse_url($server, PHP_URL_HOST);
        return $host;
    }

    public static function getDestinationPort()
    {
        $server = self::getServer();
        $port = parse_url($server, PHP_URL_PORT);
        if (empty($port)) {
            $port = 1935;
        }
        return $port;
    }

    public static function getPlayerDestinationHost()
    {
        $obj = AVideoPlugin::getDataObjectIfEnabled('Live');
        $host = parse_url($obj->playerServer, PHP_URL_HOST);
        return $host;
    }

    public static function getPlayerDestinationPort()
    {
        $obj = AVideoPlugin::getDataObjectIfEnabled('Live');
        $port = parse_url($obj->playerServer, PHP_URL_PORT);
        if (empty($port)) {
            $port = 1935;
        }
        return $port;
    }

    public static function getServer($live_servers_id = -1)
    {
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

    public static function getAPPName()
    {
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

    public static function getControlOrPublic($key, $live_servers_id = 0)
    {
        global $global;
        if (isDocker()) {
            return 'http://live:8080/control/';
        }
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

    public static function getDropURL($key, $live_servers_id = 0)
    {
        _error_log("getDropURL($key, $live_servers_id)", AVideoLog::$WARNING);
        $obj = AVideoPlugin::getObjectData("Live");
        $domain = self::getControlOrPublic($key, $live_servers_id);
        if (isDocker()) {
            $domain .= 'drop/publisher';
        }
        $app = self::getAPPName();
        $domain = addQueryStringParameter($domain, 'command', 'drop_publisher');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getIsRecording($key, $live_servers_id = 0)
    {
        $domain = self::getControlOrPublic($key, $live_servers_id);
        if (isDocker()) {
            $domain .= 'record/status';
        }
        $app = self::getAPPName();
        $domain = addQueryStringParameter($domain, 'command', 'is_recording');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getStartRecordURL($key, $live_servers_id = 0)
    {
        $domain = self::getControlOrPublic($key, $live_servers_id);
        if (isDocker()) {
            $domain .= 'record/stop';
        }
        $app = self::getAPPName();
        $domain = addQueryStringParameter($domain, 'command', 'record_start');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function getStopRecordURL($key, $live_servers_id = 0)
    {
        $domain = self::getControlOrPublic($key, $live_servers_id);
        if (isDocker()) {
            $domain .= 'record/status';
        }
        $app = self::getAPPName();
        $domain = addQueryStringParameter($domain, 'command', 'record_stop');
        $domain = addQueryStringParameter($domain, 'app', $app);
        $domain = addQueryStringParameter($domain, 'name', $key);
        $domain = addQueryStringParameter($domain, 'token', getToken(60));
        return $domain;
    }

    public static function controlRecording($key, $live_servers_id, $start = true, $try = 0)
    {
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

    public static function controlRecordingAsync($key, $live_servers_id, $start = true)
    {
        global $global;
        outputAndContinueInBackground();
        $command = get_php() . " {$global['systemRootPath']}plugin/Live/controlRecording.php '$key' '$live_servers_id' '$start'";

        _error_log("NGINX Live::controlRecordingAsync start  ($command)");
        $pid = execAsync($command);
        _error_log("NGINX Live::controlRecordingAsync end {$pid}");
        return $pid;
    }

    public static function userCanRecordLive($users_id)
    {
        if (!AVideoPlugin::isEnabledByName('SendRecordedToEncoder')) {
            return false;
        }
        return SendRecordedToEncoder::canRecord($users_id);
    }

    public static function getButton($command, $key, $live_servers_id = 0, $iconsOnly = false, $label = "", $class = "", $tooltip = "")
    {
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
            case "save_the_moment":
                $obj2 = AVideoPlugin::getDataObjectIfEnabled('SendRecordedToEncoder');
                if (empty($obj2) || empty($obj2->saveTheMomentEnable)) {
                    return '<!-- SendRecordedToEncoder saveDVREnable is not present -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_save_dvr && !User::isAdmin()) {
                    return '<!-- User Cannot save DVR controllButtonsShowOnlyToAdmin_save_dvr -->';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                $class .= 'btn btn-warning ';
                return '<!-- SendRecordedToEncoder::getsaveTheMomentButton -->' . SendRecordedToEncoder::getsaveTheMomentButton($key, $live_servers_id, $class);
                break;
            case "download_the_moment":
                $obj2 = AVideoPlugin::getDataObjectIfEnabled('SendRecordedToEncoder');
                if (empty($obj2) || empty($obj2->downloadTheMomentEnable)) {
                    return '<!-- SendRecordedToEncoder saveDVREnable is not present -->';
                }
                if ($obj->controllButtonsShowOnlyToAdmin_save_dvr && !User::isAdmin()) {
                    return '<!-- User Cannot save DVR controllButtonsShowOnlyToAdmin_save_dvr -->';
                }
                if (!self::userCanRecordLive(User::getId())) {
                    return '<!-- User Cannot record -->';
                }
                $class .= 'btn btn-info ';
                return '<!-- SendRecordedToEncoder::getsaveTheMomentButton -->' . SendRecordedToEncoder::getdownloadTheMomentButton($key, $live_servers_id, $class);
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

    public static function getRecordControlls($key, $live_servers_id = 0, $iconsOnly = false)
    {
        if (!User::canStream()) {
            return "";
        }

        $btn = "<div class=\"btn-group justified\">";
        $btn .= self::getButton("record_start", $key, $live_servers_id, $iconsOnly);
        $btn .= self::getButton("record_stop", $key, $live_servers_id, $iconsOnly);
        $btn .= "</div>";

        return $btn;
    }

    public static function getAllControlls($key, $live_servers_id = 0, $iconsOnly = false, $btnClass = '')
    {
        global $global;
        if (!Live::canManageLiveFromLiveKey($key, User::getId())) {
            return '';
        }

        $btn = "<div class=\"btn-group justified recordLiveControlsDiv keepLabels\" style=\"display: none;\" id=\"liveControls\">";
        //$btn .= self::getButton("drop_publisher", $live_transmition_id, $live_servers_id);
        $btn .= self::getButton("save_dvr", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("save_the_moment", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("download_the_moment", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("drop_publisher_reset_key", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("record_start", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= self::getButton("record_stop", $key, $live_servers_id, $iconsOnly, '', $btnClass);
        $btn .= "</div>";
        $btn .= "<script src=\"{$global['webSiteRootURL']}plugin/Live/view/isOnlineLabel.js\"></script>";

        return $btn;
    }

    public static function getRestreamer($live_servers_id = -1)
    {
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

    public static function getControl($live_servers_id = -1)
    {
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

    public static function getRTMPLink($users_id, $forceIndex = false, $doNotCheckUser = false)
    {
        $key = self::getKeyFromUser($users_id, $doNotCheckUser);
        return self::getRTMPLinkFromKey($key, $forceIndex);
    }

    public static function getRTMPLinkFromKey($key, $forceIndex = false)
    {
        $lso = new LiveStreamObject($key);

        return $lso->getRTMPLink($forceIndex);
    }

    public static function getRTMPLinkWithOutKey($users_id, $short = true)
    {
        $lso = new LiveStreamObject(self::getKeyFromUser($users_id));

        return $lso->getRTMPLinkWithOutKey($short);
    }

    public static function getRTMPLinkWithOutKeyFromKey($key)
    {
        $lso = new LiveStreamObject($key);

        return $lso->getRTMPLinkWithOutKey();
    }

    public static function getKeyFromUser($users_id, $doNotCheckUser = false)
    {
        if (!isCommandLineInterface() && !$doNotCheckUser && (!User::isLogged() || ($users_id !== User::getId() && !User::isAdmin()))) {
            return false;
        }
        $user = new User($users_id);
        $trasnmition = LiveTransmition::createTransmitionIfNeed($users_id);
        if (empty($trasnmition)) {
            _error_log("Live::getKeyFromUser error on create live transmission {$users_id} ");
            return false;
        }
        if (empty($trasnmition['key'])) {
            _error_log("Live::getKeyFromUser error on get key " . json_encode($trasnmition));
            return false;
        }
        return $trasnmition['key'];
    }

    public static function getDynamicKey($key)
    {
        $objLive = AVideoPlugin::getDataObject("Live");
        if ($objLive->allowMultipleLivesPerUser) {
            $key .= '-' . date('His');
        }
        return $key;
    }

    public static function getPlayerServer($ignoreCDN = false)
    {
        $obj = AVideoPlugin::getObjectData("Live");
        /**
         * @var string $url
         */
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

    public static function getUseAadaptiveMode()
    {
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

    public static function getRemoteFile()
    {
        return self::getRemoteFileFromLiveServersID(self::getCurrentLiveServersId());
    }

    public static function getRemoteFileFromLiveServersID($live_servers_id)
    {
        global $global;
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($live_servers_id) || !empty($obj->useLiveServers)) {
            $ls = new Live_servers($live_servers_id);
            $url = $ls->getGetRemoteFile();
            if (isValidURL($url)) {
                return $url;
            }
        }
        return "{$global['webSiteRootURL']}plugin/Live/standAloneFiles/getRecordedFile.php";
    }

    public static function getRemoteFileFromRTMPHost($rtmpHostURI)
    {
        $live_servers_id = Live_servers::getServerIdFromRTMPHost($rtmpHostURI);
        return self::getRemoteFileFromLiveServersID($live_servers_id);
    }

    public static function getLiveServersIdRequest()
    {
        if (empty($_REQUEST['live_servers_id'])) {
            if (!empty($_POST['tcurl'])) {
                $url = $_POST['tcurl'];
            }
            if (empty($url)) {
                $url = @$_POST['swfurl'];
            }
            if (!empty($url)) {
                return Live_servers::getServerIdFromRTMPHost($url);
            }
            return 0;
        }
        return intval($_REQUEST['live_servers_id']);
    }

    public static function getLiveScheduleIdRequest()
    {
        if (!empty($_REQUEST['live_schedule_id'])) {
            return intval($_REQUEST['live_schedule_id']);
        }
        if (!empty($_REQUEST['live_schedule'])) {
            return intval($_REQUEST['live_schedule']);
        }
        return 0;
    }

    public static function getM3U8File($uuid, $doNotProtect = false, $ignoreCDN = false)
    {
        $live_servers_id = self::getLiveServersIdRequest();
        $lso = new LiveStreamObject($uuid, $live_servers_id, false, false);
        $parts = self::getLiveParametersFromKey($uuid);
        $allowOnlineIndex = false;
        if (!empty($parts['live_index'])) {
            $allowOnlineIndex = $parts['live_index'];
        } elseif (!empty($_REQUEST['live_index'])) {
            $allowOnlineIndex = false;
        }
        //_error_log("Live:getM3U8File($uuid) ". json_encode($parts));
        return $lso->getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN);
    }

    public function getDisableGifThumbs()
    {
        $o = $this->getDataObject();
        return $o->disableGifThumbs;
    }

    public function getStatsURL($live_servers_id = 0)
    {
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

    public function getStatsObject($live_servers_id = 0, $force_recreate = false, $tries = 0)
    {
        global $global, $_getStatsObject_force_recreate_executed;

        if ($force_recreate) {
            if (!empty($_getStatsObject_force_recreate_executed)) {
                // already forced, ignore it
                $force_recreate = false;
            }
            $_getStatsObject_force_recreate_executed = true;
        }

        if (!empty($global['disableGetStatsObject'])) {
            return [];
        }

        if (!function_exists('simplexml_load_file')) {
            _error_log("Live::getStatsObject: You need to install the simplexml_load_file function to be able to see the Live stats", AVideoLog::$ERROR);
            return false;
        }
        if (!isset($global['isStatsAccessible'])) {
            $global['isStatsAccessible'] = array();
        }
        $name = "live_servers_id_{$live_servers_id}_getStatsObject";
        $cacheHandler = new LiveCacheHandler();
        global $getStatsObject;
        if (!isset($getStatsObject)) {
            $getStatsObject = [];
        }
        if (empty($force_recreate) && empty($_REQUEST['debug'])) {
            //_error_log("Live::getStatsObject[$live_servers_id] 1: searching for cache");
            if (isset($getStatsObject[$live_servers_id])) {
                //_error_log("Live::getStatsObject[$live_servers_id] 2: return cached result");
                return $getStatsObject[$live_servers_id];
            }
            $result = $cacheHandler->getCache($name, maxLifetime() + 90);

            if (!empty($result)) {
                //_error_log("Live::getStatsObject[$live_servers_id] 3: return cached result $name [lifetime=" . (maxLifetime() + 60) . "]");
                $json = _json_decode($result);
                return $json;
            }
            //_error_log("Live::getStatsObject[$live_servers_id] 4: cache not found");
        } else {
            _error_log("Live::getStatsObject[$live_servers_id] 5: forced to be recreated");
        }

        $o = AVideoPlugin::getDataObject('Live');
        if (empty($o->requestStatsTimout)) {
            $o->requestStatsTimout = 10;
        }
        $xml = $this->createCacheStatsObject($live_servers_id, $o->requestStatsTimout);
        $getStatsObject[$live_servers_id] = $xml;
        $cacheHandler->setCache($xml);

        if (!empty($force_recreate) || !empty($_REQUEST['debug'])) {
            _error_log("Live::getStatsObject[$live_servers_id] 5: forced to be recreated done " . json_encode(debug_backtrace()));
        }
        //var_dump(__LINE__, $xml);
        $global['isStatsAccessible'][$live_servers_id] = !empty($xml);
        return $xml;
    }

    public function createCacheStatsObject($live_servers_id = 0, $requestStatsTimout = 15)
    {
        if (!function_exists('simplexml_load_file')) {
            _error_log("Live::createCacheStatsObject: You need to install the simplexml_load_file function to be able to see the Live stats", AVideoLog::$ERROR);
            return false;
        }
        global $global;
        if (!isset($global['isStatsAccessible'])) {
            $global['isStatsAccessible'] = array();
        }

        if (!AVideoPlugin::isEnabledByName('Live')) {
            _error_log("Live::createCacheStatsObject: live plugin is disabled " . json_encode(debug_backtrace()), AVideoLog::$DEBUG);
            return false;
        }
        $name = "live_servers_id_{$live_servers_id}_getStatsObject";
        $cacheHandler = new LiveCacheHandler();
        $cacheHandler->setSuffix($name);
        ini_set('allow_url_fopen ', 'ON');
        if (isDocker()) {
            $url = getDockerStatsURL();
        } else {
            $url = $this->getStatsURL($live_servers_id);
        }


        if (!empty($_REQUEST['debug'])) {
            _error_log("Live::getStatsObject $url ");
        }

        $data = $this->get_data($url,  $requestStatsTimout);
        if (empty($data)) {
            _error_log("Live::getStatsObject RTMP Server ($url) is OFFLINE requestStatsTimout={$requestStatsTimout} we could not connect on it => live_servers_id = ($live_servers_id) ", AVideoLog::$ERROR);
            $data = '<?xml version="1.0" encoding="utf-8" ?><?xml-stylesheet type="text/xsl" href="stat.xsl" ?><rtmp><server><application><name>The RTMP Server is Unavailable</name><live><nclients>0</nclients></live></application></server></rtmp>';
        } else if (!empty($_REQUEST['debug'])) {
            _error_log("Live::getStatsObject $data ");
        }
        $xml = simplexml_load_string($data);
        $xml = json_encode($xml);
        $xml = _json_decode($xml);
        $getStatsObject[$live_servers_id] = $xml;
        $cacheHandler->setCache($xml);
        $global['isStatsAccessible'][$live_servers_id] = !empty($xml);
        return $xml;
    }

    static function isStatsAccessible($live_servers_id)
    {
        global $global;

        if (!isset($global['isStatsAccessible'])) {
            $l = AVideoPlugin::loadPlugin('Live');
            $l->getStatsObject($live_servers_id);
        }

        return !empty($global['isStatsAccessible']) && !empty($global['isStatsAccessible'][$live_servers_id]);
    }

    public function get_data($url, $timeout)
    {
        global $global;
        if (!IsValidURL($url)) {
            _error_log("Live::getStatsObject get_data($url, $timeout) invalid URL");
            return false;
        }

        //_error_log_debug("Live::getStatsObject get_data($url, $timeout) ");
        return url_get_contents($url, '', $timeout);
    }

    public function getChartTabs()
    {
        return '<li><a data-toggle="tab" id="liveVideos" href="#liveVideosMenu"><i class="fas fa-play-circle"></i> ' . __('Live videos') . '</a></li>';
    }

    public function getChartContent()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/report.php';
    }

    public static function saveHistoryLog($key)
    {
        // get the latest history for this key
        $latest = LiveTransmitionHistory::getLatest($key);

        if (!empty($latest)) {
            LiveTransmitionHistoryLog::addLog($latest['id']);
        }
    }

    public function dataSetup()
    {
        $obj = $this->getDataObject();
        if (!isLive() || $obj->disableDVR) {
            return "";
        }
        return "liveui: true";
    }

    public static function stopLive($users_id)
    {
        if (!User::isAdmin() && User::getId() != $users_id) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj)) {
            $lt = new LiveTransmition(0);
            $lt->loadByUser($users_id);
            $key = $lt->getKey();
            return self::stopLiveFromkey($key);
        }
    }

    public static function stopLiveFromkey($key)
    {
        if (!User::isAdmin()) {
            $responseObj = new stdClass();
            $responseObj->error = true;
            $responseObj->code = 0;
            $responseObj->msg = "User is not admin.";
            return $responseObj;
        }

        $obj = AVideoPlugin::getObjectData("Live");
        if (!empty($obj)) {
            $server = str_replace(["stats", "stat"], "", $obj->stats);
            if (isDocker()) {
                $server = 'http://live:8080/';
            }
            $appName = self::getApplicationName();
            $url = "{$server}control/drop/publisher?app={$appName}&name=$key";

            $responseObj = url_get_response($url);

            _error_log("stopLiveFromkey($key) {$url} HTTP Code: " . $responseObj->code);

            if (!$responseObj->error) {
                $dir = $obj->hls_path . "/$key";
                if (is_dir($dir)) {
                    rrmdir($dir);
                }
            }
        } else {
            $responseObj = new stdClass();
            $responseObj->error = true;
            $responseObj->code = 0;
            $responseObj->msg = "Live plugin object data is empty.";
        }

        return $responseObj;
    }




    // not implemented yet
    public static function startRecording($users_id)
    {
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

    public static function getApplicationName()
    {
        $rtmpServer = self::getServer();
        $parts = explode('/', $rtmpServer);
        $live = end($parts);

        if (!preg_match('/^live/i', $live)) {
            $live = 'live';
        }
        return trim($live);
    }

    // not implemented yet
    public static function stopRecording($users_id)
    {
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

    public static function getLinkToLiveFromUsers_id($users_id, $live_schedule_id = 0)
    {
        $live_servers_id = self::getCurrentLiveServersId();
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, null, $live_schedule_id);
    }

    public static function getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $live_index = null, $live_schedule_id = 0)
    {
        if (empty($users_id)) {
            return false;
        }
        global $global;
        $user = new User($users_id);
        if (empty($user->getChannelName())) {
            return false;
        }
        return self::getLinkToLiveFromChannelNameAndLiveServer($user->getChannelName(), $live_servers_id, $live_index, $live_schedule_id);
    }

    public static function getLinkToLiveFromChannelNameAndLiveServer($channelName, $live_servers_id, $live_index = null, $live_schedule_id = 0)
    {
        global $global;
        $live_servers_id = intval($live_servers_id);
        $channelName = trim($channelName);
        if (empty($channelName)) {
            return false;
        }

        $obj = AVideoPlugin::getDataObject('Live');

        if (empty($obj->useLiveServers)) {
            $live_servers_id = 0;
        }

        $url = "{$global['webSiteRootURL']}live/{$live_servers_id}/" . urlencode($channelName);

        $live_schedule_id = intval($live_schedule_id);
        if (!empty($live_schedule_id)) {
            $url = "{$url}/ls/{$live_schedule_id}";
        }

        if (!empty($live_index)) {
            $url .= '/' . urlencode($live_index);
        } elseif (!isset($live_index) && !empty($_REQUEST['live_index'])) {
            $url .= '/' . urlencode($_REQUEST['live_index']);
        }

        if (!empty($_REQUEST['playlists_id_live'])) {
            $url = addQueryStringParameter($url, 'playlists_id_live', $_REQUEST['playlists_id_live']);
        }

        //var_dump($url, $channelName, $live_servers_id, $live_index, $live_schedule_id);
        //return "{$global['webSiteRootURL']}plugin/Live/?live_servers_id={$live_servers_id}&c=" . urlencode($channelName);
        return $url;
    }

    public static function getAvailableLiveServersId()
    {
        $ls = self::getAvailableLiveServer();
        if (empty($ls)) {
            return 0;
        } else {
            return intval($ls->live_servers_id);
        }
    }

    public static function getLastServersIdFromUser($users_id)
    {
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($last)) {
            return 0;
        } else {
            return intval($last['live_servers_id']);
        }
    }

    public static function getLastsLiveHistoriesFromUser($users_id, $count = 10)
    {
        return LiveTransmitionHistory::getLastsLiveHistoriesFromUser($users_id, $count);
    }

    public static function getLinkToLiveFromUsers_idWithLastServersId($users_id)
    {
        $live_servers_id = self::getLastServersIdFromUser($users_id);
        return self::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id);
    }

    public static function getCurrentLiveServersId()
    {
        $live_servers_id = self::getLiveServersIdRequest();
        if ($live_servers_id) {
            return $live_servers_id;
        } else {
            return self::getAvailableLiveServersId();
        }
    }

    public function getVideosManagerListButtonTitle()
    {
        global $global;
        if (!User::isAdmin()) {
            return "";
        }
        $btn = '<div class="clearfix"></div><button type="button" class="btn btn-default btn-light btn-sm btn-xs" onclick="document.location = \\\'' . $global['webSiteRootURL'] . 'plugin/Live/?users_id=\' + row.users_id + \'\\\';" data-row-id="right" ><i class="fa fa-circle"></i> ' . __("Live Info") . '</button>';
        return $btn;
    }

    public function getPluginMenu()
    {
        global $global;

        $obj = $this->getDataObject();

        $btn = '<button onclick="avideoModalIframeLarge(\'' . $global['webSiteRootURL'] . 'plugin/Live/view/editor.php\');" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> ' . __('Edit Live Servers') . '</button>';
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/Live/view/finishAll.json.php\', {});" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-ban"></i> ' . __('Mark all as finished') . '</button>';
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/Live/view/deleteHistory.json.php\', {});" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-trash"></i> ' . __('Delete History') . '</button>';

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

    public static function checkAllFromStats($force_recreate = false)
    {
        self::finishAllFromStats();
        self::unfinishAllFromStats($force_recreate);
    }

    public static function finishAllFromStats()
    {
        $obj = AVideoPlugin::getObjectData("Live");
        $stats = Live::getStatsApplications();
        if (empty($obj->useLiveServers)) {
            $lives = LiveTransmitionHistory::getActiveLives(0, true);
            foreach ($lives as $live) {
                $found = false;
                foreach ($stats as $liveFromStats) {
                    if (!empty($liveFromStats['key']) && $liveFromStats['key'] == $live['key']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    LiveTransmitionHistory::finishFromTransmitionHistoryId($live['id']);
                }
            }
        } else {
            $rows = Live_servers::getAllActive();

            foreach ($rows as $liveS) {
                $lives = LiveTransmitionHistory::getActiveLives($liveS['id'], true);
                foreach ($lives as $live) {
                    $found = false;
                    foreach ($stats as $liveFromStats) {
                        if (!empty($liveFromStats['key']) && $liveFromStats['key'] == $live['key']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        LiveTransmitionHistory::finishFromTransmitionHistoryId($live['id']);
                    }
                }
            }
        }
    }

    public static function unfinishAllFromStats($force_recreate = false)
    {
        global $unfinishAllFromStatsDone;
        if (!empty($unfinishAllFromStatsDone)) {
            return false;
        }
        $unfinishAllFromStatsDone = 1;
        $stats = Live::getStatsApplications($force_recreate);

        foreach ($stats as $key => $live) {
            if (!empty($live['key'])) {
                $row = LiveTransmitionHistory::getLatest($live['key'], @$live['live_servers_id']);
                if (!empty($row['finished'])) {
                    LiveTransmitionHistory::unfinishFromTransmitionHistoryId($row['id']);
                } else {
                    $row = LiveTransmition::keyExists($live['key']);
                    if (!empty($row)) {
                        $lth = new LiveTransmitionHistory();
                        $lth->setTitle($row['title']);
                        $lth->setDescription($row['description']);
                        $lth->setKey($live['key']);
                        $lth->setUsers_id($row['users_id']);
                        $lth->setLive_servers_id(@$live['live_servers_id']);
                        $id = $lth->save();
                        _error_log("unfinishAllFromStats saving LiveTransmitionHistory {$live['key']} [{$id}] ");
                    }
                }
            }
        }
    }

    public static function getStatsApplications($force_recreate = false)
    {
        $applications = array();
        $stats = Live::getStats($force_recreate);
        foreach ($stats as $key => $server) {
            if (is_array($server) || is_object($server)) {
                foreach ($server as $key2 => $live) {
                    if (!empty($live->key)) {
                        $applications[] = object_to_array($live);
                    } else if (is_array($live) && !empty($live['key'])) {
                        $applications[] = $live;
                    } else {
                        if ($key2 == 'applications' && is_array($live)) {
                            foreach ($live as $key3 => $value3) {
                                $applications[] = object_to_array($value3);
                            }
                        }
                    }
                }
            }
        }
        return $applications;
    }

    public static function getStats($force_recreate = false)
    {
        global $getStatsLive, $_getStats, $getStatsObject;
        $timeName = "stats.json.php getStats";
        TimeLogStart($timeName);
        if (empty($force_recreate) && empty($_REQUEST['debug'])) {
            if (isset($getStatsLive)) {
                //_error_log('Live::getStats: return cached result');
                return $getStatsLive;
            }
        }

        TimeLogEnd($timeName, __LINE__);
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->server_type->value)) {
            _error_log("Live::getStats obj->server_type->value={$obj->server_type->value}");
            $rows = LiveTransmitionHistory::getActiveLiveFromUser(0, '', '', 50);
            TimeLogEnd($timeName, __LINE__);
            $servers = [];
            $servers['applications'] = [];
            foreach ($rows as $value) {
                if (!is_array($value)) {
                    continue;
                }
                $servers['applications'][] = LiveTransmitionHistory::getApplicationObject($value['id']);
            }
            TimeLogEnd($timeName, __LINE__);
            return $servers;
        } elseif (empty($obj->useLiveServers)) {
            if (!empty($_REQUEST['debug'])) {
                _error_log("Live::getStats empty obj->useLiveServers}");;
            }
            //
            //_error_log('getStats getStats 1: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));
            $getStatsLive = self::_getStats(0, $force_recreate);
            TimeLogEnd($timeName, __LINE__);
            //_error_log('Live::getStats(0) 1');
            return $getStatsLive;
        } else {
            $rows = Live_servers::getAllActive();
            TimeLogEnd($timeName, __LINE__);

            if (!empty($_REQUEST['debug'])) {
                _error_log("Live::getStats Live_servers::getAllActive total=" . count($rows));
            }
            $liveServers = [];
            foreach ($rows as $key => $row) {
                if (!empty($row['playerServer'])) {
                    //_error_log("getStats getStats: self::_getStats ".json_encode($row));
                    $server = self::_getStats($row['id'], $force_recreate);
                    $server->live_servers_id = $row['id'];
                    $server->playerServer = $row['playerServer'];
                    $getStatsLive = $server;
                    $liveServers[] = $server;
                }
            }
            TimeLogEnd($timeName, __LINE__);
            if (!empty($liveServers)) {
                return $liveServers;
            }
        }
        $ls = Live_servers::getAllActive();
        TimeLogEnd($timeName, __LINE__);
        $liveServers = [];
        $getLiveServersIdRequest = self::getLiveServersIdRequest();
        TimeLogEnd($timeName, __LINE__);
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
        TimeLogEnd($timeName, __LINE__);
        if (!empty($_REQUEST['debug'])) {
            _error_log("Live::getStats return " . json_encode($liveServers));;
        }
        $_REQUEST['live_servers_id'] = $getLiveServersIdRequest;
        $getStatsLive = $liveServers;
        return $liveServers;
    }

    public static function isAdaptive($key)
    {
        if (!is_string($key)) {
            _error_log('isAdaptive ERROR ' . _json_encode($key));
            return false;
        }
        if (preg_match('/_(hi|low|mid)$/i', $key)) {
            return true;
        }
        return false;
    }

    public static function getAllServers()
    {
        $obj = AVideoPlugin::getObjectData("Live");
        if (empty($obj->useLiveServers)) {
            return ["id" => 0, "name" => __("Default"), "status" => "a", "rtmp_server" => $obj->server, 'playerServer' => $obj->playerServer, "stats_url" => $obj->stats, "disableDVR" => $obj->disableDVR, "disableGifThumbs" => $obj->disableGifThumbs, "useAadaptiveMode" => $obj->useAadaptiveMode, "protectLive" => $obj->protectLive, "getRemoteFile" => ""];
        } else {
            return Live_servers::getAllActive();
        }
    }

    public static function getAvailableLiveServer()
    {
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

    public static function canSeeLiveFromLiveKey($key)
    {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->userCanSeeTransmition();
    }

    public static function isPasswordProtected($key)
    {
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


    public static function isRebroadcast($key)
    {
        global $_isRebroadcast;
        if (empty($key)) {
            return false;
        }
        if (!isset($_isRebroadcast)) {
            $_isRebroadcast = array();
        }
        if (!isset($_isRebroadcast[$key])) {
            $lt = self::getLiveTransmitionObjectFromKey($key);
            if (empty($lt)) {
                $_isRebroadcast[$key] = false;
            } else {
                $rb = $lt->getIsRebroadcast();
                if (!empty($rb)) {
                    $_isRebroadcast[$key] = true;
                } else {
                    $_isRebroadcast[$key] = false;
                }
            }
        }
        //var_dump($key, $_isPasswordProtected[$key]);
        return $_isRebroadcast[$key];
    }

    public static function canManageLiveFromLiveKey($key, $users_id)
    {
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

    public static function isAPrivateLiveFromLiveKey($key)
    {
        $lt = self::getLiveTransmitionObjectFromKey($key);
        if (empty($lt)) {
            return false;
        }
        return $lt->isAPrivateLive();
    }

    public static function getTitleFromUsers_Id($users_id)
    {
        if (empty($users_id)) {
            return '';
        }
        $lt = self::getLiveTransmitionObjectFromUsers_id($users_id);
        if (empty($lt)) {
            return '';
        }
        return self::getTitleFromKey($lt->getKey(), $lt->getTitle());
    }

    public static function getLiveTransmitionObjectFromUsers_id($users_id)
    {
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (!empty($latest)) {
            $key = $latest['key'];
        } else {
            $key = self::getLiveKey($users_id);
        }
        return self::getLiveTransmitionObjectFromKey($key);
    }

    public static function getLiveTransmitionObjectFromKey($key)
    {
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
                if (!empty($livet['live_schedule_id'])) {
                    $lt = new Live_schedule($livet['live_schedule_id']);
                } else {
                    $lt = new LiveTransmition($livet['id']);
                }

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

    public static function _getStats($live_servers_id = 0, $force_recreate = false)
    {
        global $global, $_getStats, $cacheNotFound;
        $timeName = "stats.json.php _getStats";
        TimeLogStart($timeName);
        if (empty($_REQUEST['name'])) {
            //_error_log("Live::_getStats {$live_servers_id} GET " . json_encode($_GET));
            //_error_log("Live::_getStats {$live_servers_id} POST " . json_encode($_POST));
            //_error_log("Live::_getStats {$live_servers_id} REQUEST " . json_encode($_REQUEST));
            $_REQUEST['name'] = "undefined";
        }
        //_error_log('Live::_getStats ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));
        $cacheName = "live_servers_id_{$live_servers_id}_{$_REQUEST['name']}_" . User::getId();
        $cacheHandler = new LiveCacheHandler();
        //$force_recreate = true;
        if (empty($force_recreate)) {
            if (!empty($_getStats[$live_servers_id][$_REQUEST['name']]) && is_object($_getStats[$live_servers_id][$_REQUEST['name']])) {
                //_error_log("Live::_getStats cached result 1 {$_REQUEST['name']} ");
                return $_getStats[$live_servers_id][$_REQUEST['name']];
            }
            $global['ignoreSessionCache'] = 1;
            $cacheNotFound = 0;
            $result = $cacheHandler->getCache($cacheName, 90);
            $global['ignoreSessionCache'] = 0;
            if (!empty($result)) {
                //_error_log("Live::_getStats cached result 2 {$_REQUEST['name']} {$cacheName}");
                return _json_decode($result);
            }
        }

        TimeLogEnd($timeName, __LINE__);
        _session_write_close();
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
        $stream = false;
        $lifeStream = [];
        $applicationName = self::getApplicationName();
        TimeLogEnd($timeName, __LINE__);
        if (empty($xml) || !is_object($xml)) {
            _error_log("_getStats XML is not an object live_servers_id=$live_servers_id");
        } else {
            //$obj->server = $xml->server;
            if (!empty($xml->server->application) && !is_array($xml->server->application)) {
                $application = $xml->server->application;
                $xml->server->application = [];
                $xml->server->application[] = $application;
            }
            TimeLogEnd($timeName, __LINE__);

            //_error_log("_getStats XML ". json_encode($xml->server));
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
            TimeLogEnd($timeName, __LINE__);
        }
        $obj->disableGif = $p->getDisableGifThumbs();

        TimeLogEnd($timeName, __LINE__);
        $REQUEST = $_REQUEST;

        $VideoPlaylistSchedulerIsEnabled = AVideoPlugin::isEnabledByName('VideoPlaylistScheduler');

        foreach ($lifeStream as $value) {
            unset($_REQUEST['playlists_id_live']);
            unset($_REQUEST['live_index']);
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
                $title = self::getTitleFromKey($value->name, $row['title']);
                $titleSet = __LINE__;
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
                    $titleSet = __LINE__;
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

                /*
                  $array = array(
                  'users_id'=>$users_id,
                  'title'=>$title,
                  'link'=>$link,
                  'imgJPG'=> $imgJPG,
                  'imgGIF'=> $imgGIF,
                  'type'=>'live',
                  'LiveUsersLabelLive'=>$LiveUsersLabelLive,
                  'uid'=>$uid,
                  'callback'=>'',
                  'startsOnDate'=>'',
                  'class'=>'live_' . $value->name,
                  'description'=>''
                  );
                 *
                 */

                $app = self::getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, 'live', $LiveUsersLabelLive, $uid, '', $uid, 'live_' . $value->name);
                $app['live_servers_id'] = $live_servers_id;
                $app['key'] = $value->name;
                $app['isPrivate'] = self::isPrivate($app['key']);
                $app['isPasswordProtected'] = self::isPasswordProtected($app['key']);
                $app['isRebroadcast'] = self::isRebroadcast($app['key']);
                $app['method'] = 'Live::_getStats';
                $app['titleSet'] = $titleSet . ' => ' . $app['title'];
                //var_dump($app['isPrivate'],$app['key']);exit;
                if (!self::isApplicationListed($app['key']) || ($VideoPlaylistSchedulerIsEnabled && VideoPlaylistScheduler::iskeyShowScheduledHidden($app['key']))) {
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
        $_REQUEST = $REQUEST;
        TimeLogEnd($timeName, __LINE__);

        $obj->countLiveStream = count($obj->applications);
        $obj->error = false;
        $_getStats[$live_servers_id][$_REQUEST['name']] = $obj;
        //_error_log("Live::_getStats NON cached result {$_REQUEST['name']} " . json_encode($obj));
        $cache = $cacheHandler->setCache($obj);
        //_error_log("Live::_getStats NOT cached {$cacheName} ".json_encode($cache));
        TimeLogEnd($timeName, __LINE__);
        return $obj;
    }

    static function getDescriptionFromKey($key, $description = '')
    {
        if (empty($key)) {
            return $description;
        }
        $row = LiveTransmition::keyExists($key);
        if (empty($row)) {
            return $description;
        }
        if (AVideoPlugin::isEnabledByName('PlayLists')) {
            $ps = Playlists_schedules::iskeyPlayListScheduled($key);
            if (!empty($ps)) {
                return Playlists_schedules::getDynamicDescription($ps['playlists_schedules']);
            }
        }
        if (AVideoPlugin::isEnabledByName('Rebroadcaster')) {
            $rb = Rebroadcaster::isKeyARebroadcast($key);;
            if (!empty($rb) && !empty($rb['videos_id'])) {
                $video = new Video('', '', $rb['videos_id']);
                return $video->getDescription();
            }
        }
        if (empty($description)) {
            $description = $row['description'];
        }

        return $description;
    }

    static function getTitleFromKey($key, $title = '')
    {
        if (empty($key)) {
            return $title;
        }
        $row = LiveTransmition::keyExists($key);
        if (empty($row)) {
            return $title;
        }
        if (AVideoPlugin::isEnabledByName('PlayLists')) {
            $ps = Playlists_schedules::iskeyPlayListScheduled($key);
            if (!empty($ps)) {
                return Playlists_schedules::getDynamicTitle($ps['playlists_schedules']);
            }
        }
        if (AVideoPlugin::isEnabledByName('Rebroadcaster')) {
            $rb = Rebroadcaster::isKeyARebroadcast($key);;
            if (!empty($rb) && !empty($rb['videos_id'])) {
                $video = new Video('', '', $rb['videos_id']);
                return $video->getTitle();
            }
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
        if (self::isPrivate(@$row['key'])) {
            $title = " <i class=\"fas fa-eye-slash\"></i> {$title}";
        }
        if (self::isPasswordProtected(@$row['key'])) {
            $title = " <i class=\"fas fa-lock\"></i> {$title}";
        }

        if (!empty($row['users_id'])) {
            $u = new User($row['users_id']);
            $status = $u->getStatus();
            if ($status !== 'a') {
                $title = " <i class=\"fas fa-user-alt-slash\"></i><!-- user status={$status} users_id={$row['users_id']}  --> {$title}";
            }
        }

        $parameters = self::getLiveParametersFromKey($key);
        $playlists_id_live = $parameters['playlists_id_live'];
        $live_index = $parameters['live_index'];
        if (!empty($live_index) && $live_index !== 'false') {
            $title .= " <small class=\"text-muted pull-right\">({$live_index})</small>";
        }

        return $title;
    }

    public static function isApplicationListed($key, $listItIfIsAdminOrOwner = true)
    {
        global $_isApplicationListed;
        if (!isset($_isApplicationListed)) {
            $_isApplicationListed = array();
        }
        if (empty($key)) {
            $_isApplicationListed[$key] = __LINE__;
        }
        if ($listItIfIsAdminOrOwner && User::isAdmin()) {
            $_isApplicationListed[$key] = __LINE__;
        }
        if (!isset($_isApplicationListed[$key])) {
            $row = LiveTransmition::keyExists($key);
            if (empty($row) || empty($row['users_id'])) {
                $_isApplicationListed[$key] = __LINE__;
                //} else if (!empty($row['scheduled'])) {
                //    $_isApplicationListed[$key] = __LINE__;
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

    public static function isPrivate($key)
    {
        if (!empty($key)) {
            $lt = LiveTransmition::getFromKey($key);
            if (empty($lt['public'])) {
                return true;
            }
        }
        return false;
    }

    public static function byPass()
    {
        if (preg_match('/socket_notification/', $_SERVER['SCRIPT_FILENAME'])) {
            return true;
        }

        return false;
    }

    public static function getLiveParametersFromKey($key)
    {
        if (empty($key)) {
            return ['key' => '', 'cleanKey' => '', 'live_index' => '', 'playlists_id_live' => 0];
        }
        $key = preg_replace('/[^a-z0-9_-]/i', '', $key);
        //$obj = AVideoPlugin::getObjectData('Live');
        $playlists_id_live = false;
        if (preg_match("/.*_([0-9]+)/", $key, $matches)) {
            if (!empty($matches[1])) {
                $playlists_id_live = intval($matches[1]);
            }
        }
        $live_index = '';

        if (preg_match("/[^-]+-([0-9a-z-]+)/i", $key, $matches)) {
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

    public static function getLiveIndexFromKey($key)
    {
        $parameters = self::getLiveParametersFromKey($key);
        return $parameters['live_index'];
    }

    public static function cleanUpKey($key)
    {
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

    public static function isAdaptiveTransmition($key)
    {
        // check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            $adaptive = ['hi', 'low', 'mid'];
            if (in_array($parts[1], $adaptive)) {
                return $parts[0];;
            }
        }
        return false;
    }

    public static function isPlayListTransmition($key)
    {
        // check if is a subtransmition
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    public static function isSubTransmition($key)
    {
        // check if is a subtransmition
        $parts = explode("-", $key);
        if (!empty($parts[1])) {
            return $parts[0];
        } else {
            return false;
        }
    }

    public static function getImage($users_id, $live_servers_id, $playlists_id_live = 0, $live_index = '')
    {
        $p = AVideoPlugin::loadPlugin("Live");
        if (self::isLive($users_id, $live_servers_id, $live_index)) {
            $url = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $live_index);
            $url = addQueryStringParameter($url, "playlists_id_live", $playlists_id_live);
        } else {
            $url = self::getOfflineImage(false);
        }
        return $url;
    }

    public static function getLatestKeyFromUser($users_id)
    {
        if (empty($users_id)) {
            return false;
        }
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        if (empty($latest)) {
            return false;
        }
        return $latest['key'];
    }

    public static function getLatest($active = false, $users_id = 0, $categories_id = 0)
    {
        $latest = LiveTransmitionHistory::getLatest('', null, $active, $users_id, $categories_id);
        if (empty($latest)) {
            return false;
        }
        return $latest;
    }

    public static function isLive($users_id, $live_servers_id = 0, $live_index = '', $force_recreate = false)
    {
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
            //_error_log("Live::isLive we could not found any active livestream for user $users_id, $live_servers_id");
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


    public static function isKeyLiveInStatsV2($key, $live_servers_id = 0, $live_index = '', $force_recreate = false, $doNotCheckDatabase = true)
    {
        global $_isLiveFromKey, $global, $_isLiveFromKeyLineFound;
        $_isLiveFromKeyLineFound = __LINE__;
        if (empty($key) || $key == '-1') {
            _error_log('Live::isKeyLiveInStats key is empty');
            $_isLiveFromKeyLineFound = __LINE__;
            return false;
        }

        if (!empty($global['disableIsKeyLiveInStats'])) {
            _error_log('disableIsKeyLiveInStats');
            $_isLiveFromKeyLineFound = __LINE__;
            return true;
        }
        $index = "$key, $live_servers_id,$live_index";
        if (!isset($_isLiveFromKey)) {
            $_isLiveFromKey = [];
        }

        if (empty($force_recreate) && isset($_isLiveFromKey[$index])) {
            //_error_log('Live::isKeyLiveInStats key is already set');
            $_isLiveFromKeyLineFound = __LINE__;
            return $_isLiveFromKey[$index];
        }

        //_error_log("Live::isLiveFromKey($key, $live_servers_id, $live_index, $force_recreate )");
        $o = AVideoPlugin::getObjectData("Live");
        if ($doNotCheckDatabase) {
            if (empty($o->server_type->value) || !empty($live_servers_id)) {
                //_error_log("Live::isLiveFromKey return LiveTransmitionHistory::isLive($key, $live_servers_id)");
                $_isLiveFromKeyLineFound = __LINE__;
                return LiveTransmitionHistory::isLive($key, $live_servers_id);
            }
        }

        $stats = Live::getStatsApplications($force_recreate);
        $_isLiveFromKey[$index] = false;
        $keyWithIndex = Live::cleanUpKey($key);
        if (!empty($live_index)) {
            $keyC = Live::cleanUpKey($key);
            $keyWithIndex = "$keyC-$live_index";
        }
        foreach ($stats as $value) {

            if (!is_array($value) || empty($value) || empty($value['key'])) {
                continue;
            }

            $namesFound[] = "({$value['key']})";
            if (preg_match("/{$keyWithIndex}.*/", $value['key'])) {
                if (empty($live_servers_id)) {
                    $_isLiveFromKey[$index] = true;
                    break;
                } else {
                    if (intval(@$value['live_servers_id']) == $live_servers_id) {
                        $_isLiveFromKey[$index] = true;
                        break;
                    }
                }
            } else {
                _error_log("Live::isKeyLiveInStatsV2 /{$keyWithIndex}.*/, {$value['key']}");
            }
        }
        $_isLiveFromKeyLineFound = __LINE__;
        return $_isLiveFromKey[$index];
    }

    public static function isKeyLiveInStats($key, $live_servers_id = 0, $live_index = '', $force_recreate = false, $doNotCheckDatabase = true)
    {
        return self::isKeyLiveInStatsv2($key, $live_servers_id, $live_index, $force_recreate, $doNotCheckDatabase);
        global $_isLiveFromKey, $global;
        if (empty($key) || $key == '-1') {
            _error_log('Live::isKeyLiveInStats key is empty');
            return false;
        }

        if (!empty($global['disableIsKeyLiveInStats'])) {
            _error_log('disableIsKeyLiveInStats');
            return true;
        }
        $index = "$key, $live_servers_id,$live_index";
        if (!isset($_isLiveFromKey)) {
            $_isLiveFromKey = [];
        }

        if (empty($force_recreate) && isset($_isLiveFromKey[$index])) {
            //_error_log('Live::isKeyLiveInStats key is already set');
            return $_isLiveFromKey[$index];
        }

        //_error_log("Live::isLiveFromKey($key, $live_servers_id, $live_index, $force_recreate )");
        $o = AVideoPlugin::getObjectData("Live");
        if ($doNotCheckDatabase) {
            if (empty($o->server_type->value) || !empty($live_servers_id)) {
                //_error_log("Live::isLiveFromKey return LiveTransmitionHistory::isLive($key, $live_servers_id)");
                return LiveTransmitionHistory::isLive($key, $live_servers_id);
            }
        }

        //_error_log("Live::isLiveFromKey($key, $live_servers_id, $live_index, $force_recreate )");
        //_error_log('getStats execute getStats: ' . __LINE__ . ' ' . __FILE__);
        //$json = getStatsNotifications($force_recreate);
        //_error_log('getStats execute getStats: ' . ($force_recreate?'force_recreate':'DO NOT force_recreate'));

        $json = self::getStats($force_recreate);
        //_error_log("Live::isKeyLiveInStats:self::getStats " . json_encode($json));
        //var_dump($json);
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
                            break 2;
                        } else {
                            if (intval(@$value['live_servers_id']) == $live_servers_id) {
                                $_isLiveFromKey[$index] = true;
                                break 2;
                            }
                        }
                    }
                }
                //var_dump($item->hidden_applications);
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
                                break 2;
                            } else {
                                if (intval(@$value['live_servers_id']) == $live_servers_id) {
                                    $_isLiveFromKey[$index] = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            //_error_log("Live::isLiveFromKey namesFound " . json_encode($namesFound));
        } else {
            _error_log("Live::isLiveFromKey Stats respond empty");
        }
        if (empty($_isLiveFromKey[$index])) {
            //_error_log("Live::isLiveFromKey is NOT online [{$key}]");
            //_error_log(debug_backtrace());
        } else {
            //_error_log("Live::isLiveFromKey is online [{$key}]");
        }
        return $_isLiveFromKey[$index];
    }

    public static function isLiveAndIsReadyFromKey($key, $live_servers_id = 0, $live_index = '', $force_recreate = false)
    {
        if (isBot()) {
            return true;
        }
        global $_isLiveAndIsReadyFromKey;

        if (!isset($_isLiveAndIsReadyFromKey)) {
            $_isLiveAndIsReadyFromKey = [];
        }
        $name = "isLiveAndIsReadyFromKey{$key}_{$live_servers_id}";
        $cacheHandler = new LiveCacheHandler();
        if (empty($force_recreate)) {
            if (isset($_isLiveAndIsReadyFromKey[$name])) {
                //_error_log("isLiveAndIsReadyFromKey::key: {$key} isset");
                return $_isLiveAndIsReadyFromKey[$name];
            }
            $cache = $cacheHandler->getCache($name, 90);

            //_error_log("isLiveAndIsReadyFromKey::key: {$key} get cache  ".json_encode(array($cache, $name)));
        } else {
            //_error_log("isLiveAndIsReadyFromKey($key, $live_servers_id, $live_index, $force_recreate) force_recreate " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            $cacheHandler->setSuffix($name);
        }
        if (!empty($cache)) {
            $json = _json_decode($cache);
        }

        if (!empty($json) && is_object($json)) {
            //_error_log("isLiveAndIsReadyFromKey::key: {$key} getCache");
            $_isLiveAndIsReadyFromKey[$name] = $json->result;
        } else {
            //_error_log("isLiveAndIsReadyFromKey::key: {$key} $name  ".json_encode(array($cache, $json)));
            $json = new stdClass();
            $key = self::getLiveKeyFromRequest($key, $live_index);
            _error_log("isLiveAndIsReadyFromKey::key: {$key} checking live_servers_id={$live_servers_id} " . (@$_SERVER['HTTP_USER_AGENT']));
            $isLiveFromKey = self::isKeyLiveInStats($key, $live_servers_id, $live_index, $force_recreate);
            $_isLiveAndIsReadyFromKey[$name] = true;
            if (empty($isLiveFromKey)) {
                //_error_log("isLiveAndIsReadyFromKey the key {$key} is not present on the stats live_servers_id=$live_servers_id ".json_encode($isLiveFromKey));
                $_isLiveAndIsReadyFromKey[$name] = false;
            } else {
                $ls = @$_REQUEST['live_servers_id'];
                $_REQUEST['live_servers_id'] = $live_servers_id;
                $m3u8 = self::getM3U8File($key, false, true);
                if (isDocker()) {
                    $parts = explode('/live/', $m3u8);
                    $m3u8 = getDockerInternalURL() . 'live/' . $parts[1];
                }
                $_REQUEST['live_servers_id'] = $ls;
                //_error_log('getStats execute isURL200: ' . __LINE__ . ' ' . __FILE__);
                $is200 = isValidM3U8Link($m3u8, true);
                //_error_log("isLiveAndIsReadyFromKey the key {$key} m3u8=$m3u8 is200=".json_encode($is200));
                if (empty($is200)) {
                    //_error_log("isLiveAndIsReadyFromKey the m3u8 file is not present {$m3u8} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
                    $_isLiveAndIsReadyFromKey[$name] = false;
                }
            }

            $json->result = $_isLiveAndIsReadyFromKey[$name];
            $saved = $cacheHandler->setCache($json);
            //_error_log("isLiveAndIsReadyFromKey::key: {$key} end  ".json_encode(array($saved, $json)));
        }

        //_error_log("isLiveAndIsReadyFromKey the key {$key} ".json_encode($_isLiveAndIsReadyFromKey[$name]));
        return $_isLiveAndIsReadyFromKey[$name];
    }

    public static function getOnlineLivesFromUser($users_id)
    {
        $key = self::getLiveKey($users_id);
        return self::getOnlineLivesFromKey($key);
    }

    public static function getOnlineLivesFromKey($key)
    {
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

    public static function keyIsFromPlaylist($key)
    {
        $parts = explode("_", $key);
        if (empty($parts[1])) {
            return false;
        }
        return ['key' => $parts[0], 'playlists_id' => $parts[1]];
    }
    /**
     * @return string
     */
    public static function getLiveKey($users_id)
    {
        $lt = new LiveTransmition(0);
        $lt->loadByUser($users_id);
        return $lt->getKey();
    }

    public static function getLiveKeyFromUser($users_id, $live_index = '', $playlists_id_live = '')
    {
        $key = self::getLiveKey($users_id);
        return self::getLiveKeyFromRequest($key, $live_index, $playlists_id_live);
    }

    public static function getLiveKeyFromRequest($key, $live_index = '', $playlists_id_live = '')
    {
        if (strpos($key, '-') === false) {
            if (!empty($live_index)) {
                $key .= '-' . preg_replace('/[^0-9a-z-]/i', '', $live_index);
            } elseif (!empty($_REQUEST['live_index'])) {
                $key .= '-' . preg_replace('/[^0-9a-z-]/i', '', $_REQUEST['live_index']);
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

    public function getImageGif($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '')
    {
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

    private static function getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, $posterType)
    {
        global $global, $getPosterImageLive;

        if(empty($getPosterImageLive)){
            $getPosterImageLive = array();
        }
        $index = "$users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, $posterType";
        if(isset($getPosterImageLive[$index])){
            return $getPosterImageLive[$index];
        }
        $getPosterImageLive[$index] = false;
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
        $file = self::_getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, $posterType);
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
        $getPosterImageLive[$index] = $file;
        return $file;
    }

    public static function getRegularPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        return self::getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, self::$posterType_regular);
    }

    public static function getPrerollPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        return self::getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, self::$posterType_preroll);
    }
    /**
     * @return object
     */
    public static function getPrerollPosterImageTimes($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        global $global;
        $path = self::getPrerollPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id);
        $jsonPath = $global['systemRootPath'] . str_replace('.jpg', '.json', $path);

        if (file_exists($jsonPath)) {
            $times = _json_decode($jsonPath);
        }
        if (empty($times)) {
            $times = new stdClass();
            $times->liveImgCloseTimeInSeconds = 10;
            $times->liveImgTimeInSeconds = 30;
        }
        return $times;
    }

    public static function getPostrollPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        return self::getPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id, self::$posterType_postroll);
    }

    public static function getPostrollPosterImageTimes($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        global $global;
        $path = self::getPostrollPosterImage($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id);
        $jsonPath = $global['systemRootPath'] . str_replace('.jpg', '.json', $path);
        if (file_exists($jsonPath)) {
            $times = _json_decode($jsonPath);
        }
        if (empty($times)) {
            $times = new stdClass();
            $times->liveImgCloseTimeInSeconds = 30;
            $times->liveImgTimeInSeconds = 30;
        }
        return $times;
    }

    public static function posterExists($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id, $posterType)
    {
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

        $file = self::_getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id, $posterType);
        return file_exists("{$global['systemRootPath']}{$file}");
    }

    public static function prerollPosterExists($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        return self::posterExists($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id, self::$posterType_preroll);
    }

    public static function postrollPosterExists($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        return self::posterExists($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id, self::$posterType_postroll);
    }

    public static function getPosterImageOrFalse($users_id, $live_servers_id)
    {
        $poster = self::getRegularPosterImage($users_id, $live_servers_id, 0, 0);
        if (preg_match('/OnAir.jpg$/', $poster)) {
            return false;
        }

        return $poster;
    }

    public function getLivePosterImage($users_id, $live_servers_id = 0, $playlists_id_live = 0, $live_index = '', $format = 'jpg', $live_schedule_id = 0)
    {
        global $global;

        return self::getLivePosterImageRelativePath($users_id, $live_servers_id, 0, $playlists_id_live, $live_index, $format, $live_schedule_id, true);
    }

    public static function getLivePosterImageRelativePath($users_id, $live_servers_id, $ppv_schedule_id, $playlists_id_live = 0, $live_index = '', $format = 'jpg', $live_schedule_id = 0, $returnURL = false)
    {
        global $global;
        if (empty($live_servers_id)) {
            $live_servers_id = self::getCurrentLiveServersId();
        }
        $live_schedule_id = intval($live_schedule_id);
        if (self::isLiveThumbsDisabled()) {
            if ($format !== 'jpg') {
                return false;
            }
            $file = self::_getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id);

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

    public static function isLiveThumbsDisabled()
    {
        $obj = AVideoPlugin::getDataObject("Live");
        if (!empty($obj->disableLiveThumbs)) {
            return true;
        }
        return false;
    }

    public static function getPosterThumbsImage($users_id, $live_servers_id, $cominsoon = false)
    {
        global $global;
        if (empty($_REQUEST['live_schedule'])) {
            $file = self::_getPosterThumbsImage($users_id, $live_servers_id);
        } else {
            $array = Live_schedule::getPosterPaths($_REQUEST['live_schedule'], 0);
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

    public static function getPoster($users_id, $live_servers_id, $key)
    {
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
            //_error_log("getPoster empty activity");
            $_getPoster[$index] = $poster;
            return $_getPoster[$index];
        }
        $parameters = self::getLiveParametersFromKey($lh['key']);
        $live_index = $parameters['live_index'];
        $playlists_id_live = $parameters['playlists_id_live'];
        if (self::isLiveAndIsReadyFromKey($lh['key'], $lh['live_servers_id'])) {
            $_getPoster[$index] = self::getLivePosterImageRelativePath($users_id, $live_servers_id, 0, $playlists_id_live, $live_index);
            //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' is live and ready');
            return $_getPoster[$index];
        } else {
            if (self::isKeyLiveInStats($lh['key'], $lh['live_servers_id'])) {
                //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is in the stats');
                $_getPoster[$index] = self::getRegularPosterImage($users_id, $live_servers_id, 0, 0);
            } else {
                //_error_log('getImage: ' . ("[{$lh['key']}, {$lh['live_servers_id']}]") . ' key is NOT in the stats');
                $_getPoster[$index] = $poster;
            }
            return $_getPoster[$index];
        }
    }

    public static function getOfflineImage($includeURL = true)
    {
        global $global;
        $img = "plugin/Live/view/Offline.jpg";
        if ($includeURL) {
            $img = getURL($img);
        }
        return $img;
    }

    public static function getOnAirImage($includeURL = true)
    {
        global $global;
        $img = "plugin/Live/view/OnAir.jpg";
        if ($includeURL) {
            $img = getURL($img);
        }
        return $img;
    }

    public static function getComingSoonImage($includeURL = true)
    {
        global $global;
        $img = "plugin/Live/view/ComingSoon.jpg";
        if ($includeURL) {
            $img = getURL($img);
        }
        return $img;
    }

    public static function _getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id = 0, $posterType = 0)
    {

        $users_id = intval($users_id);
        $ppv_schedule_id = intval($ppv_schedule_id);
        $live_servers_id = intval($live_servers_id);
        $live_schedule_id = intval($live_schedule_id);
        $posterType = intval($posterType);

        if (!empty($live_schedule_id) || !empty($ppv_schedule_id)) {
            $paths = Live_schedule::getPosterPaths($live_schedule_id, $ppv_schedule_id, $posterType);
            return $paths['relative_path'];
        }
        $type = '';
        if (!empty($posterType)) {
            $type = "_{$posterType}_";
        }
        $file = "videos/userPhoto/Live/user_{$users_id}_bg_{$live_servers_id}{$type}.jpg";
        return $file;
    }

    public static function _getPosterThumbsImage($users_id, $live_servers_id, $posterType = 0)
    {
        $posterType = intval($posterType);
        $type = '';
        if (!empty($posterType)) {
            $type = "_{$posterType}_";
        }
        $file = "videos/userPhoto/Live/user_{$users_id}_thumbs_{$live_servers_id}{$type}.jpg";
        return $file;
    }

    public static function _on_publish($liveTransmitionHistory_id, $isReconnection)
    {
        $obj = AVideoPlugin::getDataObject("Live");
        if (empty($obj->disableRestream)) {
            self::restream($liveTransmitionHistory_id);
        }
        $lt = new LiveTransmitionHistory($liveTransmitionHistory_id);
        $users_id = $lt->getUsers_id();
        $live_servers_id = $lt->getLive_servers_id();
        _error_log("on_publish: liveTransmitionHistory_id={$liveTransmitionHistory_id} users_id={$users_id} live_servers_id={$live_servers_id} isReconnection=$isReconnection ");
        AVideoPlugin::on_publish($users_id, $live_servers_id, $liveTransmitionHistory_id, $lt->getKey(), $isReconnection);
    }

    public static function deleteStatsCache($clearFirstPage = false)
    {
        global $getStatsLive, $_getStats, $getStatsObject, $_getStatsNotifications, $__getAVideoCache, $_isLiveFromKey, $_isLiveAndIsReadyFromKey;
        $cacheDir = ObjectYPT::getTmpCacheDir() . 'getStats/';
        _error_log("deleteStatsCache: {$cacheDir} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        rrmdir($cacheDir);
        if (class_exists('CachesInDB')) {
            $cacheHandler = new LiveCacheHandler();
            $cacheHandler->deleteCache();
        }
        if ($clearFirstPage) {
            clearCache(true);
        }
        // temporary solution to when you go online
        //ObjectYPT::deleteALLCache();
        //isURL200Clear();
        unset($__getAVideoCache);
        unset($getStatsLive);
        unset($getStatsObject);
        unset($_getStats);
        unset($_getStatsNotifications);
        unset($_isLiveFromKey);
        unset($_isLiveAndIsReadyFromKey);
    }

    public static function getReverseRestreamObject($m3u8, $users_id, $live_servers_id = -1, $forceIndex = false)
    {
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

    public static function getRestreamObject($liveTransmitionHistory_id)
    {
        if (empty($liveTransmitionHistory_id)) {
            return false;
        }
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        if (empty($lth->getKey())) {
            return false;
        }

        $rows = Live_restreams::getAllFromUser($lth->getUsers_id());
        $restreamRowItems = array();
        foreach ($rows as $value) {
            $value['stream_url'] = addLastSlash($value['stream_url']);
            $restreamsDestination = "{$value['stream_url']}{$value['stream_key']}";
            $id = $value['id'];
            $live_url = $value['live_url'];

            $restreamRowItems[$value['id']] = self::gettRestreamRowItem($restreamsDestination, $id, $live_url);
        }

        return self::_getRestreamObject($liveTransmitionHistory_id, $restreamRowItems);
    }

    public static function _getRestreamObject($liveTransmitionHistory_id, $restreamRowItems)
    {
        if (empty($liveTransmitionHistory_id)) {
            return false;
        }
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        if (empty($lth->getKey())) {
            return false;
        }
        $_REQUEST['live_servers_id'] = $lth->getLive_servers_id();
        $obj = new stdClass();
        $key = $lth->getKey();

        $obj->m3u8 = self::getM3U8File($key, true);
        $obj->restreamerURL = self::getRestreamer($lth->getLive_servers_id());
        $obj->restreamsDestinations = [];
        $obj->token = getToken(60);
        $obj->users_id = $lth->getUsers_id();
        $obj->liveTransmitionHistory_id = $liveTransmitionHistory_id;
        $obj->key = $key;

        foreach ($restreamRowItems as $key => $value) {
            $obj->restreamsDestinations[$key] = $value['restreamsDestinations'];
            if (!empty($value['restreamsToken'])) {
                $obj->restreamsToken[$key] = $value['restreamsToken'];
            }
            if (!empty($value['live_url'])) {
                $obj->live_url[$key] = $value['live_url'];
            }
        }
        return $obj;
    }


    public static function gettRestreamRowItem($restreamsDestination, $id, $live_url)
    {
        return array('restreamsDestinations' => $restreamsDestination, 'restreamsToken' => encryptString($id),  'live_url' => $live_url);
    }



    public static function reverseRestream($m3u8, $users_id, $live_servers_id = -1, $forceIndex = false)
    {
        _error_log("Live:reverseRestream start");
        $obj = self::getReverseRestreamObject($m3u8, $users_id, $live_servers_id, $forceIndex);
        _error_log("Live:reverseRestream obj " . _json_encode($obj));
        return self::sendRestream($obj);
    }

    public static function restream($liveTransmitionHistory_id, $live_restreams_id = 0, $test = false)
    {
        if (empty($test)) {
            outputAndContinueInBackground();
        }
        $obj = self::getRestreamObject($liveTransmitionHistory_id);
        $obj->live_restreams_id = $live_restreams_id;
        if ($test) {
            $obj->test = 1;
        }
        return self::sendRestream($obj);
    }


    public static function restreamToDestination($liveTransmitionHistory_id, $restreamsDestination)
    {
        _error_log("restreamToDestination($liveTransmitionHistory_id, $restreamsDestination)");
        $restreamRowItems = array();
        $id = 0;
        $live_url = '';
        $restreamRowItems[$id] = self::gettRestreamRowItem($restreamsDestination, $id, $live_url);
        $obj = self::_getRestreamObject($liveTransmitionHistory_id, $restreamRowItems);
        if(empty($obj)){
            return false;
        }
        $obj->live_restreams_id = 0;
        return self::sendRestream($obj);
    }

    private static function sendRestream($obj, $doNotNotifyStreamer = false)
    {
        _error_log("Live:sendRestream start");
        try {
            if (empty($obj)) {
                _error_log("Live:sendRestream object is empty");
                return false;
            }
            set_time_limit(30);

            $obj->responseToken = encryptString(array('users_id' => $obj->users_id, 'time' => time(), 'liveTransmitionHistory_id' => $obj->liveTransmitionHistory_id));
            $obj->doNotNotifyStreamer = $doNotNotifyStreamer;

            $data_string = json_encode($obj);
            _error_log("Live:sendRestream ({$obj->restreamerURL}) {$data_string} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
            //open connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
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
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string),
                ]
            );
            $info = curl_getinfo($ch);
            $output = curl_exec($ch);
            curl_close($ch);
            _error_log("Live:sendRestream complete " . json_encode(array($output)));
            return true;
        } catch (Exception $exc) {
            _error_log("Live:sendRestream " . $exc->getTraceAsString());
            return false;
        }
        return false;
    }

    public static function canScheduleLive()
    {
        if (!User::canStream()) {
            return false;
        }

        $obj = AVideoPlugin::getObjectDataIfEnabled("Live");
        if (!empty($obj->disable_live_schedule)) {
            return false;
        }

        return true;
    }

    public static function canStreamWithMeet()
    {
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

    public function getUploadMenuButton()
    {
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

    public static function getAllVideos($status = "", $showOnlyLoggedUserVideos = false, $activeUsersOnly = true)
    {
        global $global, $config, $advancedCustom;
        if (AVideoPlugin::isEnabledByName("VideoTags")) {
            if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
            }
        }
        $status = str_replace("'", "", $status);

        $sql = "SELECT STRAIGHT_JOIN  u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, v.modified as videoModified "
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

        if ($status == Video::SORT_TYPE_PUBLICONLY) {
            $sql .= " AND v.public = 1 ";
        } elseif (!empty($status)) {
            $sql .= " AND v.`public` = '{$status}'";
        }

        if (!empty($_REQUEST['catName'])) {
            $catName = ($_REQUEST['catName']);
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
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    public static function finishLive($key)
    {
        $lh = LiveTransmitionHistory::finish($key);
    }

    public static function updateVideosUserGroup($videos_id, $key)
    {
        $lt = LiveTransmition::keyExists($key);
        if (!empty($lt)) {
            $lt = new LiveTransmition($lt['id']);
            $groups = $lt->getGroups();
            if (!empty($groups)) {
                UserGroups::updateVideoGroups($videos_id, $groups);
            }
        }
    }

    public static function notifySocketStats($callBack = 'socketLiveONCallback', $array = [])
    {
        $array['iskeyPlayListScheduled'] = false;
        if (!empty($array['key'])) {
            if (AVideoPlugin::isEnabledByName('PlayLists')) {
                $array['iskeyPlayListScheduled'] = Playlists_schedules::iskeyPlayListScheduled($array['key']);
            }
        }
        //clearAllUsersSessionCache();
        if (empty($array['stats'])) {
            $array['stats'] = getStatsNotifications();
        }
        _error_log("NGINX Live::on_publish_socket_notification sendSocketMessageToAll Start {$callBack}");
        $socketObj = sendSocketMessageToAll($array, $callBack);
        _error_log("NGINX Live::on_publish_socket_notification SocketMessageToAll END {$callBack}");
        return $socketObj;
    }

    public static function getImageType($content)
    {
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

    public static function isLiveImage($content)
    {
        return self::getImageType($content) === LiveImageType::$LIVE;
    }

    public static function isDefaultImage($content)
    {
        $type = self::getImageType($content);
        return $type === LiveImageType::$ONAIRENCODER || $type === LiveImageType::$ONAIR || $type === LiveImageType::$OFFLINE || $type === LiveImageType::$DEFAULTGIF;
    }

    public static function iskeyOnline($key)
    {
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

    public static function getValidNotOnlineLiveIndex($key, $live_index)
    {
        if (empty($live_index)) {
            return 1;
        }
        if (AVideoPlugin::isEnabled('VideoPlaylistScheduler') && VideoPlaylistScheduler::iskeyShowScheduledHidden("$key-$live_index")) {
            // it is a VideoPlaylistScheduler do not change it
            return $live_index;
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

    public static function getLatestValidNotOnlineLiveIndex($key)
    {
        $live_index = LiveTransmitionHistory::getLatestIndexFromKey($key);
        $live_index = self::getValidNotOnlineLiveIndex($key, $live_index);
        return $live_index;
    }

    public static function getLivesOnlineFromKey($key)
    {
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

    public static function getFirstLiveOnlineFromKey($key)
    {
        $onliveApplications = self::getLivesOnlineFromKey($key);
        if (!empty($onliveApplications[0])) {
            return $onliveApplications[0];
        }
        return false;
    }

    public static function getUserHash($users_id)
    {
        return encryptString(_json_encode(['users_id' => $users_id, 'time' => time()]));
    }

    public static function decryptHash($hash)
    {
        $string = decryptString($hash);
        $json = _json_decode($string);
        return object_to_array($json);
    }

    public static function getServerURL($key, $users_id, $short = true)
    {
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

    public static function passwordIsGood($key)
    {
        $row = LiveTransmition::getFromKey($key, true);
        if (empty($row) || empty($row['id']) || empty($row['users_id'])) {
            return true;
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

    public static function checkIfPasswordIsGood($key)
    {
        global $global;
        if (!self::passwordIsGood($key)) {
            $_REQUEST['key'] = $key;
            include $global['systemRootPath'] . 'plugin/Live/confirmLivePassword.php';
            exit;
        }
        return true;
    }

    public static function getMediaSession($key, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        $lt = LiveTransmition::getFromKey($key);
        $posters = self::getMediaSessionPosters($lt['users_id'], $lt['live_servers_id'], $lt['live_schedule_id'], $ppv_schedule_id);
        if (empty($posters)) {
            $posters = array();
        }
        //var_dump($posters);exit;
        $category = Category::getCategory($lt['categories_id']);
        $MediaMetadata = new stdClass();

        $MediaMetadata->title = $lt['title'];
        $MediaMetadata->artist = User::getNameIdentificationById($lt['users_id']);
        $MediaMetadata->album = $category['name'];
        $MediaMetadata->artwork = array();
        foreach ($posters as $key => $value) {
            $MediaMetadata->artwork[] = array('src' => $value['url'], 'sizes' => "{$key}x{$key}", 'type' => 'image/jpg');
        }
        return $MediaMetadata;
    }

    public static function getMediaSessionPosters($users_id, $live_servers_id, $live_schedule_id, $ppv_schedule_id)
    {
        global $global;

        $file = self::_getPosterImage($users_id, $live_servers_id, $ppv_schedule_id, $live_schedule_id);
        $imagePath = $global['systemRootPath'] . $file;
        //var_dump($imagePath);exit;
        if (file_exists($imagePath)) {
            return getMediaSessionPosters($imagePath);
        }
        return false;
    }

    public static function getInfo($key, $live_servers_id = null, $live_index = '', $playlists_id_live = '', $doNotCheckDatabase = true)
    {
        global $global;
        //var_dump($key, $live_index);exit;
        //var_dump($live_servers_id);exit;
        $lso = new LiveStreamObject($key, $live_servers_id, $live_index, $playlists_id_live);

        $keyWithIndex = $lso->getKeyWithIndex();
        _error_log("Live::getInfo LiveStreamObject=$keyWithIndex => $key, $live_servers_id, $live_index, $playlists_id_live");
        //var_dump($key, $live_index, $keyWithIndex);exit;
        $key = $lso->getKey();
        $array = array(
            'return_line' => __LINE__,
            'key' => $key,
            'keyWithIndex' => $keyWithIndex,
            'live_schedule_id' => 0,
            'users_id' => 0,
            'live_servers_id' => $live_servers_id,
            'live_index' => $live_index,
            'playlists_id_live' => $playlists_id_live,
            'history' => false,
            'isLive' => false,
            'isFinished' => false,
            'finishedDateTime' => __('Not finished'),
            'finishedSecondsAgo' => 0,
            'finishedHumanAgo' => __('Not finished'),
            'isStarded' => false,
            'startedDateTime' => __('Not started'),
            'startedSecondsAgo' => 0,
            'startedHumanAgo' => __('Not started'),
        );

        $lt = LiveTransmition::getFromKey($key);
        if (empty($lt)) {
            $array['return_line'] = __LINE__;
            return $array;
        }
        $array['transmission'] = $lt;
        $array['live_schedule_id'] = $lt['live_schedule_id'];
        $array['users_id'] = $lt['users_id'];

        $otherLivesSameUser = LiveTransmitionHistory::getActiveLiveFromUser($array['users_id'], '', '', 100);

        $array['otherLivesSameUser'] = array();
        foreach ($otherLivesSameUser as $value) {
            if ($value['key'] !== $keyWithIndex) {
                $array['otherLivesSameUser'][] = $value;
            }
        }
        $lth = LiveTransmitionHistory::getLatest($keyWithIndex, $live_servers_id);
        if (empty($lth)) {
            _error_log("Live::getInfo empty latest LiveTransmitionHistory::getLatest($keyWithIndex, $live_servers_id)");
            $array['return_line'] = __LINE__;
            return $array;
        }
        $isLiveAndIsReadyFromKey = Live::isLiveAndIsReadyFromKey($lth['key'], $live_servers_id);
        $isStatsAccessible = self::isStatsAccessible($live_servers_id);
        //var_dump('Line: '.__LINE__, 'File: '.__FILE__, $isLiveAndIsReadyFromKey, $isStatsAccessible, $global['isStatsAccessible']);exit;
        if (empty($isLiveAndIsReadyFromKey) && $isStatsAccessible) {
            $array['isLive'] = false;
        } else {
            $array['isLive'] = true;
        }
        $array['isLiveAndIsReadyFromKey'] = $isLiveAndIsReadyFromKey;
        $array['isStatsAccessible'] = $isStatsAccessible;

        $array['history'] = $lth;
        $array['isStarded'] = true;
        $array['startedDateTime'] = $lth['created'];
        $array['startedSecondsAgo'] = secondsIntervalFromNow($lth['created'], true);
        $array['startedHumanAgo'] = __('Started') . ' ' . humanTimingAgo($lth['created']);

        if (!empty($lth['finished'])) {
            $isKeyLiveInStats = self::isKeyLiveInStats($key, $live_servers_id, $live_index, false, $doNotCheckDatabase);
            if (empty($isKeyLiveInStats)) {
                $array['isLive'] = false;
                $array['isFinished'] = true;
                $array['finishedDateTime'] = $lth['finished'];
                $array['finishedSecondsAgo'] = secondsIntervalFromNow($lth['finished'], true);
                $array['finishedHumanAgo'] = __('Finished') . ' ' . humanTimingAgo($lth['finished']);
            } else {
                LiveTransmitionHistory::unfinishFromTransmitionHistoryId($lth['id']);
            }
        }

        $array['displayTime'] = '';
        if ($array['isFinished']) {
            $array['displayTime'] = $array['finishedHumanAgo'];
            if (!empty($lt['scheduled_time'])) {
                $time = getTimeInTimezone($lt['scheduled_time'], $lt['timezone']);
                $displayTime = strtotime($array['finishedDateTime']);

                //var_dump($time, $displayTime, $lt['scheduled_time'], $lt['timezone'], $array['finishedDateTime']);exit;
                if ($time > $displayTime) {
                    $array['displayTime'] = __('Will start in') . ' ' . humanTiming($time) . ", {$lt['scheduled_time']}, {$lt['timezone']}";
                }
            }
        } else if ($array['isStarded']) {
            $array['displayTime'] = $array['startedHumanAgo'];
        }

        $array['return_line'] = __LINE__;
        if (empty($array['isLive'])) {
            _error_log("Live::getInfo LiveTransmitionHistory::finishFromTransmitionHistoryId({$lth['id']}) isLiveAndIsReadyFromKey({$lth['key']}, {$live_servers_id}) [{$lth['id']}]");
            LiveTransmitionHistory::finishFromTransmitionHistoryId($lth['id']);
        }
        return $array;
    }

    public static function setLiveScheduleReminder($live_schedule_id, $minutesEarlier = 0, $deleteIfExists = false)
    {

        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->deleted = false;
        $obj->scheduler_commands_id = 0;
        $obj->deleted_id = 0;

        if (!User::isLogged()) {
            $obj->msg = __('Must be logged');
            return $obj;
        }

        if (!AVideoPlugin::isEnabledByName('Scheduler')) {
            $obj->msg = 'Scheduler is disabled';
            return $obj;
        }

        if (empty($live_schedule_id)) {
            $obj->msg = 'live_schedule_id cannot be empty';
            return $obj;
        }

        $ls = new Live_schedule($live_schedule_id);
        $to_users_id = User::getId();
        $users_id = Live_schedule::getUsers_idOrCompany($live_schedule_id);

        if (empty($users_id)) {
            $obj->msg = 'users_id cannot be empty';
            return $obj;
        }

        $date_to_execute = strtotime($ls->getScheduled_time() . " -{$minutesEarlier} minutes");

        $reminders = self::getLiveScheduleReminders($live_schedule_id);
        foreach ($reminders as $value) {
            if (strtotime($value['date_to_execute']) === $date_to_execute) {
                if ($deleteIfExists) {
                    $e = new Scheduler_commands($value['id']);
                    $obj->deleted_id = $value['id'];
                    $obj->deleted = $e->delete();
                    $obj->error = empty($obj->deleted);
                } else {
                    $obj->msg = __('Reminder already set');
                }
                return $obj;
            }
        }


        $objLive = AVideoPlugin::getDataObject('Live');
        $emailEmailBody = __($objLive->reminderText->value, true);
        $UserIdentification = User::getNameIdentification();
        $liveTitle = Live::getTitleFromUsers_Id($users_id);
        $link = Live::getLinkToLiveFromUsers_id($users_id);
        $timeToStart = humanTiming($ls->getScheduled_time());
        $emailEmailBody = str_replace(array('{UserIdentification}', '{liveTitle}', '{link}', '{timeToStart}'), array($UserIdentification, $liveTitle, $link, $timeToStart), $emailEmailBody);

        $emailTo = $to_users_id;
        $emailSubject = $ls->getTitle() . ' - ' . __('Live reminder');
        $emailFrom = $users_id;
        $emailFromName = User::getNameIdentificationById($users_id);

        $type = self::getLiveScheduleReminderBaseNameType($live_schedule_id, $minutesEarlier);
        $type = "{$type}_{$users_id}";

        $obj->scheduler_commands_id = Scheduler::addSendEmail($date_to_execute, $emailTo, $emailSubject, $emailEmailBody, $emailFrom, $emailFromName, $type);

        $obj->error = empty($obj->scheduler_commands_id);

        return $obj;
    }

    public static function getLiveScheduleReminders($live_schedule_id)
    {
        $type = self::getLiveScheduleReminderBaseNameType($live_schedule_id);
        return Scheduler_commands::getAllActiveOrToRepeat($type);
    }

    public static function getLiveScheduleReminderBaseNameType($live_schedule_id, $minutesEarlier = '')
    {
        $to_users_id = User::getId();
        $type = "LiveScheduleReminder_{$to_users_id}_{$live_schedule_id}";
        if (!empty($minutesEarlier)) {
            $type .= "_{$minutesEarlier}";
        }
        return $type;
    }

    static public function getScheduleReminderOptions($live_schedule_id)
    {
        global $global;
        $destinationURL = "{$global['webSiteRootURL']}plugin/Live/remindMe.json.php";
        $destinationURL = addQueryStringParameter($destinationURL, 'live_schedule_id', $live_schedule_id);
        $destinationURL = addQueryStringParameter($destinationURL, 'deleteIfExists', 1);
        $selectedEarlierOptions = array();

        $schedules = self::getLiveScheduleReminders($live_schedule_id);
        $type = self::getLiveScheduleReminderBaseNameType($live_schedule_id);
        //var_dump($type);
        foreach ($schedules as $value) {
            $parts = explode('_', $value["type"]);
            //var_dump($parts);
            $selectedEarlierOptions[] = intval($parts[3]);
        }

        $ls = new Live_schedule($_REQUEST['live_schedule_id']);

        $ls = new Live_schedule($live_schedule_id);
        $users_id = Live_schedule::getUsers_idOrCompany($live_schedule_id);
        $title = $ls->getTitle();
        /**
         * @var string $date_start
         */
        $date_start = $ls->getScheduled_time();
        $date_end = '';
        $joinURL = Live::getLinkToLiveFromUsers_id($users_id, $live_schedule_id);

        //        , $date_start, $selectedEarlierOptions = array(), $date_end = '', $joinURL='', $description=''

        return Scheduler::getReminderOptions($destinationURL, $title, $date_start, $selectedEarlierOptions, $date_end, $joinURL);
    }

    public function getWatchActionButton($videos_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/actionButton.php';
    }

    private static function getProcess($key)
    {
        if (empty($key)) {
            error_log("Live:getProcess key is empty");
            return false;
        }
        exec("ps -ax 2>&1", $output, $return_var);
        //error_log("Live:getProcess ". json_encode($output));
        $pattern = "/^([0-9]+).*ffmpeg .*" . str_replace('/', '\/', $key) . "/i";
        error_log("Live:getProcess {$pattern}");
        foreach ($output as $value) {
            if (preg_match($pattern, trim($value), $matches)) {
                return $matches;
            }
        }
        return false;
    }

    static function killIfIsRunning($key)
    {
        $process = self::getProcess($key);
        error_log("Live::killIfIsRunning checking if there is a process running for {$key} ");
        if (!empty($process)) {
            error_log("Live::killIfIsRunning there is a process running for {$key} " . json_encode($process));
            $pid = intval($process[1]);
            if (!empty($pid)) {
                error_log("Live::killIfIsRunning killing {$pid} ");
                exec("kill -9 {$pid} 2>&1", $output, $return_var);
            }
            return true;
        } else {
            //error_log("Restreamer.json.php killIfIsRunning there is not a process running for {$m3u8} ");
        }
        return false;
    }

    function getPermissionsOptions()
    {
        $permissions = array();
        $permissions[] = new PluginPermissionOption(self::PERMISSION_CAN_RESTREAM, __("Can Restream"), __("Can restream live videos"), 'Live');
        return $permissions;
    }

    static function canRestream()
    {
        if (User::isAdmin()) {
            return true;
        }
        if (!empty($_REQUEST['token'])) {
            $live_restreams_id = intval(decryptString($_REQUEST['token']));
            if (!empty($live_restreams_id)) {
                _error_log('Live::canRestream: canRestream by pass ' . $live_restreams_id);
                return true;
            }
        }
        $canStream = User::canStream();
        if (empty($canStream)) {
            _error_log('Live::canRestream: user cannot restream');
            return false;
        }
        $obj = AVideoPlugin::getDataObject('Live');
        if (!empty($obj->disableRestream)) {
            _error_log('Live::canRestream: disableRestream is active');
            return false;
        }
        if ($obj->whoCanRestream->value == self::CAN_RESTREAM_All_USERS) {
            return true;
        }
        $permission = Permissions::hasPermission(self::PERMISSION_CAN_RESTREAM, 'Live');
        _error_log('Live::canRestream: permission is ' . json_encode($permission));
        return $permission;
    }

    public static function _getUserNotificationButton()
    {
        $obj = AVideoPlugin::getDataObject('Live');
        if (Live::canScheduleLive()) {
?>
            <button class="btn btn-primary btn-sm" onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/Live/view/Live_schedule/panelIndex.php');" data-toggle="tooltip" title="<?php echo __('Schedule') ?>">
                <i class="far fa-calendar"></i> <span class="hidden-sm hidden-xs"><?php echo __('Schedule'); ?></span>
            </button>
<?php
        }
    }

    public function getUserNotificationButton()
    {
        self::_getUserNotificationButton();
    }

    function executeEveryMinute()
    {
        $start = microtime(true);
        $live_servers_ids = array(0);
        $objLive = AVideoPlugin::getDataObject("Live");
        if (!empty($objLive->useLiveServers)) {
            $live_servers_ids = array();
            $rows = Live_servers::getAllActive();
            foreach ($rows as $value) {
                if (empty($value['stats_url'])) {
                    continue;
                }
                $live_servers_ids[] = $value['id'];
            }
        }

        foreach ($live_servers_ids as $live_servers_id) {
            //_error_log("Live::executeEveryMinute live_servers_id=$live_servers_id");
            $this->createCacheStatsObject($live_servers_id);
        }
        Live::checkAllFromStats(true);
        $end = microtime(true) - $start;
        //_error_log("Live::executeEveryMinute complete in {$end} seconds");
        include __DIR__ . '/standAloneFiles/kill_ffmpeg_restream.php';
    }

    function executeEveryHour()
    {
        global $global;
        $obj = $this->getDataObject();
        if (!empty($obj->autoFishLiveEveryHour)) {
            exec('php ' . $global['systemRootPath'] . 'plugin/Live/view/finishAll.json.php');
        }
    }

    static function getLiveControls($live_key, $live_servers_id = 0)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/Live/myLiveControls.php';
    }

    public function on_publish_done($live_transmitions_history_id, $users_id, $key, $live_servers_id)
    {
        $custom = User::getRedirectCustomUrl($users_id);
        if (isValidURL($custom['url'])) {
            if (!empty($custom['autoRedirect'])) {
                $lt = new LiveTransmitionHistory($live_transmitions_history_id);
                $key = $lt->getKey();
                $row = LiveTransmition::keyExists($key);
                $obj = new stdClass();
                $obj->row = $row;
                $obj->viewerUrl = $custom['url'];
                $obj->customMessage = $custom['msg'];
                $obj->live_key = $key;
                $obj->live_servers_id = intval($live_servers_id);
                $obj->sendSocketMessage = sendSocketMessage(array('redirectLive' => $obj), 'redirectLive', 0);
                _error_log('on_publish_done::redirectLive ' . json_encode($obj));
            }
        }
    }
}

class LiveImageType
{

    public static $UNKNOWN = 'unknown';
    public static $OFFLINE = 'offline';
    public static $ONAIR = 'onair';
    public static $ONAIRENCODER = 'onair_encoder';
    public static $DEFAULTGIF = 'defaultgif';
    public static $LIVE = 'live';
}

class LiveStreamObject
{

    private $key;
    private $live_servers_id;
    private $live_index;
    private $playlists_id_live;
    private $live_schedule;

    public function __construct($key, $live_servers_id = 0, $live_index = 0, $playlists_id_live = 0, $live_schedule = 0)
    {
        $this->key = $key;
        $this->live_servers_id = intval($live_servers_id);
        $this->live_index = $live_index;
        $this->playlists_id_live = intval($playlists_id_live);
        $this->live_schedule = intval($live_schedule);
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
        if (!isset($this->live_index)) {
            $this->live_index = '';
        }
        $this->live_index = preg_replace('/[^0-9a-z-]/i', '', $this->live_index);
    }
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function getKeyWithIndex($forceIndexIfEnabled = false, $allowOnlineIndex = false)
    {
        if (!empty($forceIndexIfEnabled)) {
            if (is_string($forceIndexIfEnabled) || is_int($forceIndexIfEnabled)) {
                $this->live_index = $forceIndexIfEnabled;
            } else {
                $this->live_index = $this->getIndex($allowOnlineIndex);
            }
        }
        return Live::getLiveKeyFromRequest($this->key, $this->live_index, $this->playlists_id_live);
    }

    public function getIndex($allowOnlineIndex = false)
    {
        global $global;
        $objLive = AVideoPlugin::getDataObject("Live");
        $live_index = '';
        if (!empty($objLive->allowMultipleLivesPerUser)) {
            if (empty($allowOnlineIndex) && empty($global['getLatestValidNotOnlineLiveIndexRequested'])) {
                $global['getLatestValidNotOnlineLiveIndexRequested'] = 1;
                $live_index = Live::getLatestValidNotOnlineLiveIndex($this->key);
                $global['getLatestValidNotOnlineLiveIndexRequested'] = 0;
            } else {
                $live_index = LiveTransmitionHistory::getLatestIndexFromKey($this->key);
            }
        }
        return $live_index;
    }

    public function getLive_servers_id()
    {
        return $this->live_servers_id;
    }

    public function getLive_index()
    {
        return $this->live_index;
    }

    public function getPlaylists_id_live()
    {
        return $this->playlists_id_live;
    }

    public function getURL()
    {
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

        if (!empty($this->live_schedule)) {
            $url .= "/ls/{$this->live_schedule}";
        }

        if (!empty($this->live_index)) {
            $url .= '/' . urlencode($this->live_index);
        }

        if (!empty($this->playlists_id_live)) {
            $url = addQueryStringParameter($url, 'playlists_id_live', $this->playlists_id_live);
        }

        return $url;
    }

    public function getURLEmbed()
    {
        $url = $this->getURL();
        return addQueryStringParameter($url, 'embed', 1);
    }

    public function getM3U8($doNotProtect = false, $allowOnlineIndex = false, $ignoreCDN = false)
    {
        global $global;
        $o = AVideoPlugin::getObjectData("Live");
        $uuid = $this->getKeyWithIndex($allowOnlineIndex, $allowOnlineIndex);
        //_error_log("Live:getM3U8($doNotProtect , $allowOnlineIndex e, $ignoreCDN) $uuid ($allowOnlineIndex");
        if (empty($o->server_type->value)) {
            $row = LiveTransmitionHistory::getLatest($this->key, $this->live_servers_id);
            if (!empty($row['domain'])) {
                if ($row['domain'] == 'http://avideo:8080/') {
                    $row['domain'] = $o->playerServer;
                }
                $url = "{$row['domain']}live/{$uuid}.m3u8";
                //_error_log("getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN) ".__LINE__." {$url}");
                return $url;
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
        if ($playerServer == 'http://avideo:8080/live/') {
            $dockerVars = getDockerVars();
            $playerServer = "https://{$dockerVars->SERVER_NAME}:{$dockerVars->NGINX_HTTPS_PORT}/live/";
            //_error_log("getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN) {$playerServer} ".__LINE__);
        }
        if ($o->protectLive && empty($doNotProtect)) {
            $url = "{$global['webSiteRootURL']}plugin/Live/m3u8.php?live_servers_id={$this->live_servers_id}&uuid=" . encryptString($uuid);
            //_error_log("getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN) ".__LINE__." {$url}");
        } elseif ($o->useAadaptiveMode) {
            $url = $playerServer . "{$uuid}.m3u8";
            //_error_log("getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN) ".__LINE__." {$url}");
        } else {
            $url = $playerServer . "{$uuid}/index.m3u8";
            //_error_log("getM3U8($doNotProtect, $allowOnlineIndex, $ignoreCDN) {$playerServer} ".__LINE__." {$url}");
        }
        return $url;
    }

    public function getOnlineM3U8($users_id, $doNotProtect = false)
    {
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

    public function getRTMPLink($forceIndex = false)
    {
        $key = $this->getKeyWithIndex(true);
        if (!empty($forceIndex)) {
            // make sure the key is unique
            $parts = explode('-', $key);
            $key = $parts[0] . "-{$forceIndex}";
        }
        $url = addLastSlash($this->getRTMPLinkWithOutKey()) . $key;
        //_error_log("getRTMPLink: {$url}");
        return $url;
    }

    public function getRTMPLinkWithOutKey($short = true)
    {
        if (empty($this->key)) {
            return '';
        }
        $lt = LiveTransmition::getFromKey($this->key);
        if (!is_array($lt)) {
            //_error_log('getRTMPLinkWithOutKey error ' . json_encode(array($this->key, $lt, debug_backtrace())));

            return '';
        }
        return Live::getServerURL($this->key, $lt['users_id'], $short);
    }
}
