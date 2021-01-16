<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_restreams extends ObjectYPT {

    protected $id,$name,$stream_url,$stream_key,$status,$parameters,$users_id;
    
    static function getSearchFieldsNames() {
        return array('name','stream_url','stream_key','parameters');
    }

    static function getTableName() {
        return 'live_restreams';
    }
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setName($name) {
        $this->name = $name;
    } 
 
    function setStream_url($stream_url) {
        $this->stream_url = $stream_url;
    } 
 
    function setStream_key($stream_key) {
        $this->stream_key = $stream_key;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
 
    function setParameters($parameters) {
        $this->parameters = $parameters;
    } 
 
    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getName() {
        return $this->name;
    }  
 
    function getStream_url() {
        return $this->stream_url;
    }  
 
    function getStream_key() {
        return $this->stream_key;
    }  
 
    function getStatus() {
        return $this->status;
    }  
 
    function getParameters() {
        return $this->parameters;
    }  
 
    function getUsers_id() {
        return intval($this->users_id);
    }  


    static function getAllFromUser($users_id, $status = 'a') {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        
        $users_id = intval($users_id);
        if(empty($users_id)){
            return false;
        }
        
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = $users_id ";

        if(!empty($status)){
           $sql .= " AND status = '$status' " ;
        }
        
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
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
        
}
