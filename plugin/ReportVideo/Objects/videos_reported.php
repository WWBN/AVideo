<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class VideosReported extends ObjectYPT {

    protected $id, $obs, $videos_id, $users_id;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'videos_reported';
    }    

    static function getFromDbUserAndVideo($users_id, $videos_id) {
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
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




}
