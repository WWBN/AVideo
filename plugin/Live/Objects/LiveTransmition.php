<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmition extends ObjectYPT {

    protected $id, $title, $public, $saveTransmition, $users_id, $categories_id, $key, $description, $showOnTV;

    static function getSearchFieldsNames() {
        return array('title');
    }

    static function getTableName() {
        return 'live_transmitions';
    }

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getPublic() {
        return $this->public;
    }

    function getSaveTransmition() {
        return $this->saveTransmition;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getCategories_id() {
        return $this->categories_id;
    }

    function getKey() {
        return $this->key;
    }

    function getDescription() {
        return $this->description;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTitle($title) {
        global $global;
        //$title = $global['mysqli']->real_escape_string($title);
        $this->title = xss_esc($title);
    }

    function setPublic($public) {
        $this->public = intval($public);
    }

    function setSaveTransmition($saveTransmition) {
        $this->saveTransmition = $saveTransmition;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setDescription($description) {
        global $global;
        //$description = $global['mysqli']->real_escape_string($description);
        $this->description = xss_esc($description);
    }

    function loadByUser($user_id) {
        $user = self::getFromDbByUser($user_id);
        if (empty($user))
            return false;
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    function loadByKey($uuid) {
        $row = self::getFromKey($uuid);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static function getFromDbByUser($user_id) {
        global $global;
        $user_id = intval($user_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($user_id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
        } else {
            $user = false;
        }
        return $user;
    }

    static function createTransmitionIfNeed($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $row = static::getFromDbByUser($user_id);
        if ($row) {
            return $row;
        }
        $l = new LiveTransmition(0);
        $l->setTitle("Empty Title");
        $l->setDescription("");
        $l->setKey(uniqid());
        $l->setCategories_id(1);
        $l->setUsers_id($user_id);
        $l->save();
        return static::getFromDbByUser($user_id);
    }

    static function resetTransmitionKey($user_id) {
        $row = static::getFromDbByUser($user_id);

        $l = new LiveTransmition($row['id']);
        $newKey = uniqid();
        $l->setKey($newKey);
        if($l->save()){
            return $newKey;
        }else{
            return false;
        }
    }

    static function getFromDbByUserName($userName) {
        global $global;
        _mysql_connect();
        $userName = $global['mysqli']->real_escape_string($userName);
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($userName), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
            if(empty($user)){
                return false;
            }
            return static::getFromDbByUser($user['id']);
        } else {
            return false;
        }
    }

    static function keyExists($key) {
        global $global;
        if (!is_string($key)) {
            return false;
        }
        if(Live::isAdaptiveTransmition($key)){
            return false;
        }
        $key = Live::cleanUpKey($key);
        $sql = "SELECT u.*, lt.* FROM " . static::getTableName() . " lt "
                . " LEFT JOIN users u ON u.id = users_id AND u.status='a' WHERE  `key` = '$key' LIMIT 1";
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

    function save() {
        $this->public = intval($this->public);
        $this->saveTransmition = intval($this->saveTransmition);
        $this->showOnTV = intval($this->showOnTV);
        $id = parent::save();
        Category::clearCacheCount();
        Live::deleteStatsCache(true);
        
        $socketObj = sendSocketMessageToAll(array('stats'=>getStatsNotifications()), "socketLiveONCallback");
        
        return $id;
    }

    function deleteGroupsTrasmition() {
        if (empty($this->id)) {
            return false;
        }
        global $global;
        $sql = "DELETE FROM live_transmitions_has_users_groups WHERE live_transmitions_id = ?";
        return sqlDAL::writeSql($sql, "i", array($this->id));
    }

    function insertGroup($users_groups_id) {
        global $global;
        $sql = "INSERT INTO live_transmitions_has_users_groups (live_transmitions_id, users_groups_id) VALUES (?,?)";
        return sqlDAL::writeSql($sql, "ii", array($this->id, $users_groups_id));
    }
    
    function isAPrivateLive(){
        return !empty($this->getGroups());
    }
    
    function getGroups() {
        $rows = array();
        if (empty($this->id)) {
            return $rows;
        }
        global $global;
        $sql = "SELECT * FROM live_transmitions_has_users_groups WHERE live_transmitions_id = ?";
        $res = sqlDAL::readSql($sql, "i", array($this->id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row["users_groups_id"];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    function userCanSeeTransmition() {
        global $global;
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        require_once $global['systemRootPath'] . 'objects/user.php';
        if (User::isAdmin()) {
            return true;
        }

        $transmitionGroups = $this->getGroups();
        if (!empty($transmitionGroups)) {
            if (empty($this->id)) {
                return false;
            }
            if (!User::isLogged()) {
                return false;
            }
            $userGroups = UserGroups::getUserGroups(User::getId());
            if (empty($userGroups)) {
                return false;
            }
            foreach ($userGroups as $ugvalue) {
                foreach ($transmitionGroups as $tgvalue) {
                    if ($ugvalue['id'] == $tgvalue) {
                        return true;
                    }
                }
            }
            return false;
        } else {
            return true;
        }
    }

    static function getFromKey($key) {
        global $global;
        $key = Live::cleanUpKey($key);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `key` = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($key), true);
        //var_dump($sql, $key);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
        } else {
            $user = false;
        }
        return $user;
    }
    
    static function keyNameFix($key){
        $key = str_replace('/', '', $key);
        if(!empty($_REQUEST['live_index']) && !preg_match("/.*-([0-9a-zA-Z]+)/", $key)){
            if(!empty($_REQUEST['live_index']) && $_REQUEST['live_index']!=='false'){
                $key .= "-{$_REQUEST['live_index']}";
            }
        }
        if(!empty($_REQUEST['playlists_id_live']) && !preg_match("/.*_([0-9]+)/", $key)){
            $key .= "_{$_REQUEST['playlists_id_live']}";
        }
        return $key;
    }
    
    function getShowOnTV() {
        return $this->showOnTV;
    }

    function setShowOnTV($showOnTV) {
        $this->showOnTV = $showOnTV;
    }

    static function canSaveTransmition($users_id){
        $lt = self::getFromDbByUser($users_id);
        return !empty($lt['saveTransmition']);
    }

}
