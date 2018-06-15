<?php

header('Access-Control-Allow-Origin: *');
/**
 * https://support.google.com/adsense/answer/4455881
 * https://support.google.com/adsense/answer/1705822
 * AdSense for video: Publisher Approval Form
 * https://services.google.com/fb/forms/afvapproval/
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';

class AD_Server extends PluginAbstract {

    public function getDescription() {
        return "VAST Ad Server";
    }

    public function getName() {
        return "AD_Server";
    }

    public function getUUID() {
        return "3f2a707f-3c06-4b78-90f9-a22f2fda92ef";
    }

    public function getHeadCode() {
        $obj = $this->getDataObject();
        if (empty($_GET['videoName'])) {
            return "";
        }
        global $global;
        $_GET['vmap_id'] = uniqid();
        
        $js   = '<script src="//imasdk.googleapis.com/js/sdkloader/ima3.js"></script>';
        $css  = '<link href="' . $global['webSiteRootURL'] . 'plugin/AD_Server/videojs-ima/videojs.ima.css" rel="stylesheet" type="text/css"/>';
        
        if(!empty($obj->showMarkers)){
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/AD_Server/videojs-markers/videojs.markers.css" rel="stylesheet" type="text/css"/>';            
        }
        $css .= '<style>.ima-ad-container{z-index:1000 !important;}</style>';
        return $js . $css;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->start = true;
        $obj->mid25Percent = false;
        $obj->mid50Percent = false;
        $obj->mid75Percent = false;
        $obj->end = true;
        $obj->skipoffset = "10%";
        $obj->showMarkers = true;
        $obj->showAdsOnEachVideoView = 1;
        return $obj;
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        if (empty($_GET['videoName'])) {
            return "";
        }
        global $global;

        $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        $video_length = parseDurationToSeconds($video['duration']);
        
        $vmap_id = @$_GET['vmap_id'];        
        
        if(!empty($_GET['vmap_id']) && !empty($_SESSION['vmap'][$_GET['vmap_id']])){
            $vmaps = unserialize($_SESSION['vmap'][$_GET['vmap_id']]);
        }else{
            $vmaps = $this->getVMAPs($video_length);
            $_SESSION['vmap'][$_GET['vmap_id']] = serialize($vmaps);
        }
        
        if($this->VMAPsHasVideos()){
            include $global['systemRootPath'] . 'plugin/AD_Server/footer.php';
        }else{
            echo "<!-- NO Videos found for VAST ads -->";
        }
    }

    public function getVMAPs($video_length) {
        $vmaps = array();
        
        $obj = $this->getDataObject();
        if (!empty($obj->start)) {
            $vmaps[] = new VMAP("start", new VAST(1));
        }
        if (!empty($obj->mid25Percent)) {
            $val = $video_length * (25 / 100);
            $vmaps[] = new VMAP($val, new VAST(2));
        }
        if (!empty($obj->mid50Percent)) {
            $val = $video_length * (50 / 100);
            $vmaps[] = new VMAP($val, new VAST(3));
        }
        if (!empty($obj->mid75Percent)) {
            $val = $video_length * (75 / 100);
            $vmaps[] = new VMAP($val, new VAST(4));
        }
        if (!empty($obj->end)) {
            $vmaps[] = new VMAP("end", new VAST(5), $video_length);
        }
        
        return $vmaps;
    }
    
    public function VMAPsHasVideos() {
        $vmaps = $this->getVMAPs(100);
        //var_dump($vmaps);exit;
        foreach($vmaps as $value){
            if(empty($value->VAST->campaing)){
                return false;
            }
        }
        return true;
    }
    
    public function showAdsNow(){
        if(!$this->VMAPsHasVideos()){
            return false;
        }
        
    }

    static public function getVideos() {
        $campaings = VastCampaigns::getValidCampaigns();
        //var_dump($campaings);
        $videos = array();
        foreach ($campaings as $key => $value) {
            $v = VastCampaignsVideos::getValidVideos($value['id']);
            $videos = array_merge($videos, $v);
            $campaings[$key]['videos'] = $v;
        }
        return array('campaigns' => $campaings, 'videos' => $videos);
    }

    static public function getRandomVideo() {
        $result = static::getVideos();
        $videos = $result['videos'];
        shuffle($videos);
        return array_pop($videos);
    }

    static public function getRandomCampaign() {
        $result = static::getVideos();
        $campaing = $result['campaigns'];
        shuffle($campaing);
        return array_pop($campaing);
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/AD_Server/pluginMenu.html';
        return file_get_contents($filename);
    }

}

class VMAP {

    public $timeOffset;
    public $timeOffsetSeconds;
    public $VAST;
    public $idTag = "preroll-ad";

    function __construct($time, VAST $VAST, $video_length = 0) {
        if ($time === 'start') {
            $this->timeOffsetSeconds = 0;
        } else if ($time === 'end') {
            $this->timeOffsetSeconds = $video_length;
        } else {
            $this->timeOffsetSeconds = $time;
        }
        $this->VAST = $VAST;
        $this->setTimeOffset($time);
    }

    function setTimeOffset($time) {
        if (empty($time)) {
            //$time = "start";
        }
        // if is longer then the video lenght will be END
        if (empty($time) || $time == "start") {
            $this->idTag = "preroll-ad-" . $this->VAST->id;
        } else if ($time == "end") {
            $this->idTag = "postroll-ad-" . $this->VAST->id;
        } else if (is_numeric($time)) {
            $time = $this->format($time);
            $this->idTag = "midroll-" . $this->VAST->id;
        }
        // format to 00:00:15.000
        $this->timeOffset = $time;
    }

    private function format($seconds) {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d:%02d.000', $hours, $mins, $secs);
    }

}

class VAST {

    public $id;
    public $campaing;

    function __construct($id) {
        $this->id = $id;
        $row = AD_Server::getRandomVideo();
        $this->campaing = $row['id'];
    }

}
