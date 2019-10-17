<?php

global $global, $config, $videosPaths;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/include_config.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
require_once $global['systemRootPath'] . 'objects/sites.php';
require_once $global['systemRootPath'] . 'objects/Object.php';
if (!class_exists('Video')) {

    class Video {

        private $id;
        private $title;
        private $clean_title;
        private $filename;
        private $description;
        private $views_count;
        private $status;
        private $duration;
        private $users_id;
        private $categories_id;
        private $old_categories_id;
        private $type;
        private $rotation;
        private $zoom;
        private $videoDownloadedLink;
        private $videoLink;
        private $next_videos_id;
        private $isSuggested;
        static $types = array('webm', 'mp4', 'mp3', 'ogg', 'pdf');
        private $videoGroups;
        private $trailer1;
        private $trailer2;
        private $trailer3;
        private $rate;
        private $can_download;
        private $can_share;
        private $only_for_paid;
        private $rrating;
        private $externalOptions;
        private $sites_id;
        private $serie_playlists_id;
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
            'ximg' => 'get image error',
            't' => 'transfering');
        static $rratingOptions = array('', 'g', 'pg', 'pg-13', 'r', 'nc-17', 'ma');
//ver 3.4
        private $youtubeId;
        static $typeOptions = array('audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie');

        function __construct($title = "", $filename = "", $id = 0) {
            global $global;
            $this->rotation = 0;
            $this->zoom = 1;
            if (!empty($id)) {
                $this->load($id);
            }
            if (!empty($title)) {
                $this->setTitle($title);
            }
            if (!empty($filename)) {
                $this->filename = $filename;
            }
        }

        function addView($currentTime = 0) {
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $sql = "UPDATE videos SET views_count = views_count+1, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", array($this->id));

            if ($insert_row) {
                $obj = new stdClass();
                $obj->videos_statistics_id = VideoStatistic::create($this->id, $currentTime);
                $obj->videos_id = $this->id;
                $this->views_count++;
                YouPHPTubePlugin::addView($this->id, $this->views_count);
                return $obj;
            } else {
                die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }

        function addViewPercent($percent = 25) {
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $sql = "UPDATE videos SET views_count_{$percent} = IFNULL(views_count_{$percent}, 0)+1, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", array($this->id));

            if ($insert_row) {
                return true;
            } else {
                die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }

        // allow users to count a view again in case it is refreshed
        static function unsetAddView($videos_id) {
            // allow users to count a view again in case it is refreshed
            if (!empty($_SESSION['addViewCount'][$videos_id]['time']) && $_SESSION['addViewCount'][$videos_id]['time'] <= time()) {
                $close = false;
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                    $close = true;
                }
                unset($_SESSION['addViewCount'][$videos_id]);
                if (!empty($close)) {
                    session_write_close();
                }
            }
        }

        function load($id) {
            $video = self::getVideoLight($id);
            if (empty($video))
                return false;
            foreach ($video as $key => $value) {
                $this->$key = $value;
            }
        }

        function setUsers_id($users_id) {
            $this->users_id = $users_id;
        }

        function getSites_id() {
            return $this->sites_id;
        }

        function setSites_id($sites_id) {
            $this->sites_id = $sites_id;
        }

        function save($updateVideoGroups = false, $allowOfflineUser = false) {
            global $advancedCustom;
            global $global;
            if (!User::isLogged() && !$allowOfflineUser) {
                header('Content-Type: application/json');
                die('{"error":"' . __("Permission denied") . '"}');
            }
            if (empty($this->title)) {
                $this->title = uniqid();
            }

            $this->clean_title = substr($this->clean_title, 0, 187);

            if (empty($this->clean_title)) {
                $this->setClean_title($this->title);
            }
            $this->clean_title = self::fixCleanTitle($this->clean_title, 1, $this->id);

            if (empty($this->status)) {
                $this->status = 'e';
            }

            if (empty($this->type) || !in_array($this->type, self::$typeOptions)) {
                $this->type = 'video';
            }

            if (empty($this->isSuggested)) {
                $this->isSuggested = 0;
            } else {
                $this->isSuggested = 1;
            }

            if (empty($this->categories_id)) {
                $p = YouPHPTubePlugin::loadPluginIfEnabled("PredefinedCategory");
                $category = Category::getCategoryDefault();
                $categories_id = $category['id'];
                if (empty($categories_id)) {
                    $categories_id = 'NULL';
                }
                if ($p) {
                    $this->categories_id = $p->getCategoryId();
                } else {
                    $this->categories_id = $categories_id;
                }
                if (empty($this->categories_id)) {
                    $this->categories_id = $categories_id;
                }
            }
// check if category exists
            $cat = new Category($this->categories_id);
            if (empty($cat->getName())) {
                $catDefault = Category::getCategoryDefault();
                $this->categories_id = $catDefault['id'];
            }
            $this->setTitle($global['mysqli']->real_escape_string(trim($this->title)));
            $this->description = ($global['mysqli']->real_escape_string($this->description));

            if (forbiddenWords($this->title) || forbiddenWords($this->description)) {
                return false;
            }

            if (empty($this->users_id)) {
                $this->users_id = User::getId();
            }

            $this->next_videos_id = intval($this->next_videos_id);
            if (empty($this->next_videos_id)) {
                $this->next_videos_id = 'NULL';
            }

            $this->sites_id = intval($this->sites_id);
            if (empty($this->sites_id)) {
                $this->sites_id = 'NULL';
            }

            $this->serie_playlists_id = intval($this->serie_playlists_id);
            if (empty($this->serie_playlists_id)) {
                $this->serie_playlists_id = 'NULL';
            }

            $this->can_download = intval($this->can_download);
            $this->can_share = intval($this->can_share);
            $this->only_for_paid = intval($this->only_for_paid);

            $this->rate = floatval($this->rate);

            if (!filter_var($this->videoLink, FILTER_VALIDATE_URL)) {
                $this->videoLink = '';
                if ($this->type == 'embed') {
                    $this->type = 'video';
                }
            }

            if (!empty($this->id)) {
                if (!$this->userCanManageVideo() && !$allowOfflineUser) {
                    header('Content-Type: application/json');
                    die('{"error":"' . __("Permission denied") . '"}');
                }
                $sql = "UPDATE videos SET title = '{$this->title}',clean_title = '{$this->clean_title}',"
                        . " filename = '{$this->filename}', categories_id = '{$this->categories_id}', status = '{$this->status}',"
                        . " description = '{$this->description}', duration = '{$this->duration}', type = '{$this->type}', videoDownloadedLink = '{$this->videoDownloadedLink}', youtubeId = '{$this->youtubeId}', videoLink = '{$this->videoLink}', next_videos_id = {$this->next_videos_id}, isSuggested = {$this->isSuggested}, users_id = {$this->users_id}, "
                        . " trailer1 = '{$this->trailer1}', trailer2 = '{$this->trailer2}', trailer3 = '{$this->trailer3}', rate = '{$this->rate}', can_download = '{$this->can_download}', can_share = '{$this->can_share}', only_for_paid = '{$this->only_for_paid}', rrating = '{$this->rrating}', externalOptions = '{$this->externalOptions}', sites_id = {$this->sites_id}, serie_playlists_id = {$this->serie_playlists_id} , modified = now()"
                        . " WHERE id = {$this->id}";
            } else {
                $sql = "INSERT INTO videos "
                        . "(title,clean_title, filename, users_id, categories_id, status, description, duration,type,videoDownloadedLink, next_videos_id, created, modified, videoLink, can_download, can_share, only_for_paid, rrating, externalOptions, sites_id, serie_playlists_id) values "
                        . "('{$this->title}','{$this->clean_title}', '{$this->filename}', {$this->users_id},{$this->categories_id}, '{$this->status}', '{$this->description}', '{$this->duration}', '{$this->type}', '{$this->videoDownloadedLink}', {$this->next_videos_id},now(), now(), '{$this->videoLink}', '{$this->can_download}', '{$this->can_share}','{$this->only_for_paid}', '{$this->rrating}', '$this->externalOptions', {$this->sites_id}, {$this->serie_playlists_id})";
            }
            $insert_row = sqlDAL::writeSql($sql);
            if ($insert_row) {
                Category::clearCacheCount();
                if (empty($this->id)) {
                    $id = $global['mysqli']->insert_id;
                    $this->id = $id;

// check if needs to add the video in a user group
                    $p = YouPHPTubePlugin::loadPluginIfEnabled("PredefinedCategory");
                    if ($p) {
                        $updateVideoGroups = true;
                        $this->videoGroups = $p->getUserGroupsArray();
                    }
                } else {
                    $id = $this->id;
                }
                self::deleteTagsAsync($this->id);
                if ($updateVideoGroups) {
                    require_once $global['systemRootPath'] . 'objects/userGroups.php';
// update the user groups
                    UserGroups::updateVideoGroups($id, $this->videoGroups);
                }
                Video::autosetCategoryType($id);
                if (!empty($this->old_categories_id)) {
                    Video::autosetCategoryType($this->old_categories_id);
                }
                clearVideosURL($this->filename);
                return $id;
            } else {
                error_log($sql . ' Save Video Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error . " $sql");
                return false;
            }
        }

        static function autosetCategoryType($catId) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5.01')) {
                return false;
            }
            $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";

            $res = sqlDAL::readSql($sql, "i", array($catId));
            $catTypeCache = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);

            $videoFound = false;
            $audioFound = false;
            if ($catTypeCache) {
// 3 means auto
                if ($catTypeCache['manualSet'] == "0") {
// start incremental search and save
                    $sql = "SELECT * FROM `videos` WHERE categories_id = ?";
                    $res = sqlDAL::readSql($sql, "i", array($catId));
                    $fullResult = sqlDAL::fetchAllAssoc($res);
                    sqlDAL::close($res);
                    if ($res != false) {
                        foreach ($fullResult as $row) {

                            if ($row['type'] == "audio") {
// echo "found audio";
                                $audioFound = true;
                            } else if ($row['type'] == "video") {
//echo "found video";
                                $videoFound = true;
                            }
                        }
                    }

                    if (($videoFound == false) || ($audioFound == false)) {
                        $sql = "SELECT * FROM `categories` WHERE parentId = ?";
                        $res = sqlDAL::readSql($sql, "i", array($catId));
                        $fullResult = sqlDAL::fetchAllAssoc($res);
                        sqlDAL::close($res);
                        if ($res != false) {
//$tmpVid = $res->fetch_assoc();
                            foreach ($fullResult as $row) {
                                $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
                                $res = sqlDAL::readSql($sql, "i", array($row['parentId']));
                                $fullResult2 = sqlDAL::fetchAllAssoc($res);
                                sqlDAL::close($res);
                                foreach ($fullResult2 as $row) {
                                    if ($row['type'] == "audio") {
//  echo "found audio";
                                        $audioFound = true;
                                    } else if ($row['type'] == "video") {
//echo "found video";
                                        $videoFound = true;
                                    }
                                }
                            }
                        }
                    }
                    $sql = "UPDATE `category_type_cache` SET `type` = '";
                    if (($videoFound) && ($audioFound)) {
                        $sql .= "0";
                    } else if ($audioFound) {
                        $sql .= "1";
                    } else if ($videoFound) {
                        $sql .= "2";
                    } else {
                        $sql .= "0";
                    }
                    $sql .= "' WHERE `category_type_cache`.`categoryId` = ?;";
                    sqlDAL::writeSql($sql, "i", array($catId));
                }
            } else {
// start incremental search and save - and a lot of this redundant stuff in a method..
                $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
                $res = sqlDAL::readSql($sql, "i", array($catId));
                $fullResult2 = sqlDAL::fetchAllAssoc($res);
                sqlDAL::close($res);
                if ($res != false) {
                    foreach ($fullResult2 as $row) {
                        if ($row['type'] == "audio") {
                            $audioFound = true;
                        } else if ($row['type'] == "video") {
                            $videoFound = true;
                        }
                    }
                }
                if (($videoFound == false) || ($audioFound == false)) {
                    $sql = "SELECT parentId FROM `categories` WHERE parentId = ?;";
                    $res = sqlDAL::readSql($sql, "i", array($catId));
                    $fullResult2 = sqlDAL::fetchAllAssoc($res);
                    sqlDAL::close($res);
                    if ($res != false) {
                        foreach ($fullResult2 as $cat) {
                            $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
                            $res = sqlDAL::readSql($sql, "i", array($cat['parentId']));
                            $fullResult2 = sqlDAL::fetchAllAssoc($res);
                            sqlDAL::close($res);
                            if ($res != false) {
                                foreach ($fullResult2 as $row) {
                                    if ($row['type'] == "audio") {
                                        $audioFound = true;
                                    } else if ($row['type'] == "video") {
                                        $videoFound = true;
                                    }
                                }
                            }
                        }
                    }
                }
                $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";
                $res = sqlDAL::readSql($sql, "i", array($catId));
                $exist = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                $sqlType = 99;
                if (($videoFound) && ($audioFound)) {
                    $sqlType = 0;
                } else if ($audioFound) {
                    $sqlType = 1;
                } else if ($videoFound) {
                    $sqlType = 2;
                }
                $values = array();
                if (empty($exist)) {
                    $sql = "INSERT INTO `category_type_cache` (`categoryId`, `type`) VALUES (?, ?);";
                    $values = array($catId, $sqlType);
                } else {
                    $sql = "UPDATE `category_type_cache` SET `type` = ? WHERE `category_type_cache`.`categoryId` = ?;";
                    $values = array($sqlType, $catId);
                }
                sqlDAL::writeSql($sql, "ii", $values);
            }
        }

