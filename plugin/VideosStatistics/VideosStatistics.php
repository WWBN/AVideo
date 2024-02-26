<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

class VideosStatistics extends PluginAbstract {

    public function getDescription() {
        $desc = "VideosStatistics Plugin";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    public function getName() {
        return "VideosStatistics";
    }

    public function getUUID() {
        return "VideosStatistics-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        global $global;
        /*
          if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
          sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
          }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
        $o = new stdClass();
        $o->type = array(
            0=>'Do not delete', 
            7=>'Delete records older than 1 week', 
            30=>'Delete records older than 1 month', 
        );
        for ($i=60; $i < 365; $i+=60) { 
            $o->type[$i] = "Delete records older than ".($i/30)." months";
        }
        $o->value = 60;
        $obj->autoCleanStatisticsTable = $o;
        self::addDataObjectHelper('autoCleanStatisticsTable', 'Auto clean statistics table', 'This option is good to speed up your page');
        
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $btn = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/VideosStatistics/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/VideosStatistics/autoclean.json.php\')" class="btn btn-danger btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Clean Old Records</button>';
        return $btn;
    }

    static public function getTotalVideos($users_id) {
        $sql = "SELECT count(id) as total FROM videos WHERE 1 = 1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }

        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        return $total;
    }

    static public function getTotalVideosViews($users_id) {
        $sql = "SELECT sum(views_count) as total FROM videos WHERE 1 = 1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }

        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        return $total;
    }

    static public function getTotalSubscriptions($users_id) {
        return Subscribe::getTotalSubscribes($users_id);
    }

    static public function getTotalComments($users_id) {
        $sql = "SELECT count(id) as total FROM comments WHERE 1 = 1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND videos_id IN (SELECT ID from videos WHERE users_id = $users_id )";
        }

        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        return $total;
    }

    static public function getTotalLikes($users_id) {
        return self::getTotalLikesDislikesFromVideos($users_id, 1, 0);
    }

    static public function getTotalDislikes($users_id) {
        return self::getTotalLikesDislikesFromVideos($users_id, -1, 0);
    }

    static public function getTotalLikesDislikesFromVideos($users_id, $like, $days) {
        global $_getTotalLikesDislikes;

        $index = "$users_id, $like, $days";

        if (!isset($_getTotalLikesDislikes)) {
            $_getTotalLikesDislikes = array();
        }

        if (isset($_getTotalLikesDislikes[$index])) {
            return $_getTotalLikesDislikes[$index];
        }

        $column = 'likes';
        if ($like == -1) {
            $column = 'dislikes';
        }

        $sql = "SELECT sum({$column}) as total FROM videos WHERE 1=1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }
        if (!empty($days)) {
            $sql .= " AND modified  > (NOW() - INTERVAL {$days} DAY) ";
        }
        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        $_getTotalLikesDislikes[$index] = $total;
        return $total;
    }

    static public function getTotalLikesDislikes($users_id, $like, $days) {
        global $_getTotalLikesDislikes;

        $index = "$users_id, $like, $days";

        if (!isset($_getTotalLikesDislikes)) {
            $_getTotalLikesDislikes = array();
        }

        if (isset($_getTotalLikesDislikes[$index])) {
            return $_getTotalLikesDislikes[$index];
        }

        $sql = "SELECT count(id) as total FROM likes WHERE `like` = {$like} ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND videos_id IN (SELECT ID from videos WHERE users_id = $users_id )";
        }

        if (!empty($days)) {
            $sql .= " AND modified  > (NOW() - INTERVAL {$days} DAY) ";
        }
        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        $_getTotalLikesDislikes[$index] = $total;
        return $total;
    }

    static public function getTotalDuration($users_id) {
        $sql = "SELECT sum(duration_in_seconds) as total FROM videos WHERE 1 = 1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }

        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        return $total;
    }

    static public function getTotalSize($users_id) {
        $sql = "SELECT sum(filesize) as total FROM videos WHERE 1 = 1 ";
        $users_id = intval($users_id);
        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }

        $total = 0;
        $res = sqlDAL::readSql($sql);
        if ($res != false) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $total = intval($row['total']);
        }
        return $total;
    }

    static public function getMostViewedVideosFromLastDays($users_id, $days = 7, $limit = 15) {
        global $global;
        $sql = "SELECT v.*, "
                . " (SELECT count(id) FROM videos_statistics vs WHERE vs.videos_id = v.id AND modified  > (NOW() - INTERVAL {$days} DAY)) as total_views, "
                . " (SELECT count(id) FROM comments c WHERE c.videos_id = v.id AND modified  > (NOW() - INTERVAL {$days} DAY)) as total_comments  "
                . " FROM videos v WHERE (SELECT count(id) FROM videos_statistics vs WHERE vs.videos_id = v.id AND modified  > (NOW() - INTERVAL {$days} DAY)) > 0 ";

        $users_id = intval($users_id);
        $days = intval($days);
        $limit = intval($limit);

        if (!empty($users_id)) {
            $sql .= " AND users_id = $users_id ";
        }

        if (!empty($days)) {
            $sql .= " AND modified  > (NOW() - INTERVAL {$days} DAY) ";
        }

        $sql .= " ORDER BY v.modified DESC ";

        $sql .= " LIMIT {$limit} ";

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        $obj = new stdClass();
        $obj->videos = array();
        $obj->totalComents = 0;
        $obj->totalVideosViews = 0;
        $obj->totalDurationVideos = 0;
        $obj->totalLikes = 0;
        $obj->totalDislikes = 0;

        if ($res != false) {
            foreach ($fullData as $row) {
                $video = new stdClass();
                $video->id = $row['id'];
                $video->title = $row['title'];
                $video->clean_title = $row['clean_title'];
                $video->totalComents = $row['total_comments'];
                $video->total_views = $row['total_views'];
                $video->modified = $row['modified'];
                $video->total_likes = self::getTotalLikesDislikes($users_id, 1, $days);
                $video->total_dislikes = self::getTotalLikesDislikes($users_id, -1, $days);
                $video->human = humanTimingAgo($video->modified, 2);
                $video->poster = Video::getPoster($row['id']);

                $obj->videos[] = $video;
                $obj->totalComents += $video->totalComents;
                $obj->totalVideosViews += $video->total_views;
                $obj->totalDurationVideos += $row['duration_in_seconds'];
                $obj->totalLikes += $video->total_likes;
                $obj->totalDislikes += $video->total_dislikes;
            }
        }
        
        if (!empty($obj->videos)) {
            usort(
                    $obj->videos,
                    function ($a, $b) {
                        return $b->total_views - $a->total_views;
                    }
            );
        }

        $obj->totalVideos = count($obj->videos);
        return $obj;
    }

    static public function getVideosToReward($percentage_watched, $only_logged_users = false, $users_id = 0) {
        global $global;
        // Preparing the SQL statement
        $sql = "SELECT vs.*, v.duration_in_seconds, v.users_id as video_owner_users_id, "
                . " (vs.seconds_watching_video / v.duration_in_seconds * 100) as percentage_watched "
                . " FROM videos_statistics as vs "
                . " JOIN videos as v ON vs.videos_id = v.id "
                . " WHERE vs.seconds_watching_video IS NOT NULL ";

        if (!empty($only_logged_users)) {
            $sql .= " AND vs.users_id IS NOT NULL AND vs.users_id > 0 ";
        }

        $sql .= " AND vs.rewarded = 0 AND vs.seconds_watching_video >= (v.duration_in_seconds * ? / 100) ";
        $formats = "s";
        $values = [$percentage_watched];

        if (!empty($users_id)) {
            $sql .= " AND v.users_id = ?";
            $formats .= 'i';
            $values[] = $users_id;
        }

        //var_dump($sql, $formats, $values);
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }
    
    static public function setRewarded($ids_array) {
        if(empty($ids_array)){
            return true;
        }
        if(!is_array($ids_array)){
            $ids_array = array($ids_array);
        }
        global $global;
        // Preparing the SQL statement
        $sql = "UPDATE videos_statistics SET rewarded = 1 WHERE id IN (".implode(',', $ids_array).") ";
        
        return sqlDAL::writeSql($sql);
    }

    function executeEveryDay() {
        self::autoCleanStatisticsTable();
    }

    static function autoCleanStatisticsTable(){
        $obj = AVideoPlugin::getDataObject('VideosStatistics');
        if(!empty($obj->autoCleanStatisticsTable)){
            $interval = intval($obj->autoCleanStatisticsTable->value);
            $sql = "DELETE FROM videos_statistics
            WHERE created < NOW() - INTERVAL {$interval} DAY;";
            return sqlDAL::writeSql($sql);
        }
        return false;
    }


    
    public static function profileTabName($users_id) {
        global $global;
        include $global['systemRootPath'] . 'plugin/VideosStatistics/profileTabName.php';
    }

    public static function profileTabContent($users_id) {
        global $global;
        include $global['systemRootPath'] . 'plugin/VideosStatistics/profileTabContent.php';
    }
    
    public function navBarButtons()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/VideosStatistics/navBarButtons.php';
    }
}
