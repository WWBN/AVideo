<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_servers extends ObjectYPT {

    protected $id, $name, $url, $status, $rtmp_server, $playerServer, $stats_url, $disableDVR, $disableGifThumbs, $useAadaptiveMode, $protectLive, $getRemoteFile, $restreamerURL, $controlURL;

    static function getSearchFieldsNames() {
        return array('name', 'url', 'rtmp_server', 'playerServer', 'stats_url', 'getRemoteFile');
    }

    static function getTableName() {
        return 'live_servers';
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setName($name) {
        $this->name = $name;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setRtmp_server($rtmp_server) {
        $this->rtmp_server = $rtmp_server;
    }

    function setPlayerServer($playerServer) {
        $this->playerServer = $playerServer;
    }

    function setStats_url($stats_url) {
        $this->stats_url = $stats_url;
    }

    function setDisableDVR($disableDVR) {
        $this->disableDVR = intval($disableDVR);
    }

    function setDisableGifThumbs($disableGifThumbs) {
        $this->disableGifThumbs = intval($disableGifThumbs);
    }

    function setUseAadaptiveMode($useAadaptiveMode) {
        $this->useAadaptiveMode = intval($useAadaptiveMode);
    }

    function setProtectLive($protectLive) {
        $this->protectLive = intval($protectLive);
    }

    function setGetRemoteFile($getRemoteFile) {
        $this->getRemoteFile = $getRemoteFile;
    }

    function getId() {
        return intval($this->id);
    }

    function getName() {
        return $this->name;
    }

    function getUrl() {
        return $this->url;
    }

    function getStatus() {
        return $this->status;
    }

    function getRtmp_server() {
        return trim($this->rtmp_server);
    }

    function getPlayerServer() {
        return $this->playerServer;
    }

    function getStats_url() {
        return $this->stats_url;
    }

    function getDisableDVR() {
        return intval($this->disableDVR);
    }

    function getDisableGifThumbs() {
        return intval($this->disableGifThumbs);
    }

    function getUseAadaptiveMode() {
        return intval($this->useAadaptiveMode);
    }

    function getProtectLive() {
        return intval($this->protectLive);
    }

    function getGetRemoteFile() {
        return $this->getRemoteFile;
    }
    
    function getRestreamerURL() {
        return $this->restreamerURL;
    }

    function setRestreamerURL($restreamerURL) {
        $this->restreamerURL = $restreamerURL;
    }
    
    function getControlURL() {
        return $this->controlURL;
    }

    function setControlURL($controlURL) {
        $this->controlURL = $controlURL;
    }
        
    static function getStatsFromId($live_servers_id, $force_recreate = false) {
        $ls = new Live_servers($live_servers_id);
        if (empty($ls->getStatus()) || $ls->getStatus()=='i') {
            _error_log("Live_servers:: getStatsFromId ERROR ".json_encode($ls));
            return false;
        }
        return Live::_getStats($live_servers_id, $force_recreate);
    }

    static function getAllActive() {
        global $global, $liveServersgetAllActive;
        if(isset($liveServersgetAllActive)){
            return $liveServersgetAllActive;
        }
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' ";

        //$sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $liveServersgetAllActive = $rows;
        return $rows;
    }

    static function getServerFromRTMPHost($rtmpHostURI) {
        $obj = AVideoPlugin::getObjectData('Live');
        if(empty($obj->useLiveServers)){
            return 0;
        }
        global $global;
        $host = trim($rtmpHostURI);
        $parts = parse_url($host);
        $host = "rtmp://{$parts["host"]}{$parts["path"]}";
        $host = $global['mysqli']->real_escape_string($host);
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE rtmp_server LIKE '%{$host}%' AND status = 'a' ";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getServerIdFromRTMPHost($rtmpHostURI) {
        $data = self::getServerFromRTMPHost($rtmpHostURI);
        if ($data) {
            $row = $data['id'];
        } else {
            $row = 0;
        }
        return intval($row);
    }

    public function save() {
        $id = parent::save();
        if($id){            
            _session_start();
            $_SESSION['useAadaptiveMode'] = array();
            $_SESSION['playerServer'] = array();
        }
        return $id;
    }

    public function delete() {
        
        if(!empty($this->id)){
            LiveTransmitionHistory::deleteAllFromLiveServer($this->id);
        }
        
        return parent::delete();
    }
    
}