// i would like to simplify the big part of the method above in this method, but won't work as i want.
        static function internalAutoset($catId, $videoFound, $audioFound) {
            global $config;
            if ($config->currentVersionLowerThen('5.01')) {
                return false;
            }
            $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
            $res = sqlDAL::readSql($sql, "i", array($catId));
            $fullResult2 = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if ($res != false) {
                foreach ($fullResult2 as $row) {
                    if ($row['type'] == "audio") {
                        $audioFound = true;
                    } else if ($row['type'] == "video") {
                        $videoFound = true;
                    }
                }
            }
            if (($videoFound == false) || ($audioFound == false)) {
                $sql = "SELECT parentId,categories_id FROM `categories` WHERE parentId = ?;";
                $res = sqlDAL::readSql($sql, "i", array($catId));
                $fullResult2 = sqlDAL::fetchAllAssoc($res);
                sqlDAL::close($res);
                if ($res != false) {
                    foreach ($fullResult2 as $cat) {
                        $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
                        $res = sqlDAL::readSql($sql, "i", array($cat['parentId']));
                        $fullResult = sqlDAL::fetchAllAssoc($res);
                        sqlDAL::close($res);
                        if ($res != false) {
                            foreach ($fullResult as $row) {
                                if ($row['type'] == "audio") {
                                    $audioFound = true;
                                } else if ($row['type'] == "video") {
                                    $videoFound = true;
                                }
                            }
                        }
                    }
                }
            }
            return array($videoFound, audioFound);
        }

        function setClean_title($clean_title) {
            $clean_title = preg_replace('/[^0-9a-z]+/', '-', trim(strtolower(cleanString($clean_title))));
            $this->clean_title = $clean_title;
        }

        function setDuration($duration) {
            $this->duration = $duration;
        }

        function getDuration() {
            return $this->duration;
        }

        function getIsSuggested() {
            return $this->isSuggested;
        }

        function setIsSuggested($isSuggested) {
            if (empty($isSuggested) || $isSuggested === "false") {
                $this->isSuggested = 0;
            } else {
                $this->isSuggested = 1;
            }
        }

        function setStatus($status) {
            if (!empty($this->id)) {
                global $global;
                $sql = "UPDATE videos SET status = ?, modified = now() WHERE id = ? ";
                $res = sqlDAL::writeSql($sql, 'si', array($status, $this->id));
                if ($global['mysqli']->errno != 0) {
                    die('Error on update Status: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
                self::deleteTagsAsync($this->id);
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
                $res = sqlDAL::writeSql($sql);
                if ($global['mysqli']->errno != 0) {
                    die('Error on update Rotation: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
            }
            $this->rotation = $saneRotation;
        }

        function getRotation() {
            return $this->rotation;
        }

        function getUsers_id() {
            return $this->users_id;
        }

        function setZoom($zoom) {
            $saneZoom = abs(floatval($zoom));

            if ($saneZoom < 0.1 || $saneZoom > 10) {
                die('Zoom level must be between 0.1 and 10');
            }

            if (!empty($this->id)) {
                global $global;
                $sql = "UPDATE videos SET zoom = '{$saneZoom}', modified = now() WHERE id = {$this->id} ";
                $res = sqlDAL::writeSql($sql);
                if ($global['mysqli']->errno != 0) {
                    die('Error on update Zoom: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
            }

            $this->zoom = $saneZoom;
        }

        function getZoom() {
            return $this->zoom;
        }

        static function getUserGroupsCanSeeSQL() {
            global $global;

            if (User::isAdmin()) {
                return "";
            }
            $sql = " (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0 ";
            if (User::isLogged()) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                $userGroups = UserGroups::getUserGroups(User::getId());
                $groups_id = array();
                foreach ($userGroups as $value) {
                    $groups_id[] = $value['id'];
                }
                if (!empty($groups_id)) {
                    $sql = " (({$sql}) OR ((SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id AND users_groups_id IN ('" . implode("','", $groups_id) . "') ) > 0)) ";
                }
            }
            return " AND " . $sql;
        }

        static function getVideo($id = "", $status = "viewable", $ignoreGroup = false, $random = false, $suggetedOnly = false, $showUnlisted = false, $ignoreTags = false, $activeUsersOnly = true) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            $id = intval($id);
            if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                }
            }
            $sql = "SELECT u.*, v.*, "
                    . " nv.title as next_title,"
                    . " nv.clean_title as next_clean_title,"
                    . " nv.filename as next_filename,"
                    . " nv.id as next_id,"
                    . " c.id as category_id,c.iconClass,c.name as category,c.iconClass,  c.clean_name as clean_category,c.description as category_description,c.nextVideoOrder as category_order, v.created as videoCreation, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes ";
            if (User::isLogged()) {
                $sql .= ", (SELECT `like` FROM likes as l where l.videos_id = v.id AND users_id = '" . User::getId() . "' ) as myVote ";
            } else {
                $sql .= ", 0 as myVote ";
            }
            $sql .= " FROM videos as v "
                    . "LEFT JOIN categories c ON categories_id = c.id "
                    . "LEFT JOIN users u ON v.users_id = u.id "
                    . "LEFT JOIN videos nv ON v.next_videos_id = nv.id "
                    . " WHERE 1=1 ";
            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }

            if (!empty($id)) {
                $sql .= " AND v.id = '$id' ";
            }
            $sql .= YouPHPTubePlugin::getVideoWhereClause();
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL();
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } else if ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }

            if (!empty($videosArrayId) && is_array($videosArrayId)) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }
            if ($status == "viewable") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_GET['catName'])) {
                $sql .= " AND (c.clean_name = '{$_GET['catName']}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$_GET['catName']}' ))";
            }

            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                if (!empty($user['id'])) {
                    $sql .= " AND v.users_id = '{$user['id']}' ";
                }
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_POST['searchPhrase'])) {
                if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost(array('v.title', 'v.description', 'c.name', 'c.description'), "OR");
                    $sql .= ")";
                } else {
                    $sql .= BootGrid::getSqlSearchFromPost(array('v.title', 'v.description', 'c.name', 'c.description'));
                }
            }
            if (!$ignoreGroup) {
                $arrayNotIN = YouPHPTubePlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (empty($id)) {
                if (empty($random) && !empty($_GET['videoName'])) {
                    $sql .= " AND v.clean_title = '{$_GET['videoName']}' ";
                } elseif (!empty($random)) {
                    $sql .= " AND v.id != {$random} ";
                    $sql .= " ORDER BY RAND() ";
                } else if ($suggetedOnly && empty($_GET['videoName']) && empty($_GET['search']) && empty($_GET['searchPhrase'])) {
                    $sql .= " AND v.isSuggested = 1 ";
                    $sql .= " ORDER BY RAND() ";
                } else {
                    $sql .= " ORDER BY v.Created DESC ";
                }
            }
            $sql .= " LIMIT 1";
//echo $sql;exit;
            $res = sqlDAL::readSql($sql);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res != false) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                if (!empty($video)) {
                    $video['category'] = xss_esc_back($video['category']);
                    $video['groups'] = UserGroups::getVideoGroups($video['id']);
                    $video['title'] = UTF8encode($video['title']);
                    $video['description'] = UTF8encode($video['description']);
                    $video['progress'] = self::getVideoPogressPercent($video['id']);
                    if (!$ignoreTags) {
                        $video['tags'] = self::getTags($video['id']);
                    }
                    if (!empty($video['externalOptions'])) {
                        $video['externalOptions'] = json_decode($video['externalOptions']);
                    } else {
                        $video['externalOptions'] = new stdClass();
                    }

                    if (!$ignoreTags && YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                        $video['videoTags'] = Tags::getAllFromVideosId($video['id']);
                        $video['videoTagsObject'] = Tags::getObjectFromVideosId($video['id']);
                    }
                    unset($video['password']);
                    unset($video['recoverPass']);
                }
            } else {
                $video = false;
            }
            return $video;
        }

        static function getVideoLight($id) {
            global $global, $config;
            $id = intval($id);
            $sql = "SELECT * FROM videos WHERE id = '$id' LIMIT 1";
            $res = sqlDAL::readSql($sql, "", array(), true);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $video;
        }

        static function getVideoFromFileName($fileName, $ignoreGroup = false, $ignoreTags = false) {
            global $global;
            if (empty($fileName)) {
                return false;
            }
            $sql = "SELECT id FROM videos WHERE filename = ? LIMIT 1";

            $res = sqlDAL::readSql($sql, "s", array($fileName));
            if ($res != false) {
                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if (!empty($video['id'])) {
                    return self::getVideo($video['id'], "", $ignoreGroup, false, false, true, $ignoreTags);
                }
            }
            return false;
        }

        static function getVideoFromFileNameLight($fileName) {
            global $global;
            if (empty($fileName)) {
                return false;
            }
            $sql = "SELECT * FROM videos WHERE filename = ? LIMIT 1";

            $res = sqlDAL::readSql($sql, "s", array($fileName));
            if ($res != false) {
                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                return $video;
            }
            return false;
        }

        static function getVideoFromCleanTitle($clean_title) {
// for some reason in some servers (CPanel) we got the error "Error while sending QUERY packet centos on a select"
// even increasing the max_allowed_packet it only goes away when close and reopen the connection
            global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
            $global['mysqli']->close();
            $global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);
            if (!empty($global['mysqli_charset'])) {
                $global['mysqli']->set_charset($global['mysqli_charset']);
            }
            $sql = "SELECT id  FROM videos  WHERE clean_title = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", array($clean_title));
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res) {
                return self::getVideo($video['id'], "", true, false, false, true);
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
        static function getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }

            if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                }
            }
            $status = str_replace("'", "", $status);

            $sql = "SELECT u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, v.modified as videoModified, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes "
                    . " FROM videos as v "
                    . " LEFT JOIN categories c ON categories_id = c.id "
                    . " LEFT JOIN users u ON v.users_id = u.id "
                    . " WHERE 1=1 ";

            if ($showOnlyLoggedUserVideos === true && !User::isAdmin()) {
                $uid = intval(User::getId());
                $sql .= " AND v.users_id = '{$uid}'";
            } elseif (!empty($showOnlyLoggedUserVideos)) {
                $uid = intval($showOnlyLoggedUserVideos);
                $sql .= " AND v.users_id = '{$uid}'";
            } else if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $uid = intval($user['id']);
                $sql .= " AND v.users_id = '{$uid}' ";
            }
            if (!empty($videosArrayId) && is_array($videosArrayId)) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }

            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }

            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $arrayNotIN = YouPHPTubePlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL();
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } else if ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }

            if ($status == "viewable") {
                if (User::isLogged()) {
                    $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND v.users_id ='" . User::getId() . "'))";
                } else {
                    $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
                }
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_GET['catName'])) {
                $sql .= " AND (c.clean_name = '{$_GET['catName']}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$_GET['catName']}' ))";
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_GET['modified'])) {
                $_GET['modified'] = str_replace("'", "", $_GET['modified']);
                $sql .= " AND v.modified >= '{$_GET['modified']}'";
            }

            if (!empty($_POST['searchPhrase'])) {
                if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost(array('v.title', 'v.description', 'c.name', 'c.description'), "OR");
                    $sql .= ")";
                } else {
                    $sql .= BootGrid::getSqlSearchFromPost(array('v.title', 'v.description', 'c.name', 'c.description'));
                }
            }

            $sql .= YouPHPTubePlugin::getVideoWhereClause();
            if (!isset($_POST['sort']['trending']) && !isset($_GET['sort']['trending'])) {
                $sql .= BootGrid::getSqlFromPost(array(), empty($_POST['sort']['likes']) ? "v." : "", "", true);
            } else {
                unset($_POST['sort']['trending']);
                unset($_GET['sort']['trending']);
                $_POST['sort']['created'] = 'DESC';
                $rowCount = $_POST['rowCount'];
                $_POST['rowCount'] *= 2; // double it to make it random
                $rows = self::getAllVideosLight($status, $showOnlyLoggedUserVideos, $showUnlisted);
                $_POST['rowCount'] = $rowCount;
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = $row['id'];
                }
                if (!empty($ids)) {
                    $sql .= " AND v.id IN (" . implode(",", $ids) . ") ORDER BY RAND() LIMIT {$rowCount}";
                }
            }

            if (!empty($_GET['limitOnceToOne'])) {
                $sql .= " LIMIT 1";
                unset($_GET['limitOnceToOne']);
            }

