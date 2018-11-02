<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';

class VastCampaigns extends ObjectYPT {

    protected $id, $name, $type, $status, $start_date, $end_date, $pricing_model,
            $price, $max_impressions, $max_clicks, $priority, $users_id, $visibility, $cpc_budget_type, $cpc_total_budget, $cpc_max_price_per_click, $cpm_max_prints, $cpm_current_prints;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'vast_campaigns';
    }

    function getName() {
        return $this->name;
    }

    function getType() {
        return $this->type;
    }

    function getStatus() {
        return $this->status;
    }

    function getStart_date() {
        return $this->start_date;
    }

    function getEnd_date() {
        return $this->end_date;
    }

    function getPricing_model() {
        return $this->pricing_model;
    }

    function getPrice() {
        return $this->price;
    }

    function getMax_impressions() {
        return $this->max_impressions;
    }

    function getMax_clicks() {
        return $this->max_clicks;
    }

    function getPriority() {
        return $this->priority;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getVisibility() {
        return $this->visibility;
    }

    function getCpc_budget_type() {
        return $this->cpc_budget_type;
    }

    function getCpc_total_budget() {
        return $this->cpc_total_budget;
    }

    function getCpc_max_price_per_click() {
        return $this->cpc_max_price_per_click;
    }

    function getCpm_max_prints() {
        return $this->cpm_max_prints;
    }

    function getCpm_current_prints() {
        return $this->cpm_current_prints;
    }
    
    function getPrintsLeft() {
        return ($this->cpm_max_prints-$this->cpm_current_prints);
    }

    function setName($name) {
        $this->name = $name;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setStart_date($start_date) {
        $this->start_date = $start_date;
    }

    function setEnd_date($end_date) {
        $this->end_date = $end_date;
    }

    function setPricing_model($pricing_model) {
        $this->pricing_model = $pricing_model;
    }

    function setPrice($price) {
        $this->price = $price;
    }

    function setMax_impressions($max_impressions) {
        $this->max_impressions = $max_impressions;
    }

    function setMax_clicks($max_clicks) {
        $this->max_clicks = $max_clicks;
    }

    function setPriority($priority) {
        $this->priority = $priority;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setVisibility($visibility) {
        $this->visibility = $visibility;
    }

    function setCpc_budget_type($cpc_budget_type) {
        $this->cpc_budget_type = $cpc_budget_type;
    }

    function setCpc_total_budget($cpc_total_budget) {
        $this->cpc_total_budget = $cpc_total_budget;
    }

    function setCpc_max_price_per_click($cpc_max_price_per_click) {
        $this->cpc_max_price_per_click = $cpc_max_price_per_click;
    }

    function setCpm_max_prints($cpm_max_prints) {
        $this->cpm_max_prints = $cpm_max_prints;
    }

    function setCpm_current_prints($cpm_current_prints) {
        $this->cpm_current_prints = $cpm_current_prints;
    }

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function save() {
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

    function addVideo($videos_id, $status = 'a') {
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

    static public function getValidCampaigns() {
        global $global;

        $ad_server_location = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server_Location');
        $sql = "SELECT * from " . static::getTableName() . " vc  WHERE status = 'a' AND start_date <= now() AND end_date >=now() AND cpm_max_prints > cpm_current_prints ";
        if(!empty($ad_server_location) && !empty($_SESSION['User_Location']) && $_SESSION['User_Location']['country_name'] !== '-'){
            // show only campaign for the user location
            $sql .= " AND ( (vc.id IN (SELECT vast_campaigns_id FROM campaign_locations WHERE (country_name = 'All' OR country_name IS NULL OR country_name = '') OR  "
                    . " (country_name = \"{$_SESSION['User_Location']['country_name']}\" AND region_name = 'All') OR "
                    . " (country_name = \"{$_SESSION['User_Location']['country_name']}\" AND region_name = \"{$_SESSION['User_Location']['region_name']}\" AND city_name = 'All') OR"
                    . " (country_name = \"{$_SESSION['User_Location']['country_name']}\" AND region_name = \"{$_SESSION['User_Location']['region_name']}\" AND city_name = \"{$_SESSION['User_Location']['city_name']}\") ) ) "
                    . " OR vc.id NOT IN(SELECT vast_campaigns_id FROM campaign_locations) )";
        }

        $sql .= " ORDER BY priority ";
        //echo $sql;
        //error_log($sql);
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = array();
        if ($res != false) {
            foreach ($rows as $row) {
                $row['printsLeft'] = $row['cpm_max_prints'] - $row['cpm_current_prints'];
                $r[] = $row;
            }
        }

        return $r;
    }

    static function getAll() {
        global $global;
        $ad_server_location = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server_Location');
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
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

    function addView() {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET cpm_current_prints = cpm_current_prints+1 ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($this->id));
        }
        error_log("Id for table " . static::getTableName() . " not defined for add view");
        return false;
    }

    function delete() {
        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM vast_campaigns_logs ";
            $sql .= " WHERE vast_campaigns_has_videos_id IN (SELECT id FROM vast_campaigns_has_videos WHERE vast_campaigns_id = ?)";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            $campaigns_video_log = sqlDAL::writeSql($sql, "i", array($this->id));


            $sql = "DELETE FROM vast_campaigns_has_videos ";
            $sql .= " WHERE vast_campaigns_id = ?";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            $campaigns_video = sqlDAL::writeSql($sql, "i", array($this->id));
        }
        return parent::delete();
    }
    
    static public function getValidCampaignsFromVideo($videos_id) {
        global $global;

        $sql = "SELECT vchv.*, vc.* from " . static::getTableName() . " vc LEFT JOIN vast_campaigns_has_videos vchv ON vchv.vast_campaigns_id = vc.id WHERE vc.status = 'a' "
                . " AND start_date <= now() AND end_date >=now() AND cpm_max_prints > cpm_current_prints AND videos_id = {$videos_id}";
        //echo $sql;
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = array();
        if ($res != false) {
            foreach ($rows as $row) {
                $row['printsLeft'] = $row['cpm_max_prints'] - $row['cpm_current_prints'];
                $r[] = $row;
            }
        }

        return $r;
    }

}
