<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';

class VastCampaigns extends ObjectYPT
{
    protected $id;
    protected $name;
    protected $type;
    protected $status;
    protected $start_date;
    protected $end_date;
    protected $pricing_model;
    protected $price;
    protected $max_impressions;
    protected $max_clicks;
    protected $priority;
    protected $users_id;
    protected $visibility;
    protected $cpc_budget_type;
    protected $cpc_total_budget;
    protected $cpc_max_price_per_click;
    protected $cpm_max_prints;
    protected $cpm_current_prints;

    public static function getSearchFieldsNames()
    {
        return ['name'];
    }

    public static function getTableName()
    {
        return 'vast_campaigns';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStart_date()
    {
        return $this->start_date;
    }

    public function getEnd_date()
    {
        return $this->end_date;
    }

    public function getPricing_model()
    {
        return $this->pricing_model;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getMax_impressions()
    {
        return $this->max_impressions;
    }

    public function getMax_clicks()
    {
        return $this->max_clicks;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getUsers_id()
    {
        return $this->users_id;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function getCpc_budget_type()
    {
        return $this->cpc_budget_type;
    }

    public function getCpc_total_budget()
    {
        return $this->cpc_total_budget;
    }

    public function getCpc_max_price_per_click()
    {
        return $this->cpc_max_price_per_click;
    }

    public function getCpm_max_prints()
    {
        return $this->cpm_max_prints;
    }

    public function getCpm_current_prints()
    {
        return $this->cpm_current_prints;
    }

    public function getPrintsLeft()
    {
        return ($this->cpm_max_prints-$this->cpm_current_prints);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setStart_date($start_date)
    {
        $this->start_date = $start_date;
    }

    public function setEnd_date($end_date)
    {
        $this->end_date = $end_date;
    }

    public function setPricing_model($pricing_model)
    {
        $this->pricing_model = $pricing_model;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setMax_impressions($max_impressions)
    {
        $this->max_impressions = $max_impressions;
    }

    public function setMax_clicks($max_clicks)
    {
        $this->max_clicks = $max_clicks;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = $users_id;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    public function setCpc_budget_type($cpc_budget_type)
    {
        $this->cpc_budget_type = $cpc_budget_type;
    }

    public function setCpc_total_budget($cpc_total_budget)
    {
        $this->cpc_total_budget = $cpc_total_budget;
    }

    public function setCpc_max_price_per_click($cpc_max_price_per_click)
    {
        $this->cpc_max_price_per_click = $cpc_max_price_per_click;
    }

    public function setCpm_max_prints($cpm_max_prints)
    {
        $this->cpm_max_prints = $cpm_max_prints;
    }

    public function setCpm_current_prints($cpm_current_prints)
    {
        $this->cpm_current_prints = $cpm_current_prints;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function save()
    {
        $this->cpm_current_prints = intval($this->cpm_current_prints);
        if (empty($this->visibility)) {
            $this->visibility = 'listed';
        }
        if (empty($this->cpc_budget_type)) {
            $this->cpc_budget_type = 'Campaign Total';
        }
        if (empty($this->cpc_total_budget)) {
            $this->cpc_total_budget = 0;
        }
        if (empty($this->cpc_max_price_per_click)) {
            $this->cpc_max_price_per_click = 0;
        }
        if (empty($this->visibility)) {
            $this->visibility = 'listed';
        }

        return parent::save();
    }

    public function addVideo($videos_id, $status = 'a')
    {
        $vast_campaigns_id = $this->getId();
        if (empty($vast_campaigns_id)) {
            $this->setId($this->save());
            $vast_campaigns_id = $this->getId();
        }
        $campainVideos = new VastCampaignsVideos(0);
        $campainVideos->loadFromCampainVideo($vast_campaigns_id, $videos_id);
        $campainVideos->setStatus($status);
        return $campainVideos->save();
    }

    public static function getValidCampaigns()
    {
        global $global;
        $ad_server_location = AVideoPlugin::loadPluginIfEnabled('AD_Server_Location');
        AVideoPlugin::loadPlugin('User_Location');
        $User_Location = User_Location::getSessionLocation();
        $sql = "SELECT * from " . static::getTableName() . " vc  WHERE status = 'a' AND start_date <= now() AND end_date >=now() AND cpm_max_prints > cpm_current_prints ";
        if (!empty($ad_server_location) && !empty($User_Location) && $User_Location['country_name'] !== '-') {
            // show only campaign for the user location
            $sql .= " AND ( (vc.id IN (SELECT vast_campaigns_id FROM campaign_locations WHERE (country_name = 'All' OR country_name IS NULL OR country_name = '') OR  "
                    . " (country_name = \"{$User_Location['country_name']}\" AND region_name = 'All') OR "
                    . " (country_name = \"{$User_Location['country_name']}\" AND region_name = \"{$User_Location['region_name']}\" AND city_name = 'All') OR"
                    . " (country_name = \"{$User_Location['country_name']}\" AND region_name = \"{$User_Location['region_name']}\" AND city_name = \"{$User_Location['city_name']}\") ) ) "
                    . " OR vc.id NOT IN(SELECT vast_campaigns_id FROM campaign_locations) )";
        }

        $sql .= " ORDER BY priority ";
        //echo $sql;
        //_error_log($sql);
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = [];
        if ($res != false) {
            foreach ($rows as $row) {
                $row['printsLeft'] = $row['cpm_max_prints'] - $row['cpm_current_prints'];
                $r[] = $row;
            }
        }

        return $r;
    }

    public static function getAll()
    {
        global $global;
        $ad_server_location = AVideoPlugin::loadPluginIfEnabled('AD_Server_Location');
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['data'] = VastCampaignsLogs::getDataFromCampaign($row['id']);
                $row['printsLeft'] = $row['cpm_max_prints'] - $row['cpm_current_prints'];
                if (!empty($ad_server_location)) {
                    $row['locations'] = $ad_server_location->getCampaignLocations($row['id']);
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function addView()
    {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET cpm_current_prints = cpm_current_prints+1 ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$this->id]);
        }
        _error_log("Id for table " . static::getTableName() . " not defined for add view");
        return false;
    }

    public function delete()
    {
        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM vast_campaigns_logs ";
            $sql .= " WHERE vast_campaigns_has_videos_id IN (SELECT id FROM vast_campaigns_has_videos WHERE vast_campaigns_id = ?)";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            $campaigns_video_log = sqlDAL::writeSql($sql, "i", [$this->id]);


            $sql = "DELETE FROM vast_campaigns_has_videos ";
            $sql .= " WHERE vast_campaigns_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            $campaigns_video = sqlDAL::writeSql($sql, "i", [$this->id]);
        }
        return parent::delete();
    }

    public static function getValidCampaignsFromVideo($videos_id)
    {
        global $global;

        $sql = "SELECT vchv.*, vc.* from " . static::getTableName() . " vc LEFT JOIN vast_campaigns_has_videos vchv ON vchv.vast_campaigns_id = vc.id WHERE vc.status = 'a' "
                . " AND start_date <= now() AND end_date >=now() AND cpm_max_prints > cpm_current_prints AND videos_id = {$videos_id}";
        //echo $sql;
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = [];
        if ($res != false) {
            foreach ($rows as $row) {
                $row['printsLeft'] = $row['cpm_max_prints'] - $row['cpm_current_prints'];
                $r[] = $row;
            }
        }

        return $r;
    }
}