//echo $sql;exit;
//error_log("getAllVideos($status, $showOnlyLoggedUserVideos , $ignoreGroup , ". json_encode($videosArrayId).")" . $sql);
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $videos = array();
            if ($res != false) {
                require_once 'userGroups.php';
                foreach ($fullData as $row) {
                    unset($row['password']);
                    unset($row['recoverPass']);
                    if ($getStatistcs) {
                        $previewsMonth = date("Y-m-d 00:00:00", strtotime("-30 days"));
                        $previewsWeek = date("Y-m-d 00:00:00", strtotime("-7 days"));
                        $today = date('Y-m-d 23:59:59');
                        $row['statistc_all'] = VideoStatistic::getStatisticTotalViews($row['id']);
                        $row['statistc_today'] = VideoStatistic::getStatisticTotalViews($row['id'], false, date('Y-m-d 00:00:00'), $today);
                        $row['statistc_week'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsWeek, $today);
                        $row['statistc_month'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsMonth, $today);
                        $row['statistc_unique_user'] = VideoStatistic::getStatisticTotalViews($row['id'], true);
                    }
                    $row['progress'] = self::getVideoPogressPercent($row['id']);
                    $row['category'] = xss_esc_back($row['category']);
                    $row['groups'] = UserGroups::getVideoGroups($row['id']);
                    $row['tags'] = self::getTags($row['id']);
                    $row['title'] = UTF8encode($row['title']);
                    $row['description'] = UTF8encode($row['description']);
                    $row = array_merge($row, YouPHPTubePlugin::getAllVideosArray($row['id']));
                    $videos[] = $row;
                }
//$videos = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $videos;
        }

        static function getAllVideosAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true) {
            global $global, $advancedCustom;
            $return = array();
            $users_id = User::getId();
            $get = json_encode(@$_GET);
            $post = json_encode(@$_POST);
            $md5 = md5("{$users_id}{$get}{$post}{$status}{$showOnlyLoggedUserVideos}{$ignoreGroup}" . implode("_", $videosArrayId) . "{$getStatistcs}{$showUnlisted}{$activeUsersOnly}");
            $path = $global['systemRootPath'] . "videos/cache/getAllVideosAsync/";
            make_path($path);
            $cacheFileName = "{$path}{$md5}";
            if (empty($advancedCustom->AsyncJobs) || !file_exists($cacheFileName) || filesize($cacheFileName) === 0) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getAllVideosAsync.php' '$status' '$showOnlyLoggedUserVideos' '$ignoreGroup' '" . json_encode($videosArrayId) . "' '$getStatistcs' '$showUnlisted' '$activeUsersOnly' '{$get}' '{$post}' '{$cacheFileName}'");
                error_log("getAllVideosAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return object_to_array($return);
        }

        /**
         * Same as getAllVideos() method but a lighter query
         * @global type $global
         * @global type $config
         * @param type $showOnlyLoggedUserVideos
         * @return boolean
         */
        static function getAllVideosLight($status = "viewable", $showOnlyLoggedUserVideos = false, $showUnlisted = false) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            $sql = "SELECT v.* "
                    . " FROM videos as v "
                    . " WHERE 1=1 ";

            if ($showOnlyLoggedUserVideos === true && !User::isAdmin()) {
                $sql .= " AND v.users_id = '" . User::getId() . "'";
            } elseif (!empty($showOnlyLoggedUserVideos)) {
                $sql .= " AND v.users_id = '{$showOnlyLoggedUserVideos}'";
            }
            if ($status == "viewable") {
                if (User::isLogged()) {
                    $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND v.users_id ='" . User::getId() . "'))";
                } else {
                    $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
                }
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }


            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $sql .= " AND v.users_id = '{$user['id']}' ";
            }

            $sql .= YouPHPTubePlugin::getVideoWhereClause();

            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $videos = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    $videos[] = $row;
                }
