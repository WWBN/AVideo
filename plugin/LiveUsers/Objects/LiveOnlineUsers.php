<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveOnlineUsers extends Object {

    protected $id, $users_id, $transmition_key, $session_id;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'live_online_users';
    }

    function getTransmition_key() {
        return $this->transmition_key;
    }

    function getSession_id() {
        return $this->session_id;
    }

    function setTransmition_key($transmition_key) {
        $this->transmition_key = $transmition_key;
    }

    function save() {
        $this->users_id = intval(User::getId());
        $this->session_id = session_id();
        return parent::save();
    }

    function loadFromTransmitionKey($transmition_key) {
        $this->session_id = session_id();
        $this->transmition_key = $transmition_key;
        $userQuery = "";
        if(User::isLogged()){
            $userQuery = " OR users_id = ". User::getId();
        }
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE (session_id = ? {$userQuery}) AND transmition_key = ? ";
        if ($stmt = $global['mysqli']->prepare($sql)) {

            /* bind parameters for markers */
            $stmt->bind_param("ss", $this->session_id, $this->transmition_key);
            $stmt->execute();
            /* instead of bind_result: */
            $result = $stmt->get_result();
            /* now you can fetch the results into an array */
            if ($result && $myrow = $result->fetch_assoc()) {
                foreach ($myrow as $key => $value) {
                    $this->$key = $value;
                }
                return true;
            }
        }
        return false;
    }
    
    function getUsersFromTransmitionKey($transmition_key, $secondsInactive = 5){
        $obj = new stdClass();
        $obj->online = 0;
        $obj->views = 0;
        $obj->transmition_key = $transmition_key;
        $obj->secondsInactive = $secondsInactive;
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE transmition_key = ? ";
        if ($stmt = $global['mysqli']->prepare($sql)) {

            /* bind parameters for markers */
            $stmt->bind_param("s", $transmition_key);
            $stmt->execute();
            /* instead of bind_result: */
            $result = $stmt->get_result();
            /* now you can fetch the results into an array */
            while($result && $myrow = $result->fetch_assoc()) {
                $obj->views++;
                if(strtotime($myrow['modified'])>strtotime("-{$secondsInactive} seconds")){
                    $obj->online++;
                }
            }
        }
        return $obj;
    }

}
