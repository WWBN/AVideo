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

        
}