//$videos = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $videos;
        }

        static function getTotalVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $showUnlisted = false, $activeUsersOnly = true) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            $cn = "";
            if (!empty($_GET['catName'])) {
                $cn .= ", c.clean_name as cn";
            }

            $sql = "SELECT v.users_id, v.type, v.id, v.title,v.description, c.name as category {$cn} "
                    . "FROM videos v "
                    . "LEFT JOIN categories c ON categories_id = c.id "
                    . " LEFT JOIN users u ON v.users_id = u.id "
                    . " WHERE 1=1 ";

            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL();
            }
            if ($status == "viewable") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }
            if ($showOnlyLoggedUserVideos === true && !User::isAdmin()) {
                $sql .= " AND v.users_id = '" . User::getId() . "'";
            } elseif (is_int($showOnlyLoggedUserVideos)) {
                $sql .= " AND v.users_id = '{$showOnlyLoggedUserVideos}'";
            }
            if (!empty($_GET['catName'])) {
                $sql .= " AND c.clean_name = '{$_GET['catName']}'";
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } else if ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }
            if (!$ignoreGroup) {
                $arrayNotIN = YouPHPTubePlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $uid = intval($user['id']);
                $sql .= " AND v.users_id = '{$uid}' ";
            }

            $sql .= YouPHPTubePlugin::getVideoWhereClause();

            $sql .= BootGrid::getSqlSearchFromPost(array('v.title', 'v.description', 'c.name'));

            $res = sqlDAL::readSql($sql);
            $numRows = sqlDal::num_rows($res);
            sqlDAL::close($res);

            return $numRows;
        }

        static function getTotalVideosInfo($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array()) {
            $obj = new stdClass();
            $obj->likes = 0;
            $obj->disLikes = 0;
            $obj->views_count = 0;
            $obj->total_minutes = 0;

            $videos = static::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId);

            foreach ($videos as $value) {
                $obj->likes += intval($value['likes']);
                $obj->disLikes += intval($value['dislikes']);
                $obj->views_count += intval($value['views_count']);
                $obj->total_minutes += intval(parseDurationToSeconds($value['duration']) / 60);
            }

            return $obj;
        }

        static function getTotalVideosInfoAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false) {
            global $global, $advancedCustom;
            $path = $global['systemRootPath'] . "videos/cache/getTotalVideosInfo/";
            make_path($path);
            $cacheFileName = "{$path}_{$status}_{$showOnlyLoggedUserVideos}_{$ignoreGroup}_" . implode($videosArrayId) . "_{$getStatistcs}";
            $return = array();
            if (empty($advancedCustom->AsyncJobs) || !file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getTotalVideosInfo($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTotalVideosInfoAsync.php' "
                        . " '$status' '$showOnlyLoggedUserVideos' '$ignoreGroup', '" . json_encode($videosArrayId) . "', "
                        . " '$getStatistcs', '$cacheFileName'");
                //error_log("getTotalVideosInfoAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        static function getViewableStatus($showUnlisted = false) {
            /**
              a = active
              i = inactive
              e = encoding
              x = encoding error
              d = downloading
              u = unlisted
              xmp4 = encoding mp4 error
              xwebm = encoding webm error
              xmp3 = encoding mp3 error
              xogg = encoding ogg error
             */
            $viewable = array('a', 'xmp4', 'xwebm', 'xmp3', 'xogg');
            if ($showUnlisted) {
                $viewable[] = "u";
            } else if (!empty($_GET['videoName'])) {
                $post = $_POST;
                if (self::isOwnerFromCleanTitle($_GET['videoName']) || User::isAdmin()) {
                    $viewable[] = "u";
                }
                $_POST = $post;
            }
            return $viewable;
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
                $content = @url_get_contents($progressFilename);
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

        function delete($allowOfflineUser = false) {
            if (!$allowOfflineUser && !$this->userCanManageVideo()) {
                return false;
            }

            global $global;
            if (!empty($this->id)) {
                $this->removeNextVideos($this->id);
                $this->removeTrailerReference($this->id);
                $this->removeCampaign($this->id);
                $video = self::getVideoLight($this->id);
                $sql = "DELETE FROM videos WHERE id = ?";
            } else {
                return false;
            }

            $resp = sqlDAL::writeSql($sql, "i", array($this->id));
            if ($resp == false) {
                die('Error (delete on video) : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            } else {
                $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
                $bb_b2 = YouPHPTubePlugin::loadPluginIfEnabled('Blackblaze_B2');
                $ftp = YouPHPTubePlugin::loadPluginIfEnabled('FTP_Storage');
                $YPTStorage = YouPHPTubePlugin::loadPluginIfEnabled('YPTStorage');
                if (!empty($aws_s3)) {
                    $aws_s3->removeFiles($video['filename']);
                }
                if (!empty($bb_b2)) {
                    $bb_b2->removeFiles($video['filename']);
                }
                if (!empty($ftp)) {
                    $ftp->removeFiles($video['filename']);
                }
                if (!empty($YPTStorage)) {
                    $YPTStorage->removeFiles($video['filename'], $video['sites_id']);
                }
                $this->removeFiles($video['filename']);
                self::deleteThumbs($video['filename']);
            }
            return $resp;
        }

        private function removeNextVideos($videos_id) {
            if (!$this->userCanManageVideo()) {
                return false;
            }

            global $global;

            if (!empty($videos_id)) {
                $sql = "UPDATE videos SET next_videos_id = NULL WHERE next_videos_id = ?";
                sqlDAL::writeSql($sql, "s", array($videos_id));
            } else {
                return false;
            }
            return true;
        }

        private function removeTrailerReference($videos_id) {
            if (!$this->userCanManageVideo()) {
                return false;
            }

            global $global;

            if (!empty($videos_id)) {
                $videoURL = self::getLink($videos_id, '', true);
                $sql = "UPDATE videos SET trailer1 = '' WHERE trailer1 = ?";
                sqlDAL::writeSql($sql, "s", array($videoURL));
                $sql = "UPDATE videos SET trailer2 = '' WHERE trailer2 = ?";
                sqlDAL::writeSql($sql, "s", array($videoURL));
                $sql = "UPDATE videos SET trailer3 = '' WHERE trailer3 = ?";
                sqlDAL::writeSql($sql, "s", array($videoURL));
            } else {
                return false;
            }
            return true;
        }

        private function removeCampaign($videos_id) {
            if (ObjectYPT::isTableInstalled('vast_campaigns_has_videos')) {
                if (!empty($this->id)) {
                    $sql = "DELETE FROM vast_campaigns_has_videos ";
                    $sql .= " WHERE videos_id = ?";
                    $global['lastQuery'] = $sql;
                    return sqlDAL::writeSql($sql, "i", array($videos_id));
                }
            }
            return false;
        }

        private function removeFiles($filename) {
            if (empty($filename)) {
                return false;
            }
            global $global;
            $file = "{$global['systemRootPath']}videos/original_{$filename}";
            $this->removeFilePath($file);

            $files = "{$global['systemRootPath']}videos/{$filename}";
            $this->removeFilePath($files);
        }

        private function removeFilePath($filePath) {
            if (empty($filePath)) {
                return false;
            }
// Streamlined for less coding space.
            $files = glob("{$filePath}*");
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if (is_dir($file)) {
                        self::rrmdir($file);
                    } else {
                        @unlink($file);
                    }
                }
            }
        }

        static private function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . "/" . $object))
                            self::rrmdir($dir . "/" . $object);
                        else
                            unlink($dir . "/" . $object);
                    }
                }
                rmdir($dir);
            }
        }

        function setDescription($description) {
            global $global, $advancedCustom;
            if (empty($advancedCustom->disableHTMLDescription)) {
                $articleObj = YouPHPTubePlugin::getObjectData('Articles');
                require_once $global['systemRootPath'] . 'objects/htmlpurifier/HTMLPurifier.auto.php';
                $configPuri = HTMLPurifier_Config::createDefault();
                $purifier = new HTMLPurifier($configPuri);
                if (empty($articleObj->allowAttributes)) {
                    $configPuri->set('HTML.AllowedAttributes', array('a.href', 'a.target', 'a.title', 'a.title', 'img.src', 'img.width', 'img.height')); // remove all attributes except a.href
                    $configPuri->set('Attr.AllowedFrameTargets', array('_blank'));
                }
                if (empty($articleObj->allowAttributes)) {
                    $configPuri->set('CSS.AllowedProperties', array()); // remove all CSS
                }
                $configPuri->set('AutoFormat.RemoveEmpty', true); // remove empty elements
                $pure = $purifier->purify($description);
                $parts = explode("<body>", $pure);
                if (!empty($parts[1])) {
                    $parts = explode("</body>", $parts[1]);
                }
                $this->description = $parts[0];
            } else {
                $this->description = strip_tags($description);
            }
            //var_dump($this->description, $description, $parts);exit;
        }

        function setCategories_id($categories_id) {
            if (!Category::userCanAddInCategory($categories_id)) {
                return false;
            }

// to update old cat as well when auto..
            if (!empty($this->categories_id)) {
                $this->old_categories_id = $this->categories_id;
            }
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
                $duration = $durationParts[0];
                $durationParts = explode(':', $duration);
                if (count($durationParts) == 1) {
                    return '0:00:' . static::addZero($durationParts[0]);
                } elseif (count($durationParts) == 2) {
                    return '0:' . static::addZero($durationParts[0]) . ':' . static::addZero($durationParts[1]);
                }
                return $duration;
            }
        }

        static private function addZero($str) {
            if (intval($str) < 10) {
                return '0' . intval($str);
            }
            return $str;
        }

        static function getItemPropDuration($duration = '') {
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            return 'PT' . intval($parts[0]) . 'H' . intval($parts[1]) . 'M' . intval($parts[2]) . 'S';
        }

        static function getItemDurationSeconds($duration = '') {
            if ($duration == "EE:EE:EE") {
                return 0;
            }
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            return intval($parts[0] * 60 * 60) + intval($parts[1] * 60) + intval($parts[2]);
        }

        static function getDurationFromFile($file) {
            global $global;
            require_once($global['systemRootPath'] . 'objects/getid3/getid3.php');
// get movie duration HOURS:MM:SS.MICROSECONDS
            if (!file_exists($file)) {
                error_log('{"status":"error", "msg":"getDurationFromFile ERROR, File (' . $file . ') Not Found"}');
                return "EE:EE:EE";
            }
// Initialize getID3 engine
            $getID3 = new getID3;
// Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($file);
            return static::getCleanDuration(@$ThisFileInfo['playtime_string']);
        }

        static function getHLSDurationFromFile($file) {
            $plugin = YouPHPTubePlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return 0;
            }
            return VideoHLS::getHLSDurationFromFile($file);
        }

        function updateHLSDurationIfNeed() {
            $plugin = YouPHPTubePlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return false;
            }
            return VideoHLS::updateHLSDurationIfNeed($this);
        }

        function updateDurationIfNeed($fileExtension = ".mp4") {
            global $global;
            $source = self::getSourceFile($this->filename, $fileExtension, true);
            $file = $source['path'];

            if (!empty($this->id) && $this->duration == "EE:EE:EE" && file_exists($file)) {
                $this->duration = Video::getDurationFromFile($file);
                error_log("Duration Updated: " . json_encode($this));

                $sql = "UPDATE videos SET duration = ?, modified = now() WHERE id = ?";
                $res = sqlDAL::writeSql($sql, "si", array($this->duration, $this->id));
                return $this->id;
            } else {
                error_log("Do not need update duration: " . json_encode($this));
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
                echo '{"status":"error", "msg":"isLandscape ERROR, File (' . $pathFileName . ') Not Found"}';
                return true;
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
            if (User::isAdmin()) {
                return true;
            }
            if (empty($this->users_id) || !User::canUpload()) {
                return false;
            }
// if you not admin you can only manager yours video
            if ($this->users_id != User::getId()) {
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
            global $advancedCustom;
            if (empty($advancedCustom->AsyncJobs)) {
                return self::getTags_($video_id, $type);
            } else {
                return self::getTagsAsync($video_id, $type);
            }
        }

        static function getTags_($video_id, $type = "") {
            global $advancedCustom;
            $video = new Video("", "", $video_id);
            $tags = array();

            if (empty($type) || $type === "paid") {
                if (!empty($advancedCustom->paidOnlyShowLabels)) {
                    $obj = new stdClass();
                    $obj->label = __("Paid Content");
                    if (!empty($video->getOnly_for_paid())) {
                        $obj->type = "warning";
                        $obj->text = $advancedCustom->paidOnlyLabel;
                    } else {
                        $obj->type = "success";
                        $obj->text = $advancedCustom->paidOnlyFreeLabel;
                    }
                    $tags[] = $obj;
                    $obj = new stdClass();
                }
            }

            /**
              a = active
              i = inactive
              e = encoding
              x = encoding error
              d = downloading
              u = unlisted
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
                    case 'u':
                        $obj->type = "info";
                        $obj->text = __("Unlisted");
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
                $obj = new stdClass();
            }

            if (empty($type) || $type === "userGroups") {
                $groups = UserGroups::getVideoGroups($video_id);
                $obj = new stdClass();
                $obj->label = __("Group");
                if (empty($groups)) {
                    $status = $video->getStatus();
                    if ($status == 'u') {
                        $obj->type = "info";
                        $obj->text = __("Unlisted");
                        $tags[] = $obj;
                        $obj = new stdClass();
                    } else {
//$obj->type = "success";
//$obj->text = __("Public");
                    }
                } else {
                    foreach ($groups as $value) {
                        $obj = new stdClass();
                        $obj->label = __("Group");
                        $obj->type = "warning";
                        $obj->text = "{$value['group_name']}";
                        $tags[] = $obj;
                        $obj = new stdClass();
                    }
                }
            }

            if (empty($type) || $type === "category") {
                require_once 'category.php';
                if (!empty($_POST['sort']['title'])) {
                    unset($_POST['sort']);
                }
                $category = Category::getCategory($video->getCategories_id());
                $obj = new stdClass();
                $obj->label = __("Category");
                if (!empty($category)) {
                    $obj->type = "default";
                    $obj->text = $category['name'];
                    $tags[] = $obj;
                    $obj = new stdClass();
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
                    $obj = new stdClass();
                } else {
                    $obj->type = "info";
                    $obj->text = __("Local File");
                    $tags[] = $obj;
                    $obj = new stdClass();
                }
            }

            $tags = array_merge($tags, YouPHPTubePlugin::getVideoTags($video_id));
            //var_dump($tags);
            return $tags;
        }

        static function deleteTagsAsync($video_id) {
            global $global;
            if (empty($video_id)) {
                return false;
            }
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            unset($_SESSION['getVideoTags'][$video_id]);
            $path = $global['systemRootPath'] . "videos/cache/getTagsAsync/";
            if (!is_dir($path)) {
                return false;
            }

            $cacheFileName = "{$path}_{$video_id}_";

            $files = glob("{$cacheFileName}*");
            foreach ($files as $file) {
                unlink($file);
            }
        }

        static function getTagsAsync($video_id, $type = "video") {
            global $global, $advancedCustom;
            $path = $global['systemRootPath'] . "videos/cache/getTagsAsync/";
            make_path($path);
            $cacheFileName = "{$path}_{$video_id}_{$type}";

            $return = array();
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getTags_($video_id, $type);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > 300) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTags.php' '$video_id' '$type' '{$cacheFileName}'");
                //error_log("getTags: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return (array) $return;
        }

        function getCategories_id() {
            return $this->categories_id;
        }

        function getType() {
            return $this->type;
        }

        static function fixCleanTitle($clean_title, $count, $videoId, $original_title = "") {
            global $global;

            if (empty($original_title)) {
                $original_title = $clean_title;
            }

            $sql = "SELECT * FROM videos WHERE clean_title = '{$clean_title}' ";
            if (!empty($videoId)) {
                $sql .= " AND id != {$videoId} ";
            }
            $sql .= " LIMIT 1";
            $res = sqlDAL::readSql($sql, "", array(), true);
            $cleanTitleExists = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($cleanTitleExists != false) {
                return self::fixCleanTitle($original_title . "-" . $count, $count + 1, $videoId, $original_title);
            }
            return $clean_title;
        }

        /**
         *
         * @global type $global
         * @param type $videos_id
         * @param type $users_id if is empty will use the logged user
         * @return boolean
         */
        static function isOwner($videos_id, $users_id = 0) {
            global $global;
            if (empty($users_id)) {
                $users_id = User::getId();
                if (empty($users_id)) {
                    return false;
                }
            }

            $video_owner = self::getOwner($videos_id);
            if ($video_owner) {
                if ($video_owner == $users_id) {
                    return true;
                }
            }
            return false;
        }

        static function isOwnerFromCleanTitle($clean_title, $users_id = 0) {
            global $global;
            $video = self::getVideoFromCleanTitle($clean_title);
            return self::isOwner($video['id'], $users_id);
        }

        /**
         *
         * @global type $global
         * @param type $videos_id
         * @param type $users_id if is empty will use the logged user
         * @return boolean
         */
        static function getOwner($videos_id) {
            global $global;
            $sql = "SELECT users_id FROM videos WHERE id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "i", array($videos_id));
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res) {
                if ($videoRow != false) {
                    return $videoRow['users_id'];
                }
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        /**
         *
         * @param type $videos_id
         * @param type $users_id if is empty will use the logged user
         * @return boolean
         */
        static function canEdit($videos_id, $users_id = 0) {
            if (empty($users_id)) {
                $users_id = User::getId();
                if (empty($users_id)) {
                    return false;
                }
            }
            $user = new User($users_id);
            if (empty($user)) {
                return false;
            }

            if ($user->getIsAdmin()) {
                return true;
            }

            return self::isOwner($videos_id, $users_id);
        }

        static function getRandom($excludeVideoId = false) {
            return static::getVideo("", "viewable", false, $excludeVideoId);
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
                        $videos = getVideosURL($this->getFilename());
                        foreach ($videos as $value) {
                            if ($value['type'] == 'video' && file_exists($value['path'])) {
                                return $value['path'];
                            }
                        }
                        $file = false;
                    }
                }
            }
            return $file;
        }

        function getTrailer1() {
            return $this->trailer1;
        }

        function getTrailer2() {
            return $this->trailer2;
        }

        function getTrailer3() {
            return $this->trailer3;
        }

        function getRate() {
            return $this->rate;
        }

        function setTrailer1($trailer1) {
            if (filter_var($trailer1, FILTER_VALIDATE_URL)) {
                $this->trailer1 = $trailer1;
            } else {
                $this->trailer1 = "";
            }
        }

        function setTrailer2($trailer2) {
            if (filter_var($trailer2, FILTER_VALIDATE_URL)) {
                $this->trailer2 = $trailer2;
            } else {
                $this->trailer2 = "";
            }
        }

        function setTrailer3($trailer3) {
            if (filter_var($trailer3, FILTER_VALIDATE_URL)) {
                $this->trailer3 = $trailer3;
            } else {
                $this->trailer3 = "";
            }
        }

        function setRate($rate) {
            $this->rate = floatval($rate);
        }

        function getYoutubeId() {
            return $this->youtubeId;
        }

        function setYoutubeId($youtubeId) {
            $this->youtubeId = $youtubeId;
        }

        function setTitle($title) {
            $this->title = strip_tags($title);
            if (strlen($this->title) > 190)
                $this->title = substr($this->title, 0, 187) . '...';
        }

        function setFilename($filename) {
            if (empty($this->filename)) {
                $this->filename = $filename;
            }
            return $this->filename;
        }

        function getNext_videos_id() {
            return $this->next_videos_id;
        }

        function setNext_videos_id($next_videos_id) {
            $this->next_videos_id = $next_videos_id;
        }

        function queue() {
            global $config;
            if (!User::canUpload()) {
                return false;
            }
            global $global;
            $obj = new stdClass();
            $obj->error = true;

            $target = $config->getEncoderURL() . "queue";
            $postFields = array(
                'user' => User::getUserName(),
                'pass' => User::getUserPass(),
                'fileURI' => $global['webSiteRootURL'] . "videos/original_{$this->getFilename()}",
                'filename' => $this->getFilename(),
                'videos_id' => $this->getId(),
                "notifyURL" => "{$global['webSiteRootURL']}"
            );

            if (YouPHPTubePlugin::isEnabledByName("VideoHLS")) {
                $postFields['inputHLS'] = 1;
            }

            error_log("SEND To QUEUE: " . print_r($postFields, true));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $target);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            $r = curl_exec($curl);
            $obj->response = $r;
            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);
