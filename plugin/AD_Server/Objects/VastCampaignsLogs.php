<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';

class VastCampaignsLogs extends ObjectYPT {

    protected $id, $users_id, $type, $vast_campaigns_has_videos_id, $ip;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'vast_campaigns_logs';
    }
    
    function getId() {
        return $this->id;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getType() {
        return $this->type;
    }

    function getVast_campaigns_has_videos_id() {
        return $this->vast_campaigns_has_videos_id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setVast_campaigns_has_videos_id($vast_campaigns_has_videos_id) {
        $this->vast_campaigns_has_videos_id = $vast_campaigns_has_videos_id;
    }

    function getIp() {
        return $this->ip;
    }
    
    function save() {
        $this->ip = getRealIpAddr();
        return parent::save();
    }

    static function getData($vast_campaigns_has_videos_id){
        global $global;
        $sql = "SELECT `type`, count(*) as total FROM vast_campaigns_logs WHERE vast_campaigns_has_videos_id = $vast_campaigns_has_videos_id GROUP BY `type`";

        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $data = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $data[$row['type']] = $row['total'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $data;
    }
    
    static function getDataFromCampaign($vast_campaigns_id){
        global $global;
        $sql = "SELECT `type`, count(vast_campaigns_id) as total FROM vast_campaigns_logs vcl LEFT JOIN vast_campaigns_has_videos vchv ON vast_campaigns_id = vchv.id WHERE vast_campaigns_id = $vast_campaigns_id GROUP BY `type`";

        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $data = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $data[$row['type']] = $row['total'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $data;
    }



}
