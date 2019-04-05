<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

class VideoStatistic extends ObjectYPT {

    protected $id;
    protected $when;
    protected $ip;
    protected $users_id;
    protected $videos_id;
    protected $lastVideoTime;
    protected $session_id;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'videos_statistics';
    }

    static function create($videos_id, $currentTime = 0) {
        global $global;
        /**
         * Dont crash if is an old version
        
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'videos_statistics'");
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);
        if (empty($result)) {
            echo "<div class='alert alert-danger'>You need to <a href='{$global['webSiteRootURL']}update'>update your system</a></div>";
            return false;
        }
 */
        if (empty($videos_id)) {
            die(__("You need a video to generate statistics"));
        }

        $userId = empty($_SESSION["user"]["id"]) ? "NULL" : $_SESSION["user"]["id"];

        $lastVideoTime = 0;
        if (empty($currentTime)) {
            $lastStatistic = self::getLastStatistics($videos_id, $userId);
            if (empty($currentTime) && !empty($lastStatistic)) {
                $lastVideoTime = intval($lastStatistic['lastVideoTime']);
            }
        } else {
            $lastVideoTime = intval($currentTime);
        }

        $sql = "INSERT INTO videos_statistics "
                . "(`when`,ip, users_id, videos_id, lastVideoTime, created, modified, session_id) values "
                . "(now(),?," . $userId . ",?,{$lastVideoTime},now(),now(),'" . session_id() . "')";
        $insert_row = sqlDAL::writeSql($sql, "si", array(getRealIpAddr(), $videos_id));

        if (!empty($global['mysqli']->insert_id)) {
            return $global['mysqli']->insert_id;
        } else {
            die($sql . ' Save Video Statistics Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function updateStatistic($videos_id, $users_id, $lastVideoTime) {
        $lastStatistic = self::getLastStatistics($videos_id, $users_id);
        if (empty($lastStatistic)) {
            $vs = new VideoStatistic(0);
            $vs->setUsers_id($users_id);
            $vs->setVideos_id($videos_id);
            $vs->setWhen(date("Y-m-d h:i:s"));
        } else {
            $vs = new VideoStatistic($lastStatistic['id']);
        }
        $vs->setLastVideoTime($lastVideoTime);
        return $vs->save();
    }

    function save() {
        $this->setSession_id(session_id());
        if (empty($this->session_id) && empty($this->users_id)) {
            return false;
        }
        if (empty($this->users_id)) {
            $this->setUsers_id('null');
        }
        return parent::save();
    }

    static function getLastStatistics($videos_id, $users_id = 0) {
        if (!empty($users_id)) {
            $sql = "SELECT * FROM videos_statistics WHERE videos_id = ? AND users_id = ? ORDER BY modified DESC LIMIT 1 ";
            $res = sqlDAL::readSql($sql, 'ii', array($videos_id, $users_id));
        } else {
            $sql = "SELECT * FROM videos_statistics WHERE videos_id = ? AND session_id = ? ORDER BY modified DESC LIMIT 1 ";
            $res = sqlDAL::readSql($sql, 'is', array($videos_id, session_id()));
        }
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            return $result;
        }
        return false;
    }

    static function getLastVideoTimeFromVideo($videos_id, $users_id) {
        $row = self::getLastStatistics($videos_id, $users_id);
        if (empty($row)) {
            return 0;
        }
        return intval($row['lastVideoTime']);
    }

    static function getStatisticTotalViews($videos_id, $uniqueUsers = false, $startDate = "", $endDate = "") {
        global $global;
        if ($uniqueUsers) {
            $ast = "distinct(users_id)";
        } else {
            $ast = "*";
        }
        $sql = "SELECT count({$ast}) as total FROM videos_statistics WHERE 1=1 ";
        $formats = "";
        $values = array();
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

    static function getTotalLastDaysAsync($video_id, $numberOfDays) {
        global $global, $advancedCustom;
        $md5 = ("{$video_id}_{$numberOfDays}");
        $path = $global['systemRootPath'] . "videos/cache/getTotalLastDaysAsync/";
        make_path($path);
        $cacheFileName = "{$path}{$md5}";
        if (empty($advancedCustom->AsyncJobs) || !file_exists($cacheFileName)) {
            if (file_exists($cacheFileName . ".lock")) {
                return array();
            }
            $total = static::getTotalLastDays($video_id, $numberOfDays);
            file_put_contents($cacheFileName, json_encode($total));
            return $total;
        }
        $return = json_decode(file_get_contents($cacheFileName));
        if (time() - filemtime($cacheFileName) > 60) {
            // file older than 1 min
            $command = ("php '{$global['systemRootPath']}objects/getTotalLastDaysAsync.php' '$video_id' '$numberOfDays' '$cacheFileName'");
            error_log("getTotalLastDaysAsync: {$command}");
            exec($command . " > /dev/null 2>/dev/null &");
        }
        return $return;
    }

    static function getTotalLastDays($video_id, $numberOfDays, $returnArray = array()) {
        if ($numberOfDays < 0) {
            return $returnArray;
        }
        $date = date("Y-m-d", strtotime("-{$numberOfDays} days"));
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date . " 00:00:00", $date . " 23:59:59");
        $numberOfDays--;
        return static::getTotalLastDays($video_id, $numberOfDays, $returnArray);
    }

    static function getTotalToday($video_id, $hour = 0, $returnArray = array()) {
        if ($hour >= 24) {
            return $returnArray;
        }
        $date = date("Y-m-d {$hour}", time());
        //echo $date;exit;
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date . ":00:00", $date . ":59:59");
        $hour++;
        return static::getTotalToday($video_id, $hour, $returnArray);
    }

    static function getTotalTodayAsync($video_id) {
        global $global, $advancedCustom;
        $cacheFileName = $global['systemRootPath'] . "videos/cache/getTotalTodayAsync_{$video_id}";
        if (empty($advancedCustom->AsyncJobs) || !file_exists($cacheFileName)) {
            if (file_exists($cacheFileName . ".lock")) {
                return array();
            }
            $total = static::getTotalToday($video_id);
            file_put_contents($cacheFileName, json_encode($total));
            return $total;
        }
        $return = json_decode(file_get_contents($cacheFileName));
        if (time() - filemtime($cacheFileName) > 60) {
            // file older than 1 min
            $command = ("php '{$global['systemRootPath']}objects/getTotalTodayAsync.php' '$video_id' '$cacheFileName'");
            error_log("getTotalTodayAsync: {$command}");
            exec($command . " > /dev/null 2>/dev/null &");
        }
        return $return;
    }

    function getWhen() {
        return $this->when;
    }

    function getIp() {
        return $this->ip;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getLastVideoTime() {
        return $this->lastVideoTime;
    }

    function setWhen($when) {
        $this->when = $when;
    }

    function setIp($ip) {
        $this->ip = $ip;
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
        if (empty($this->users_id)) {
            $this->users_id = 'null';
        }
    }

    function setVideos_id($videos_id) {
        $this->videos_id = intval($videos_id);
    }

    function setLastVideoTime($lastVideoTime) {
        $this->lastVideoTime = intval($lastVideoTime);
    }

    function getSession_id() {
        return $this->session_id;
    }

    function setSession_id($session_id) {
        $this->session_id = $session_id;
    }

}
