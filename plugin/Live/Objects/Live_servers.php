<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_servers extends ObjectYPT
{
    protected $id;
    protected $name;
    protected $url;
    protected $status;
    protected $rtmp_server;
    protected $playerServer;
    protected $stats_url;
    protected $disableDVR;
    protected $disableGifThumbs;
    protected $useAadaptiveMode;
    protected $protectLive;
    protected $getRemoteFile;
    protected $restreamerURL;
    protected $controlURL;

    public static function getSearchFieldsNames()
    {
        return ['name', 'url', 'rtmp_server', 'playerServer', 'stats_url', 'getRemoteFile'];
    }

    public static function getTableName()
    {
        return 'live_servers';
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setRtmp_server($rtmp_server)
    {
        $this->rtmp_server = $rtmp_server;
    }

    public function setPlayerServer($playerServer)
    {
        $this->playerServer = $playerServer;
    }

    public function setStats_url($stats_url)
    {
        $this->stats_url = $stats_url;
    }

    public function setDisableDVR($disableDVR)
    {
        $this->disableDVR = intval($disableDVR);
    }

    public function setDisableGifThumbs($disableGifThumbs)
    {
        $this->disableGifThumbs = intval($disableGifThumbs);
    }

    public function setUseAadaptiveMode($useAadaptiveMode)
    {
        $this->useAadaptiveMode = intval($useAadaptiveMode);
    }

    public function setProtectLive($protectLive)
    {
        $this->protectLive = intval($protectLive);
    }

    public function setGetRemoteFile($getRemoteFile)
    {
        $this->getRemoteFile = $getRemoteFile;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getRtmp_server()
    {
        return trim($this->rtmp_server);
    }
    /**
     * @return string
     */
    public function getPlayerServer()
    {
        return $this->playerServer;
    }

    public function getStats_url()
    {
        return $this->stats_url;
    }

    public function getDisableDVR()
    {
        return intval($this->disableDVR);
    }

    public function getDisableGifThumbs()
    {
        return intval($this->disableGifThumbs);
    }

    public function getUseAadaptiveMode()
    {
        return intval($this->useAadaptiveMode);
    }

    public function getProtectLive()
    {
        return intval($this->protectLive);
    }
    /**
     * 
     * @return string
     */
    public function getGetRemoteFile()
    {
        return $this->getRemoteFile;
    }

    public function getRestreamerURL()
    {
        return $this->restreamerURL;
    }

    public function setRestreamerURL($restreamerURL)
    {
        $this->restreamerURL = $restreamerURL;
    }

    public function getControlURL()
    {
        return $this->controlURL;
    }

    public function setControlURL($controlURL)
    {
        $this->controlURL = $controlURL;
    }

    public static function getStatsFromId($live_servers_id, $force_recreate = false)
    {
        global $_getStatsFromId;
        if (empty($force_recreate)) {
            if (!isset($_getStatsFromId)) {
                $_getStatsFromId = [];
            }

            if (isset($_getStatsFromId[$live_servers_id])) {
                return $_getStatsFromId[$live_servers_id];
            }
        }
        $ls = new Live_servers($live_servers_id);
        if (empty($ls->getStatus()) || $ls->getStatus()=='i') {
            _error_log("Live_servers:: getStatsFromId ERROR ".json_encode($ls));
            $_getStatsFromId[$live_servers_id] = false;
        } else {
            $_getStatsFromId[$live_servers_id] = Live::_getStats($live_servers_id, $force_recreate);
        }
        return $_getStatsFromId[$live_servers_id];
    }

    public static function getAllActive()
    {
        global $global, $liveServersgetAllActive;
        if (isset($liveServersgetAllActive)) {
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
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } 
        $liveServersgetAllActive = $rows;
        return $rows;
    }

    public static function getServerFromRTMPHost($rtmpHostURI)
    {
        $obj = AVideoPlugin::getObjectData('Live');
        if (empty($obj->useLiveServers)) {
            return 0;
        }
        global $global;
        $host = trim($rtmpHostURI);
        $parts = parse_url($host);
        $host = "rtmp://{$parts["host"]}{$parts["path"]}";
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

    public static function getServerIdFromRTMPHost($rtmpHostURI)
    {
        $data = self::getServerFromRTMPHost($rtmpHostURI);
        if ($data) {
            $row = $data['id'];
        } else {
            $row = 0;
        }
        return intval($row);
    }

    public function save()
    {
        $id = parent::save();
        if ($id) {
            _session_start();
            $_SESSION['useAadaptiveMode'] = [];
            $_SESSION['playerServer'] = [];
        }
        return $id;
    }

    public function delete()
    {
        if (!empty($this->id)) {
            LiveTransmitionHistory::deleteAllFromLiveServer($this->id);
        }

        return parent::delete();
    }
}
