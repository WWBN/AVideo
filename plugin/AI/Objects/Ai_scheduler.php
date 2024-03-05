<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_scheduler extends ObjectYPT {

    static $statusActive = 'a';
    static $statusInactive = 'i';
    static $statusExecuted = 'e';

    static $typeCutVideo = 'cutVideo';


    protected $id,$json,$status,$ai_scheduler_type,$created_php_time,$modified_php_time;
    
    static function getSearchFieldsNames() {
        return array('ai_scheduler_type');
    }

    static function getTableName() {
        return 'ai_scheduler';
    }
         
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setJson($json) {
        if(!is_string($json)){
            $json = _json_encode($json);
        }
        $this->json = $json;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
 
    function setAi_scheduler_type($ai_scheduler_type) {
        $this->ai_scheduler_type = $ai_scheduler_type;
    } 
 
    function setCreated_php_time($created_php_time) {
        $this->created_php_time = $created_php_time;
    } 
 
    function setModified_php_time($modified_php_time) {
        $this->modified_php_time = $modified_php_time;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getJson() {
        return $this->json;
    }  
 
    function getStatus() {
        return $this->status;
    }  
 
    function getAi_scheduler_type() {
        return $this->ai_scheduler_type;
    }  
 
    function getCreated_php_time() {
        return $this->created_php_time;
    }  
 
    function getModified_php_time() {
        return $this->modified_php_time;
    }  

        
}
