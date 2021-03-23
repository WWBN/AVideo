<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistory extends ObjectYPT {

    protected $id, $title, $description, $key, $created, $modified, $users_id, $live_servers_id, $finished;

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
        $liveUsersEnabled = AVideoPlugin::isEnabledByName("LiveUsers");
        $p = AVideoPlugin::loadPlugin("Live");
        $obj = new stdClass();
        $users_id = $lth->getUsers_id();
        $u = new User($users_id);
        $live_servers_id = $lth->getLive_servers_id();
        if(empty($live_servers_id) && !empty($_REQUEST['live_servers_id'])){
            $live_servers_id = $_REQUEST['live_servers_id'];
        }
        $key = $lth->getKey();
        $title = $lth->getTitle();
        $photo = $u->getPhotoDB();
        $m3u8 = Live::getM3U8File($key);
        $poster = $global['webSiteRootURL'] . $p->getPosterImage($users_id, $live_servers_id, $lth->getLive_index());
        $playlists_id_live = 0;
        if (preg_match("/.*_([0-9]+)/", $key, $matches)) {
            if (!empty($matches[1])) {
                $_REQUEST['playlists_id_live'] = intval($matches[1]);
                $playlists_id_live = $_REQUEST['playlists_id_live'];
                $photo = PlayLists::getImage($_REQUEST['playlists_id_live']);
                $title = PlayLists::getNameOrSerieTitle($_REQUEST['playlists_id_live']);
            }
        }

        $obj->UserPhoto = $u->getPhotoDB();
        $obj->isAdaptive = self::isAdaptive($value->name);
        $obj->photo = $photo;
        $obj->channelName = $u->getChannelName();
        $obj->live_index = $lth->getLive_index();
        $obj->live_cleanKey = $lth->getLive_cleanKey();
        $obj->live_servers_id = $live_servers_id;
        $obj->href = Live::getLinkToLiveFromUsers_idAndLiveServer($users_id, $live_servers_id, $obj->live_index);
        $obj->key = $key;
        $obj->isPrivate = Live::isAPrivateLiveFromLiveKey($obj->key);
        $obj->link = addQueryStringParameter($obj->href, 'embed', 1);
        $obj->name = $u->getNameIdentificationBd();
        $obj->playlists_id_live = $playlists_id_live;
        $obj->poster = $poster;
        $obj->title = $title;
        $obj->user = $u->getUser();
        $users = false;
        if ($liveUsersEnabled) {
            $filename = $global['systemRootPath'] . 'plugin/LiveUsers/Objects/LiveOnlineUsers.php';
            if (file_exists($filename)) {
                require_once $filename;
                $liveUsers = new LiveOnlineUsers(0);
                $users = $liveUsers->getUsersFromTransmitionKey($key, $live_servers_id);
            }
        }
        $obj->users = $users;
        
        $obj->m3u8 =$m3u8;
        $obj->isURL200 = isURL200($m3u8);
        $obj->users_id = $users_id;
        
        return $obj;
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

    static function getLatest($key, $live_servers_id=null) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `key` = ? ";
        if(isset($live_servers_id)){
            $sql .= " AND live_servers_id = ".intval($live_servers_id);
        }
        $sql .= " ORDER BY created DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about

        $res = sqlDAL::readSql($sql, "s", array($key));
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

    public function save() {
        if (empty($this->live_servers_id)) {
            $this->live_servers_id = 'NULL';
        }

        AVideoPlugin::onLiveStream($this->users_id, $this->live_servers_id);

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
