<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Publisher_social_medias extends ObjectYPT {

    protected $id,$name,$api_details,$status,$timezone;
    
    static function getSearchFieldsNames() {
        return array('name','api_details','timezone');
    }

    static function getTableName() {
        return 'publisher_social_medias';
    }
    
    static function getFromProvider($provider)
    {
        global $global;
        if(!class_exists('sqlDAL')){
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "s", [$provider], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setName($name) {
        if (!$item = SocialMediaPublisher::getProiderItem($name)) {
            $msg = "Invalid provider {$name}";
            _error_log($msg);
            forbiddenPage($msg);
        }
        $this->name = $name;
    } 
 
    function setApi_details($api_details) {
        $this->api_details = $api_details;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
 
    function setTimezone($timezone) {
        $this->timezone = $timezone;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getName() {
        return $this->name;
    }  
 
    function getApi_details() {
        return $this->api_details;
    }  
 
    function getStatus() {
        return $this->status;
    }  
 
    function getTimezone() {
        return $this->timezone;
    }  

        
}
