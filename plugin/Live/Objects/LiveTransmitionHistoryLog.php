<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistoryLog extends ObjectYPT {

    protected $id, $live_transmitions_history_id, $users_id, $session_id;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'live_transmition_history_log';
    }
    
    function getLive_transmitions_history_id() {
        return $this->live_transmitions_history_id;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getSession_id() {
        return $this->session_id;
    }

    function setLive_transmitions_history_id($live_transmitions_history_id) {
        $this->live_transmitions_history_id = $live_transmitions_history_id;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setSession_id($session_id) {
        $this->session_id = $session_id;
    }
    
    static function addLog($live_transmitions_history_id){
        $session_id = session_id();
        $users_id = intval(User::getId());
        
        $log = new LiveTransmitionHistoryLog(0);
        $log->setLive_transmitions_history_id($live_transmitions_history_id);
        $log->setUsers_id($users_id);
        $log->setSession_id($session_id);
        $log->save();
        
    }
    
    function getFromHistoryAndSession($live_transmitions_history_id, $session_id){
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  live_transmitions_history_id = ? AND session_id = ? ORDER BY created LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql,"is",array($live_transmitions_history_id, $session_id)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getAllFromHistory($live_transmitions_history_id) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE live_transmitions_history_id={$live_transmitions_history_id} ";

        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    function save() {
        $row = $this->getFromHistoryAndSession($this->live_transmitions_history_id, $this->session_id);
        if(!empty($row)){
            $this->id = $row['id'];
        }
        return parent::save();
    }
    
}
