<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class VideosReported extends ObjectYPT {

    protected $id, $obs, $videos_id, $users_id, $status, $reported_users_id;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'videos_reported';
    }

    static function getFromDbUserAndVideo($users_id, $videos_id) {
        global $global;
        if(!self::isTableInstalled()){
            _error_log("We cannot report/block users yet, you need to install the tables", AVideoLog::$ERROR);
            return array();
        }

        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql,"ii",array($users_id, $videos_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getFromDbUserAndReportedUser($users_id, $reported_users_id) {
        global $global;
        if(!self::isTableInstalled()){
            _error_log("We cannot report/block users yet, you need to install the tables", AVideoLog::$ERROR);
            return array();
        }

        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND reported_users_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql,"ii",array($users_id, $reported_users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getAllReportedUsersIdFromUser($users_id) {
        global $global;

        if(!self::isTableInstalled()){
            _error_log("We cannot report/block users yet, you need to install the tables", AVideoLog::$ERROR);
            return array();
        }

        $users_id = intval($users_id);
        if(empty($users_id)){
            return array();
        }

        if(!self::isTableInstalled()){
            return array();
        }
        
        $sql = "SELECT reported_users_id FROM " . static::getTableName() . " WHERE  users_id = ? LIMIT 1";

        $res = sqlDAL::readSql($sql,"i",array($users_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row['reported_users_id'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    function getId() {
        return $this->id;
    }

    function getObs() {
        return $this->obs;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setObs($obs) {
        $this->obs = $obs;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function getStatus() {
        return $this->status;
    }

    function getReported_users_id() {
        return $this->reported_users_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setReported_users_id($reported_users_id) {
        $this->reported_users_id = $reported_users_id;
    }

    function save() {
        if(empty($this->status)){
            $this->status = 'a';
        }

        return parent::save();
    }

}
