<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Monetize_user_reward_log extends ObjectYPT {

    protected $id,$videos_id,$video_owner_users_id,$percentage_watched,$seconds_watching_video,$when_watched,$who_watched_users_id, $total_reward;
    
    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'monetize_user_reward_log';
    }
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setVideos_id($videos_id) {
        $this->videos_id = intval($videos_id);
    } 
 
    function setVideo_owner_users_id($video_owner_users_id) {
        $this->video_owner_users_id = intval($video_owner_users_id);
    } 
 
    function setPercentage_watched($percentage_watched) {
        $this->percentage_watched = $percentage_watched;
    } 
 
    function setSeconds_watching_video($seconds_watching_video) {
        $this->seconds_watching_video = intval($seconds_watching_video);
    } 
 
    function setWhen_watched($when_watched) {
        $this->when_watched = $when_watched;
    } 
 
    function setWho_watched_users_id($who_watched_users_id) {
        $this->who_watched_users_id = intval($who_watched_users_id);
    } 
 
    function setTotal_reward($total_reward) {
        $this->total_reward = floatval($total_reward);
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getVideos_id() {
        return intval($this->videos_id);
    }  
 
    function getVideo_owner_users_id() {
        return intval($this->video_owner_users_id);
    }  
 
    function getPercentage_watched() {
        return $this->percentage_watched;
    }  
 
    function getSeconds_watching_video() {
        return intval($this->seconds_watching_video);
    }  
 
    function getWhen_watched() {
        return $this->when_watched;
    }  
 
    function getWho_watched_users_id() {
        return intval($this->who_watched_users_id);
    }  
 
    function getTotal_reward() {
        return $this->total_reward;
    }  

    static function getLastRewardTime(){
        global $global;
        $sql = "SELECT MAX(created_php_time) as created_php_time FROM monetize_user_reward_log";
        $res = sqlDAL::readSql($sql, '', [], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res && !empty($data['created_php_time'])) {
            _error_log("MonetizeUsers getLastRewardTime {$when_from}");
            return $data['created_php_time'];
        } else {
            return strtotime('-24 hours');
        }
    }
    
    public function save() {
        if(empty($this->percentage_watched)){
            $this->percentage_watched = 0;
        }
        return parent::save();
    }
}
