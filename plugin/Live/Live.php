<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistoryLog.php';

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
        return "3.0";   
    }
    
    public function updateScript() {
        global $global;
        //update version 2.0
        $sql = "SELECT 1 FROM live_transmitions_history LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch=sqlDAL::fetchAssoc($res);
        if(!$fetch){
            sqlDal::writeSql(file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV2.0.sql'));
        }
        //update version 3.0
        $sql = "SELECT 1 FROM live_transmition_history_log LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $fetch=sqlDAL::fetchAssoc($res);
        if(!$fetch){
            sqlDal::writeSql(file_get_contents($global['systemRootPath'] . 'plugin/Live/install/updateV3.0.sql')); 
            return true;
        }
        return true;
    }

    public function getEmptyDataObject() {
        global $global;
        $server = parse_url($global['webSiteRootURL']);
        
        $scheme = "http";
        $port = "8080";
        if(strtolower($server["scheme"])=="https"){
            $scheme = "https";
            $port = "444";
        }        
        
        $obj = new stdClass();
        $obj->button_title = "LIVE";
        $obj->server = "rtmp://{$server['host']}/live";
        $obj->playerServer = "{$scheme}://{$server['host']}:{$port}/live";
        // for secure connections
        //$obj->playerServer = "https://{$server['host']}:444/live";
        $obj->stats = "{$scheme}://{$server['host']}:{$port}/stat";
        $obj->disableDVR = false;
        $obj->disableGifThumbs = false;
        $obj->useAadaptiveMode = false;
        $obj->experimentalWebcam = false;
        $obj->doNotShowLiveOnVideosList = false;
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

    public function getServer() {
        $o = $this->getDataObject();
        return $o->server;
    }

    public function getM3U8File($uuid) {
        $o = $this->getDataObject();
        $playerServer = $o->playerServer;
        if($o->useAadaptiveMode){
            return $playerServer."/{$uuid}.m3u8";
        }else{
            return $playerServer."/{$uuid}/index.m3u8";
        }
    }

    public function getDisableGifThumbs() {
        $o = $this->getDataObject();
        return $o->disableGifThumbs;
    }
    public function getStatsURL() {
        $o = $this->getDataObject();
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

    function getStatsObject() {
        ini_set('allow_url_fopen ', 'ON');
        $xml = simplexml_load_string($this->get_data($this->getStatsURL()));
        return $xml;
    }

    function get_data($url) {
        return url_get_contents($url);
    }
    
    public function getTags() {
        return array('free', 'live', 'streaming', 'live stream');
    }
    
    public function getChartTabs() {
        return '<li><a data-toggle="tab" id="liveVideos" href="#liveVideosMenu"><i class="fab fa-youtube"></i> Live videos</a></li>';
    }
    
    public function getChartContent() {
        global $global;
        include $global['systemRootPath'].'plugin/Live/report.php';         
    }
    
    static public function saveHistoryLog($key){
        // get the latest history for this key
        $latest = LiveTransmitionHistory::getLatest($key);
        
        if(!empty($latest)){
            LiveTransmitionHistoryLog::addLog($latest['id']);
        }
        
    }

}
