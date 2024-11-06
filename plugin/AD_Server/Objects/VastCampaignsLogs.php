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
    protected $video_position;
    protected $external_referrer;

    public function getVideo_position()
    {
        return $this->video_position;
    }

    public function setVideo_position($video_position)
    {
        $this->video_position = intval($video_position);
    }

    public function getExternal_referrer()
    {
        return $this->external_referrer;
    }

    public function setExternal_referrer($external_referrer)
    {
        $this->external_referrer = $external_referrer;
    }

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
        //_error_log("ADSLOG {$this->type}" . json_encode(array(debug_backtrace(), User::getId(), $_SERVER)));
        $this->ip = getRealIpAddr();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (empty($this->vast_campaigns_has_videos_id)) {
            $this->vast_campaigns_has_videos_id = 'null';
        }

        if (empty($this->users_id)) {
            $this->users_id = 'null';
        }

        $vast_campaigns_logs_id = parent::save();
        $obj = AVideoPlugin::getDataObject('AD_Server');
        if ($this->type == AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED) {
            if ($obj->onlyRewardLoggedUsers && !User::isLogged()) {
                // only reward logged users
                return $vast_campaigns_logs_id;
            }
            self::reward($vast_campaigns_logs_id);
        }
        return $vast_campaigns_logs_id;
    }

    static function reward($vast_campaigns_logs_id)
    {
        $log = new VastCampaignsLogs($vast_campaigns_logs_id);
        $vast_campaigns_has_videos_id = $log->getVast_campaigns_has_videos_id();
        $vast_campaign_videos = new VastCampaignsVideos($vast_campaigns_has_videos_id);
        $vast_campaigns_id = $vast_campaign_videos->getVast_campaigns_id();
        $vast_campaign = new VastCampaigns($vast_campaigns_id);
        $reward_per_impression = $vast_campaign->getReward_per_impression();
        if (!empty($reward_per_impression)) {
            $plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
            $videos_id = $vast_campaign_videos->getVideos_id();
            $video = new Video('', '', $videos_id);
            $campaignName = $vast_campaign->getName();
            $users_id = $video->getUsers_id();
            $array = array('vast_campaigns_has_videos_id' => $vast_campaigns_has_videos_id);
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
        if ($res != false) {
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
        if ($res != false) {
            foreach ($fullData as $row) {
                $data[$row['type']] = $row['total'];
            }
        }
        return $data;
    }

    static function getCampaignLogs($vast_campaigns_id, $start_date, $end_date)
    {
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
                    AND logs.type = '" . AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED . "' 
                    AND logs.created BETWEEN ? AND ?";

        // Execute the query
        $res = sqlDAL::readSql($sql, 'iss', array($vast_campaigns_id, $start_date, $end_date));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getAdsByVideoAndPeriod($vast_campaigns_id, $startDate = null, $endDate = null, $videos_id = null, $event_type = null, $campaign_type = 'all', $external_referrer = null)
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT v.title as video_title, u.channelName, v.users_id, vcl.videos_id, COUNT(vcl.id) as total_ads, vc.name as campaign_name
        FROM vast_campaigns_logs vcl
        LEFT JOIN videos v ON v.id = vcl.videos_id
        LEFT JOIN users u ON u.id = v.users_id
        LEFT JOIN vast_campaigns_has_videos vchv ON vchv.id = vcl.vast_campaigns_has_videos_id
        LEFT JOIN vast_campaigns vc ON vc.id = vchv.vast_campaigns_id
        WHERE 1 = 1";

        if (!empty($vast_campaigns_id)) {
            $sql .= " AND vchv.vast_campaigns_id = ?";
            $formats .= 'i';
            $values[] = $vast_campaigns_id;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND vcl.created_php_time BETWEEN ? AND ?";
            $formats .= 'ii';
            $values[] = _strtotime($startDate); // Assuming _strtotime() converts date string to UNIX timestamp
            $values[] = _strtotime($endDate);
        }

        if (!empty($videos_id)) {
            $sql .= " AND vcl.videos_id = ?";
            $formats .= 'i';
            $values[] = $videos_id;
        }

        // Optional event type filter
        if (!empty($event_type)) {
            $sql .= " AND vcl.type = ?";
            $formats .= 's';
            $values[] = $event_type;
        }
        
        if (!empty($external_referrer)) {
            $sql .= " AND vcl.external_referrer LIKE ? ";
            $formats .= 's';
            $values[] = '%' . $external_referrer . '%';
        }

        // Apply filter based on the campaign type
        if ($campaign_type === 'own') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NOT NULL";
        } elseif ($campaign_type === 'third-party') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NULL";
        }

        // Properly group by all non-aggregated fields to comply with ONLY_FULL_GROUP_BY SQL mode
        $sql .= " GROUP BY vcl.videos_id, v.title, vc.name ORDER BY total_ads DESC";

        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public static function getAdTypesByPeriod($vast_campaigns_id, $startDate = null, $endDate = null, $event_type = null, $campaign_type = 'all', $external_referrer = null)
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT vcl.type, COUNT(vcl.id) as total_ads, vc.name as campaign_name
            FROM vast_campaigns_logs vcl
            LEFT JOIN vast_campaigns_has_videos vchv ON vchv.id = vcl.vast_campaigns_has_videos_id
            LEFT JOIN vast_campaigns vc ON vc.id = vchv.vast_campaigns_id
            WHERE 1 = 1";

        if (!empty($vast_campaigns_id)) {
            $sql .= " AND vchv.vast_campaigns_id = ? ";
            $formats .= 'i';
            $values[] = $vast_campaigns_id;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND vcl.created_php_time BETWEEN ? AND ?";
            $formats .= 'ii';
            $values[] = _strtotime($startDate);
            $values[] = _strtotime($endDate);
        }

        // Optional event type filter
        if (!empty($event_type)) {
            $sql .= " AND vcl.type = ? ";
            $formats .= 's';
            $values[] = $event_type;
        }
        
        if (!empty($external_referrer)) {
            $sql .= " AND vcl.external_referrer LIKE ? ";
            $formats .= 's';
            $values[] = '%' . $external_referrer . '%';
        }

        // Apply filter based on the campaign type
        if ($campaign_type === 'own') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NOT NULL "; // Only own campaigns
        } elseif ($campaign_type === 'third-party') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NULL "; // Only third-party campaigns
        }

        $sql .= " GROUP BY vcl.type, vc.name ORDER BY total_ads DESC";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public static function getAdsByUserAndEventType($users_id, $startDate = null, $endDate = null, $event_type = null, $campaign_type = 'all')
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT vcl.type, COUNT(vcl.id) as total_ads, vc.name as campaign_name, v.users_id
            FROM vast_campaigns_logs vcl
            LEFT JOIN videos v ON v.id = vcl.videos_id
            LEFT JOIN vast_campaigns_has_videos vchv ON vchv.id = vcl.vast_campaigns_has_videos_id
            LEFT JOIN vast_campaigns vc ON vc.id = vchv.vast_campaigns_id
            WHERE v.users_id = ?";

        $formats .= 'i';
        $values[] = $users_id;

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND vcl.created_php_time BETWEEN ? AND ?";
            $formats .= 'ii';
            $values[] = _strtotime($startDate);
            $values[] = _strtotime($endDate);
        }

        // Optional event type filter
        if (!empty($event_type)) {
            $sql .= " AND vcl.type = ? ";
            $formats .= 's';
            $values[] = $event_type;
        }

        // Apply filter based on the campaign type
        if ($campaign_type === 'own') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NOT NULL "; // Only own campaigns
        } elseif ($campaign_type === 'third-party') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NULL "; // Only third-party campaigns
        }

        $sql .= " GROUP BY vcl.type, vc.name ORDER BY total_ads DESC";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public static function getAdsByVideoAndEventType($videos_id, $startDate = null, $endDate = null, $event_type = null, $campaign_type = 'all', $external_referrer = null)
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT vcl.type, COUNT(vcl.id) as total_ads, vc.name as campaign_name
            FROM vast_campaigns_logs vcl
            LEFT JOIN vast_campaigns_has_videos vchv ON vchv.id = vcl.vast_campaigns_has_videos_id
            LEFT JOIN vast_campaigns vc ON vc.id = vchv.vast_campaigns_id
            WHERE vcl.videos_id = ?";

        $formats .= 'i';
        $values[] = $videos_id;

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND vcl.created_php_time BETWEEN ? AND ?";
            $formats .= 'ii';
            $values[] = _strtotime($startDate);
            $values[] = _strtotime($endDate);
        }

        // Optional event type filter
        if (!empty($event_type)) {
            $sql .= " AND vcl.type = ? ";
            $formats .= 's';
            $values[] = $event_type;
        }
        
        // Optional event type filter
        if (!empty($external_referrer)) {
            $sql .= " AND vcl.external_referrer LIKE ? ";
            $formats .= 's';
            $values[] = '%' . $external_referrer . '%';
        }

        // Apply filter based on the campaign type
        if ($campaign_type === 'own') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NOT NULL "; // Only own campaigns
        } elseif ($campaign_type === 'third-party') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NULL "; // Only third-party campaigns
        }

        $sql .= " GROUP BY vcl.type, vc.name ORDER BY total_ads DESC";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public static function getAdsByVideoForUser($users_id, $startDate = null, $endDate = null, $event_type = null, $campaign_type = 'all', $external_referrer = null)
    {
        global $global;
        $formats = '';
        $values = [];
    
        // Query to fetch advertisement events related to videos by a specific user
        $sql = "SELECT v.title AS video_title, vcl.videos_id, COUNT(vcl.id) AS total_ads, vc.name AS campaign_name, v.users_id
                FROM vast_campaigns_logs vcl
                LEFT JOIN videos v ON v.id = vcl.videos_id
                LEFT JOIN vast_campaigns_has_videos vchv ON vchv.id = vcl.vast_campaigns_has_videos_id
                LEFT JOIN vast_campaigns vc ON vc.id = vchv.vast_campaigns_id
                WHERE v.users_id = ?";
    
        $formats .= 'i';
        $values[] = $users_id;
    
        // Filter events by provided period
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND vcl.created_php_time BETWEEN ? AND ?";
            $formats .= 'ii';
            $values[] = strtotime($startDate);
            $values[] = strtotime($endDate);
        }
    
        // Optional filter for event type
        if (!empty($event_type)) {
            $sql .= " AND vcl.type = ?";
            $formats .= 's';
            $values[] = $event_type;
        }
        
        if (!empty($external_referrer)) {
            $sql .= " AND vcl.external_referrer LIKE ? ";
            $formats .= 's';
            $values[] = '%' . $external_referrer . '%';
        }
    
        // Filter based on campaign type
        if ($campaign_type === 'own') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NOT NULL";
        } elseif ($campaign_type === 'third-party') {
            $sql .= " AND vcl.vast_campaigns_has_videos_id IS NULL";
        }
    
        // Group results by video to get total advertisement events per video
        $sql .= " GROUP BY vcl.videos_id, v.title, vc.name ORDER BY total_ads DESC";
    
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
    
        return $fullData;
    }

    public static function getEventType()
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT distinct vcl.type
                FROM vast_campaigns_logs vcl";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public static function getExternalReferrer()
    {
        global $global;
        $formats = '';
        $values = [];

        $sql = "SELECT distinct vcl.external_referrer
                FROM vast_campaigns_logs vcl";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }
}
