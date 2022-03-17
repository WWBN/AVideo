<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';

class VastCampaignsVideos extends ObjectYPT
{
    protected $id;
    protected $vast_campaigns_id;
    protected $videos_id;
    protected $status;
    protected $link;
    protected $ad_title;

    public static function getSearchFieldsNames()
    {
        return [];
    }

    public static function getTableName()
    {
        return 'vast_campaigns_has_videos';
    }

    public function loadFromCampainVideo($vast_campaigns_id, $videos_id)
    {
        $row = self::getCampainVideo($vast_campaigns_id, $videos_id);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    protected static function getCampainVideo($vast_campaigns_id, $videos_id)
    {
        global $global;
        $vast_campaigns_id = intval($vast_campaigns_id);
        $videos_id = intval($videos_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  vast_campaigns_id = ? , videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "ii", [$vast_campaigns_id, $videos_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getRandomCampainVideo($vast_campaigns_id)
    {
        global $global;
        $vast_campaigns_id = intval($vast_campaigns_id);
        if (empty($vast_campaigns_id)) {
            $campaings = VastCampaigns::getValidCampaigns();
            if (empty($campaings[0])) {
                return false;
            }
            $vast_campaigns_id = $campaings[0]['id'];
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  vast_campaigns_id = ? ORDER BY RAND() LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", [$vast_campaigns_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getAllFromCampaign($vast_campaigns_id, $getImages = false)
    {
        global $global;
        $vast_campaigns_id = intval($vast_campaigns_id);
        $sql = "SELECT v.*, c.* FROM  " . static::getTableName() . " c "
                . " LEFT JOIN videos v ON videos_id = v.id WHERE 1=1 ";

        if (!empty($vast_campaigns_id)) {
            $sql .= " AND vast_campaigns_id=$vast_campaigns_id ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                if ($getImages) {
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

    public static function getValidVideos($vast_campaigns_id)
    {
        global $global;

        $sql = "SELECT v.*, c.* from " . static::getTableName() . " c LEFT JOIN videos v ON v.id = videos_id WHERE vast_campaigns_id = ? AND c.status = 'a' ";

        $res = sqlDAL::readSql($sql, "i", [$vast_campaigns_id]);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = [];
        if ($res!=false) {
            foreach ($rows as $row) {
                $r[] = $row;
            }
        }

        return $r;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getVast_campaigns_id()
    {
        return $this->vast_campaigns_id;
    }

    public function getVideos_id()
    {
        return $this->videos_id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setVast_campaigns_id($vast_campaigns_id)
    {
        $this->vast_campaigns_id = $vast_campaigns_id;
    }

    public function setVideos_id($videos_id)
    {
        $this->videos_id = $videos_id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getAd_title()
    {
        return $this->ad_title;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function setAd_title($ad_title)
    {
        $this->ad_title = $ad_title;
    }

    public function delete()
    {
        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM vast_campaigns_logs ";
            $sql .= " WHERE vast_campaigns_has_videos_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            $campaigns_video_log = sqlDAL::writeSql($sql, "i", [$this->id]);
        }
        return parent::delete();
    }

    public function save()
    {
        if (empty($this->vast_campaigns_id) || strtolower($this->vast_campaigns_id)=='null') {
            return false;
        }
        return parent::save();
    }
}
