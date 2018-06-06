<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/include_config.php';

class Video_ad {

    private $id;
    private $ad_title;
    private $starts;
    private $finish;
    private $skip_after_seconds;
    private $redirect;
    private $finish_max_clicks;
    private $finish_max_prints;
    private $videos_id;
    private $categories_id;

    function __construct($videos_id, $categories_id, $ad_title = "", $starts = "", $id = 0) {
        global $global;
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($ad_title)) {
            $this->ad_title = $global['mysqli']->real_escape_string($ad_title);
        }
        if (!empty($starts)) {
            $this->starts = $starts;
        }
        if (!empty($videos_id)) {
            $this->videos_id = $videos_id;
        }
        if (!empty($categories_id)) {
            $this->categories_id = $categories_id;
        }
    }

    function load($id) {
        $video = self::getVideoAds($id);
        if (empty($video))
            return false;
        foreach ($video as $key => $value) {
            $this->$key = $value;
        }
    }

    function save() {
        if (!User::isAdmin()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        if (empty($this->starts)) {
            $this->starts = date('Y-m-d h:i:s');
        }
        if (empty($this->ad_title)) {
            return false;
        }
        if (empty($this->finish)) {
            $finish = "NULL";
        } else {
            $finish = "'{$this->finish}'";
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE video_ads SET "
                    . " ad_title = '{$this->ad_title}', "
                    . " starts = '{$this->starts}',"
                    . " finish = {$finish}, "
                    . " skip_after_seconds = '{$this->getSkip_after_seconds()}', "
                    . " redirect = '{$this->redirect}',"
                    . " finish_max_clicks = '{$this->getFinish_max_clicks()}', "
                    . " finish_max_prints = '{$this->getFinish_max_prints()}', "
                    . " videos_id = '{$this->videos_id}', "
                    . " categories_id = '{$this->categories_id}', "
                    . " modified = now()"
                    . " WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO video_ads "
                    . "(ad_title, starts, finish, skip_after_seconds, redirect, finish_max_clicks, finish_max_prints, videos_id,categories_id, created, modified) values "
                    . "('{$this->ad_title}','{$this->starts}', {$finish}, '{$this->getSkip_after_seconds()}',"
                    . "'{$this->redirect}', '{$this->getFinish_max_clicks()}', '{$this->getFinish_max_prints()}', '{$this->videos_id}', '{$this->categories_id}', now(), now())";
        }

        $insert_row = sqlDAL::writeSql($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            die($sql . ' Save Video Ads Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function getVideoAds($id = "") {
        global $global;
        $id = intval($id);
        if (empty($id)) {
            return false;
        }
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'video_ads'");
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);
        if (empty($result)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.7</a>";
            header("Location: {$global['webSiteRootURL']}user?error={$_GET['error']}");
            return false;
        }

        $sql = "SELECT * from video_ads WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql,"i",array($id));
        $data = sqlDal::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res!=false) {
            $ad = $data;
        } else {
            $ad = false;
        }
        return $ad;
    }

    static function getAllVideos($videos_id = "") {
        global $global;
        $formats = "";
        $values = array();
        $sql = "SELECT v.*, va.*, "
                . " (SELECT count(*) FROM video_ads_logs as val WHERE val.video_ads_id = va.id AND clicked = 1) as clicks, "
                . " (SELECT count(*) FROM video_ads_logs as val WHERE val.video_ads_id = va.id) as prints "
                . " FROM video_ads as va "
                . "LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE 1=1 ";

        if (!empty($videos_id)) {
            $sql .= " AND videos_id = ? ";
            $formats .= "i";
            $values[] = $videos_id;
        }

        $sql .= BootGrid::getSqlFromPost(array('ad_title', 'title'), "va.");
        $res = sqlDAL::readSql($sql,$formats,$values);
        $fullResult = sqlDal::fetchAllAssoc($res);
        sqlDAL::close($res);
        $videos = array();
        if ($res!=false) {
            require_once 'video.php';
            foreach ($fullResult as $row) {
                $row['tags'] = Video::getTags($row['videos_id']);
                $videos[] = $row;
            }
        } else {
            $videos = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    static function getTotalVideos() {
        global $global;
        $sql = "SELECT * from video_ads WHERE 1=1 ";
        $sql .= BootGrid::getSqlSearchFromPost(array('title', 'description'));
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDal::num_rows($res);
        sqlDAL::close($res);
        return $numRows;
    }

    function delete() {
        if (!User::isAdmin()) {
            return false;
        }
        $video_ad = self::getVideoAds($this->id);

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM video_ads WHERE id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql,"i",array($this->id));
    }

    function getId() {
        return $this->id;
    }

    function getAd_title() {
        return $this->ad_title;
    }

    function getStarts() {
        return $this->starts;
    }

    function getFinish() {
        return $this->finish;
    }

    function getSkip_after_seconds() {
        return intval($this->skip_after_seconds);
    }

    function getRedirect() {
        return $this->redirect;
    }

    function getFinish_max_clicks() {
        return intval($this->finish_max_clicks);
    }

    function getFinish_max_prints() {
        return intval($this->finish_max_prints);
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getCategories_id() {
        return $this->categories_id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setAd_title($ad_title) {
        $this->ad_title = $ad_title;
    }

    function setStarts($starts) {
        $this->starts = $starts;
    }

    function setFinish($finish) {
        $this->finish = $finish;
    }

    function setSkip_after_seconds($skip_after_seconds) {
        $this->skip_after_seconds = $skip_after_seconds;
    }

    function setRedirect($redirect) {
        $this->redirect = $redirect;
    }

    function setFinish_max_clicks($finish_max_clicks) {
        $this->finish_max_clicks = $finish_max_clicks;
    }

    function setFinish_max_prints($finish_max_prints) {
        $this->finish_max_prints = $finish_max_prints;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    static function getAdFromCategory($categories_id) {
        global $global;
        $categories_id = intval($categories_id);
        if (empty($categories_id)) {
            return false;
        }
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'video_ads'");
        $numRows = sqlDal::num_rows($res);
        sqlDAL::close($res);
        if (empty($numRows)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.7</a>";
            header("Location: {$global['webSiteRootURL']}user?error={$_GET['error']}");
            return false;
        }

        $sql = "SELECT v.*, va.* from video_ads as va "
                . " LEFT JOIN videos as v on va.videos_id = v.id "
                . "WHERE va.categories_id = ? "
                . " AND starts < now()"
                . " AND (finish IS NULL OR finish = '0000-00-00 00:00:00' OR finish > now()) "
                . " AND (finish_max_clicks = 0 OR finish_max_clicks > (SELECT count(*) FROM video_ads_logs as val WHERE val.video_ads_id = va.id AND clicked = 1 )) "
                . " AND (finish_max_prints = 0 OR finish_max_prints > (SELECT count(*) FROM video_ads_logs as val WHERE val.video_ads_id = va.id)) ";


        $sql .= "ORDER BY RAND() LIMIT 1";
        $res = sqlDAL::readSql($sql,"i",array($categories_id));
        $data = sqlDal::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res!=false) {
            $ad = $data;
        } else {
            $ad = false;
        }
        return $ad;
    }

    static function log($id){
        global $global;
        $userId = empty($_SESSION["user"]["id"]) ? "NULL" : $_SESSION["user"]["id"];
        $sql = "INSERT INTO video_ads_logs "
                    . "(datetime, clicked, ip, video_ads_id, users_id) values "
                    . "(now(),0, '".getRealIpAddr()."', ?,".$userId.")";
        $insert_row = sqlDAL::writeSql($sql,"i",array($id));
        if ($insert_row) {
            return $global['mysqli']->insert_id;
        } else {
            die($sql . ' Save Video Ads Log Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function clickLog($video_ads_log_id){
        global $global;
        $sql = "UPDATE video_ads_logs set clicked = 1 WHERE id = ?";

        $insert_row = sqlDAL::writeSql($sql,"i",array($video_ads_log_id));

        if ($insert_row) {
            return $video_ads_log_id;
        } else {
            die($sql . ' Save Click Video Ads Log Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function redirect($id){
        $ad = self::getVideoAds($id);
        header("Location: {$ad['redirect']}");
    }

}
