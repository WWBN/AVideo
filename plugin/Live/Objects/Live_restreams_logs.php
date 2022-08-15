<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_restreams_logs extends ObjectYPT {

    protected $id,$restreamer,$m3u8,$logFile,$json,$live_transmitions_history_id,$live_restreams_id;
    
    static function getSearchFieldsNames() {
        return array('restreamer','m3u8','logFile','json');
    }

    static function getTableName() {
        return 'live_restreams_logs';
    }    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setRestreamer($restreamer) {
        if(!isValidURL($restreamer)){
            return false;
        }
        $this->restreamer = $restreamer;
    } 
 
    function setM3u8($m3u8) {
        if(!isValidURL($m3u8)){
            return false;
        }
        $this->m3u8 = $m3u8;
    } 
 
    function setLogFile($logFile) {
        $logFile = basename($logFile);
        $this->logFile = $logFile;
    } 
 
    function setJson($json) {
        $this->json = $json;
    } 
 
    function setLive_transmitions_history_id($live_transmitions_history_id) {
        $this->live_transmitions_history_id = intval($live_transmitions_history_id);
    } 
 
    function setLive_restreams_id($live_restreams_id) {
        $this->live_restreams_id = intval($live_restreams_id);
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
 
    function getLogFile() {
        return $this->logFile;
    }  
 
    function getJson() {
        return $this->json;
    }  
 
    function getLive_transmitions_history_id() {
        return intval($this->live_transmitions_history_id);
    }  
 
    function getLive_restreams_id() {
        return intval($this->live_restreams_id);
    }  

        
}
