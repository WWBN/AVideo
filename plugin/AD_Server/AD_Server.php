<?php

/**
 * https://support.google.com/adsense/answer/4455881
 * https://support.google.com/adsense/answer/1705822
 * AdSense for video: Publisher Approval Form
 * https://services.google.com/fb/forms/afvapproval/
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';

class AD_Server extends PluginAbstract
{

    const STATUS_THAT_DETERMINE_AD_WAS_PLAYED = 'start';

    const AD_STARTED = 'AdStarted';
    const AD_FIRST_QUARTILE = 'AdFirstQuartile';
    const AD_MIDPOINT = 'AdMidpoint';
    const AD_THIRD_QUARTILE = 'AdThirdQuartile';
    const AD_COMPLETED = 'AdCompleted';
    const AD_PAUSED = 'AdPaused';
    const AD_RESUMED = 'AdResumed';
    const AD_SKIPPED = 'AdSkipped';
    const AD_CLICKED = 'AdClicked';
    const AD_ERROR = 'AdError';
    const AD_MUTED = 'AdMuted';
    const AD_UNMUTED = 'AdUnmuted';
    const AD_REWIND = 'AdRewind';
    const AD_FULLSCREEN = 'AdFullscreen';
    const AD_CREATIVE_VIEW = 'AdCreativeView';
    const AD_EXIT_FULLSCREEN = 'AdExitFullscreen';
    const AD_ACCEPT_INVITATION_LINEAR = 'AdAcceptInvitationLinear';
    const AD_CLOSE_LINEAR = 'AdCloseLinear';

    public function getTags()
    {
        return [
            PluginTags::$MONETIZATION,
            PluginTags::$ADS,
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        ];
    }

    public function getDescription()
    {
        $desc = "VAST Ad Server<br><small><a href='https://github.com/WWBN/AVideo/wiki/Ad-Server-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc;
    }

    public function getName()
    {
        return "AD_Server";
    }

    public function getUUID()
    {
        return "3f2a707f-3c06-4b78-90f9-a22f2fda92ef";
    }

    public function getPluginVersion()
    {
        return "5.0";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->prerollLive = true;
        self::addDataObjectHelper('prerollLive', 'Pre Roll Live Stream');
        $obj->start = true;
        self::addDataObjectHelper('start', 'Show Pre-Roll ads');
        $obj->mid25Percent = true;
        self::addDataObjectHelper('mid25Percent', 'Show Mid-Roll ads at 25%');
        $obj->mid50Percent = true;
        self::addDataObjectHelper('mid50Percent', 'Show Mid-Roll ads at 50%');
        $obj->mid75Percent = true;
        self::addDataObjectHelper('mid75Percent', 'Show Mid-Roll ads at 75%');
        $obj->end = true;
        self::addDataObjectHelper('end', 'Show Post-Roll ads');

        $o = new stdClass();
        $o->type = [];
        for ($i = 0; $i <= 100; $i++) {
            $o->type[$i . '%'] = "The skip button will appear when you watch {$i}% of the video";
        }
        $o->value = '10%';
        $obj->skipoffset = $o;
        self::addDataObjectHelper('skipoffset', 'Skip Offset', 'This is the percentage where the skip button should appear');

        $obj->showMarkers = true;
        self::addDataObjectHelper('showMarkers', 'Show Markers', 'Check it if you want to show the yellow markers on the video, where the advertising should appear');

        $o = new stdClass();
        $o->type = [1 => 'Every video'];
        for ($i = 2; $i < 10; $i++) {
            $o->type[$i] = "Show ads on each {$i} videos";
        }
        $o->value = 1;
        $obj->showAdsOnEachVideoView = $o;
        self::addDataObjectHelper('showAdsOnEachVideoView', 'Show Ads on', 'This defines how often advertisements will appear, for example: if it is set to 2, you will see ads each 2 videos, but if it is set to 1 you will see ads on every video');

        $o = new stdClass();
        $o->type = [0 => 'All positions'];
        for ($i = 1; $i < 5; $i++) {
            $o->type[$i] = "Show ads on {$i} random positions";
        }
        $o->value = 2;
        $obj->showAdsOnRandomPositions = $o;
        self::addDataObjectHelper('showAdsOnRandomPositions', 'Show Ads On Positions', 'This will pick random positions to display the ads, but it will pic only the positions you have checked above. For example, if you want to have 2 random positions, but do not want to have videos on the start position, you must uncheck the start video position checkbox;');

        $o = new stdClass();
        $o->type = [0 => 'Do not auto add new videos on campaign'];
        $rows = VastCampaigns::getAllActive();
        if (!is_array($rows)) {
            $o->value = 0;
            $obj->autoAddNewVideosInCampaignId = $o;
            return $obj;
        }
        foreach ($rows as $row) {
            $o->type[$row['id']] = '- ' . $row['name'];
        }
        $o->value = 0;
        $obj->autoAddNewVideosInCampaignId = $o;
        self::addDataObjectHelper('autoAddNewVideosInCampaignId', 'Auto Add New Videos In Campaign');


        $obj->onlyRewardLoggedUsers = true;
        self::addDataObjectHelper('onlyRewardLoggedUsers', 'Rewards for impressions in your campaigns will only be given to logged-in users');

        return $obj;
    }

    static function addVideoIdIntoCampaignId($videos_id, $vast_campaigns_id)
    {
        if (!empty($vast_campaigns_id)) {
            $vc = new VastCampaigns($vast_campaigns_id);
            if (!empty($vc->getName())) {
                $video = new Video("", "", $videos_id);
                if (!empty($video->getTitle()) && !empty($vast_campaigns_id)) {
                    _error_log("AD_Server:afterNewVideo saving");
                    $o = new VastCampaignsVideos(0);
                    $o->setVast_campaigns_id($vast_campaigns_id);
                    $o->setVideos_id($videos_id);
                    $o->setLink("");
                    $o->setAd_title($video->getTitle());
                    $o->setStatus('a');
                    $id = $o->save();
                    _error_log("AD_Server:addVideoIdIntoCampaignId saved {$id}");
                    return $id;
                } else {
                    _error_log("AD_Server:addVideoIdIntoCampaignId videos_id NOT found {$videos_id}");
                }
            } else {
                _error_log("AD_Server:addVideoIdIntoCampaignId autoAddNewVideosInCampaignId NOT found ");
            }
        } else {
            _error_log("AD_Server:addVideoIdIntoCampaignId is disabled");
        }
        return false;
    }

    public function afterNewVideo($videos_id)
    {
        _error_log("AD_Server:afterNewVideo start");
        $obj = $this->getDataObject();
        return self::addVideoIdIntoCampaignId($videos_id, @$obj->autoAddNewVideosInCampaignId->value);
    }

    public function canLoadAds()
    {
        global $global;

        // Check if GoogleAds_IMA plugin is enabled
        if (AVideoPlugin::isEnabledByName('GoogleAds_IMA')) {
            return ['canLoad' => false, 'reason' => 'GoogleAds_IMA plugin is enabled'];
        }

        // Get the video ID and check its type
        $videos_id = getVideos_id();
        if (!empty($videos_id)) {
            $video = new Video('', '', $videos_id);
            if ($video->getType() !== Video::$videoTypeVideo) {
                return ['canLoad' => false, 'reason' => 'Video type is not standard'];
            }
            // Check if ads should be shown for this video
            $showAds = AVideoPlugin::showAds($videos_id);
            if (!$showAds) {
                return ['canLoad' => false, 'reason' => 'Ads are disabled for this video'];
            }
        }

        // Get the plugin settings object
        $obj = $this->getDataObject();

        // Check for preroll ads on live streams
        if ($obj->prerollLive) {
            if (isLive()) {
                return ['canLoad' => true, 'reason' => ''];
            } else if (isLiveLink()) {
                return ['canLoad' => true, 'reason' => ''];
            }
        }

        // Control ad frequency based on time
        if (empty($_SESSION['lastAdShowed']) || $_SESSION['lastAdShowed'] + 2 <= time()) {
            _session_start();
            $_SESSION['lastAdShowed'] = time();

            if (!isset($_SESSION['showAdsCount'])) {
                $_SESSION['showAdsCount'] = 1;
            } else {
                $_SESSION['showAdsCount']++;
            }
        }

        // Check if ads should be shown based on view count
        if (!empty($obj->showAdsOnEachVideoView->value) && $_SESSION['showAdsCount'] % $obj->showAdsOnEachVideoView->value === 0) {
            return ['canLoad' => true, 'reason' => ''];
        }

        // Default to not loading ads
        return ['canLoad' => false, 'reason' => 'Conditions for showing ads are not met'];
    }


    public function getHeadCode()
    {
        global $_showAds;
        //$obj = $this->getDataObject();
        $canLoadAds = $this->canLoadAds();
        if (!$canLoadAds['canLoad']) {
            return "<!-- AD_Server getHeadCode canLoadAds {$canLoadAds['reason']} " . json_encode($_showAds) . " -->";
        }
        global $global;
        $_GET['vmap_id'] = session_id();

        $css = '<link href="' . getURL('node_modules/videojs-contrib-ads/dist/videojs.ads.css') . '" rel="stylesheet" type="text/css"/>'
            . '<link href="' . getURL('node_modules/videojs-ima/dist/videojs.ima.css') . '" rel="stylesheet" type="text/css"/>';

        $css .= '<style>.ima-ad-container{z-index:1000 !important;}</style>';
        return $css;
    }

    private static function getVideoLength()
    {
        $video_length = 3600; // 1 hour
        $videos_id = getVideos_id();
        $video = new Video('', '', $videos_id);
        $duration = $video->getDuration();
        if (!empty($duration)) {
            $video_length = parseDurationToSeconds($duration);
        }
        return $video_length;
    }

    public static function getVMAPSFromRequest()
    {
        if (!empty($_REQUEST['vmaps'])) {
            $vmaps = _json_decode(base64_decode($_REQUEST['vmaps']));
        }
        if (empty($vmaps)) {
            if (!empty($_REQUEST['video_length'])) {
                $video_length = intval($_REQUEST['video_length']);
            } else {
                $video_length = self::getVideoLength();
            }
            $ad_server = AVideoPlugin::loadPlugin('AD_Server');
            $vmaps = $ad_server->getVMAPs($video_length);
        }
        return object_to_array($vmaps);
    }

    public static function addVMAPS($url, $vmaps)
    {
        if (empty($vmaps)) {
            $vmaps = self::getVMAPSFromRequest();
        }
        $base64 = base64_encode(_json_encode($vmaps));
        $vmapURL = addQueryStringParameter($url, 'vmaps', $base64);
        return $vmapURL;
    }

    public function afterVideoJS()
    {
        global $_showAds;
        $obj = $this->getDataObject();
        $canLoadAds = $this->canLoadAds();
        if (!$canLoadAds['canLoad']) {
            return "<!-- AD_Server afterVideoJS canLoadAds {$canLoadAds['reason']} " . json_encode($_showAds) . " -->";
        }
        /*
        if (empty($_GET['vmap_id'])) {
            return "<!-- AD_Server empty vmap_id -->";
        }
        */
        global $global;
        $vmap_id = $_GET['vmap_id'] ?? '';
        $vmaps = self::getVMAPSFromRequest();
        $video_length = self::getVideoLength();
        $videos_id = getVideos_id();
        $vmapURL = "{$global['webSiteRootURL']}plugin/AD_Server/VMAP.php";
        $vmapURL = addQueryStringParameter($vmapURL, 'video_length', $video_length);
        $vmapURL = addQueryStringParameter($vmapURL, 'vmap_id', $vmap_id);
        $vmapURL = addQueryStringParameter($vmapURL, 'random', _uniqid());
        $vmapURL = addQueryStringParameter($vmapURL, 'videos_id', $videos_id);
        $vmapURL = self::addVMAPS($vmapURL, $vmaps);
        //var_dump($vmapURL, $vmaps);exit;
        PlayerSkins::setIMAADTag($vmapURL);

        if (!empty($obj->showMarkers)) {
            $rows = array();
            foreach ($vmaps as $value) {
                $vastCampaingVideos = new VastCampaignsVideos($value['VAST']['campaing']);
                $video = new Video("", "", $vastCampaingVideos->getVideos_id());
                if (!empty($video_length) && $value['timeOffsetSeconds'] >= $video_length) {
                    $value['timeOffsetSeconds'] = $video_length - 5;
                }
                $rows[] = array('timeInSeconds' => $value['timeOffsetSeconds'], 'name' => $video->getTitle());
            }

            PlayerSkins::createMarker($rows);
        }

        $js = '';
        $js .= '<script src="//imasdk.googleapis.com/js/sdkloader/ima3.js"></script>';
        $js .= '<script src="' . getURL('node_modules/videojs-contrib-ads/dist/videojs.ads.min.js') . '" type="text/javascript"></script>';
        $js .= '<script src="' . getURL('node_modules/videojs-ima/dist/videojs.ima.min.js') . '" type="text/javascript"></script>';

        return $js;
    }

    private function getRandomPositions()
    {
        $obj = $this->getDataObject();
        $oldId = session_id();
        if (!empty($_GET['vmap_id'])) {
            _session_write_close();
            session_id($_GET['vmap_id']);
        }
        _session_start();
        $options = [];

        if (!empty($obj->start)) {
            $options[] = 1;
        }
        if (!empty($obj->mid25Percent)) {
            $options[] = 2;
        }
        if (!empty($obj->mid50Percent)) {
            $options[] = 3;
        }
        if (!empty($obj->mid75Percent)) {
            $options[] = 4;
        }
        if (!empty($obj->end)) {
            $options[] = 5;
        }

        $selectedOptions = [];
        if (empty($_SESSION['lastAdRandomPositions']) || $_SESSION['lastAdRandomPositions'] + 20 <= time()) {
            $_SESSION['lastAdRandomPositions'] = time();

            if (empty($obj->showAdsOnRandomPositions->value)) {
                $selectedOptions = $options;
            } else {
                for ($i = 0; $i < $obj->showAdsOnRandomPositions->value; $i++) {
                    shuffle($options);
                    $selectedOptions[] = array_pop($options);
                }
            }
            $_SESSION['adRandomPositions'] = $selectedOptions;
        }
        $adRandomPositions = $_SESSION['adRandomPositions'];
        if (session_status() !== PHP_SESSION_NONE) {
            _session_write_close();
        }
        session_id($oldId);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        //_error_log("VMAP select those options: " . print_r($adRandomPositions, true));
        return $adRandomPositions;
    }

    public function getVMAPs($video_length)
    {
        $vmaps = [];

        $obj = $this->getDataObject();

        $selectedOptions = $this->getRandomPositions();

        if (isLive() || isLiveLink()) {
            if ($obj->prerollLive) {
                $vmaps[] = new VMAP("start", new VAST(1));
            }
        } else if (is_array($selectedOptions)) {
            if (!empty($obj->start) && in_array(1, $selectedOptions)) {
                $vmaps[] = new VMAP("start", new VAST(1));
            }
            if (!empty($obj->mid25Percent) && in_array(2, $selectedOptions)) {
                $val = $video_length * (25 / 100);
                $vmaps[] = new VMAP($val, new VAST(2));
            }
            if (!empty($obj->mid50Percent) && in_array(3, $selectedOptions)) {
                $val = $video_length * (50 / 100);
                $vmaps[] = new VMAP($val, new VAST(3));
            }
            if (!empty($obj->mid75Percent) && in_array(4, $selectedOptions)) {
                $val = $video_length * (75 / 100);
                $vmaps[] = new VMAP($val, new VAST(4));
            }
            if (!empty($obj->end) && in_array(5, $selectedOptions)) {
                $vmaps[] = new VMAP("end", new VAST(5), $video_length);
            }
        }

        return $vmaps;
    }

    public function VMAPsHasVideos()
    {
        $vmaps = $this->getVMAPs(100);
        //var_dump($vmaps);exit;
        foreach ($vmaps as $value) {
            if (empty($value->VAST->campaing)) {
                return false;
            }
        }
        return true;
    }

    public function showAdsNow()
    {
        if (!$this->VMAPsHasVideos()) {
            return false;
        }
    }

    public static function getVideos()
    {
        $campaings = VastCampaigns::getValidCampaigns();
        //var_dump($campaings);
        $videos = [];
        foreach ($campaings as $key => $value) {
            $v = VastCampaignsVideos::getValidVideos($value['id']);
            $videos = array_merge($videos, $v);
            $campaings[$key]['videos'] = $v;
        }
        return ['campaigns' => $campaings, 'videos' => $videos];
    }

    public static function getRandomVideo()
    {
        $result = static::getVideos();
        $videos = $result['videos'];
        shuffle($videos);
        return array_pop($videos);
    }

    public static function getRandomCampaign()
    {
        $result = static::getVideos();
        $campaing = $result['campaigns'];
        shuffle($campaing);
        return array_pop($campaing);
    }

    public function getPluginMenu()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/AD_Server/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getValidCampaignsFromVideo($videos_id)
    {
        return VastCampaigns::getValidCampaignsFromVideo($videos_id);
    }


    public static function getVASTLinks($videos_id = 0)
    {
        global $global;
        $campaings = VastCampaigns::getValidCampaigns();
        $vastURI = "{$global['webSiteRootURL']}plugin/AD_Server/VAST.php";
        if (!empty($videos_id)) {
            $vastURI = addQueryStringParameter($vastURI, 'videos_id', $videos_id);
        }
        $vastLinks = [];
        foreach ($campaings as $key => $value) {
            $AdTagURI = addQueryStringParameter($vastURI, 'campaign_id', $value['id']);
            $vastLinks[] = $AdTagURI;
        }
        return $vastLinks;
    }

    public function onNewVideo($videos_id)
    {
        if (!empty($_REQUEST['return_vars'])) {
            $json = json_decode($_REQUEST['return_vars']);
            if (!empty($json) && !empty($json->callback)) {
                $callback = json_decode(base64_decode($json->callback));
                if (!empty($callback) && !empty($callback->vast_campaigns_id)) {
                    return self::addVideoIdIntoCampaignId($videos_id, $callback->vast_campaigns_id);
                }
            }
        }
        return false;
    }
}

