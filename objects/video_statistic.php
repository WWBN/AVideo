<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

class VideoStatistic {

    private $id;
    private $when;
    private $ip;
    private $users_id;
    private $videos_id;

    static function save($videos_id) {
        global $global;
        /**
         * Dont crash if is an old version
         */
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'videos_statistics'");
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);
        if (empty($result)) {
            echo "<div class='alert alert-danger'>You need to <a href='{$global['webSiteRootURL']}update'>update your system</a></div>";
            return false;
        }

        if (empty($videos_id)) {
            die(__("You need a video to generate statistics"));
        }

        $userId = empty($_SESSION["user"]["id"]) ? "NULL" : $_SESSION["user"]["id"];

        $sql = "INSERT INTO videos_statistics "
                . "(`when`,ip, users_id, videos_id) values "
                . "(now(),?,?,?)";
        $insert_row = sqlDAL::readSql($sql,"sii",array(getRealIpAddr(),$userId,$videos_id));

        if (!empty($global['mysqli']->insert_id)) {
            return $global['mysqli']->insert_id;
        } else {
            die($sql . ' Save Video Statistics Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
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
        $res = sqlDAL::readSql($sql,$formats,$values);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            //echo "<hr>".$row['total']." --- ".$sql, "<br>";
            return $result['total'];
        }
        return 0;
    }

    static function getTotalLastDays($video_id, $numberOfDays, $returnArray = array()) {
        if ($numberOfDays < 0) {
            return $returnArray;
        }
        $date = date("Y-m-d", strtotime("-{$numberOfDays} days"));
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date." 00:00:00", $date." 23:59:59");
        $numberOfDays--;
        return static::getTotalLastDays($video_id, $numberOfDays, $returnArray);
    }

    static function getTotalToday($video_id, $hour=0, $returnArray = array()) {
        if ($hour >= 24) {
            return $returnArray;
        }
        $date = date("Y-m-d {$hour}", time());
        //echo $date;exit;
        $returnArray[] = static::getStatisticTotalViews($video_id, false, $date.":00:00", $date.":59:59");
        $hour++;
        return static::getTotalToday($video_id, $hour, $returnArray);
    }

}
