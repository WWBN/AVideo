<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Cache_schedule_delete extends ObjectYPT {

    protected $id,$name;
    
    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'cache_schedule_delete';
    }
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setName($name) {
        $this->name = $name;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getName() {
        return $this->name;
    }  

    public static function insert($name) {
        $sql = "INSERT IGNORE INTO cache_schedule_delete (name) VALUES (?)";
        $res = sqlDAL::writeSql($sql, "s", [$name]);
        if ($res) {
            return true;
        } else {
            error_log("ObjectYPT::insert::Error on save: " . $sql . " Error : " . json_encode($res));
            return false;
        }
    }
        
}
