<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_restreams_logs extends ObjectYPT {

    protected $id,$restreamer,$m3u8,$destinations,$logFile,$users_id,$json;
    
    static function getSearchFieldsNames() {
        return array('restreamer','m3u8','destinations','logFile','json');
    }

    static function getTableName() {
        return 'live_restreams_logs';
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
 
    function setRestreamer($restreamer) {
        if(isValidURL($restreamer)){
            $this->restreamer = $restreamer;
        }else{
            _error_log("setRestreamer($restreamer) invalid URL");
        }
    } 
 
    function setM3u8($m3u8) {
        if(isValidURL($m3u8)){
            $this->m3u8 = $m3u8;
        }
    } 
 
    function setDestinations($destinations) {
        $this->destinations = $destinations;
    } 
 
    function setLogFile($logFile) {
        //$logFile = preg_replace('/[^a-z0-9/_.-]/i', '', $logFile);
        $this->logFile = $logFile;
    } 
 
    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    } 
 
    function setJson($json) {
        $this->json = $json;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getRestreamer() {
        return $this->restreamer;
    }  
 
    function getM3u8() {
        return $this->m3u8;
    }  
 
    function getDestinations() {
        return $this->destinations;
    }  
 
    function getLogFile() {
        return $this->logFile;
    }  
 
    function getUsers_id() {
        return intval($this->users_id);
    }  
 
    function getJson() {
        return $this->json;
    }  

        
}
