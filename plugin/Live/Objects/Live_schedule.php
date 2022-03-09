<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_schedule extends ObjectYPT
{
    protected $id;
    protected $title;
    protected $description;
    protected $key;
    protected $users_id;
    protected $live_servers_id;
    protected $scheduled_time;
    protected $timezone;
    protected $status;
    protected $poster;
    protected $public;
    protected $saveTransmition;
    protected $showOnTV;
    protected $scheduled_password;
    protected $users_id_company;

    public static function getSearchFieldsNames()
    {
        return ['title', 'description', 'key', 'timezone', 'poster'];
    }

    public static function getTableName()
    {
        return 'live_schedule';
    }
    
    function getUsers_id_company(): int {
        return intval($this->users_id_company);
    }

    function setUsers_id_company($users_id_company): void {
        $this->users_id_company = intval($users_id_company);
    }

    public static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getAllLive_servers()
    {
        global $global;
        $table = "live_servers";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getPosterPaths($live_schedule_id)
    {
        $live_schedule_id = intval($live_schedule_id);
        if (empty($live_schedule_id)) {
            return false;
        }

        $subdir = "live_schedule_posters";

        $array = [];

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

    public static function getPosterURL($live_schedule_id){
        $paths = self::getPosterPaths($live_schedule_id);
        if (file_exists($paths['path'])) {
            return $paths['url'];
        } else {
            return Live::getComingSoonImage();
        }
    }
    
    public static function isLive($live_schedule_id){
        $ls = self::getFromDb($live_schedule_id);
        if(empty($ls['key'])){
            return false;
        }
        $isLive = LiveTransmitionHistory::isLive($ls['key']);
        //var_dump($ls['key'], $isLive);exit;
        return $isLive;
    }

    public static function getAll($users_id=0, $activeHoursAgo=false)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $users_id = intval($users_id);
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }
        if ($activeHoursAgo) {
            $sql .= " AND scheduled_time > DATE_SUB(NOW(), INTERVAL {$activeHoursAgo} HOUR) ";
        }
        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['future'] = isTimeForFuture($row['scheduled_time'], $row['timezone']);
                $row['secondsIntervalHuman'] = secondsIntervalHuman($row['scheduled_time'], $row['timezone']);
                //var_dump($row['secondsIntervalHuman']);exit;
                $row['posterURL'] = self::getPosterURL($row['id']);
                $row['serverURL'] = Live::getServerURL($row['key'], $row['users_id']);

                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getAllActiveLimit($limit = 10)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        // to convert time must load time zone table into mysql

        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' "
                . " AND (CONVERT_TZ(scheduled_time, timezone, @@session.time_zone ) > NOW() || scheduled_time > NOW()) "
                . " ORDER BY scheduled_time ASC LIMIT {$limit} ";
        //echo $sql;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    public function setLive_servers_id($live_servers_id)
    {
        $this->live_servers_id = intval($live_servers_id);
    }

    public function setScheduled_time($scheduled_time)
    {
        $this->scheduled_time = $scheduled_time;
    }

    private function _setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setPoster($poster)
    {
        $this->poster = $poster;
    }

    public function setPublic($public)
    {
        $this->public = intval($public);
    }

    public function setSaveTransmition($saveTransmition)
    {
        $this->saveTransmition = intval($saveTransmition);
    }

    public function setShowOnTV($showOnTV)
    {
        $this->showOnTV = intval($showOnTV);
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getUsers_id()
    {
        return intval($this->users_id);
    }

    public function getLive_servers_id()
    {
        return intval($this->live_servers_id);
    }

    public function getScheduled_time()
    {
        return $this->scheduled_time;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function getPublic()
    {
        return intval($this->public);
    }

    public function getSaveTransmition()
    {
        return intval($this->saveTransmition);
    }

    public function getShowOnTV()
    {
        return intval($this->showOnTV);
    }

    public function save()
    {
        if (empty($this->live_servers_id)) {
            $this->live_servers_id = 'NULL';
        }

        if (empty($this->public)) {
            $this->public = 'NULL';
        }

        if (empty($this->saveTransmition)) {
            $this->saveTransmition = 'NULL';
        }

        if (empty($this->showOnTV)) {
            $this->showOnTV = 'NULL';
        }
        
        if (empty($this->users_id_company)) {
            $this->users_id_company = 'NULL';
        }

        if (empty($this->key)) {
            $this->key = uniqid();
        }

        $this->_setTimeZone(date_default_timezone_get());

        //$key = Live::getKeyFromUser($this->getUsers_id());
        //$this->setKey($key);

        $id = parent::save();

        if (!empty($id)) {
            $array = [];
            $array['users_id'] = $this->users_id;
            $array['stats'] = getStatsNotifications(true);
            $array['key'] = $this->key;
            $array['live_servers_id'] = $this->live_servers_id;
            $array['cleanKey'] = Live::cleanUpKey($array['key']);
            Live::notifySocketStats("socketLiveONCallback", $array);
            self::clearScheduleCache();
        }
        return $id;
    }

    public function delete()
    {
        $t = new Live_schedule($this->id);
        $array = setLiveKey($t->key, $t->live_servers_id);
        $id = parent::delete();
        if (!empty($id)) {
            $array['stats'] = getStatsNotifications(true);
            Live::notifySocketStats("socketLiveOFFCallback", $array);
            self::clearScheduleCache();
        }
        return $id;
    }

    public static function clearScheduleCache()
    {
        clearCache(true);
        deleteStatsNotifications();
        //ObjectYPT::deleteAllSessionCache();
        ObjectYPT::deleteALLCache();
    }

    public static function keyExists($key)
    {
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
            $row['scheduled_password'] = $data['scheduled_password'];
        } else {
            $row = false;
        }
        return $row;
    }
    
    function getScheduled_password() {
        return $this->scheduled_password;
    }

    function setScheduled_password($scheduled_password): void {
        $this->scheduled_password = $scheduled_password;
    }

    static function getUsers_idOrCompany($live_schedule_id) {
        $lt = new Live_schedule($live_schedule_id);
        $users_id = $lt->getUsers_id();
        if(!empty($lt->getUsers_id_company())){
            $users_id = $lt->getUsers_id_company();
        }
        return $users_id;
    }

}
