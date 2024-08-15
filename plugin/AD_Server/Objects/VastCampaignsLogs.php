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
    protected $user_agent;
    protected $videos_id;
    protected $json;

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

    
    public function getVideos_id()
    {
        return $this->videos_id;
    }

    public function setVideos_id($videos_id)
    {
        $this->videos_id = $videos_id;
    }


    public function save()
    {
        _error_log("ADSLOG {$this->type}".json_encode(array(debug_backtrace(), User::getId(), $_SERVER)));
        $this->ip = getRealIpAddr();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $vast_campaigns_logs_id = parent::save();
        $obj = AVideoPlugin::getDataObject('AD_Server');
        if($this->type == AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED ){
            if($obj->onlyRewardLoggedUsers && !User::isLogged()){
                // only reward logged users
                return $vast_campaigns_logs_id;
            }
            self::reward($vast_campaigns_logs_id);
        }
        return $vast_campaigns_logs_id;
    }

    static function reward($vast_campaigns_logs_id){
        $log = new VastCampaignsLogs($vast_campaigns_logs_id);
        $vast_campaigns_has_videos_id = $log->getVast_campaigns_has_videos_id();
        $vast_campaign_videos = new VastCampaignsVideos($vast_campaigns_has_videos_id);
        $vast_campaigns_id = $vast_campaign_videos->getVast_campaigns_id();
        $vast_campaign = new VastCampaigns($vast_campaigns_id);
        $reward_per_impression = $vast_campaign->getReward_per_impression();
        if(!empty($reward_per_impression)){
            $plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
            $videos_id = $vast_campaign_videos->getVideos_id();
            $video = new Video('', '', $videos_id);
            $campaignName = $vast_campaign->getName();
            $users_id = $video->getUsers_id();
            $array = array('vast_campaigns_has_videos_id'=>$vast_campaigns_has_videos_id);
            $plugin->addBalance($users_id, $reward_per_impression, "AD Reward from Campaign [$campaignName] video={$videos_id} users_id={$users_id}", $array);
        }
    }

    public static function getViews()
    {
        global $global;
        $sql = "SELECT count(*) as total FROM vast_campaigns_logs WHERE `type` = ?";

        $res = sqlDAL::readSql($sql, 's', [AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED]);
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

    static function getCampaignLogs($vast_campaigns_id, $start_date, $end_date) {
        global $global;
    
        // Ensure dates are in the correct format (Y-m-d H:i:s)
        $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
        $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
    
        // Prepare the SQL query
        $sql = "SELECT 
                    logs.*, 
                    vchv.videos_id  as campaign_videos_id
                FROM 
                    vast_campaigns_logs logs
                INNER JOIN 
                    vast_campaigns_has_videos vchv ON logs.vast_campaigns_has_videos_id = vchv.id
                WHERE 
                    vchv.vast_campaigns_id = ? 
                    AND logs.type = '".AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED."' 
                    AND logs.created BETWEEN ? AND ?";
    
        // Execute the query
        $res = sqlDAL::readSql($sql, 'iss', array($vast_campaigns_id, $start_date, $end_date));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }
    
}