//echo "cURL error ({$errno}):\n {$error_message}";
                $obj->msg = "cURL error ({$errno}):\n {$error_message}";
            } else {
                $obj->error = false;
            }
            error_log("QUEUE CURL: ($target) " . json_encode($obj));
            curl_close($curl);
            return $obj;
        }

        function getVideoLink() {
            return $this->videoLink;
        }

        function setVideoLink($videoLink) {
            $this->videoLink = $videoLink;
        }

        function getCan_download() {
            return $this->can_download;
        }

        function getCan_share() {
            return $this->can_share;
        }

        function setCan_download($can_download) {
            $this->can_download = (empty($can_download) || $can_download === "false") ? 0 : 1;
        }

        function setCan_share($can_share) {
            $this->can_share = (empty($can_share) || $can_share === "false") ? 0 : 1;
        }

        function getOnly_for_paid() {
            return $this->only_for_paid;
        }

        function setOnly_for_paid($only_for_paid) {
            $this->only_for_paid = (empty($only_for_paid) || $only_for_paid === "false") ? 0 : 1;
        }

        /**
         *
         * @param type $filename
         * @param type $type
         * @return type .jpg .gif .webp _thumbs.jpg _Low.mp4 _SD.mp4 _HD.mp4
         */
        static function getSourceFile($filename, $type = ".jpg", $includeS3 = false) {
            global $global, $advancedCustom, $videosPaths;

            // check if there is a webp image
            if ($type === '.gif') {
                $path = "{$global['systemRootPath']}videos/{$filename}.webp";
                if (file_exists($path)) {
                    $type = ".webp";
                }
            }

            if (empty($videosPaths[$filename][$type][intval($includeS3)])) {
                $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
                $bb_b2 = YouPHPTubePlugin::loadPluginIfEnabled('Blackblaze_B2');
                $ftp = YouPHPTubePlugin::loadPluginIfEnabled('FTP_Storage');
                if (!empty($aws_s3)) {
                    $aws_s3_obj = $aws_s3->getDataObject();
                    if (!empty($aws_s3_obj->useS3DirectLink)) {
                        $includeS3 = true;
                    }
                } else if (!empty($bb_b2)) {
                    $bb_b2_obj = $bb_b2->getDataObject();
                    if (!empty($bb_b2_obj->useDirectLink)) {
                        $includeS3 = true;
                    }
                } else if (!empty($ftp)) {
                    $includeS3 = true;
                }
                $token = "";
                $secure = YouPHPTubePlugin::loadPluginIfEnabled('SecureVideosDirectory');
                if (!empty($secure) && ($type == ".mp3" || $type == ".mp4" || $type == ".webm" || $type == ".m3u8" || $type == ".pdf")) {
                    $token = "?" . $secure->getToken($filename);
                }
                $source = array();
                $source['path'] = "{$global['systemRootPath']}videos/{$filename}{$type}";

                if ($type == ".m3u8") {
                    $source['path'] = "{$global['systemRootPath']}videos/{$filename}/index{$type}";
                }
                $video = Video::getVideoFromFileNameLight(str_replace(array('_Low', '_SD', '_HD'), array('', '', ''), $filename));
                $canUseCDN = canUseCDN($video['id']);

                if (!empty($video['sites_id']) && ($type == ".mp3" || $type == ".mp4" || $type == ".webm" || $type == ".m3u8" || $type == ".pdf")) {
                    $site = new Sites($video['sites_id']);
                    $siteURL = rtrim($site->getUrl(), '/') . '/';
                    $source['url'] = "{$siteURL}videos/{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$siteURL}videos/{$filename}/index{$type}{$token}";
                    }
                } else if (!empty($advancedCustom->videosCDN) && $canUseCDN) {
                    $advancedCustom->videosCDN = rtrim($advancedCustom->videosCDN, '/') . '/';
                    $source['url'] = "{$advancedCustom->videosCDN}videos/{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$advancedCustom->videosCDN}videos/{$filename}/index{$type}{$token}";
                    }
                } else {
                    $source['url'] = "{$global['webSiteRootURL']}videos/{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$global['webSiteRootURL']}videos/{$filename}/index{$type}{$token}";
                    }
                }
                /* need it because getDurationFromFile */
                if ($includeS3 && ($type == ".mp4" || $type == ".webm" || $type == ".mp3" || $type == ".ogg" || $type == ".pdf")) {
                    if (!file_exists($source['path']) || filesize($source['path']) < 1024) {
                        if (!empty($aws_s3)) {
                            $source = $aws_s3->getAddress("{$filename}{$type}");
                        } else if (!empty($bb_b2)) {
                            $source = $bb_b2->getAddress("{$filename}{$type}");
                        } else if (!empty($ftp)) {
                            $source = $ftp->getAddress("{$filename}{$type}");
                        }
                    }
                }
                if (!file_exists($source['path']) || ($type !== ".m3u8" && !is_dir($source['path']) && (filesize($source['path']) < 1000 && filesize($source['path']) != 10 ))) {
                    if ($type != "_thumbsV2.jpg" && $type != "_thumbsSmallV2.jpg" && $type != "_portrait_thumbsV2.jpg" && $type != "_portrait_thumbsSmallV2.jpg") {
                        return array('path' => false, 'url' => false);
                    }
                }

                $videosPaths[$filename][$type][intval($includeS3)] = $source;
            } else {
                $source = $videosPaths[$filename][$type][intval($includeS3)];
            }
