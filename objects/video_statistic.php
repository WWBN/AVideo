<?php

global $global, $config;

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

class VideoStatistic extends ObjectYPT
{

    protected $id;
    protected $when;
    protected $ip;
    protected $users_id;
    protected $videos_id;
    protected $lastVideoTime;
    protected $session_id;
    protected $seconds_watching_video;
    protected $json;
    protected $rewarded;
    protected $created_php_time;
    protected $user_agent;
    protected $app;

    public function getUser_agent()
    {
        return $this->user_agent;
    }

    public function setUser_agent($user_agent)
    {
        $this->user_agent = $user_agent;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function setApp($app)
    {
        $app = preg_replace('/[^a-zA-Z0-9]/', '', $app);
        $this->app = $app;
    }

    public static function getSearchFieldsNames()
    {
        return ['json', 'ip', 'when', 'user', 'name', 'email', 'channelName'];
    }

    public static function getTableName()
    {
        return 'videos_statistics';
    }

    public static function create($videos_id, $currentTime = 0)
    {
        global $global;
        /**
         * Don't crash if it's an old version
         *
         * $res = sqlDAL::readSql("SHOW TABLES LIKE 'videos_statistics'");
         * $result = sqlDal::num_rows($res);
         * sqlDAL::close($res);
         * if (empty($result)) {
         * echo "<div class='alert alert-danger'>You need to <a href='{$global['webSiteRootURL']}update'>update your system</a></div>";
         * return false;
         * }
         */
        if (empty($videos_id)) {
            die(__("You need a video to generate statistics"));
        }

        if (empty($_SESSION["user"]["id"])) {
            $userId = 0;
        } else {
            $userId = $_SESSION["user"]["id"];
        }

        $lastVideoTime = 0;
        if (empty($currentTime)) {
            $lastStatistic = self::getLastStatistics($videos_id, $userId, getRealIpAddr(), session_id());
            if (empty($currentTime) && !empty($lastStatistic)) {
                $lastVideoTime = intval($lastStatistic['lastVideoTime']);
            }
        } else {
            $lastVideoTime = intval($currentTime);
        }

        $columns = array('`when`', 'ip', 'videos_id', 'lastVideoTime', 'created', 'modified', 'session_id', 'timezone', 'created_php_time');
        $values = array('now()', '?', '?', '?', 'now()', 'now()', '?', '?', '?');
        $formatValues = [getRealIpAddr(), $videos_id, $lastVideoTime, session_id(), date_default_timezone_get(), time()];
        $formats = 'siissi';

        if (!empty($userId)) {
            $columns[] = 'users_id';
            $values[] = '?';
            $formats .= 'i';
            $formatValues[] = $userId;
        }

        $sql = "INSERT INTO videos_statistics (" . implode(',', $columns) . ") values "
            . "(" . implode(',', $values) . ")";
        //var_dump($sql, $formats, $formatValues, $columns, $values);
        $insert_row = sqlDAL::writeSql($sql, $formats, $formatValues);
        //if($videos_id==4){_error_log($sql);}
        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        if (!empty($global['mysqli']->insert_id)) {
            return $global['mysqli']->insert_id;
        } else {
            die($sql . ' Save Video Statistics Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    public static function updateStatistic($videos_id, $users_id, $lastVideoTime, $seconds_watching_video = 0)
    {
        global $_updateStatisticFailMessage;
        $_updateStatisticFailMessage = array();
        //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
        if (isBot()) {
            $_updateStatisticFailMessage[] = 'Is Bot';
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            return false;
        }
        $lastStatistic = self::getLastStatistics($videos_id, $users_id, getRealIpAddr(), session_id());
        if (empty($lastStatistic)) {
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            $vs = new VideoStatistic(0);
            $vs->setUsers_id($users_id);
            $vs->setVideos_id($videos_id);
            $vs->setWhen(date("Y-m-d h:i:s"));
        } else {
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            $vs = new VideoStatistic($lastStatistic['id']);
            $elapsedTime = time() - $vs->created_php_time;
            if ($seconds_watching_video > $elapsedTime) {
                $seconds_watching_video = $elapsedTime;
            }
        }
        if (empty($lastVideoTime) && empty($seconds_watching_video) && !empty($lastStatistic)) {
            if (empty($lastVideoTime)) {
                $_updateStatisticFailMessage[] = 'lastVideoTime is empty';
            }
            if (empty($seconds_watching_video)) {
                $_updateStatisticFailMessage[] = 'seconds_watching_video is empty';
            }
            if (!empty($lastStatistic)) {
                $_updateStatisticFailMessage[] = 'lastStatistic is NOT empty';
            }
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            // do not save because there is already a record and it is saving 0
        } else {
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            $vs->setLastVideoTime($lastVideoTime);
        }
        //var_dump($lastVideoTime);exit;
        $vs->setIp(getRealIpAddr());

        if (!empty($seconds_watching_video) && $seconds_watching_video > 0) {
            //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
            $totalVideoWatched = $vs->getSeconds_watching_video() + $seconds_watching_video;
            //_error_log("updateStatistic: add more [$seconds_watching_video] to video [$videos_id] " . get_browser_name());
            $vs->setSeconds_watching_video($totalVideoWatched);
            $v = new Video('', '', $videos_id);
            $v->addSecondsWatching($seconds_watching_video);

            //$totalVideoSeconds = timeToSeconds($hms);
            //Video::addViewPercent();
        }
        //error_log("updateStatistic: videos_id=$videos_id lastVideoTime=$lastVideoTime, seconds_watching_video=$seconds_watching_video line=" . __LINE__);
        //if($videos_id==4){ _error_log("updateStatistic $videos_id, $users_id, $lastVideoTime, $seconds_watching_video ".json_encode($lastStatistic));}
        $id = $vs->save();
        if (!empty($id)) {
            //Video::clearCache($videos_id);

            $_updateStatisticFailMessage[] = 'Saved '.$id;
        }else{
            $_updateStatisticFailMessage[] = 'Fail os save';
        }
        return $id;
    }

    public function save()
    {
        global $global;
        if (empty($this->videos_id)) {
            return false;
        }
        $this->setSession_id(session_id());
        if (empty($this->session_id) && empty($this->users_id)) {
            return false;
        }
        if (empty($this->users_id)) {
            $this->setUsers_id('null');
        }

        $this->lastVideoTime = intval(@$this->lastVideoTime);

        $this->seconds_watching_video = intval($this->seconds_watching_video);

        $this->json = ($this->json);

        if(empty($this->user_agent) && !empty($_SERVER['HTTP_USER_AGENT'])){
            $this->user_agent = ($_SERVER['HTTP_USER_AGENT']);
        }

        if(empty($this->app)){
            if(!empty($_SERVER['platform'])){
            $this->app = ($_SERVER['platform']);
            }else if(!empty($_SERVER['HTTP_USER_AGENT'])){
                $this->app = getUserAgentInfo($_SERVER['HTTP_USER_AGENT']);
            }
        }

        if (empty($this->id)) {
            $this->rewarded = 0;
            $row = self::getLastStatistics($this->videos_id, $this->users_id, getRealIpAddr(), session_id());
            if (!empty($row)) {
                $this->id = $row['id'];
            }
        }

        return parent::save();
    }

    public static function getLastStatistics($videos_id, $users_id = 0, $ip = '', $session_id = '')
    {

        if (empty($videos_id)) {
            return false;
        }

        $sql = "SELECT * FROM videos_statistics WHERE 1=1 ";

        $conditions = [];
        $params = [];

        $sql .= " AND videos_id = ? AND ";
        $formats = 'i';
        $params[] = $videos_id;

        if (!empty($users_id)) {
            $sql .= " users_id = ? ";
            $formats .= 'i';
            $params[] = $users_id;
        } else {
            $sql .= " users_id IS NULL ";
            if (!empty($session_id)) {
                $conditions[] = "session_id = ? ";
                $formats .= 's';
                $params[] = $session_id;
            }
            if (!empty($ip)) {
                $conditions[] = " ip = ? ";
                $formats .= 's';
                $params[] = $ip;
            }
            if (!empty($conditions)) {
                $sql .= " AND ( ";
                $sql .= implode(' OR ', $conditions);
                $sql .= " ) ";
            }
        }

        $sql .= " ORDER BY id DESC LIMIT 1";

        $res = sqlDAL::readSql($sql, $formats, $params, true);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        return !empty($result) ? $result : false;
    }


    public static function getLastVideoTimeFromVideo($videos_id, $users_id)
    {
        $row = self::getLastStatistics($videos_id, $users_id);
        //var_dump($row);
        if (empty($row)) {
            return 0;
        }
        return intval($row['lastVideoTime']);
    }

    public static function getStatisticTotalViews($videos_id, $uniqueUsers = false, $startDate = "", $endDate = "")
    {
        global $global;
        if ($uniqueUsers) {
            $ast = "distinct(users_id)";
        } else {
            $ast = "*";
        }
        $sql = "SELECT count({$ast}) as total FROM videos_statistics WHERE 1=1 ";
        $formats = '';
        $values = [];
        if (!empty($videos_id)) {
            $sql .= " AND videos_id = ? ";
            $formats .= "i";
            $values[] = $videos_id;
        }
        if (!empty($startDate)) {
            $sql .= " AND `when` >= ? ";
            $formats .= "s";
            $values[] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND `when` <= ? ";
            $formats .= "s";
            $values[] = $endDate;
        }
        $res = sqlDAL::readSql($sql, $formats, $values);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            //echo "<hr>".$row['total']." --- ".$sql, "<br>";
            return $result['total'];
        }
        return 0;
    }

    public static function getTotalLastDaysAsync($video_id, $numberOfDays)
    {
        global $global, $advancedCustom;
        $md5 = ("{$video_id}_{$numberOfDays}");
        $path = getCacheDir() . "getTotalLastDaysAsync/";
        make_path($path);
        $cacheFileName = "{$path}{$md5}";
        if (!file_exists($cacheFileName)) {
            if (file_exists($cacheFileName . ".lock")) {
                return [];
            }
            $total = static::getTotalLastDays($video_id, $numberOfDays);
            file_put_contents($cacheFileName, json_encode($total));
            return $total;
        }
        $return = _json_decode(file_get_contents($cacheFileName));
        if (time() - filemtime($cacheFileName) > 60) {
            // file older than 1 min
            $command = ("php '{$global['systemRootPath']}objects/getTotalLastDaysAsync.php' '$video_id' '$numberOfDays' '$cacheFileName'");
            _error_log("getTotalLastDaysAsync: {$command}");
            exec($command . " > /dev/null 2>/dev/null &");
        }
        return $return;
    }

    public static function getTotalLastDays($video_id, $numberOfDays, $returnArray = [])
    {
        if ($numberOfDays < 0) {
            return $returnArray;
        }
        $date = date("Y-m-d", strtotime("-{$numberOfDays} days"));
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date . " 00:00:00", $date . " 23:59:59");
        $numberOfDays--;
        return static::getTotalLastDays($video_id, $numberOfDays, $returnArray);
    }

    public static function getTotalToday($video_id, $hour = 0, $returnArray = [])
    {
        if ($hour >= 24) {
            return $returnArray;
        }
        $date = date("Y-m-d {$hour}", time());
        //echo $date;exit;
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date . ":00:00", $date . ":59:59");
        $hour++;
        return static::getTotalToday($video_id, $hour, $returnArray);
    }

    public static function getTotalTodayAsync($video_id)
    {
        global $global, $advancedCustom;
        $cacheFileName = getCacheDir() . "getTotalTodayAsync_{$video_id}";
        if (!file_exists($cacheFileName)) {
            if (file_exists($cacheFileName . ".lock")) {
                return [];
            }
            $total = static::getTotalToday($video_id);
            file_put_contents($cacheFileName, json_encode($total));
            return $total;
        }
        $return = _json_decode(file_get_contents($cacheFileName));
        if (time() - filemtime($cacheFileName) > 60) {
            // file older than 1 min
            $command = ("php '{$global['systemRootPath']}objects/getTotalTodayAsync.php' '$video_id' '$cacheFileName'");
            _error_log("getTotalTodayAsync: {$command}");
            exec($command . " > /dev/null 2>/dev/null &");
        }
        return $return;
    }

    public function getWhen()
    {
        return $this->when;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getUsers_id()
    {
        return $this->users_id;
    }

    public function getVideos_id()
    {
        return $this->videos_id;
    }

    public function getLastVideoTime()
    {
        return $this->lastVideoTime;
    }

    public function setWhen($when)
    {
        $this->when = $when;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
        if (empty($this->users_id)) {
            $this->users_id = 'null';
        }
    }

    public function setVideos_id($videos_id)
    {
        $this->videos_id = intval($videos_id);
    }

    public function setLastVideoTime($lastVideoTime)
    {
        $this->lastVideoTime = intval($lastVideoTime);
    }

    public function getSession_id()
    {
        return $this->session_id;
    }

    public function setSession_id($session_id)
    {
        $this->session_id = $session_id;
    }

    public static function getChannelsWithMoreViews($daysLimit = 0)
    {
        global $global, $advancedCustom;

        if (empty($daysLimit)) {
            $daysLimit = getTrendingLimit();
        }

        //$dateDaysLimit = getTrendingLimitDate();
        $cacheName3 = "getChannelsWithMoreViews{$daysLimit}" . DIRECTORY_SEPARATOR . md5(json_encode([$_GET, $_POST]));
        $cache = ObjectYPT::getCacheGlobal($cacheName3, 3600); // 1 hour cache
        if (!empty($cache)) {
            //_error_log('getChannelsWithMoreViews cache found ' . $cacheName3);
            return object_to_array($cache);
        } else {
            _error_log('getChannelsWithMoreViews no cache found ' . $cacheName3);
        }

        // get unique videos ids from the requested timeframe
        $sql = "SELECT distinct(videos_id) as videos_id FROM videos_statistics "
            . " WHERE DATE(`when`) >= DATE_SUB(DATE(NOW()), INTERVAL {$daysLimit} DAY) ";
        $channels = [];
        $channelsPerUser = [];
        $cacheName2 = "getChannelsWithMoreViews" . DIRECTORY_SEPARATOR . md5($sql);
        $cache2 = ObjectYPT::getCache($cacheName2, 3600); // 1 hour cache
        if (!empty($cache2)) {
            $channelsPerUser = object_to_array($cache2);
        }

        if (empty($channelsPerUser)) {
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if ($res !== false) {
                // get the channel owner from each of those videos
                foreach ($fullData as $row) {
                    $users_id = Video::getOwner($row['videos_id']);
                    if (empty($channelsPerUser[$users_id])) {
                        $channelsPerUser[$users_id] = [];
                    }
                    $channelsPerUser[$users_id][] = $row['videos_id'];
                }
            }
            $response = ObjectYPT::setCacheGlobal($cacheName2, $channelsPerUser);
        }

        if (!empty($channelsPerUser)) {
            foreach ($channelsPerUser as $key => $value) {
                // count how many views each one has
                $sql2 = "SELECT count(id) as total FROM videos_statistics "
                    . " WHERE videos_id IN (" . implode(",", $value) . ") "
                    . " AND DATE(created) >= DATE_SUB(DATE(NOW()), INTERVAL {$daysLimit} DAY) ";
                $res2 = sqlDAL::readSql($sql2);
                $result2 = sqlDAL::fetchAssoc($res2);
                sqlDAL::close($res2);
                if (!empty($result2)) {
                    $channels[$key]['users_id'] = $key;
                    $channels[$key]['total'] = intval($result2['total']);
                }
            }

            // return more first
            usort($channels, function ($a, $b) {
                return $a['total'] - $b['total'];
            });
        }
        $response = ObjectYPT::setCache($cacheName3, $channels);
        _error_log('getChannelsWithMoreViews cache saved [' . json_encode($response) . '] ' . $cacheName3);
        return $channels;
    }

    public static function getVideosWithMoreViews($status, $showOnlyLoggedUserVideos, $showUnlisted, $suggestedOnly, $daysLimit = 0)
    {
        global $global, $advancedCustom;

        if (empty($daysLimit)) {
            $daysLimit = getTrendingLimit();
        }

        $dateDaysLimit = getTrendingLimitDate();

        // get unique videos ids from the requested timeframe
        $sql = "SELECT distinct(videos_id) as videos_id FROM videos_statistics s "
            . " LEFT JOIN videos v ON v.id = videos_id "
            . " WHERE v.created > '{$dateDaysLimit}' "
            . " AND DATE(s.`when`) >= DATE_SUB(DATE(NOW()), INTERVAL {$daysLimit} DAY) ";

        if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
            $sql .= " AND v.users_id = '" . User::getId() . "'";
        } elseif (!empty($showOnlyLoggedUserVideos)) {
            $sql .= " AND v.users_id = '{$showOnlyLoggedUserVideos}'";
        }

        if (!empty($_GET['channelName'])) {
            $user = User::getChannelOwner($_GET['channelName']);
            if (!empty($user)) {
                $sql .= " AND v.users_id = '{$user['id']}' ";
            }
        }
        if ($status == Video::SORT_TYPE_VIEWABLE) {
            if (User::isLogged()) {
                $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND v.users_id ='" . User::getId() . "'))";
            } else {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            }
        } elseif ($status == Video::SORT_TYPE_VIEWABLENOTUNLISTED) {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "') ";
        } elseif (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }
        $sql .= AVideoPlugin::getVideoWhereClause();

