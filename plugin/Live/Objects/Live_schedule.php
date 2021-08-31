<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_schedule extends ObjectYPT {

    protected $id, $title, $description, $key, $users_id, $live_servers_id, $scheduled_time, $timezone, $status, $poster, $public, $saveTransmition, $showOnTV;

    static function getSearchFieldsNames() {
        return array('title', 'description', 'key', 'timezone', 'poster');
    }

    static function getTableName() {
        return 'live_schedule';
    }

    static function getAllUsers() {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getAllLive_servers() {
        global $global;
        $table = "live_servers";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getPosterPaths($live_schedule_id) {
        $live_schedule_id = intval($live_schedule_id);
        if (empty($live_schedule_id)) {
            return false;
        }

        $subdir = "live_schedule_posters";

        $array = array();

        $array['path'] = getVideosDir() . $subdir . DIRECTORY_SEPARATOR;

        make_path($array['path']);

        $imageName = "schedule_{$live_schedule_id}.jpg";
        $imageName_thumbs = "schedule_{$live_schedule_id}_thumbs.jpg";
        $array['relative_path'] = "videos/{$subdir}/{$imageName}";
        $array['path_thumbs'] = $array['path'].$imageName_thumbs;
        $array['path'] .= $imageName;
        $array['url'] = getURL("videos/{$subdir}/{$imageName}");
        $array['url_thumbs'] = getURL("videos/{$subdir}/{$imageName_thumbs}");

        return $array;
    }

    static function getPosterURL($live_schedule_id) {
        $paths = self::getPosterPaths($live_schedule_id);
        if (file_exists($paths['path'])) {
            return $paths['url'];
        } else {
            return Live::getOfflineImage();
        }
    }

    static function getAll($users_id=0) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $users_id = intval($users_id);
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        if(!empty($users_id)){
            $sql .= " AND users_id = $users_id ";
        }
        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['future'] = isTimeForFuture($row['scheduled_time'], $row['timezone']);
                $row['secondsIntervalHuman'] = secondsIntervalHuman($row['scheduled_time'], $row['timezone']);
                $row['posterURL'] = self::getPosterURL($row['id']);
                $row['serverURL'] = Live::getServerURL($row['key'], $row['users_id']);

                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getAllActiveLimit($limit = 10) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        // to convert time must load time zone table into mysql
        
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' "
                . " AND (CONVERT_TZ(scheduled_time, timezone, @@session.time_zone ) > NOW() || scheduled_time > NOW()) "
                . " ORDER BY scheduled_time ASC LIMIT {$limit} ";

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
        return $rows;
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function setLive_servers_id($live_servers_id) {
        $this->live_servers_id = intval($live_servers_id);
    }

    function setScheduled_time($scheduled_time) {
        $this->scheduled_time = $scheduled_time;
    }

    private function _setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setPoster($poster) {
        $this->poster = $poster;
    }

    function setPublic($public) {
        $this->public = intval($public);
    }

    function setSaveTransmition($saveTransmition) {
        $this->saveTransmition = intval($saveTransmition);
    }

    function setShowOnTV($showOnTV) {
        $this->showOnTV = intval($showOnTV);
    }

    function getId() {
        return intval($this->id);
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getKey() {
        return $this->key;
    }

    function getUsers_id() {
        return intval($this->users_id);
    }

    function getLive_servers_id() {
        return intval($this->live_servers_id);
    }

    function getScheduled_time() {
        return $this->scheduled_time;
    }

    function getTimezone() {
        return $this->timezone;
    }

    function getStatus() {
        return $this->status;
    }

    function getPoster() {
        return $this->poster;
    }

    function getPublic() {
        return intval($this->public);
    }

    function getSaveTransmition() {
        return intval($this->saveTransmition);
    }

    function getShowOnTV() {
        return intval($this->showOnTV);
    }

    function save() {

        if (empty($this->live_servers_id)) {
            $this->live_servers_id = 'NULL';
        }

        if (empty($this->key)) {
            $this->key = uniqid();
        }

        $this->_setTimeZone(date_default_timezone_get());

        //$key = Live::getKeyFromUser($this->getUsers_id());
        //$this->setKey($key);

        $id = parent::save();

        if (!empty($id)) {
            $array = array();
            $array['users_id'] = $this->users_id;
            $array['stats'] = getStatsNotifications(true);
            $array['key'] = $this->key;
            $array['live_servers_id'] = $this->live_servers_id;
            Live::notifySocketStats("socketLiveONCallback", $array);
        }
        return $id;
    }
    
    public function delete() {
        $t = new Live_schedule($this->id);   
        $array = setLiveKey($t->key, $t->live_servers_id);          
        $id = parent::delete();        
        if (!empty($id)) {
            $array['stats'] = getStatsNotifications(true);
            Live::notifySocketStats("socketLiveOFFCallback", $array);
        }      
        return $id;
    }

    static function keyExists($key) {
        global $global;
        if (!is_string($key)) {
            return false;
        }
        if (Live::isAdaptiveTransmition($key)) {
            return false;
        }
        $key = Live::cleanUpKey($key);
        $sql = "SELECT u.*, lt.*, ls.*, lt.public as public FROM " . static::getTableName() . " ls "
                . " LEFT JOIN users u ON u.id = ls.users_id AND u.status='a' "
                . " LEFT JOIN live_transmitions lt ON u.id = lt.users_id AND u.status='a' "
                . " WHERE "
                . " ls.`key` = '$key' "
                . " AND (ls.`scheduled_time` + INTERVAL 1 DAY) > NOW() " // expire in 1 day
                . " LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            $row = cleanUpRowFromDatabase($row);
        } else {
            $row = false;
        }
        return $row;
    }

}