//ObjectYPT::setCache($name, $source);
            return $source;
        }

        static function getVideosPaths($filename, $includeS3 = false) {
            $types = array('', '_Low', '_SD', '_HD');
            $videos = array();

            $plugin = YouPHPTubePlugin::loadPluginIfEnabled("VideoHLS");
            if (!empty($plugin)) {
                $videos = VideoHLS::getSourceFile($filename, $includeS3);
            }

            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".mp4", $includeS3);
                if (!empty($source['url'])) {
                    $videos['mp4'][str_replace("_", "", $value)] = $source['url'];
                }
            }

            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".webm", $includeS3);
                if (!empty($source['url'])) {
                    $videos['webm'][str_replace("_", "", $value)] = $source['url'];
                }
            }
            $source = self::getSourceFile($filename, ".pdf", $includeS3);
            if (!empty($source['url'])) {
                $videos['pdf'] = $source['url'];
            }
            $source = self::getSourceFile($filename, ".mp3", $includeS3);
            if (!empty($source['url'])) {
                $videos['mp3'] = $source['url'];
            }
            return $videos;
        }

        static function getStoragePath() {
            global $global;
            $path = "{$global['systemRootPath']}videos/";
            return $path;
        }

        static function getImageFromFilename($filename, $type = "video", $async = false) {
            global $advancedCustom;
            // I dont know why but I had to remove it to avoid ERR_RESPONSE_HEADERS_TOO_BIG
            header_remove('Set-Cookie');
            if (empty($advancedCustom->AsyncJobs) && !$async) {
                return self::getImageFromFilename_($filename, $type);
            } else {
                return self::getImageFromFilenameAsync($filename, $type);
            }
        }

        static function getPoster($videos_id) {
            $images = self::getImageFromID($videos_id);
            if (!empty($images->poster)) {
                return $images->poster;
            }
            if (!empty($images->posterPortrait)) {
                return $images->poster;
            }
            return false;
        }

        static function getImageFromFilename_($filename, $type = "video") {
            global $global, $advancedCustom;
            /*
              $name = "getImageFromFilename_{$filename}{$type}_";
              $cached = ObjectYPT::getCache($name, 86400);//one day
              if(!empty($cached)){
              return $cached;
              }
             *
             */
            $obj = new stdClass();
            $gifSource = self::getSourceFile($filename, ".gif");
            $gifPortraitSource = self::getSourceFile($filename, "_portrait.gif");
            $jpegSource = self::getSourceFile($filename, ".jpg");
            $jpegPortraitSource = self::getSourceFile($filename, "_portrait.jpg");
            $jpegPortraitThumbs = self::getSourceFile($filename, "_portrait_thumbsV2.jpg");
            $jpegPortraitThumbsSmall = self::getSourceFile($filename, "_portrait_thumbsSmallV2.jpg");
            $thumbsSource = self::getSourceFile($filename, "_thumbsV2.jpg");
            $thumbsSmallSource = self::getSourceFile($filename, "_thumbsSmallV2.jpg");
            $obj->poster = $jpegSource['url'];
            $obj->posterPortrait = $jpegPortraitSource['url'];
            $obj->posterPortraitPath = $jpegPortraitSource['path'];
            $obj->posterPortraitThumbs = $jpegPortraitThumbs['url'];
            $obj->posterPortraitThumbsSmall = $jpegPortraitThumbsSmall['url'];
            $obj->thumbsGif = $gifSource['url'];
            $obj->gifPortrait = $gifPortraitSource['url'];
            $obj->thumbsJpg = $thumbsSource['url'];
            $obj->thumbsJpgSmall = $thumbsSmallSource['url'];
            if (file_exists($gifSource['path'])) {
                $obj->thumbsGif = $gifSource['url'];
            }
            if (file_exists($jpegPortraitSource['path'])) {
// create thumbs
                if (!file_exists($jpegPortraitThumbs['path']) && filesize($jpegPortraitSource['path']) > 1024) {
                    error_log("Resize JPG {$jpegPortraitSource['path']}, {$jpegPortraitThumbs['path']}");
                    if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                        im_resizeV3($jpegPortraitSource['path'], $jpegPortraitThumbs['path'], 170, 250);
                    } else {
                        im_resizeV2($jpegPortraitSource['path'], $jpegPortraitThumbs['path'], 170, 250);
                    }
                }
// create thumbs
                if (!file_exists($jpegPortraitThumbsSmall['path']) && filesize($jpegPortraitSource['path']) > 1024) {
                    error_log("Resize JPG {$jpegPortraitSource['path']}, {$jpegPortraitThumbsSmall['path']}");
                    if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                        im_resizeV3($jpegPortraitSource['path'], $jpegPortraitThumbsSmall['path'], 170, 250);
                    } else {
                        im_resizeV2($jpegPortraitSource['path'], $jpegPortraitThumbsSmall['path'], 170, 250, 5);
                    }
                }
            } else {
                if ($type == "article") {
                    $obj->posterPortrait = "{$global['webSiteRootURL']}view/img/article_portrait.png";
                    $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/article_portrait.png";
                    $obj->posterPortraitThumbs = "{$global['webSiteRootURL']}view/img/article_portrait.png";
                    $obj->posterPortraitThumbsSmall = "{$global['webSiteRootURL']}view/img/article_portrait.png";
                } if ($type == "pdf") {
                    $obj->posterPortrait = "{$global['webSiteRootURL']}view/img/pdf_portrait.png";
                    $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/pdf_portrait.png";
                    $obj->posterPortraitThumbs = "{$global['webSiteRootURL']}view/img/pdf_portrait.png";
                    $obj->posterPortraitThumbsSmall = "{$global['webSiteRootURL']}view/img/pdf_portrait.png";
                } else {
                    $obj->posterPortrait = "{$global['webSiteRootURL']}view/img/notfound_portrait.jpg";
                    $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/notfound_portrait.png";
                    $obj->posterPortraitThumbs = "{$global['webSiteRootURL']}view/img/notfound_portrait.jpg";
                    $obj->posterPortraitThumbsSmall = "{$global['webSiteRootURL']}view/img/notfound_portrait.jpg";
                }
            }

            if (file_exists($jpegSource['path'])) {
                $obj->poster = $jpegSource['url'];
                $obj->thumbsJpg = $thumbsSource['url'];
// create thumbs
                if (!file_exists($thumbsSource['path']) && filesize($jpegSource['path']) > 1024) {
                    error_log("Resize JPG {$jpegSource['path']}, {$thumbsSource['path']}");
                    if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                        im_resizeV3($jpegSource['path'], $thumbsSource['path'], 250, 140);
                    } else {
                        im_resizeV2($jpegSource['path'], $thumbsSource['path'], 250, 140);
                    }
                }
