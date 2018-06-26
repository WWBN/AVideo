<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';

class VastCampaignsVideos extends ObjectYPT {

    protected $id, $vast_campaigns_id, $videos_id, $status, $link, $ad_title;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'vast_campaigns_has_videos';
    }
    
    function loadFromCampainVideo($vast_campaigns_id, $videos_id){
        $row = self::getCampainVideo($vast_campaigns_id, $videos_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
    
    static protected function getCampainVideo($vast_campaigns_id, $videos_id) {
        global $global;
        $vast_campaigns_id = intval($vast_campaigns_id);
        $videos_id = intval($videos_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  vast_campaigns_id = ? , videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql,"ii",array($vast_campaigns_id, $videos_id)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getAllFromCampaign($vast_campaigns_id, $getImages = false) {
        global $global;
        $sql = "SELECT v.*, c.* FROM  " . static::getTableName() . " c "
                . " LEFT JOIN videos v ON videos_id = v.id WHERE 1=1 ";
        
        if(!empty($vast_campaigns_id)){
            $sql .= " AND vast_campaigns_id=$vast_campaigns_id ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                if($getImages){
                    $row['poster'] = Video::getImageFromID($row['videos_id']);
                }
                $row['data'] = VastCampaignsLogs::getDataFromCampaign($row['vast_campaigns_id']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static public function getValidVideos($vast_campaigns_id){
        global $global;

            $sql = "SELECT v.*, c.* from " . static::getTableName() . " c LEFT JOIN videos v ON v.id = videos_id WHERE vast_campaigns_id = ? AND c.status = 'a' ";

            $res = sqlDAL::readSql($sql,"i",array($vast_campaigns_id)); 
            $rows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $r = array();
            if ($res!=false) {
                foreach($rows as $row) {
                    $r[] = $row;
                }
            }

            return $r;
    }
    
    function getId() {
        return $this->id;
    }

    function getVast_campaigns_id() {
        return $this->vast_campaigns_id;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getStatus() {
        return $this->status;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setVast_campaigns_id($vast_campaigns_id) {
        $this->vast_campaigns_id = $vast_campaigns_id;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }
    
    function getLink() {
        return $this->link;
    }

    function getAd_title() {
        return $this->ad_title;
    }

    function setLink($link) {
        $this->link = $link;
    }

    function setAd_title($ad_title) {
        $this->ad_title = $ad_title;
    }



}
