<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistory extends ObjectYPT {

    protected $id, $title, $description, $key, $created, $modified, $users_id, $live_servers_id, $finished, $domain, $json;

    static function getSearchFieldsNames() {
        return array('title', 'description');
    }

    static function getTableName() {
        return 'live_transmitions_history';
    }

    function getId() {
        return $this->id;
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

    function getCreated() {
        return $this->created;
    }

    function getModified() {
        return $this->modified;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function setId($id) {
        $this->id = $id;
    }
    
    function getFinished() {
        return $this->finished;
    }
    
    function getDomain() {
        return $this->domain;
    }

    function getJson() {
        return $this->json;
    }

    function setDomain($domain) {
        $this->domain = $domain;
    }

    function setJson($json) {
        $this->json = $json;
    }

        
    function setTitle($title) {
        global $global;
        $title = $global['mysqli']->real_escape_string($title);
        $this->title = $title;
    }

    function setDescription($description) {
        global $global;
        $description = $global['mysqli']->real_escape_string($description);
        $this->description = $description;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setModified($modified) {
        $this->modified = $modified;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function getLive_servers_id() {
        return intval($this->live_servers_id);
    }
    
    function getLive_index() {
        if(empty($this->key)){
            return '';
        }
        $parameters = Live::getLiveParametersFromKey($this->key);
        return $parameters['live_index'];
    }
    
    function getLive_cleanKey() {
        if(empty($this->key)){
            return '';
        }
        $parameters = Live::getLiveParametersFromKey($this->key);
        return $parameters['cleanKey'];
    }

    static function getApplicationObject($liveTransmitionHistory_id) {
        global $global;
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
        $imgGif = $p->getLivePosterImage($users_id, $live_servers_id, $playlists_id_live, $lth->getLive_index(), 'webp');
        $link = Live::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $lth->getLive_index());
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $LiveUsersLabelLive = ($liveUsersEnabled ? getLiveUsersLabelLive($key, $live_servers_id) : '');
            
            
            
        return Live::getLiveApplicationModelArray($users_id, $title, $link, $imgJPG, $imgGIF, $type, $LiveUsersLabelLive);
        
    }

    static function getStatsAndAddApplication($liveTransmitionHistory_id) {
        $stats = getStatsNotifications();
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        
        $key = $lth->getKey();
        if(!empty($stats['applications'])){
            foreach ($stats['applications'] as $value) {
                if(empty($value['key'])){
                    continue;
                }
                $value = object_to_array($value);
                $value['key']= self::getCleankeyName($value['key']);
                if(!empty($value['key']) && $value['key']==$key){ // application is already in the list
                    return $stats; 
                }
            }
        }
        if(!empty($stats['hidden_applications'])){
            foreach ($stats['hidden_applications'] as $value) {
                if(empty($value['key'])){
                    continue;
                }
                $value = object_to_array($value);
                $value['key']= self::getCleankeyName($value['key']);
                if($value['key']==$key){ // application is already in the list
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
    
    static function getCleankeyName($key){
        $parts = explode("_", $key);
        if(!empty($parts[1])){
            $adaptive = array('hi', 'low', 'mid');
            if(in_array($parts[1], $adaptive)){
                return $parts[0];
            }
        }
        return $key;
    }

    static function getStatsAndRemoveApplication($liveTransmitionHistory_id) {
        $stats = getStatsNotifications();
        $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
        
        $key = $lth->getKey();
        foreach ($stats['applications'] as $k => $value) {
            $value = object_to_array($value);
            if(!empty($value['key']) && $value['key']==$key){ // application is already in the list
                unset($stats['applications'][$k]);
                $stats['countLiveStream']--;
            }
        }
        if(empty($stats['hidden_applications'])){
            $stats['hidden_applications'] = array();
        }else{
            foreach ($stats['hidden_applications'] as $k => $value) {
                $value = object_to_array($value);
                if($value['key']==$key){ // application is already in the list
                    unset($stats['hidden_applications'][$k]);
                }
            }
        }
        
        $cacheName = "getStats" . DIRECTORY_SEPARATOR . "getStatsNotifications";
        $cache = ObjectYPT::setCache($cacheName, $stats); // update the cache
        return $stats;
    }

    function setLive_servers_id($live_servers_id) {
        $this->live_servers_id = intval($live_servers_id);
    }

    static function getAllFromUser($users_id) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = ? ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, "i", array($users_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
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

    static function isLive($key) {
        global $global;
        
        $row = self::getActiveLiveFromUser(0, '', $key);
        
        return self::getApplicationObject($row['id']);
    }
    
    static function getLatest($key, $live_servers_id=null) {
        global $global;
        
        $key = $global['mysqli']->real_escape_string($key);
        
        if(empty($key)){
            return false;
        }
        
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `key` LIKE '{$key}%' ";
        if(isset($live_servers_id)){
            $sql .= " AND (live_servers_id = ".intval($live_servers_id);
            
            if(empty($live_servers_id)){
                $sql .= " OR live_servers_id IS NULL ";
            }
            $sql .= " )";
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
    
    static function finish($key) {
        $row = self::getLatest($key);
        if(empty($row) || empty($row['id']) || !empty($row['finished'])){
            return false;
        }

        return self::finishFromTransmitionHistoryId($row['id']);
    }
    
    static function finishFromTransmitionHistoryId($live_transmitions_history_id) {
        $live_transmitions_history_id = intval($live_transmitions_history_id);
        if(empty($live_transmitions_history_id)){
            return false;
        }
        
        $sql = "UPDATE " . static::getTableName() . " SET finished = now() WHERE id = {$live_transmitions_history_id} ";
        
        $insert_row = sqlDAL::writeSql($sql);

        return $insert_row;
    }

    static function getLatestFromUser($users_id) {
        $rows = self::getLastsLiveHistoriesFromUser($users_id, 1);
        return @$rows[0];
    }

    static function getLatestFromKey($key) {
        global $global;
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
    
    static function getLatestIndexFromKey($key) {
        $row = self::getLatestFromKey($key);
        return Live::getLiveIndexFromKey(@$row['key']);
    }
    
    static function getLastsLiveHistoriesFromUser($users_id, $count=10) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `users_id` = ? ORDER BY created DESC LIMIT ?";
        
        $res = sqlDAL::readSql($sql, "ii", array($users_id, $count));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
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
    
    static function getActiveLiveFromUser($users_id, $live_servers_id='', $key='') {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE finished IS NULL ";
        
        $formats = ""; 
        $values = array();
        
        if(!empty($users_id)){
            $sql .= ' AND `users_id` = ? ';
            $formats .= "i"; 
            $values[] = $users_id;
        }
        if(!empty($live_servers_id)){
            $sql .= ' AND `live_servers_id` = ? ';
            $formats .= "i"; 
            $values[] = $live_servers_id;
        }
        if(!empty($key)){
            $sql .= ' AND `key` = ? ';
            $formats .= "s"; 
            $values[] = $key;
        }
        
        $sql .= " ORDER BY created DESC LIMIT 1";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        if(empty($row)){
            _error_log('LiveTransmitionHistory::getActiveLiveFromUser: '.$sql." [$users_id, $live_servers_id, $key]");
        }
        return $row;
    }

    public function save() {
        if (empty($this->live_servers_id)) {
            $this->live_servers_id = 'NULL';
        }

        return parent::save();
    }

    static function deleteAllFromLiveServer($live_servers_id) {
        global $global;
        $live_servers_id = intval($live_servers_id);
        if (!empty($live_servers_id)) {
            global $global;
            $sql = "SELECT id FROM  " . static::getTableName() . " WHERE live_servers_id = ? ";

            $sql .= self::getSqlFromPost();
            $res = sqlDAL::readSql($sql, "i", array($live_servers_id));
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
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

}