// create thumbs
                if (!file_exists($thumbsSmallSource['path']) && filesize($jpegSource['path']) > 1024) {
                    error_log("Resize Small JPG {$jpegSource['path']}, {$thumbsSmallSource['path']}");
                    if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                        im_resizeV3($jpegSource['path'], $thumbsSmallSource['path'], 250, 140);
                    } else {
                        im_resizeV2($jpegSource['path'], $thumbsSmallSource['path'], 250, 140, 5);
                    }
                }
            } else {
                if ($type == "article") {
                    $obj->poster = "{$global['webSiteRootURL']}view/img/article.png";
                    $obj->thumbsJpg = "{$global['webSiteRootURL']}view/img/article.png";
                    $obj->thumbsJpgSmall = "{$global['webSiteRootURL']}view/img/article.png";
                } else if ($type == "pdf") {
                    $obj->poster = "{$global['webSiteRootURL']}view/img/pdf.png";
                    $obj->thumbsJpg = "{$global['webSiteRootURL']}view/img/pdf.png";
                    $obj->thumbsJpgSmall = "{$global['webSiteRootURL']}view/img/pdf.png";
                } else if (($type !== "audio") && ($type !== "linkAudio")) {
                    $obj->poster = "{$global['webSiteRootURL']}view/img/notfound.jpg";
                    $obj->thumbsJpg = "{$global['webSiteRootURL']}view/img/notfoundThumbs.jpg";
                    $obj->thumbsJpgSmall = "{$global['webSiteRootURL']}view/img/notfoundThumbsSmall.jpg";
                } else {
                    $obj->poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                    $obj->thumbsJpg = "{$global['webSiteRootURL']}view/img/audio_waveThumbs.jpg";
                    $obj->thumbsJpgSmall = "{$global['webSiteRootURL']}view/img/audio_waveThumbsSmall.jpg";
                }
            }

            if (empty($obj->thumbsJpg)) {
                $obj->thumbsJpg = $obj->poster;
            }
            if (empty($obj->thumbsJpgSmall)) {
                $obj->thumbsJpgSmall = $obj->poster;
            }
