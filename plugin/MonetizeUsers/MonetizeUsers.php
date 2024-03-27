<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/MonetizeUsers/Objects/Monetize_user_reward_log.php';

class MonetizeUsers extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        global $global;
        $txt = "This plugin will reward your users based on their videos view, each view will affect the user's walled balance";

        $txt .= $this->isReadyLabel(array('YPTWallet'));
        $help = "<br><small><a href='{$global['webSiteRootURL']}plugin/MonetizeUsers/revenueCalculator.php' target='_blank'><i class='fa-solid fa-money-check-dollar'></i> Revenue Calculator</a></small>";

        return $txt . $help;
    }

    public function getName() {
        return "MonetizeUsers";
    }

    public function getUUID() {
        return "10573335-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "2.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->rewardPerView = 0.1;
        self::addDataObjectHelper('rewardPerView', 'Reward Value', 'This is how much you will pay the video producer for each view');

        $options = [];
        for ($index = 0; $index <= 100; $index += 5) {
            $options[$index] = "{$index}%";
        }
        $o = new stdClass();
        $o->type = $options;
        $o->value = 0;
        $obj->rewardMinimumViewPercentage = $o;
        self::addDataObjectHelper('rewardMinimumViewPercentage', 'Minimum watch video percentage', 'We will only reward if the viewer watch at least the percentage here');

        $obj->rewardOnlyLoggedUsersView = true;
        self::addDataObjectHelper('rewardOnlyLoggedUsersView', 'Reward only logged users', 'This is a good option to avoid bots. This option requires VideosStatistics plugin');

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->revenueCalculatorFooterText = $o;
        
        return $obj;
    }

    /*
      public function addView($videos_id, $total) {
      global $global;
      $obj = $this->getDataObject();
      if (empty($obj->rewardMinimumViewPercentage->value)) {
      return false;
      }
      if ($obj->rewardOnlyLoggedUsersView && !User::isLogged()) {
      return false;
      }

      // Check ownership to prevent the uploader from farming money from their own video content
      if (User::isLogged()) {
      $user_id = User::getId();
      if (Video::isOwner($videos_id, $user_id)) {
      return false; // Prevent exploitation of free money; Don't award money if viewer is uploader
      }
      }

      $wallet = AVideoPlugin::loadPlugin("YPTWallet");
      $video = new Video("", "", $videos_id);
      return YPTWallet::transferBalanceFromSiteOwner($video->getUsers_id(), $obj->rewardPerView, "Reward from video <a href='{$global['webSiteRootURL']}v/{$videos_id}'>" . $video->getTitle() . "</a>", true);
      }
     */

    function executeEveryMinute() {
        $obj = $this->getDataObject();
        if (!empty($obj->rewardMinimumViewPercentage->value)) {
            $VideosStatistics = AVideoPlugin::loadPluginIfEnabled('VideosStatistics');
            if (!empty($VideosStatistics)) {
                $percentage_watched = $obj->rewardMinimumViewPercentage->value;
                $now = date('Y-m-d H:i:s');
                //$when_from = date('Y-m-d H:i:s', strtotime('-1 year'));
                $only_logged_users = $obj->rewardOnlyLoggedUsersView;
                $users_id = 0;
                $when_to = '';
                $rows = VideosStatistics::getVideosToReward($percentage_watched, $only_logged_users, $users_id);
                //_error_log("Checking rewardMinimumViewPercentage {$percentage_watched}% ");
                //var_dump($percentage_watched);
                $ids_array = array();
                foreach ($rows as $value) {
                    if ($value['video_owner_users_id'] == $value['users_id']) {
                        continue; // Prevent exploitation of free money; Don't award money if viewer is uploader
                    }
                    if (is_null($value['percentage_watched'])) {
                        continue; // Prevent exploitation of free money; Don't award money if viewer is uploader
                    }
                    $ids_array[] = $value['id'];
                    //var_dump($value);
                    //var_dump("seconds_watching_video={$value["seconds_watching_video"]} duration_in_seconds={$value["duration_in_seconds"]} {$value["percentage_watched"]}%");
                    $this->rewardAndSaveLog(
                            $value['videos_id'],
                            $value['video_owner_users_id'],
                            $value['percentage_watched'],
                            $value['seconds_watching_video'],
                            $value['created'],
                            $value['users_id']);
                }
                VideosStatistics::setRewarded($ids_array);
            }else{
                _error_log("VideosStatistics is disabled ");
            }
        }else{
            _error_log("Checking rewardMinimumViewPercentage is empty ");
        }
    }

    function rewardAndSaveLog($videos_id, $video_owner_users_id, $percentage_watched, $seconds_watching_video, $when_watched, $who_watched_users_id) {
        global $global;
        $obj = $this->getDataObject();
        $total_reward = $obj->rewardPerView;
        if ($this->videosStatisticsToMonetizeUserRewardLog($videos_id, $video_owner_users_id, $percentage_watched, $seconds_watching_video, $when_watched, $who_watched_users_id, $total_reward)) {
            $wallet = AVideoPlugin::loadPlugin("YPTWallet");
            $video = new Video("", "", $videos_id);
            return YPTWallet::transferBalanceFromSiteOwner($video_owner_users_id, $obj->rewardPerView, "Reward from video <a href='{$global['webSiteRootURL']}v/{$videos_id}'>" . $video->getTitle() . "</a>", true);
        }
    }

    function videosStatisticsToMonetizeUserRewardLog($videos_id, $video_owner_users_id, $percentage_watched, $seconds_watching_video, $when_watched, $who_watched_users_id, $total_reward) {
        $Monetize_user_reward_log = new Monetize_user_reward_log(0);
        $Monetize_user_reward_log->setVideos_id($videos_id);
        $Monetize_user_reward_log->setVideo_owner_users_id($video_owner_users_id);
        $Monetize_user_reward_log->setPercentage_watched($percentage_watched);
        $Monetize_user_reward_log->setSeconds_watching_video($seconds_watching_video);
        $Monetize_user_reward_log->setWhen_watched($when_watched);
        $Monetize_user_reward_log->setWho_watched_users_id($who_watched_users_id);
        $Monetize_user_reward_log->setTotal_reward($total_reward);
        return $Monetize_user_reward_log->save();
    }

    static $GetRewardModeAll = 'all';
    static $GetRewardModeTotal = 'total';
    static $GetRewardModeGrouped = 'grouped';

    static function getRewards($users_id = 0, $when_from = '', $when_to = '', $mode = 'all') {
        global $global;

        // Preparing the SQL statement
        $sql = "SELECT
                u.id AS user_id,
                u.name AS user_name,
                DATE(mrl.when_watched) AS watched_date,
                HOUR(mrl.when_watched) AS watched_hour,
                mrl.videos_id,
                v.title,
                mrl.created AS record_created,";

        if ($mode === self::$GetRewardModeTotal || $mode === self::$GetRewardModeGrouped) {
            $sql .= " SUM(mrl.total_reward) AS total_reward ";
        } else { // default to 'all'
            $sql .= " mrl.total_reward AS total_reward ";
        }

        $sql .= "FROM
                monetize_user_reward_log AS mrl
                JOIN videos AS v ON mrl.videos_id = v.id
                JOIN users AS u ON v.users_id = u.id
            WHERE
                1 = 1 ";

        $formats = '';
        $values = [];

        if (!empty($users_id)) {
            $sql .= " AND v.users_id = ?";
            $formats .= 'i';
            $values[] = $users_id;
        }
        if (!empty($when_from)) {
            $sql .= " AND mrl.when_watched >= ?";
            $formats .= 's';
            $values[] = $when_from;
        }

        if ($mode === self::$GetRewardModeTotal) {
            $sql .= " GROUP BY
                        u.id,
                        u.name,
                        DATE(mrl.when_watched),
                        HOUR(mrl.when_watched),
                        mrl.videos_id,
                        v.title,
                        mrl.created ";
            $sql .= "ORDER BY
                    mrl.when_watched,
                    HOUR(mrl.when_watched)";
        } else if ($mode === self::$GetRewardModeGrouped) {
            $sql .= " GROUP BY
                        mrl.when_watched,
                        HOUR(mrl.when_watched),
                        mrl.videos_id,
                        mrl.created ";
            $sql .= "ORDER BY
                    mrl.when_watched,
                    HOUR(mrl.when_watched)";
        } else { // default to 'all'
            $sql .= "ORDER BY
                    mrl.when_watched,
                    HOUR(mrl.when_watched)";
        }

        /*
        if (!empty($_REQUEST['debug'])) {
            var_dump($sql, $formats, $values);
        }
         * 
         */

        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    public function updateScript() {
        global $global;
        
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/MonetizeUsers/install/updateV2.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        return true;
    }
}
