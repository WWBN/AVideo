<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistory extends ObjectYPT {

    protected $id, $title, $description, $key, $created, $modified, $users_id;

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
    
    function getAllFromUser($users_id){
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = ? ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, "i", array($users_id)); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
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
    
    static function getLatest($key){
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  `key` = ? ORDER BY created DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        
        $res = sqlDAL::readSql($sql,"s",array($key)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function save() {
        YouPHPTubePlugin::onLiveStream($this->users_id);
        return parent::save();
    }
    
}
