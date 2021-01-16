<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Playlists_schedules extends ObjectYPT {

    protected $id,$playlists_id,$name,$description,$status,$loop,$start_datetime,$finish_datetime,$repeat,$parameters;
    
    static $REPEAT_MONTHLY = 'm';
    static $REPEAT_WEEKLY = 'w';
    static $REPEAT_DAYLY = 'd';
    static $REPEAT_NEVER = 'n';

    static function getSearchFieldsNames() {
        return array('name','description','parameters');
    }

    static function getTableName() {
        return 'playlists_schedules';
    }    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setPlaylists_id($playlists_id) {
        $this->playlists_id = intval($playlists_id);
    } 
 
    function setName($name) {
        $this->name = $name;
    } 
 
    function setDescription($description) {
        $this->description = $description;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
 
    function setLoop($loop) {
        $this->loop = intval($loop);
    } 
 
    function setStart_datetime($start_datetime) {
        $this->start_datetime = $start_datetime;
    } 
 
    function setFinish_datetime($finish_datetime) {
        $this->finish_datetime = $finish_datetime;
    } 
 
    function setRepeat($repeat) {
        $this->repeat = $repeat;
    } 
 
    function setParameters($parameters) {
        $this->parameters = $parameters;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getPlaylists_id() {
        return intval($this->playlists_id);
    }  
 
    function getName() {
        return $this->name;
    }  
 
    function getDescription() {
        return $this->description;
    }  
 
    function getStatus() {
        return $this->status;
    }  
 
    function getLoop() {
        return intval($this->loop);
    }  
 
    function getStart_datetime() {
        return $this->start_datetime;
    }  
 
    function getFinish_datetime() {
        return $this->finish_datetime;
    }  
 
    function getRepeat() {
        return $this->repeat;
    }  
 
    function getParameters() {
        return $this->parameters;
    }  

        
}