        if ($suggestedOnly) {
            $sql .= " AND v.isSuggested = 1 ";
        }

        $sql .= static::getSqlLimit();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $channels = [];
        $videos = [];
        if ($res !== false) {
            foreach ($fullData as $key => $value) {
                // count how many views each one has
                $sql2 = "SELECT count(id) as total FROM videos_statistics "
                    . " WHERE videos_id = {$value['videos_id']} "
                    . " AND DATE(created) >= DATE_SUB(DATE(NOW()), INTERVAL {$daysLimit} DAY) ";

                $res2 = sqlDAL::readSql($sql2);
                $result2 = sqlDAL::fetchAssoc($res2);
                sqlDAL::close($res2);
                if (!empty($result2)) {
                    $video = Video::getVideo($value['videos_id'], $status, false, false, $suggestedOnly, $showUnlisted, false, $showOnlyLoggedUserVideos);
                    if (empty($video)) {
                        continue;
                    }
                    unset($video['title']);
                    unset($video['description']);
                    unset($video['descriptionHTML']);
                    $video['total'] = $result2['total'];
                    $videos[] = $video;
                }
            }
        }
        // return more first
        usort($videos, function ($a, $b) {
            return $a['total'] - $b['total'];
        });
        return $videos;
    }

    public static function getUsersIDFromChannelsWithMoreViews($daysLimit = 0)
    {
        $channels = self::getChannelsWithMoreViews($daysLimit);
        $users_id = [];
        foreach ($channels as $value) {
            $users_id[] = $value['users_id'];
        }
        return $users_id;
    }

    public static function getChannelsTotalViews($users_id, $daysLimit = 0)
    {
        global $global, $advancedCustom;

        if (empty($daysLimit)) {
            $daysLimit = getTrendingLimit();
        }

        $dateDaysLimit = getTrendingLimitDate();

        $cacheName = "getChannelsTotalViews($users_id, $daysLimit)";
        $cache = ObjectYPT::getCache($cacheName, 3600); // 1 hour cache
        if (!empty($cache)) {
            return intval($cache);
        }
        $users_id = intval($users_id);
        // count how many views each one has
        $sql2 = "SELECT count(s.id) as total FROM videos_statistics s "
            . " LEFT JOIN videos v ON v.id = videos_id WHERE v.users_id = $users_id "
            . " AND v.created > '{$dateDaysLimit}' "
            . " AND DATE(s.`when`) >= DATE_SUB(DATE(NOW()), INTERVAL {$daysLimit} DAY) ";
        $res2 = sqlDAL::readSql($sql2);
        $result2 = sqlDAL::fetchAssoc($res2);
        sqlDAL::close($res2);
        $result = 0;
        if (!empty($result2)) {
            $result = intval($result2['total']);
        }
        ObjectYPT::setCache($cacheName, $result);
        return $result;
    }

    public static function getTotalStatisticsRecords()
    {
        global $global;
        $sql2 = "SELECT count(s.id) as total FROM videos_statistics s ";
        $res2 = sqlDAL::readSql($sql2);
        $result2 = sqlDAL::fetchAssoc($res2);
        sqlDAL::close($res2);
        $result = 0;
        if (!empty($result2)) {
            return intval($result2['total']);
        }
        return 0;
    }

    public static function deleteOldStatistics($days)
    {
        global $global;
        $days = intval($days);
        if (!empty($days)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE created < DATE_SUB(NOW(), INTERVAL ? DAY) ";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$days]);
        }
        return false;
    }

    public function getSeconds_watching_video()
    {
        return intval($this->seconds_watching_video);
    }

    public function setSeconds_watching_video($seconds_watching_video)
    {
        $this->seconds_watching_video = intval($seconds_watching_video);
    }

    public function getJson()
    {
        return $this->json;
    }

    public function setJson($json)
    {
        if (!is_string($json)) {
            $json = _json_encode($json);
        }
        $this->json = $json;
    }

    public static function getAllFromVideos_id($videos_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $videos_id = intval($videos_id);

        if (empty($videos_id)) {
            return false;
        }

        $sql = "SELECT u.*, vs.* FROM  " . static::getTableName() . " vs ";
        $sql .= " LEFT JOIN users u ON vs.users_id = u.id ";
        $sql .= " WHERE videos_id=$videos_id ";

        $sql .= self::getSqlFromPost('', 'vs');
        //var_dump($_POST['searchPhrase'], $_GET['search']['value'], $sql);exit;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            $isPluginEnabled = AVideoPlugin::isEnabledByName('User_Location');

            foreach ($fullData as $row) {
                $row['users'] = User::getNameIdentificationById($row['users_id']);
                $row['when_human'] = humanTimingAgo($row['created_php_time'], 0, false);
                $row['seconds_watching_video_human'] = seconds2human($row['seconds_watching_video']);
                if ($isPluginEnabled) {
                    $json = _json_decode($row['json']);
                    if (empty($json)) {
                        $json = new stdClass();
                    }
                    if (empty($json->location)) {
                        $json->location = User_Location::getLocationFromIP($row['ip']);
                        $vs = new VideoStatistic($row['id']);
                        $vs->setJson($json);
                        $vs->save();
                    }
                    $json->location = object_to_array($json->location);
                    $row['location'] = $json->location;
                    if (empty($json->location['country_name']) || $json->location['country_name'] === '-') {
                        $row['location_name'] = $row['ip'];
                    } else {
                        $row['location_name'] = "{$json->location['country_name']}, {$json->location['city_name']}, {$json->location['region_name']}";
                    }
                } else {
                    $row['location_name'] = $row['location'] = '';
                }
                $rows[] = $row;
            }
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            $rows = [];
        }
        return $rows;
    }

    public static function getTotalFromVideos_id($videos_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $videos_id = intval($videos_id);

        if (empty($videos_id)) {
            return false;
        }

        $sql = "SELECT count(vs.id) as total FROM  " . static::getTableName() . " vs LEFT JOIN users u ON vs.users_id = u.id WHERE videos_id=$videos_id ";

        $sql .= self::getSqlSearchFromPost('vs');

        //echo $sql;//exit;
        $res = sqlDAL::readSql($sql);
        $result = sqlDAL::fetchAssoc($res);
        if (!empty($result)) {
            return intval($result['total']);
        }
        return 0;
    }

    public static function getStatisticTotalViewsAndSecondsWatchingFromUser($users_id, $startDate = "", $endDate = "")
    {
        global $global;

        $users_id = intval($users_id);

        $sql = "SELECT distinct(s.videos_id) as videos_id, title, filename, type, v.externalOptions FROM  " . static::getTableName() . " s LEFT JOIN videos v ON s.videos_id = v.id WHERE 1=1 ";

        $formats = '';
        $values = [];

        if (!empty($users_id)) {
            $sql .= " AND v.users_id = ? ";
            $formats .= "i";
            $values[] = $users_id;
        }

        if (!empty($startDate)) {
            $sql .= " AND `when` >= ? ";
            $formats .= "s";
            $values[] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND `when` <= ? ";
            $formats .= "s";
            $values[] = $endDate;
        }
        $sql .= " LIMIT 10000 ";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            $totalViews = 0;
            $totalWatchingTime = 0;
            foreach ($fullData as $row) {
                $sql = "SELECT count(s.videos_id) total_views, sum(seconds_watching_video) as seconds_watching_video FROM  " . static::getTableName() . " s WHERE 1=1 ";

                $formats = '';
                $values = [];

                $sql .= " AND s.videos_id = ? ";
                $formats .= "i";
                $values[] = $row['videos_id'];

                if (!empty($startDate)) {
                    $sql .= " AND `when` >= ? ";
                    $formats .= "s";
                    $values[] = $startDate;
                }

                if (!empty($endDate)) {
                    $sql .= " AND `when` <= ? ";
                    $formats .= "s";
                    $values[] = $endDate;
                }
                $sql .= " LIMIT 10000 ";
                $res2 = sqlDAL::readSql($sql, $formats, $values);
                $fullData2 = sqlDAL::fetchAllAssoc($res2);
                sqlDAL::close($res2);
                if ($res2 != false) {
                    foreach ($fullData2 as $row2) {
                        $totalViews += intval($row2['total_views']);
                        $totalWatchingTime += intval($row2['seconds_watching_video']);
                    }
                    foreach ($fullData2 as $row2) {
                        $row2['users_id'] = $users_id;
                        $row2['startDate'] = $startDate;
                        $row2['endDate'] = $endDate;
                        $row2['seconds_watching_video_human'] = secondsToDuration($row2['seconds_watching_video']);
                        $row2['seconds_watching_video_human2'] = seconds2human($row2['seconds_watching_video']);
                        $row2['totalViews'] = $totalViews;
                        $row2['totalWatchingTime'] = $totalWatchingTime;

                        $rows[] = array_merge($row, $row2);
                    }
                }
            }
            $totalWatchingTimeHuman = secondsToDuration($totalWatchingTime);
            $totalWatchingTimeHuman2 = seconds2human($totalWatchingTime);
            foreach ($rows as $key => $row) {
                $rows[$key]['totalViewsAllVideos'] = $totalViews;
                $rows[$key]['totalWatchingTimeAllVideos'] = $totalWatchingTime;
                $rows[$key]['totalWatchingTimeAllVideosHuman'] = $totalWatchingTimeHuman;
                $rows[$key]['totalWatchingTimeAllVideosHuman2'] = $totalWatchingTimeHuman2;
            }
        }

        return $rows;
    }
    public static function getSecondsWatchedFromVideos_id($videos_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $videos_id = intval($videos_id);

        if (empty($videos_id)) {
            return false;
        }

        $sql = "SELECT seconds_watching_video FROM  " . static::getTableName() . " WHERE videos_id=$videos_id ";

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $total = 0;
        if ($res !== false) {
            foreach ($fullData as $row) {
                $total += intval($row['seconds_watching_video']);
            }
        }
        return $total;
    }
}
