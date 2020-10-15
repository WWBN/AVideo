<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class logincontrol_history extends ObjectYPT {

    protected $id,$users_id,$uniqidV4,$ip,$user_agent,$confirmation_code,$status;
    
    static function getSearchFieldsNames() {
        return array('uniqidV4','ip','user_agent','confirmation_code');
    }

    static function getTableName() {
        return 'logincontrol_history';
    }
    
    static function getAllUsers() {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    } 
 
    function setUniqidV4($uniqidV4) {
        $this->uniqidV4 = $uniqidV4;
    } 
 
    function setIp($ip) {
        $this->ip = $ip;
    } 
 
    function setUser_agent($user_agent) {
        $this->user_agent = $user_agent;
    } 
 
    function setConfirmation_code($confirmation_code) {
        $this->confirmation_code = $confirmation_code;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getUsers_id() {
        return intval($this->users_id);
    }  
 
    function getUniqidV4() {
        return $this->uniqidV4;
    }  
 
    function getIp() {
        return $this->ip;
    }  
 
    function getUser_agent() {
        return $this->user_agent;
    }  
 
    function getConfirmation_code() {
        return $this->confirmation_code;
    }  
 
    function getStatus() {
        return $this->status;
    }  

    
    static function getLastLogins($users_id, $limit=50){
        global $global;
        $table = "users";
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = {$users_id} ORDER BY modified DESC LIMIT {$limit} ";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['device'] = self::getDeviceName($row['user_agent']);
                $row['ago'] = humanTimingAgo($row['created']);
                $row['time_ago'] = "{$row['created']} ({$row['ago']})";
                $row['type'] = ($row['status']!==logincontrol_history_status::$CONFIRMED)?__("Failed login attempt"):__("Successfully logged in");
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function getDeviceName($user_agent){
        $device = get_browser_name($user_agent);
        $device .= " - " . getOS($user_agent);
        return $device;
    }
    
    static function getLastLogin($users_id){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? ORDER BY modified DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($users_id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            if(!empty($row['created'])){
                $row['device'] = self::getDeviceName($row['user_agent']);
                $row['ago'] = humanTimingAgo($row['created']);
                $row['time_ago'] = "{$row['created']} ({$row['ago']})";
                $row['type'] = ($row['status']!==logincontrol_history_status::$CONFIRMED)?__("Failed login attempt"):__("Successfully logged in");
            }else{
                $row = false;
            }
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getPreviewsLogin($users_id){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? ORDER BY modified DESC LIMIT 1, 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($users_id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            if(!empty($row)){
                $row['device'] = self::getDeviceName($row['user_agent']);
                $row['ago'] = humanTimingAgo($row['created']);
                $row['time_ago'] = "{$row['created']} ({$row['ago']})";
                $row['type'] = ($row['status']!==logincontrol_history_status::$CONFIRMED)?__("Failed login attempt"):__("Successfully logged in");
            }
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getPreviewsConfirmedLogin($users_id){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ?  AND status = '".logincontrol_history_status::$CONFIRMED."' ORDER BY modified DESC LIMIT 1, 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($users_id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            if(!empty($row)){
                $row['device'] = self::getDeviceName($row['user_agent']);
                $row['ago'] = humanTimingAgo($row['created']);
                $row['time_ago'] = "{$row['created']} ({$row['ago']})";
                $row['type'] = ($row['status']!==logincontrol_history_status::$CONFIRMED)?__("Failed login attempt"):__("Successfully logged in");
            }
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getLastConfirmedLogin($users_id){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND status = '".logincontrol_history_status::$CONFIRMED."' ORDER BY modified DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($users_id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            if(!empty($row)){
                $row['device'] = self::getDeviceName($row['user_agent']);
                $row['ago'] = humanTimingAgo($row['created']);
                $row['time_ago'] = "{$row['created']} ({$row['ago']})";
                $row['type'] = ($row['status']!==logincontrol_history_status::$CONFIRMED)?__("Failed login attempt"):__("Successfully logged in");
            }
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function is2FAConfirmed($users_id, $uniqidV4){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND uniqidV4 = ? AND status = '".logincontrol_history_status::$CONFIRMED."' ORDER BY modified DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "is", array($users_id, $uniqidV4), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getLastLoginAttempt($users_id, $uniqidV4){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND uniqidV4 = ? ORDER BY modified DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "is", array($users_id, $uniqidV4), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
}

class logincontrol_history_status{
    static $WAITING_CONFIRMATION = 'w';
    static $CONFIRMED = 'c';
}