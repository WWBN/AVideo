<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
/*
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
*/
class VastCampaignsLogs extends ObjectYPT
{
    protected $id;
    protected $users_id;
    protected $type;
    protected $vast_campaigns_has_videos_id;
    protected $ip;

    public static function getSearchFieldsNames()
    {
        return [];
    }

    public static function getTableName()
    {
        return 'vast_campaigns_logs';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsers_id()
    {
        return $this->users_id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVast_campaigns_has_videos_id()
    {
        return $this->vast_campaigns_has_videos_id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = $users_id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setVast_campaigns_has_videos_id($vast_campaigns_has_videos_id)
    {
        $this->vast_campaigns_has_videos_id = $vast_campaigns_has_videos_id;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function save()
    {
        $this->ip = getRealIpAddr();
        return parent::save();
    }

    public static function getViews()
    {
        global $global;
        $sql = "SELECT count(*) as total FROM vast_campaigns_logs WHERE `type` = 'start'";

        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row['total'];
    }

    public static function getData($vast_campaigns_has_videos_id)
    {
        global $global;
        $sql = "SELECT `type`, count(*) as total FROM vast_campaigns_logs WHERE vast_campaigns_has_videos_id = $vast_campaigns_has_videos_id GROUP BY `type`";

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $data = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $data[$row['type']] = $row['total'];
            }
        } 
        return $data;
    }

    public static function getDataFromCampaign($vast_campaigns_id)
    {
        global $global;
        $sql = "SELECT `type`, count(vast_campaigns_id) as total FROM vast_campaigns_logs vcl "
                . " LEFT JOIN vast_campaigns_has_videos vchv ON vast_campaigns_has_videos_id = vchv.id "
                . " WHERE vast_campaigns_id = $vast_campaigns_id GROUP BY `type`";
        //echo $sql."\n";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $data = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $data[$row['type']] = $row['total'];
            }
        } 
        return $data;
    }
}
