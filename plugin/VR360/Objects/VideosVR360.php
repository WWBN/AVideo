<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class VideosVR360 extends ObjectYPT {

    protected $id, $videos_id, $clickAndDrag, $showNotice, $initLat, $initLon, $backToVerticalCenter, $videoType, $active;

    protected $intVal = array(
        'videos_id', 
        'clickAndDrag', 
        'showNotice', 
        'initLat', 
        'initLon', 
        'backToVerticalCenter', 
        'active'
        );


    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'videos_VR360';
    }
    
    protected function loadFromVideo($videos_id) {
        $row = self::getFromVideoDb($videos_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            if(in_array($key, $this->intVal)){
                $value = intval($value);
            }
            $this->$key = $value;
        }
        return true;
    }

    static protected function getFromVideoDb($videos_id) {
        global $global;
        $videos_id = intval($videos_id);
        $sql = "SELECT * FROM ".static::getTableName()." WHERE  videos_id = $videos_id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function isVR360Enabled($videos_id){
        $vr = new VideosVR360(0);
        $vr->loadFromVideo($videos_id);
        return !empty($vr->getActive());
    }
    
    static function isVR360EnabledByVideoCleanTitle($clean_title){
        $video = Video::getVideoFromCleanTitle($clean_title);
        return self::isVR360Enabled($video['id']); 
    }
    
    static function toogleVR360($videos_id){
        if(!User::canUpload()){
            return false;
        }
        $vr = new VideosVR360(0);
        $vr->loadFromVideo($videos_id);
        $vr->setVideos_id($videos_id);
        $vr->setActive(intval(!$vr->getActive()));
        $vr->save();
        return $vr->getActive();
    }
    static function toogleVR360ByVideoCleanTitle($clean_title){
        $video = Video::getVideoFromCleanTitle($clean_title);
        return self::toogleVR360($video['id']); 
    }
    
    function getId() {
        return $this->id;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getClickAndDrag() {
        return $this->clickAndDrag;
    }

    function getShowNotice() {
        return $this->showNotice;
    }

    function getInitLat() {
        return $this->initLat;
    }

    function getInitLon() {
        return $this->initLon;
    }

    function getBackToVerticalCenter() {
        return $this->backToVerticalCenter;
    }

    function getVideoType() {
        return $this->videoType;
    }

    function getActive() {
        return $this->active;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = intval($videos_id);
    }

    function setClickAndDrag($clickAndDrag) {
        $this->clickAndDrag = intval($clickAndDrag);
    }

    function setShowNotice($showNotice) {
        $this->showNotice = intval($showNotice);
    }

    function setInitLat($initLat) {
        $this->initLat = intval($initLat);
    }

    function setInitLon($initLon) {
        $this->initLon = intval($initLon);
    }

    function setBackToVerticalCenter($backToVerticalCenter) {
        $this->backToVerticalCenter = intval($backToVerticalCenter);
    }

    function setVideoType($videoType) {
        $this->videoType = $videoType;
    }

    function setActive($active) {
        $this->active = intval($active);
    }


}