//ObjectYPT::setCache($name, $obj);
            if (!empty($advancedCustom->disableAnimatedGif)) {
                $obj->thumbsGif = false;
            }
            return $obj;
        }

        static function getImageFromFilenameAsync($filename, $type = "video") {
            global $global, $advancedCustom;
            $return = array();
            $path = $global['systemRootPath'] . "videos/cache/getImageFromFilenameAsync/";
            make_path($path);
            $cacheFileName = "{$path}_{$filename}_{$type}";
            if (empty($advancedCustom->AsyncJobs) || !file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getImageFromFilename_($filename, $type);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getImageFromFilenameAsync.php' '$filename' '$type' '{$cacheFileName}'");
                //error_log("getImageFromFilenameAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        static function getImageFromID($videos_id, $type = "video") {
            $video = new Video("", "", $videos_id);
            return self::getImageFromFilename($video->getFilename());
        }

        function getViews_count() {
            return intval($this->views_count);
        }

        static function get_clean_title($videos_id) {
            global $global;

            $sql = "SELECT * FROM videos WHERE id = ? LIMIT 1";

            $res = sqlDAL::readSql($sql, "i", array($videos_id));
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);

            if ($res != false) {
                if ($videoRow != false) {
                    return $videoRow['clean_title'];
                }
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        static function get_id_from_clean_title($clean_title) {
            global $global;

            $sql = "SELECT * FROM videos WHERE clean_title = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", array($clean_title));
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res != false) {
                if ($videoRow != false) {
                    return $videoRow['id'];
                }
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        /**
         *
         * @global type $global
         * @param type $videos_id
         * @param type $clean_title
         * @param type $embed
         * @param type $type URLFriendly or permalink
         * @return String a web link
         */
        static function getLinkToVideo($videos_id, $clean_title = "", $embed = false, $type = "URLFriendly", $get = array()) {
            global $global;
            $get_http = http_build_query($get);
            if (empty($get_http)) {
                $get_http = "";
            } else {
                $get_http = "?{$get_http}";
            }
            if ($type == "URLFriendly") {
                $cat = "";
                if (!empty($_GET['catName'])) {
                    $cat = "cat/{$_GET['catName']}/";
                }

                if (!empty($videos_id) && empty($clean_title)) {
                    $clean_title = self::get_clean_title($videos_id);
                }
                if ($embed) {
                    return "{$global['webSiteRootURL']}videoEmbed/{$clean_title}{$get_http}";
                } else {
                    return "{$global['webSiteRootURL']}{$cat}video/{$clean_title}{$get_http}";
                }
            } else {
                if (empty($videos_id) && !empty($clean_title)) {
                    $videos_id = self::get_id_from_clean_title($clean_title);
                }
                if ($embed) {
                    return "{$global['webSiteRootURL']}vEmbed/{$videos_id}{$get_http}";
                } else {
                    return "{$global['webSiteRootURL']}v/{$videos_id}{$get_http}";
                }
            }
        }

        static function getPermaLink($videos_id, $embed = false, $get = array()) {
            return self::getLinkToVideo($videos_id, "", $embed, "permalink", $get);
        }

        static function getURLFriendly($videos_id, $embed = false, $get = array()) {
            return self::getLinkToVideo($videos_id, "", $embed, "URLFriendly", $get);
        }

        static function getPermaLinkFromCleanTitle($clean_title, $embed = false, $get = array()) {
            return self::getLinkToVideo("", $clean_title, $embed, "permalink", $get);
        }

        static function getURLFriendlyFromCleanTitle($clean_title, $embed = false, $get = array()) {
            return self::getLinkToVideo("", $clean_title, $embed, "URLFriendly", $get);
        }

        static function getLink($videos_id, $clean_title, $embed = false, $get = array()) {
            global $advancedCustom;
            if (!empty($advancedCustom->usePermalinks)) {
                $type = "permalink";
            } else {
                $type = "URLFriendly";
            }

            return self::getLinkToVideo($videos_id, $clean_title, $embed, $type, $get);
        }

        static function getTotalVideosThumbsUpFromUser($users_id, $startDate, $endDate) {
            global $global;

            $sql = "SELECT id from videos  WHERE users_id = ?  ";

            $res = sqlDAL::readSql($sql, "i", array($users_id));
            $videoRows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);

            $r = array('thumbsUp' => 0, 'thumbsDown' => 0);

            if ($res != false) {
                foreach ($videoRows as $row) {
                    $values = array($row['id']);
                    $format = "i";
                    $sql = "SELECT id from likes WHERE videos_id = ? AND `like` = 1  ";
                    if (!empty($startDate)) {
                        $sql .= " AND `created` >= ? ";
                        $format .= "s";
                        $values[] = $startDate;
                    }

                    if (!empty($endDate)) {
                        $sql .= " AND `created` <= ? ";
                        $format .= "s";
                        $values[] = $endDate;
                    }
                    $res = sqlDAL::readSql($sql, $format, $values);
                    $countRow = sqlDAL::num_rows($res);
                    sqlDAL::close($res);
                    $r['thumbsUp'] += $countRow;

                    $format = "";
                    $values = array();
                    $sql = "SELECT id from likes WHERE videos_id = {$row['id']} AND `like` = -1  ";
                    if (!empty($startDate)) {
                        $sql .= " AND `created` >= ? ";
                        $format .= "s";
                        $values[] = $startDate;
                    }

                    if (!empty($endDate)) {
                        $sql .= " AND `created` <= ? ";
                        $format .= "s";
                        $values[] = $endDate;
                    }
                    $res = sqlDAL::readSql($sql, $format, $values);
                    $countRow = sqlDAL::num_rows($res);
                    sqlDAL::close($res);
                    $r['thumbsDown'] += $countRow;
                }
            }

            return $r;
        }

        static function deleteThumbs($filename) {
            if (empty($filename)) {
                return false;
            }
            global $global;
            $filePath = "{$global['systemRootPath']}videos/{$filename}";
// Streamlined for less coding space.
            $files = glob("{$filePath}*_thumbs*.jpg");
            foreach ($files as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            clearVideosURL($filename);
        }

        static function getVideoPogress($videos_id, $users_id = 0) {
            if (empty($users_id)) {
                if (!User::isLogged()) {
                    return 0;
                }
                $users_id = User::getId();
            }

            return VideoStatistic::getLastVideoTimeFromVideo($videos_id, $users_id);
        }

        static function getVideoPogressPercent($videos_id, $users_id = 0) {
            $lastVideoTime = self::getVideoPogress($videos_id, $users_id);

            if (empty($lastVideoTime)) {
                return array('percent' => 0, 'lastVideoTime' => 0);
            }

// start incremental search and save
            $sql = "SELECT duration FROM `videos` WHERE id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "i", array($videos_id));
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);

            if (empty($row) || empty($row['duration'])) {
                return array('percent' => 0, 'lastVideoTime' => 0);
            }

            $duration = parseDurationToSeconds($row['duration']);

            if (empty($duration)) {
                return array('percent' => 0, 'lastVideoTime' => 0);
            }

            if ($lastVideoTime > $duration) {
                return array('percent' => 100, 'lastVideoTime' => $lastVideoTime);
            }

            return array('percent' => ($lastVideoTime / $duration) * 100, 'lastVideoTime' => $lastVideoTime);
        }

        function getRrating() {
            return $this->rrating;
        }

        function setRrating($rrating) {
            $rrating = strtolower($rrating);
            if (!in_array($rrating, self::$rratingOptions)) {
                $rrating = '';
            }
            $this->rrating = $rrating;
        }

        static function getVideoType($filename) {
            $obj = new stdClass();
            $paths = self::getVideosPaths($filename);

            $obj->mp4 = !empty($paths['mp4']) ? true : false;
            $obj->webm = !empty($paths['webm']) ? true : false;
            $obj->m3u8 = !empty($paths['m3u8']) ? true : false;
            $obj->pdf = !empty($paths['pdf']) ? true : false;
            $obj->mp3 = !empty($paths['mp3']) ? true : false;

            return $obj;
        }

        static function getVideoTypeLabels($filename) {
            $obj = self::getVideoType($filename);
            $labels = "";
            if (empty($obj->mp4) && empty($obj->webm) && empty($obj->m3u8) && empty($obj->pdf) && empty($obj->mp3)) {
                return '<span class="label label-default">Other</span>';
            }
            if ($obj->mp4) {
                $labels .= '<span class="label label-success">MP4</span>';
            }
            if ($obj->webm) {
                $labels .= '<span class="label label-warning">Webm</span>';
            }
            if ($obj->m3u8) {
                $labels .= '<span class="label label-primary">HLS</span>';
            }
            if ($obj->pdf) {
                $labels .= '<span class="label label-danger">PDF</span>';
            }
            if ($obj->mp3) {
                $labels .= '<span class="label label-info">MP3</span>';
            }
            return $labels;
        }

        static function isPublic($videos_id) {
// check if the video is not public 
            $rows = UserGroups::getVideoGroups($videos_id);

            if (empty($rows)) {
                return true;
            }
            return false;
        }

        static function userGroupAndVideoGroupMatch($users_id, $videos_id) {

            if (empty($users_id) || empty($videos_id)) {
                return false;
            }
// check if the video is not public 
            $rows = UserGroups::getVideoGroups($videos_id);
            if (empty($rows)) {
                return true;
            }
            $rowsUser = UserGroups::getUserGroups(User::getId());
            if (empty($rowsUser)) {
                return false;
            }

            foreach ($rows as $value) {
                foreach ($rowsUser as $value2) {
                    if ($value['id'] === $value2['id']) {
                        return true;
                    }
                }
            }
            return false;
        }

        function getExternalOptions() {
            return $this->externalOptions;
        }

        function setExternalOptions($externalOptions) {
            $this->externalOptions = $externalOptions;
        }

        function setVideoStartSeconds($videoStartSeconds) {
            $externalOptions = json_decode($this->getExternalOptions());
            $externalOptions->videoStartSeconds = $videoStartSeconds;
            $this->setExternalOptions(json_encode($externalOptions));
        }

        function getSerie_playlists_id() {
            return $this->serie_playlists_id;
        }

        function setSerie_playlists_id($serie_playlists_id) {
            $this->serie_playlists_id = $serie_playlists_id;
        }

    }

}
// just to convert permalink into clean_title
if (!empty($_GET['v']) && empty($_GET['videoName'])) {
    $_GET['videoName'] = Video::get_clean_title($_GET['v']);
}
