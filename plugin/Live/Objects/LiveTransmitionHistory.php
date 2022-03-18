<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistory extends ObjectYPT {

    protected $id;
    protected $title;
    protected $description;
    protected $key;
    protected $created;
    protected $modified;
    protected $users_id;
    protected $live_servers_id;
    protected $finished;
    protected $domain;
    protected $json;
    protected $max_viewers_sametime;
    protected $total_viewers;
    protected $users_id_company;

    public static function getSearchFieldsNames() {
        return ['title', 'description'];
    }

    public static function getTableName() {
        return 'live_transmitions_history';
    }

    function getUsers_id_company(): int {
        return intval($this->users_id_company);
    }

    function setUsers_id_company($users_id_company): void {
        $this->users_id_company = intval($users_id_company);
    }
        
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getKey() {
        return $this->key;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getModified() {
        return $this->modified;
    }

    public function getUsers_id() {
        return $this->users_id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getFinished() {
        return $this->finished;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getJson() {
        return $this->json;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function setJson($json) {
        $this->json = $json;
    }

    public function setTitle($title) {
        global $global;
        $title = $global['mysqli']->real_escape_string($title);
        $this->title = $title;
    }

    public function setDescription($description) {
        global $global;
        $description = $global['mysqli']->real_escape_string($description);
        $this->description = $description;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setModified($modified) {
        $this->modified = $modified;
    }

    public function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    public function getLive_servers_id() {
        return intval($this->live_servers_id);
    }

    public function getLive_index() {
        if (empty($this->key)) {
            return '';
        }
        $parameters = Live::getLiveParametersFromKey($this->key);
        return $parameters['live_index'];
    }

    public function getLive_cleanKey() {
        if (empty($this->key)) {
            return '';
        }
        $parameters = Live::getLiveParametersFromKey($this->key);
        return $parameters['cleanKey'];
    }
    
    function getMax_viewers_sametime() {
        return intval($this->max_viewers_sametime);
    }

    function getTotal_viewers() {
        return intval($this->total_viewers);
    }

    function setMax_viewers_sametime($max_viewers_sametime): void {
        $this->max_viewers_sametime = intval($max_viewers_sametime);
    }

    function setTotal_viewers($total_viewers): void {
        $this->total_viewers = intval($total_viewers);
    }
    
    public static function getApplicationObject($liveTransmitionHistory_id) {
        global $global;
        $_playlists_id_live = @$_REQUEST['playlists_id_live'];
        unset($_REQUEST['playlists_id_live']);
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);

        $users_id = $lth->getUsers_id();
        $key = $lth->getKey();
        $title = $lth->getTitle();
        $live_servers_id = $lth->getLive_servers_id();
        $playlists_id_live = 0;

        $type = 'LiveObject';

        if (preg_match("/.*_([0-9]+)/", $key, $matches)) {
            if (!empty($matches[1])) {
                $_REQUEST['playlists_id_live'] = intval($matches[1]);
                $playlists_id_live = $_REQUEST['playlists_id_live'];
                $imgJPG = PlayLists::getImage($_REQUEST['playlists_id_live']);
                $title = PlayLists::getNameOrSerieTitle($_REQUEST['playlists_id_live']);
            }
        }

        $p = AVideoPlugin::loadPlugin("Live");
        $imgJPG = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $lth->getLive_index());
        $imgGIF = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $lth->getLive_index(), 'webp');
        $link = Live::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $lth->getLive_index());
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($key, $live_servers_id) : '');
        $uid = "{$type}_{$liveTransmitionHistory_id}";
        $title = Live::getTitleFromKey($key, $title);
        //getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, $type, $LiveUsersLabelLive='', $uid ='', $callback='', $startsOnDate='', $class='')
        $obj = Live::getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, $type, $LiveUsersLabelLive, $uid, '', '', "live_{$key}");
        $obj['key'] = $key;
        $obj['live_transmitions_history_id'] = $liveTransmitionHistory_id;
        $obj['isPrivate'] = self::isPrivate($liveTransmitionHistory_id);
        $obj['isPasswordProtected'] = self::isPasswordProtected($liveTransmitionHistory_id);
        $obj['method'] = 'LiveTransmitionHistory::getApplicationObject';
        $_REQUEST['playlists_id_live'] = $_playlists_id_live;
        return $obj;
    }
    
    public static function isPrivate($liveTransmitionHistory_id) {
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        $key = $lth->getKey();
        if(!empty($key)){
            $lt = LiveTransmition::getFromKey($key);
            if(empty($lt['public'])){
                return true;
            }
        }
        return false;
    }
    
    public static function isPasswordProtected($liveTransmitionHistory_id) {
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        $key = $lth->getKey();
        if(!empty($key)){
            return Live::isPasswordProtected($key);
        }
        return false;
    }

    public static function getStatsAndAddApplication($liveTransmitionHistory_id) {
        $stats = getStatsNotifications();
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);

        $key = $lth->getKey();
        if (!empty($stats['applications'])) {
            foreach ($stats['applications'] as $value) {
                if (empty($value['key'])) {
                    continue;
                }
                $value = object_to_array($value);
                $value['key'] = self::getCleankeyName($value['key']);
                if (!empty($value['key']) && $value['key'] == $key) { // application is already in the list
                    return $stats;
                }
            }
        }
        if (!empty($stats['hidden_applications'])) {
            foreach ($stats['hidden_applications'] as $value) {
                if (empty($value['key'])) {
                    continue;
                }
                $value = object_to_array($value);
                $value['key'] = self::getCleankeyName($value['key']);
                if ($value['key'] == $key) { // application is already in the list
                    return $stats;
                }
            }
        }
        $application = self::getApplicationObject($liveTransmitionHistory_id);
        if ($application->isPrivate) {
            $stats['hidden_applications'][] = $application;
        } else {
            $stats['applications'][] = $application;
        }
        $stats['countLiveStream']++;

        $cacheName = "getStats" . DIRECTORY_SEPARATOR . "getStatsNotifications";
        $cache = ObjectYPT::setCache($cacheName, $stats); // update the cache
        //_error_log("NGINX getStatsAndAddApplication ". json_encode($stats));
        //_error_log("NGINX getStatsAndAddApplication ". json_encode($cache));

        return $stats;
    }

    public static function getCleankeyName($key) {
        $parts = explode("_", $key);
        if (!empty($parts[1])) {
            $adaptive = ['hi', 'low', 'mid'];
            if (in_array($parts[1], $adaptive)) {
                return $parts[0];
            }
        }
        return $key;
    }

    public static function getStatsAndRemoveApplication($liveTransmitionHistory_id) {
        $stats = getStatsNotifications();
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);

        $key = $lth->getKey();
        foreach ($stats['applications'] as $k => $value) {
            $value = object_to_array($value);
            if (!empty($value['key']) && $value['key'] == $key) { // application is already in the list
                unset($stats['applications'][$k]);
                $stats['countLiveStream']--;
            }
        }
        if (empty($stats['hidden_applications'])) {
            $stats['hidden_applications'] = [];
        } else {
            foreach ($stats['hidden_applications'] as $k => $value) {
                $value = object_to_array($value);
                if ($value['key'] == $key) { // application is already in the list
                    unset($stats['hidden_applications'][$k]);
                }
            }
        }

        $cacheName = "getStats" . DIRECTORY_SEPARATOR . "getStatsNotifications";
        $cache = ObjectYPT::setCache($cacheName, $stats); // update the cache
        return $stats;
    }

    public function setLive_servers_id($live_servers_id) {
        $this->live_servers_id = intval($live_servers_id);
    }

    public static function getAllFromUser($users_id=0, $onlyWithViewers=false) {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT *, "
                . " (SELECT count(id) FROM  live_transmition_history_log WHERE live_transmitions_history_id=lth.id ) as total_viewers_from_history "
                . " FROM  " . static::getTableName() . " lth "
                . " WHERE 1=1 ";
        
        if(!empty($users_id)){
            $sql .= " AND users_id = $users_id ";
        }
        
        if($onlyWithViewers){
            $sql .= " AND (total_viewers>0 OR (SELECT count(id) FROM  live_transmition_history_log WHERE live_transmitions_history_id=lth.id )>0) ";
        }
        
        $sql .= self::getSqlFromPost();
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                if(empty($row['total_viewers'])){
                    $row['total_viewers'] = $row['total_viewers_from_history'];
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function isLive($key) {
        global $global;

        $row = self::getActiveLiveFromUser(0, '', $key);
        //var_dump($key, $row);exit;
        if (empty($row)) {
            return false;
        }
        return self::getApplicationObject($row['id']);
    }

    public static function getLatest($key, $live_servers_id = null, $active=false) {
        global $global;

        $key = $global['mysqli']->real_escape_string($key);

        if (empty($key)) {
            return false;
        }

        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `key` LIKE '{$key}%' ";
        if (isset($live_servers_id)) {
            $sql .= " AND (live_servers_id = " . intval($live_servers_id);

            if (empty($live_servers_id)) {
                $sql .= " OR live_servers_id IS NULL ";
            }
            $sql .= " )";
        }
        if($active){
            $sql .= " AND finished IS NULL ";
        }
        $sql .= " ORDER BY created DESC LIMIT 1";
        //var_dump($sql, $key);exit;

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

    public static function finish($key) {
        $row = self::getLatest($key);
        if (empty($row) || empty($row['id']) || !empty($row['finished'])) {
            return false;
        }

        return self::finishFromTransmitionHistoryId($row['id']);
    }

    public static function finishFromTransmitionHistoryId($live_transmitions_history_id) {
        global $global;
        $live_transmitions_history_id = intval($live_transmitions_history_id);
        if (empty($live_transmitions_history_id)) {
            _error_log('LiveTransmitionHistory::finishALLOffline ERROR empty live_transmitions_history_id ');
            return false;
        }
        _error_log('LiveTransmitionHistory::finishALLOffline finishing $live_transmitions_history_id '. json_encode(debug_backtrace()));
        $sql = "UPDATE " . static::getTableName() . " SET finished = now() WHERE id = {$live_transmitions_history_id} ";

        $insert_row = sqlDAL::writeSql($sql);
        $global['mysqli']->commit();
        return $insert_row;
    }

    public static function finishALL($olderThan = '') {
        $sql = "UPDATE " . static::getTableName() . " SET finished = now() WHERE finished IS NULL ";

        if (!empty($olderThan)) {
            $sql .= " modified < " . date('Y-m-d H:i:s', strtotime($olderThan));
        }

        $insert_row = sqlDAL::writeSql($sql);

        return $insert_row;
    }

    public static function finishALLOffline() {
        $rows = self::getActiveLives();
        $modified = array();
        $keysChecked = array();
        foreach ($rows as $value) {
            if(in_array($keysChecked, $value['key'])){
                _error_log("LiveTransmitionHistory::finishALLOffline the key {$value['key']} is in another history record and will be finished ");
                self::finishFromTransmitionHistoryId($value['id']);
                $modified[] = $value['id'];
                continue;
            }
            $keysChecked[] = $value['key'];
            $m3u8 = Live::getM3U8File($value['key'], true, true);
            $isURL200 = isValidM3U8Link($m3u8);
            if (empty($isURL200)) {
                _error_log('LiveTransmitionHistory::finishALLOffline will be finished '.$m3u8);
                self::finishFromTransmitionHistoryId($value['id']);
                $modified[] = $value['id'];
            }else{
                _error_log('LiveTransmitionHistory::finishALLOffline still online '.$m3u8);
            }
        }
        if(!empty($modified)){
            Live::deleteStatsCache(true);
        }
        return $modified;
    }

    public static function getLatestFromUser($users_id) {
        $rows = self::getLastsLiveHistoriesFromUser($users_id, 1);
        return @$rows[0];
    }

    public static function getLatestFromKey($key) {
        global $global;
        if (!self::isTableInstalled(static::getTableName())) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        $parts = Live::getLiveParametersFromKey($key);
        $key = $parts['cleanKey'];

        $sql = "SELECT * FROM " . static::getTableName() . " WHERE `key` LIKE '{$key}%'  ";

        $sql .= " ORDER BY created DESC LIMIT 1";
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

    public static function getLatestIndexFromKey($key) {
        $row = self::getLatestFromKey($key);
        return Live::getLiveIndexFromKey(@$row['key']);
    }

    public static function getLastsLiveHistoriesFromUser($users_id, $count = 10, $finishedOnly = false) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `users_id` = ? ";
        if($finishedOnly){
            $sql .= " AND finished IS NOT NULL ";
        }
        $sql .= " ORDER BY created DESC LIMIT ?";

        $res = sqlDAL::readSql($sql, "ii", [$users_id, $count]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $log = LiveTransmitionHistoryLog::getAllFromHistory($row['id']);
                $row['totalUsers'] = count($log);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getActiveLives($live_servers_id = '') {
        global $global;
        if (!self::isTableInstalled(static::getTableName())) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE finished IS NULL ";

        $formats = "";
        $values = [];
        
        if (strtolower($live_servers_id) == 'null') {
            $sql .= ' AND `live_servers_id` IS NULL ';
        }else if (!empty($live_servers_id)) {
            $sql .= ' AND `live_servers_id` = ? ';
            $formats .= "i";
            $values[] = $live_servers_id;
        }

        $sql .= " ORDER BY created DESC";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            $total = count($fullData);
            foreach ($fullData as $row) {
                if ($total < 10 && strtotime($row['modified']) < strtotime('-1 hour')) {
                    // check if the m3u8 file still exists
                    $m3u8 = Live::getM3U8File($row['key'], false, true);
                    $isURL200 = isValidM3U8Link($m3u8);
                    if (empty($isURL200)) {
                        self::finishFromTransmitionHistoryId($row['id']);
                        //var_dump($isURL200, $m3u8, $row);exit;
                        continue;
                    } else {
                        // update it to make sure the modified date is updated
                        $lth = new LiveTransmitionHistory($row['id']);
                        $lth->save();
                    }
                }
                $log = LiveTransmitionHistoryLog::getAllFromHistory($row['id']);
                $row['totalUsers'] = count($log);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    public static function getActiveLiveFromUser($users_id, $live_servers_id = '', $key = '', $count = 1) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE finished IS NULL ";

        $formats = "";
        $values = [];

        if (!empty($users_id)) {
            $sql .= ' AND `users_id` = ? ';
            $formats .= "i";
            $values[] = $users_id;
        }
        if (strtolower($live_servers_id) == 'null') {
            $sql .= ' AND `live_servers_id` IS NULL ';
        }else if (!empty($live_servers_id)) {
            $sql .= ' AND `live_servers_id` = ? ';
            $formats .= "i";
            $values[] = $live_servers_id;
        }
        if (!empty($key)) {
            $sql .= ' AND `key` = ? ';
            $formats .= "s";
            $values[] = $key;
        }

        $sql .= " ORDER BY created DESC LIMIT {$count}";
        $res = sqlDAL::readSql($sql, $formats, $values);
        if ($count == 1) {
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res) {
                $row = $data;
            } else {
                $row = false;
            }
            if (empty($row)) {
                _error_log('LiveTransmitionHistory::getActiveLiveFromUser: ' . $sql . " formats=$formats ".json_encode($values));
            }
            return $row;
        } else {
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = [];
            if ($res != false) {
                $total = count($fullData);
                foreach ($fullData as $row) {
                    if ($total < 10 && strtotime($row['modified']) < strtotime('-1 hour')) {
                        // check if the m3u8 file still exists
                        $m3u8 = Live::getM3U8File($row['key']);
                        $isURL200 = isValidM3U8Link($m3u8);
                        if (empty($isURL200)) {
                            self::finishFromTransmitionHistoryId($row['id']);
                            //var_dump($isURL200, $m3u8, $row);exit;
                            continue;
                        } else {
                            // update it to make sure the modified date is updated
                            $lth = new LiveTransmitionHistory($row['id']);
                            $lth->save();
                        }
                    }
                    $log = LiveTransmitionHistoryLog::getAllFromHistory($row['id']);
                    $row['totalUsers'] = count($log);
                    $rows[] = $row;
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $rows;
        }
    }

    public function save() {
        global $global;
        
        $activeLive = self::getActiveLiveFromUser($this->users_id, $this->live_servers_id, $this->key);
        if(!empty($activeLive)){
            //_error_log("LiveTransmitionHistory::save: active live found ". json_encode($activeLive));
            foreach ($activeLive as $key => $value) {
                if(empty($this->$key)){
                    $this->$key = $value;
                }
            }
        }else{
            //_error_log("LiveTransmitionHistory::save: active live NOT found ");
        }
        if(empty($this->id)){
            // if is creating a new make sure all 
        }
        if (empty($this->live_servers_id)) {
            $this->live_servers_id = 'NULL';
        }
        if (empty($this->finished)) {
            $this->finished = 'NULL';
        }
        if (empty($this->users_id_company)) {
            $this->users_id_company = 'NULL';
        }
        
        $this->max_viewers_sametime = intval($this->max_viewers_sametime);
        $this->total_viewers = intval($this->total_viewers);
        
        $id = parent::save();
        //_error_log("LiveTransmitionHistory::save: id=$id ($this->users_id, $this->live_servers_id, $this->key) ". json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $global['mysqli']->commit();
        return $id;
    }

    public static function deleteAllFromLiveServer($live_servers_id) {
        global $global;
        $live_servers_id = intval($live_servers_id);
        if (!empty($live_servers_id)) {
            global $global;
            $sql = "SELECT id FROM  " . static::getTableName() . " WHERE live_servers_id = ? ";

            $sql .= self::getSqlFromPost();
            $res = sqlDAL::readSql($sql, "i", [$live_servers_id]);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = [];
            if ($res != false) {
                foreach ($fullData as $row) {
                    $lt = new LiveTransmitionHistory($row['id']);
                    $lt->delete();
                }
            }
        }
    }

    public function delete() {
        if (!empty($this->id)) {
            LiveTransmitionHistoryLog::deleteAllFromHistory($this->id);
        }
        return parent::delete();
    }
    
    public static function getLinkToLive($live_transmitions_history_id) {
        if (empty($live_transmitions_history_id)) {
            return false;
        }
        
        $lt = new LiveTransmitionHistory($live_transmitions_history_id);
        if(empty($lt->getUsers_id())){
            return false;
        }
        return Live::getLinkToLiveFromUsers_idAndLiveServer($lt->getUsers_id(), $lt->getLive_servers_id(), $lt->getLive_index());
    }
    
    static function getUsers_idOrCompany($live_transmitions_history_id) {
        $lt = new LiveTransmitionHistory($live_transmitions_history_id);
        $users_id = $lt->getUsers_id();
        if(!empty($lt->getUsers_id_company())){
            $users_id = $lt->getUsers_id_company();
        }
        return $users_id;
    }

}
