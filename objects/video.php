<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/include_config.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';

class Video {

    private $id;
    private $title;
    private $clean_title;
    private $filename;
    private $description;
    private $view_count;
    private $status;
    private $duration;
    private $users_id;
    private $categories_id;
    private $type;
    private $rotation;
    private $zoom;
    private $videoDownloadedLink;
    static $types = array('webm', 'mp4', 'mp3', 'ogg');
    private $videoGroups;
    private $videoAdsCount;
    static $statusDesc = array(
        'a' => 'active',
        'i' => 'inactive',
        'e' => 'encoding',
        'x' => 'encoding error',
        'd' => 'downloading',
        'xmp4' => 'encoding mp4 error',
        'xwebm' => 'encoding webm error',
        'xmp3' => 'encoding mp3 error',
        'xogg' => 'encoding ogg error',
        'ximg' => 'get image error');
    //ver 3.4
    private $youtubeId;

    function __construct($title = "", $filename = "", $id = 0) {
        global $global;
        $this->rotation = 0;
        $this->zoom = 1;
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($title)) {
            $this->title = $global['mysqli']->real_escape_string($title);
        }
        if (!empty($filename)) {
            $this->filename = $filename;
        }
    }

    function addView() {
        global $global;
        if (empty($this->id)) {
            return false;
        }
        $sql = "UPDATE videos SET views_count = views_count+1, modified = now() WHERE id = {$this->id}";


        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            VideoStatistic::save($this->id);
            return $this->id;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    function load($id) {
        $video = self::getVideo($id, "", true);
        if (empty($video))
            return false;
        foreach ($video as $key => $value) {
            $this->$key = $value;
        }
    }

    function save($updateVideoGroups = false) {
        if (!User::isLogged()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        if (empty($this->clean_title)) {
            $this->clean_title = $this->filename;
        }
        $this->clean_title = self::fixCleanTitle($this->clean_title, 1, $this->id);
        global $global;

        if (empty($this->status)) {
            $this->status = 'e';
        }
        $this->description = $global['mysqli']->real_escape_string($this->description);
        $this->title = $global['mysqli']->real_escape_string($this->title);
        if (!empty($this->id)) {
            if (!$this->userCanManageVideo()) {
                header('Content-Type: application/json');
                die('{"error":"' . __("Permission denied") . '"}');
            }
            $sql = "UPDATE videos SET title = '{$this->title}',clean_title = '{$this->clean_title}',"
                    . " filename = '{$this->filename}', categories_id = '{$this->categories_id}', status = '{$this->status}',"
                    . " description = '{$this->description}', duration = '{$this->duration}', type = '{$this->type}', videoDownloadedLink = '{$this->videoDownloadedLink}', youtubeId = '{$this->youtubeId}', modified = now()"
                    . " WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO videos "
                    . "(title,clean_title, filename, users_id, categories_id, status, description, duration,type,videoDownloadedLink, created, modified) values "
                    . "('{$this->title}','{$this->clean_title}', '{$this->filename}', {$_SESSION["user"]["id"]},1, '{$this->status}', '{$this->description}', '{$this->duration}', '{$this->type}', '{$this->videoDownloadedLink}', now(), now())";
        }
        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            if ($updateVideoGroups) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                // update the user groups
                UserGroups::updateVideoGroups($id, $this->videoGroups);
            }
            return $id;
        } else {
            die($sql . ' Save Video Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    function setClean_title($clean_title) {
        $clean_title = preg_replace('/[^0-9a-z]+/', '-', trim(strtolower(cleanString($clean_title))));
        $this->clean_title = $clean_title;
    }

    function setDuration($duration) {
        $this->duration = $duration;
    }

    function setStatus($status) {
        if (!empty($this->id)) {
            global $global;
            $sql = "UPDATE videos SET status = '{$status}', modified = now() WHERE id = {$this->id} ";
            if (!$global['mysqli']->query($sql)) {
                die('Error on update Status: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
        $this->status = $status;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setRotation($rotation) {
        $saneRotation = intval($rotation) % 360;

        if (!empty($this->id)) {
            global $global;
            $sql = "UPDATE videos SET rotation = '{$saneRotation}', modified = now() WHERE id = {$this->id} ";
            if (!$global['mysqli']->query($sql)) {
                die('Error on update Rotation: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
        $this->rotation = $saneRotation;
    }

    function getRotation() {
        return $this->rotation;
    }

    function setZoom($zoom) {
        $saneZoom = abs(floatval($zoom));

        if ($saneZoom < 0.1 || $saneZoom > 10) {
            die('Zoom level must be between 0.1 and 10');
        }

        if (!empty($this->id)) {
            global $global;
            $sql = "UPDATE videos SET zoom = '{$saneZoom}', modified = now() WHERE id = {$this->id} ";
            if (!$global['mysqli']->query($sql)) {
                die('Error on update Zoom: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }

        $this->zoom = $saneZoom;
    }

    function getZoom() {
        return $this->zoom;
    }

    static private function getUserGroupsCanSeeSQL() {
        global $global;

        $res = $global['mysqli']->query('select 1 from `videos_group_view` LIMIT 1');
        if (!$res) {
            if (User::isAdmin()) {
                $_GET['error'] = "You need to Update YouPHPTube to version 2.3 <a href='{$global['webSiteRootURL']}update/'>Click here</a>";
            }
            return "";
        }
        if (User::isAdmin()) {
            return "";
        }
        $result = $global['mysqli']->query("SHOW TABLES LIKE 'videos_group_view'");
        if (empty($result->num_rows)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.3</a>";
            header("Location: {$global['webSiteRootURL']}user?error={$_GET['error']}");
            return false;
        }
        $sql = " (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0 ";
        if (User::isLogged()) {
            require_once 'userGroups.php';
            $userGroups = UserGroups::getUserGroups(User::getId());
            $groups_id = array();
            foreach ($userGroups as $value) {
                $groups_id[] = $value['users_groups_id'];
            }
            if (!empty($groups_id)) {
                $sql = " (({$sql}) OR ((SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id AND users_groups_id IN (" . implode(",", $groups_id) . ") ) > 0)) ";
            }
        }
        return " AND " . $sql;
    }

    static function getVideo($id = "", $status = "viewable", $ignoreGroup = false, $random = false) {
        global $global;
        $id = intval($id);

        $result = $global['mysqli']->query("SHOW TABLES LIKE 'likes'");
        if (empty($result->num_rows)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.0</a>";
            header("Location: {$global['webSiteRootURL']}user?error={$_GET['error']}");
            return false;
        }
        $result = $global['mysqli']->query("SHOW TABLES LIKE 'video_ads'");
        if (empty($result->num_rows)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.7</a>";
            header("Location: {$global['webSiteRootURL']}user?error={$_GET['error']}");
            return false;
        }

        $sql = "SELECT u.*, v.*, c.name as category,c.iconClass,  c.clean_name as clean_category, v.created as videoCreation, "
                . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes, "
                . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes, "
                . " (SELECT count(id) FROM video_ads as va where va.videos_id = v.id) as videoAdsCount ";
        if (User::isLogged()) {
            $sql .= ", (SELECT `like` FROM likes as l where l.videos_id = v.id AND users_id = " . User::getId() . " ) as myVote ";
        } else {
            $sql .= ", 0 as myVote ";
        }
        $sql .= " FROM videos as v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . "LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 1=1 ";
        $sql .= static::getVideoQueryFileter();
        if (!$ignoreGroup) {
            $sql .= self::getUserGroupsCanSeeSQL();
        }
        if (!empty($_SESSION['type'])) {
            $sql .= " AND v.type = '{$_SESSION['type']}' ";
        }


        if ($status == "viewable" || $status == "viewableNotAd" || $status == "viewableAdOnly") {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus()) . "')";
            if ($status == "viewableNotAd") {
                $sql .= " having videoAdsCount = 0 ";
            } else if ($status == "viewableAd") {
                $sql .= " having videoAdsCount > 0 ";
            }
        } else if (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }

        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }
        if (!empty($id)) {
            $sql .= " AND v.id = $id ";
        } else if (empty($random) && !empty($_GET['videoName'])) {
            $sql .= " AND clean_title = '{$_GET['videoName']}' ";
        } else if (!empty($random)) {
            $sql .= " AND v.id != {$random} ";
            $sql .= " ORDER BY RAND() ";
        } else {
            $sql .= " ORDER BY v.Created DESC ";
        }
        $sql .= " LIMIT 1";
        //if(!empty($random))echo "<hr>".$sql;
        $res = $global['mysqli']->query($sql);
        if ($res) {
            require_once 'userGroups.php';
            $video = $res->fetch_assoc();
            //$video['groups'] = UserGroups::getVideoGroups($video['id']);
        } else {
            $video = false;
        }
        return $video;
    }

    static function getVideoFromFileName($fileName) {
        global $global;

        $sql = "SELECT id  FROM videos  WHERE filename = '{$fileName}' LIMIT 1";
        //echo $sql;
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $video = $res->fetch_assoc();
            return self::getVideo($video['id'], "");
            //$video['groups'] = UserGroups::getVideoGroups($video['id']);
        } else {
            return false;
        }
    }

    /**
     * 
     * @global type $global
     * @param type $status
     * @param type $showOnlyLoggedUserVideos you may pass an user ID to filter results
     * @param type $ignoreGroup
     * @param type $videosArrayId an array with videos to return (for filter only)
     * @return boolean
     */
    static function getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false) {
        global $global;
        $sql = "SELECT u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category, v.created as videoCreation, "
                . " (SELECT count(id) FROM video_ads as va where va.videos_id = v.id) as videoAdsCount "
                . " FROM videos as v "
                . " LEFT JOIN categories c ON categories_id = c.id "
                . " LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 1=1 ";

        $sql .= static::getVideoQueryFileter();
        if (!empty($videosArrayId) && is_array($videosArrayId)) {
            $sql .= " AND v.id IN ( " . implode(", ", $videosArrayId) . ") ";
        }

        if (!$ignoreGroup) {
            $sql .= self::getUserGroupsCanSeeSQL();
        }
        if (!empty($_SESSION['type'])) {
            $sql .= " AND v.type = '{$_SESSION['type']}' ";
        }

        if ($status == "viewable" || $status == "viewableNotAd" || $status == "viewableAdOnly") {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus()) . "')";
            if ($status == "viewableNotAd") {
                $sql .= " having videoAdsCount = 0 ";
            } else if ($status == "viewableAd") {
                $sql .= " having videoAdsCount > 0 ";
            }
        } else if (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }
        if ($showOnlyLoggedUserVideos === true && !User::isAdmin()) {
            $sql .= " AND v.users_id = '" . User::getId() . "'";
        } else if (!empty($showOnlyLoggedUserVideos)) {
            $sql .= " AND v.users_id = {$showOnlyLoggedUserVideos}";
        }

        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }

        if (!empty($_GET['search'])) {
            $_POST['searchPhrase'] = $_GET['search'];
        }

        $sql .= BootGrid::getSqlFromPost(array('title', 'description'), "v.");

        //echo $sql;
        $res = $global['mysqli']->query($sql);
        $videos = array();
        if ($res) {
            require_once 'userGroups.php';
            while ($row = $res->fetch_assoc()) {
                if($getStatistcs){                    
                    $previewsMonth = date("Y-m-d", strtotime("-30 days"));
                    $previewsWeek = date("Y-m-d", strtotime("-7 days"));
                    $today = date('Y-m-d');
                    $row['statistc_all'] = VideoStatistic::getStatisticTotalViews($row['id']);
                    $row['statistc_week'] = VideoStatistic::getStatisticTotalViews($row['id'],false, $previewsWeek, $today);
                    $row['statistc_month'] = VideoStatistic::getStatisticTotalViews($row['id'], false,$previewsMonth, $today);
                    $row['statistc_unique_user'] = VideoStatistic::getStatisticTotalViews($row['id'], true);
                }
                $row['groups'] = UserGroups::getVideoGroups($row['id']);
                $row['tags'] = self::getTags($row['id']);
                $videos[] = $row;
            }
            //$videos = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $videos = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    static function getTotalVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false) {
        global $global;
        $sql = "SELECT v.id, "
                . " (SELECT count(id) FROM video_ads as va where va.videos_id = v.id) as videoAdsCount "
                . "FROM videos v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . " WHERE 1=1  ";

        $sql .= static::getVideoQueryFileter();
        if (!$ignoreGroup) {
            $sql .= self::getUserGroupsCanSeeSQL();
        }
        if ($status == "viewable" || $status == "viewableNotAd" || $status == "viewableAdOnly") {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus()) . "')";
            if ($status == "viewableNotAd") {
                $sql .= " having videoAdsCount = 0 ";
            } else if ($status == "viewableAd") {
                $sql .= " having videoAdsCount > 0 ";
            }
        } else if (!empty($status)) {
            $sql .= " AND status = '{$status}'";
        }
        if ($showOnlyLoggedUserVideos === true && !User::isAdmin()) {
            $sql .= " AND v.users_id = '" . User::getId() . "'";
        } else if (is_int($showOnlyLoggedUserVideos)) {
            $sql .= " AND v.users_id = {$showOnlyLoggedUserVideos}";
        }
        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }

        $sql .= BootGrid::getSqlSearchFromPost(array('title', 'description'));

        $res = $global['mysqli']->query($sql);

        if (!$res) {
            return 0;
        }

        return $res->num_rows;
    }

    static private function getViewableStatus() {
        /**
          a = active
          i = inactive
          e = encoding
          x = encoding error
          d = downloading
          xmp4 = encoding mp4 error
          xwebm = encoding webm error
          xmp3 = encoding mp3 error
          xogg = encoding ogg error
         */
        return array('a', 'xmp4', 'xwebm', 'xmp3', 'xogg');
    }

    static function getVideoConversionStatus($filename) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/user.php';
        if (!User::isLogged()) {
            die("Only logged users can upload");
        }

        $object = new stdClass();

        foreach (self::$types as $value) {
            $progressFilename = "{$global['systemRootPath']}videos/{$filename}_progress_{$value}.txt";
            $content = @file_get_contents($progressFilename);
            $object->$value = new stdClass();
            if (!empty($content)) {
                $object->$value = self::parseProgress($content);
            } else {
                
            }

            if (!empty($object->$value->progress) && !is_numeric($object->$value->progress)) {

                $video = self::getVideoFromFileName($filename);
                //var_dump($video, $filename);
                if (!empty($video)) {
                    $object->$value->progress = self::$statusDesc[$video['status']];
                }
            }

            $object->$value->filename = $progressFilename;
        }

        return $object;
    }

    static private function parseProgress($content) {
        //get duration of source

        $obj = new stdClass();

        $obj->duration = 0;
        $obj->currentTime = 0;
        $obj->progress = 0;
        //var_dump($content);exit;
        preg_match("/Duration: (.*?), start:/", $content, $matches);
        if (!empty($matches[1])) {

            $rawDuration = $matches[1];

            //rawDuration is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawDuration));
            $duration = floatval($ar[0]);
            if (!empty($ar[1])) {
                $duration += intval($ar[1]) * 60;
            }
            if (!empty($ar[2])) {
                $duration += intval($ar[2]) * 60 * 60;
            }

            //get the time in the file that is already encoded
            preg_match_all("/time=(.*?) bitrate/", $content, $matches);

            $rawTime = array_pop($matches);

            //this is needed if there is more than one match
            if (is_array($rawTime)) {
                $rawTime = array_pop($rawTime);
            }

            //rawTime is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawTime));
            $time = floatval($ar[0]);
            if (!empty($ar[1])) {
                $time += intval($ar[1]) * 60;
            }
            if (!empty($ar[2])) {
                $time += intval($ar[2]) * 60 * 60;
            }

            if (!empty($duration)) {
                //calculate the progress
                $progress = round(($time / $duration) * 100);
            } else {
                $progress = 'undefined';
            }
            $obj->duration = $duration;
            $obj->currentTime = $time;
            $obj->progress = $progress;
        }
        return $obj;
    }

    function delete() {
        if (!$this->userCanManageVideo()) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $video = self::getVideo($this->id);
            $sql = "DELETE FROM videos WHERE id = {$this->id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        } else {
            foreach (self::$types as $value) {
                /*
                  $cmd = "rm -f {$global['systemRootPath']}videos/original_{$video['filename']}.{$value}";
                  exec($cmd);
                  $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}.{$value}";
                  exec($cmd);
                  $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}_progress_{$value}.txt";
                  exec($cmd);
                 * 
                 */
                $file = "{$global['systemRootPath']}videos/original_{$video['filename']}";
                if (file_exists($file)) {
                    unlink($file);
                }
                $file = "{$global['systemRootPath']}videos/{$video['filename']}.{$value}";
                if (file_exists($file)) {
                    unlink($file);
                }
                $file = "{$global['systemRootPath']}videos/{$video['filename']}_progress_{$value}.txt";
                if (file_exists($file)) {
                    unlink($file);
                }
                $file = "{$global['systemRootPath']}videos/{$video['filename']}.jpg";
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        return $resp;
    }

    function setDescription($description) {
        global $global;
        $this->description = $global['mysqli']->real_escape_string($description);
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    static function getCleanDuration($duration = "") {
        if (empty($duration)) {
            if (!empty($this) && !empty($this->duration)) {
                $durationParts = explode(".", $this->duration);
            } else {
                return "00:00:00";
            }
        } else {
            $durationParts = explode(".", $duration);
        }
        if (empty($durationParts[0])) {
            return "00:00:00";
        } else {
            return $durationParts[0];
        }
    }

    static function getItemPropDuration($duration = "") {
        $duration = static::getCleanDuration($duration);
        $parts = explode(":", $duration);
        return "PT" . intval($parts[0]) . "H" . intval($parts[1]) . "M" . intval($parts[2]) . "S";
    }

    static function getDurationFromFile($file) {
        global $config;
        // get movie duration HOURS:MM:SS.MICROSECONDS
        if (!file_exists($file)) {
            error_log('{"status":"error", "msg":"getDurationFromFile ERROR, File (' . $file . ') Not Found"}');
            return "EE:EE:EE";
        }
        //$cmd = 'ffprobe -i ' . $file . ' -sexagesimal -show_entries  format=duration -v quiet -of csv="p=0"';
        eval('$cmd="' . $config->getFfprobeDuration() . '";');
        exec($cmd . ' 2>&1', $output, $return_val);
        if ($return_val !== 0) {
            error_log('{"status":"error", "msg":' . json_encode($output) . ' ,"return_val":' . json_encode($return_val) . ', "where":"getDuration", "cmd":"' . $cmd . '"}');
            // fix ffprobe
            $duration = "EE:EE:EE";
        } else {
            preg_match("/([0-9]+:[0-9]+:[0-9]{2})/", $output[0], $match);
            if (!empty($match[1])) {
                $duration = $match[1];
            } else {
                error_log('{"status":"error", "msg":' . json_encode($output) . ' ,"match_not_found":' . json_encode($match) . ' ,"return_val":' . json_encode($return_val) . ', "where":"getDuration", "cmd":"' . $cmd . '"}');
                $duration = "EE:EE:EE";
            }
        }
        error_log("Duration founded: {$duration}");
        return $duration;
    }

    function updateDurationIfNeed($fileExtension = ".mp4") {
        global $global;
        if (!empty($this->id) && $this->duration == "EE:EE:EE") {
            $this->duration = Video::getDurationFromFile($global['systemRootPath'] . "videos/" . $this->filename . $fileExtension);
            error_log("Duration Updated: " . print_r($this, true));

            $sql = "UPDATE videos SET duration = '{$this->duration}', modified = now() WHERE id = {$this->id}";

            $global['mysqli']->query($sql);

            return $this->id;
        } else {
            error_log("Do not need update duration: " . print_r($this, true));
            return false;
        }
    }

    function getFilename() {
        return $this->filename;
    }

    function getStatus() {
        return $this->status;
    }

    function getId() {
        return $this->id;
    }

    function getVideoDownloadedLink() {
        return $this->videoDownloadedLink;
    }

    function setVideoDownloadedLink($videoDownloadedLink) {
        $this->videoDownloadedLink = $videoDownloadedLink;
    }

    static function isLandscape($pathFileName) {
        global $config;
        // get movie duration HOURS:MM:SS.MICROSECONDS
        if (!file_exists($pathFileName)) {
            echo '{"status":"error", "msg":"getDurationFromFile ERROR, File (' . $pathFileName . ') Not Found"}';
            exit;
        }
        eval('$cmd="' . $config->getExiftool() . '";');
        $resp = true; // is landscape by default
        exec($cmd . ' 2>&1', $output, $return_val);
        if ($return_val !== 0) {
            $resp = true;
        } else {
            $w = 1;
            $h = 0;
            $rotation = 0;
            foreach ($output as $value) {
                preg_match("/Image Size.*:[^0-9]*([0-9]+x[0-9]+)/i", $value, $match);
                if (!empty($match)) {
                    $parts = explode("x", $match[1]);
                    $w = $parts[0];
                    $h = $parts[1];
                }
                preg_match("/Rotation.*:[^0-9]*([0-9]+)/i", $value, $match);
                if (!empty($match)) {
                    $rotation = $match[1];
                }
            }
            if ($rotation == 0) {
                if ($w > $h) {
                    $resp = true;
                } else {
                    $resp = false;
                }
            } else {
                if ($w < $h) {
                    $resp = true;
                } else {
                    $resp = false;
                }
            }
        }
        //var_dump($cmd, $w, $h, $rotation, $resp);exit;
        return $resp;
    }

    function userCanManageVideo() {
        if (empty($this->users_id) || !User::canUpload()) {
            return false;
        }
        // if you not admin you can only manager yours video
        if (!User::isAdmin() && $this->users_id != User::getId()) {
            return false;
        }
        return true;
    }

    function getVideoGroups() {
        return $this->videoGroups;
    }

    function setVideoGroups($userGroups) {
        if (is_array($userGroups)) {
            $this->videoGroups = $userGroups;
        }
    }

    /**
     * 
     * @param type $user_id
     * text
     * label Default Primary Success Info Warning Danger
     */
    static function getTags($video_id, $type = "") {
        $video = new Video("", "", $video_id);
        $tags = array();

        if (empty($type) || $type === "ad") {
            $obj = new stdClass();
            $obj->label = __("Is Ad");
            if ($video->getIsAd()) {
                $obj->type = "success";
                $obj->text = __("Yes");
                $tags[] = $obj;
            } else {
                $obj->type = "danger";
                $obj->text = __("No");
                $tags[] = $obj;
            }
        }

        /**
          a = active
          i = inactive
          e = encoding
          x = encoding error
          d = downloading
          xmp4 = encoding mp4 error
          xwebm = encoding webm error
          xmp3 = encoding mp3 error
          xogg = encoding ogg error
          ximg = get image error
         */
        if (empty($type) || $type === "status") {
            $obj = new stdClass();
            $obj->label = __("Status");
            switch ($video->getStatus()) {
                case 'a':
                    $obj->type = "success";
                    $obj->text = __("Active");
                    break;
                case 'i':
                    $obj->type = "warning";
                    $obj->text = __("Inactive");
                    break;
                case 'e':
                    $obj->type = "info";
                    $obj->text = __("Encoding");
                    break;
                case 'd':
                    $obj->type = "info";
                    $obj->text = __("Downloading");
                    break;
                case 'xmp4':
                    $obj->type = "danger";
                    $obj->text = __("Encoding mp4 error");
                    break;
                case 'xwebm':
                    $obj->type = "danger";
                    $obj->text = __("Encoding xwebm error");
                    break;
                case 'xmp3':
                    $obj->type = "danger";
                    $obj->text = __("Encoding xmp3 error");
                    break;
                case 'xogg':
                    $obj->type = "danger";
                    $obj->text = __("Encoding xogg error");
                    break;
                case 'ximg':
                    $obj->type = "danger";
                    $obj->text = __("Get imgage error");
                    break;

                default:
                    $obj->type = "danger";
                    $obj->text = __("Status not found");
                    break;
            }
            $obj->text = $obj->text;
            $tags[] = $obj;
        }
        if (empty($type) || $type === "userGroups") {
            require_once 'userGroups.php';
            $groups = UserGroups::getVideoGroups($video_id);
            $obj = new stdClass();
            $obj->label = __("Group");
            if (empty($groups)) {
                $obj->type = "success";
                $obj->text = __("Public");
                $tags[] = $obj;
            } else {
                foreach ($groups as $value) {
                    $obj->type = "warning";
                    $obj->text = $value['group_name'];
                    $tags[] = $obj;
                }
            }
        }
        if (empty($type) || $type === "category") {
            require_once 'category.php';
            $category = Category::getCategory($video->getCategories_id());
            $obj = new stdClass();
            $obj->label = __("Category");
            if (!empty($category)) {
                $obj->type = "default";
                $obj->text = $category['name'];
                $tags[] = $obj;
            }
        }
        if (empty($type) || $type === "source") {
            $url = $video->getVideoDownloadedLink();
            $parse = parse_url($url);
            $obj = new stdClass();
            $obj->label = __("Source");
            if (!empty($parse['host'])) {
                $obj->type = "danger";
                $obj->text = $parse['host'];
                $tags[] = $obj;
            } else {
                $obj->type = "info";
                $obj->text = __("Local File");
                $tags[] = $obj;
            }
        }

        return $tags;
    }

    function getCategories_id() {
        return $this->categories_id;
    }

    function getType() {
        return $this->type;
    }

    function getIsAd() {
        return !empty($this->videoAdsCount);
    }

    static function fixCleanTitle($clean_title, $count, $videoId) {
        global $global;

        $sql = "SELECT * FROM videos WHERE clean_title = '{$clean_title}' ";
        if (!empty($videoId)) {
            $sql .= " AND id != {$videoId} ";
        }
        $sql .= " LIMIT 1";
        $res = $global['mysqli']->query($sql);

        if ($res && !empty($res->num_rows)) {
            return self::fixCleanTitle($clean_title . $count, $count + 1, $videoId);
        }
        return $clean_title;
    }

    static function getRandom($excludeVideoId = false) {
        return static::getVideo("", "viewableNotAd", false, $excludeVideoId);
    }

    static function getVideoQueryFileter() {
        global $global;
        $sql = "";
        if (!empty($_GET['playlist_id'])) {
            require_once $global['systemRootPath'] . 'objects/playlist.php';
            $ids = PlayList::getVideosIdFromPlaylist($_GET['playlist_id']);
            if (!empty($ids)) {
                $sql .= " AND v.id IN (" . implode(",", $ids) . ") ";
            }
        }
        return $sql;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getExistingVideoFile() {
        global $global;
        $file = $global['systemRootPath'] . "videos/original_" . $this->getFilename();
        if (!file_exists($file)) {
            $file = $global['systemRootPath'] . "videos/" . $this->getFilename() . ".mp4";
            if (!file_exists($file)) {
                $file = $global['systemRootPath'] . "videos/" . $this->getFilename() . ".webm";
                if (!file_exists($file)) {
                    $file = false;
                }
            }
        }
        return $file;
    }

    function getYoutubeId() {
        return $this->youtubeId;
    }

    function setYoutubeId($youtubeId) {
        $this->youtubeId = $youtubeId;
    }
    
    function setTitle($title) {
        $this->title = trim($title);
    }


}