class VMAP
{

    public $timeOffset;
    public $timeOffsetSeconds;
    public $VAST;
    public $idTag = "preroll-ad";

    public function __construct($time, VAST $VAST, $video_length = 0)
    {
        if ($time === 'start') {
            $this->timeOffsetSeconds = 0;
        } elseif ($time === 'end') {
            $this->timeOffsetSeconds = $video_length;
        } else {
            $this->timeOffsetSeconds = $time;
        }
        $this->VAST = $VAST;
        $this->setTimeOffset($time);
    }

    public function setTimeOffset($time)
    {
        if (empty($time)) {
            //$time = "start";
        }
        // if is longer then the video length will be END
        if (empty($time) || $time == "start") {
            $this->idTag = "preroll-ad-" . $this->VAST->id;
        } elseif ($time == "end") {
            $this->idTag = "postroll-ad-" . $this->VAST->id;
        } elseif (is_numeric($time)) {
            $time = $this->format($time);
            $this->idTag = "midroll-" . $this->VAST->id;
        }
        // format to 00:00:15.000
        $this->timeOffset = $time;
    }

    private function format($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d:%02d.000', $hours, $mins, $secs);
    }
}

class VAST
{

    public $id;
    public $campaing;

    public function __construct($id)
    {
        $this->id = $id;
        $row = AD_Server::getRandomVideo();
        if (!empty($row)) {
            $this->campaing = $row['id'];
        } else {
            $this->campaing = false;
        }
    }
}
