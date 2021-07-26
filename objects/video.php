<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
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
        public static $types = array('webm', 'mp4', 'mp3', 'ogg', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'webp', 'zip');
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
        private $video_password;
        private $encoderURL;
        private $filepath;
        private $filesize;
        private $live_transmitions_history_id;
        public static $statusDesc = array(
            'a' => 'Active',
            'k' => 'Active and Encoding',
            'i' => 'Inactive',
            'e' => 'Encoding',
            'x' => 'Encoding Error',
            'd' => 'Downloading',
            't' => 'Transfering',
            'u' => 'Unlisted',
            'r' => 'Recording',
            'f' => 'FansOnly');
        public static $statusActive = 'a';
        public static $statusActiveAndEncoding = 'k';
        public static $statusInactive = 'i';
        public static $statusEncoding = 'e';
        public static $statusEncodingError = 'x';
        public static $statusDownloading = 'd';
        public static $statusTranfering = 't';
        public static $statusUnlisted = 'u';
        public static $statusRecording = 'r';
        public static $statusFansOnly = 'f';
        public static $rratingOptions = array('', 'g', 'pg', 'pg-13', 'r', 'nc-17', 'ma');
        //ver 3.4
        private $youtubeId;
        public static $typeOptions = array('audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie', 'image', 'zip', 'notfound', 'blockedUser');

        public function __construct($title = "", $filename = "", $id = 0) {
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

        public function addView($currentTime = 0) {
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
                AVideoPlugin::addView($this->id, $this->views_count);
                return $obj;
            }
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        public function updateViewsCount($total) {
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $total = intval($total);
            if ($total < 0) {
                return false;
            }
            $sql = "UPDATE videos SET views_count = {$total}, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", array($this->id));

            if ($insert_row) {
                return $insert_row;
            }
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        public function addViewPercent($percent = 25) {
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $sql = "UPDATE videos SET views_count_{$percent} = IFNULL(views_count_{$percent}, 0)+1, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", array($this->id));

            if ($insert_row) {
                return true;
            }
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        // allow users to count a view again in case it is refreshed
        public static function unsetAddView($videos_id) {
            // allow users to count a view again in case it is refreshed
            if (!empty($_SESSION['addViewCount'][$videos_id]['time']) && $_SESSION['addViewCount'][$videos_id]['time'] <= time()) {
                _session_start();
                unset($_SESSION['addViewCount'][$videos_id]);
            }
        }

        public function load($id) {
            $video = self::getVideoLight($id);
            if (empty($video)) {
                return false;
            }
            foreach ($video as $key => $value) {
                $this->$key = $value;
            }
        }

        function getLive_transmitions_history_id() {
            return $this->live_transmitions_history_id;
        }

        function setLive_transmitions_history_id($live_transmitions_history_id) {
            AVideoPlugin::onVideoSetLive_transmitions_history_id($this->id, $this->live_transmitions_history_id, intval($live_transmitions_history_id));
            $this->live_transmitions_history_id = intval($live_transmitions_history_id);
        }

        public function getEncoderURL() {
            return $this->encoderURL;
        }

        public function getFilepath() {
            return $this->filepath;
        }

        public function getFilesize() {
            return intval($this->filesize);
        }

        public function setEncoderURL($encoderURL) {
            if (filter_var($encoderURL, FILTER_VALIDATE_URL) !== false) {
                AVideoPlugin::onVideoSetEncoderURL($this->id, $this->encoderURL, $encoderURL);
                $this->encoderURL = $encoderURL;
            }
        }

        public function setFilepath($filepath) {
            AVideoPlugin::onVideoSetFilepath($this->id, $this->filepath, $filepath);
            $this->filepath = $filepath;
        }

        public function setFilesize($filesize) {
            AVideoPlugin::onVideoSetFilesize($this->id, $this->filesize, $filesize);
            $this->filesize = intval($filesize);
        }

        public function setUsers_id($users_id) {
            AVideoPlugin::onVideoSetUsers_id($this->id, $this->users_id, $users_id);
            $this->users_id = $users_id;
        }

        public function getSites_id() {
            return $this->sites_id;
        }

        public function setSites_id($sites_id) {
            AVideoPlugin::onVideoSetSites_id($this->id, $this->sites_id, $sites_id);
            $this->sites_id = $sites_id;
        }

        public function getVideo_password() {
            return trim($this->video_password);
        }

        public function setVideo_password($video_password) {
            AVideoPlugin::onVideoSetVideo_password($this->id, $this->video_password, $video_password);
            $this->video_password = trim($video_password);
        }

        public function save($updateVideoGroups = false, $allowOfflineUser = false) {
            global $advancedCustom;
            global $global;
            if (!User::isLogged() && !$allowOfflineUser) {
                _error_log('Video::save permission denied to save');
                return false;
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
                $p = AVideoPlugin::loadPluginIfEnabled("PredefinedCategory");
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

            if (empty($this->filename)) {
                $prefix = $this->type;
                if (empty($prefix)) {
                    $prefix = 'v';
                }
                $paths = self::getNewVideoFilename($prefix);
                $this->filename = $paths['filename'];
            }

            $this->can_download = intval($this->can_download);
            $this->can_share = intval($this->can_share);
            $this->only_for_paid = intval($this->only_for_paid);
            $this->filesize = intval($this->filesize);

            $this->rate = floatval($this->rate);

            if (!filter_var($this->videoLink, FILTER_VALIDATE_URL)) {
                $this->videoLink = '';
                if ($this->type == 'embed') {
                    $this->type = 'video';
                }
            }

            if (empty($this->live_transmitions_history_id)) {
                $this->live_transmitions_history_id = 'NULL';
            }

            if (!empty($this->id)) {
                if (!$this->userCanManageVideo() && !$allowOfflineUser && !Permissions::canModerateVideos()) {
                    header('Content-Type: application/json');
                    die('{"error":"3 ' . __("Permission denied") . '"}');
                }
                $sql = "UPDATE videos SET title = '{$this->title}',clean_title = '{$this->clean_title}',"
                        . " filename = '{$this->filename}', categories_id = '{$this->categories_id}', status = '{$this->status}',"
                        . " description = '{$this->description}', duration = '{$this->duration}', type = '{$this->type}', videoDownloadedLink = '{$this->videoDownloadedLink}', youtubeId = '{$this->youtubeId}', videoLink = '{$this->videoLink}', next_videos_id = {$this->next_videos_id}, isSuggested = {$this->isSuggested}, users_id = {$this->users_id}, "
                        . " trailer1 = '{$this->trailer1}', trailer2 = '{$this->trailer2}', trailer3 = '{$this->trailer3}', rate = '{$this->rate}', can_download = '{$this->can_download}', can_share = '{$this->can_share}', only_for_paid = '{$this->only_for_paid}', rrating = '{$this->rrating}', externalOptions = '{$this->externalOptions}', sites_id = {$this->sites_id}, serie_playlists_id = {$this->serie_playlists_id} ,live_transmitions_history_id = {$this->live_transmitions_history_id} , video_password = '{$this->video_password}', "
                        . " encoderURL = '{$this->encoderURL}', filepath = '{$this->filepath}' , filesize = '{$this->filesize}' , modified = now()"
                        . " WHERE id = {$this->id}";

                $saved = sqlDAL::writeSql($sql);
                if ($saved) {
                    $insert_row = $this->id;
                }
            } else {
                $sql = "INSERT INTO videos "
                        . "(title,clean_title, filename, users_id, categories_id, status, description, duration,type,videoDownloadedLink, next_videos_id, created, modified, videoLink, can_download, can_share, only_for_paid, rrating, externalOptions, sites_id, serie_playlists_id,live_transmitions_history_id, video_password, encoderURL, filepath , filesize) values "
                        . "('{$this->title}','{$this->clean_title}', '{$this->filename}', {$this->users_id},{$this->categories_id}, '{$this->status}', '{$this->description}', '{$this->duration}', '{$this->type}', '{$this->videoDownloadedLink}', {$this->next_videos_id},now(), now(), '{$this->videoLink}', '{$this->can_download}', '{$this->can_share}','{$this->only_for_paid}', '{$this->rrating}', '$this->externalOptions', {$this->sites_id}, {$this->serie_playlists_id},{$this->live_transmitions_history_id}, '{$this->video_password}', '{$this->encoderURL}', '{$this->filepath}', '{$this->filesize}')";

                $insert_row = sqlDAL::writeSql($sql);
            }
            if ($insert_row) {
                _error_log("Video::save ({$this->title}) Saved id = {$insert_row} ");
                Category::clearCacheCount();
                if (empty($this->id)) {
                    $id = $global['mysqli']->insert_id;
                    $this->id = $id;

                    // check if needs to add the video in a user group
                    $p = AVideoPlugin::loadPluginIfEnabled("PredefinedCategory");
                    if ($p) {
                        $updateVideoGroups = true;
                        $this->videoGroups = $p->getUserGroupsArray();
                    }
                } else {
                    $id = $this->id;
                }
                ObjectYPT::deleteCache("getItemprop{$this->id}");
                ObjectYPT::deleteCache("getLdJson{$this->id}");
                ObjectYPT::deleteCache("getVideoTags{$this->id}");
                self::deleteTagsAsync($this->id);
                if ($updateVideoGroups) {
                    require_once $global['systemRootPath'] . 'objects/userGroups.php';
                    // update the user groups
                    UserGroups::updateVideoGroups($id, $this->videoGroups);
                }

                // I am not sure what is it for
                //Video::autosetCategoryType($id);
                if (!empty($this->old_categories_id)) {
                    //Video::autosetCategoryType($this->old_categories_id);
                }
                self::clearCache($this->id);
                return $id;
            }
            _error_log('Video::save Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error . " $sql");
            return false;
        }

        // i would like to simplify the big part of the method above in this method, but won't work as i want.
        public static function internalAutoset($catId, $videoFound, $audioFound) {
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
                    } elseif ($row['type'] == "video") {
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
                                if ($row['type'] == 'audio') {
                                    $audioFound = true;
                                } elseif ($row['type'] == 'video') {
                                    $videoFound = true;
                                }
                            }
                        }
                    }
                }
            }
            return array($videoFound, audioFound);
        }

        public function setClean_title($clean_title) {
            if (preg_match("/video-automatically-booked/i", $clean_title) && !empty($this->clean_title)) {
                return false;
            }
            $clean_title = cleanURLName($clean_title);
            AVideoPlugin::onVideoSetClean_title($this->id, $this->clean_title, $clean_title);
            $this->clean_title = $clean_title;
        }

        public function setDuration($duration) {
            AVideoPlugin::onVideoSetDuration($this->id, $this->duration, $duration);
            $this->duration = $duration;
        }

        public function getDuration() {
            return $this->duration;
        }

        public function getIsSuggested() {
            return $this->isSuggested;
        }

        public function setIsSuggested($isSuggested) {
            if (empty($isSuggested) || $isSuggested === "false") {
                $new_isSuggested = 0;
            } else {
                $new_isSuggested = 1;
            }
            AVideoPlugin::onVideoSetIsSuggested($this->id, $this->isSuggested, $new_isSuggested);
            $this->isSuggested = $new_isSuggested;
        }

        public function setStatus($status) {
            if (!empty($this->id)) {
                global $global;

                if (empty(Video::$statusDesc[$status])) {
                    _error_log("Video::setStatus({$status}) NOT found ", AVideoLog::$WARNING);
                    return false;
                }
                _error_log("Video::setStatus({$status}) " . json_encode(debug_backtrace()), AVideoLog::$WARNING);
                $sql = "UPDATE videos SET status = ?, modified = now() WHERE id = ? ";
                $res = sqlDAL::writeSql($sql, 'si', array($status, $this->id));
                if ($global['mysqli']->errno != 0) {
                    die('Error on update Status: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
                self::clearCache($this->id);
            }
            AVideoPlugin::onVideoSetStatus($this->id, $this->status, $status);
            $this->status = $status;
            return $status;
        }

        public function setAutoStatus($default = 'a') {
            global $advancedCustom;
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');
            }

            if (!empty($_POST['fail'])) {
                return $this->setStatus(Video::$statusEncodingError);
            } else {
                if (!empty($_REQUEST['overrideStatus'])) {
                    return $this->setStatus($_REQUEST['overrideStatus']);
                } else { // encoder did not provide a status
                    if (!empty($_REQUEST['keepEncoding'])) {
                        return $this->setStatus(Video::$statusActiveAndEncoding);
                    } else {
                        if ($this->getTitle() !== "Video automatically booked") {
                            if (!empty($advancedCustom->makeVideosInactiveAfterEncode)) {
                                return $this->setStatus(Video::$statusInactive);
                            } elseif (!empty($advancedCustom->makeVideosUnlistedAfterEncode)) {
                                return $this->setStatus(Video::$statusUnlisted);
                            }
                        } else {
                            return $this->setStatus(Video::$statusInactive);
                        }
                    }
                }
            }
            return $this->setStatus($default);
        }

        public function setType($type, $force = true) {
            if ($force || empty($this->type)) {
                AVideoPlugin::onVideoSetType($this->id, $this->type, $type, $force);
                $this->type = $type;
            }
        }

        public function setRotation($rotation) {
            $saneRotation = intval($rotation) % 360;
            AVideoPlugin::onVideoSetRotation($this->id, $this->rotation, $saneRotation);

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

        public function getRotation() {
            return $this->rotation;
        }

        public function getUsers_id() {
            return $this->users_id;
        }

        public function setZoom($zoom) {
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

            AVideoPlugin::onVideoSetZoom($this->id, $this->zoom, $saneZoom);
            $this->zoom = $saneZoom;
        }

        public function getZoom() {
            return $this->zoom;
        }

        public static function getUserGroupsCanSeeSQL($tableAlias = '') {
            global $global;

            if (Permissions::canModerateVideos()) {
                return "";
            }

            $obj = AVideoPlugin::getDataObject('Subscription');
            if ($obj && $obj->allowFreePlayWithAds) {
                $sql = " AND {$tableAlias}only_for_paid = 0 ";
                return $sql;
            } else {
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
        }

        public static function getVideo($id = "", $status = "viewable", $ignoreGroup = false, $random = false, $suggestedOnly = false, $showUnlisted = false, $ignoreTags = false, $activeUsersOnly = true) {
            global $global, $config, $advancedCustom, $advancedCustomUser;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            $id = intval($id);
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                }
            }
            _mysql_connect();
            $sql = "SELECT u.*, v.*, "
                    . " nv.title as next_title,"
                    . " nv.clean_title as next_clean_title,"
                    . " nv.filename as next_filename,"
                    . " nv.id as next_id,"
                    . " c.id as category_id,c.iconClass,c.name as category,c.iconClass,  c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, "
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
            $sql .= AVideoPlugin::getVideoWhereClause();
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'audio') {
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
                $catName = $global['mysqli']->real_escape_string($_GET['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' ))";
            }

            if (empty($id) && !empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                if (!empty($user['id'])) {
                    $sql .= " AND v.users_id = '{$user['id']}' ";
                }
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_POST['searchPhrase'])) {
                $searchFieldsNames = array('v.title', 'v.description', 'c.name', 'c.description');
                if ($advancedCustomUser->videosSearchAlsoSearchesOnChannelName) {
                    $searchFieldsNames[] = 'u.channelName';
                }
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }
            if (!$ignoreGroup) {
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            // replace random based on this
            $firstClauseLimit = "";
            if (empty($id)) {
                if (empty($random) && !empty($_GET['videoName'])) {
                    $sql .= " AND v.clean_title = '{$_GET['videoName']}' ";
                } elseif (!empty($random)) {
                    $sql .= " AND v.id != {$random} ";
                    $rand = rand(0, self::getTotalVideos($status, false, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly));
                    $rand = ($rand - 2) < 0 ? 0 : $rand - 2;
                    $firstClauseLimit = "$rand, ";
                    //$sql .= " ORDER BY RAND() ";
                } elseif ($suggestedOnly && empty($_GET['videoName']) && empty($_GET['search']) && empty($_GET['searchPhrase'])) {
                    $sql .= " AND v.isSuggested = 1 ";
                    $rand = rand(0, self::getTotalVideos($status, false, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly));
                    $rand = ($rand - 2) < 0 ? 0 : $rand - 2;
                    $firstClauseLimit = "$rand, ";
                    //$sql .= " ORDER BY RAND() ";
                } elseif (!empty($_GET['v']) && is_numeric($_GET['v'])) {
                    $vid = intval($_GET['v']);
                    $sql .= " AND v.id = {$vid} ";
                } else {
                    $sql .= " ORDER BY v.Created DESC ";
                }
            }
            if (strpos($sql, 'v.id IN') === false && strpos(strtolower($sql), 'limit') === false) {
                $sql .= " LIMIT {$firstClauseLimit}1";
            }
            //echo $sql, "<br>";//exit;
            $res = sqlDAL::readSql($sql);
            $video = sqlDAL::fetchAssoc($res);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($video) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getVideo($id, $status, $ignoreGroup, $random, $suggestedOnly, $showUnlisted, $ignoreTags, $activeUsersOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            if ($res != false) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                if (!empty($video)) {
                    $video = self::getInfo($video);
                }
            } else {
                $video = false;
            }
            return $video;
        }

        public static function getVideoLight($id) {
            global $global, $config;
            $id = intval($id);
            $sql = "SELECT * FROM videos WHERE id = '$id' LIMIT 1";
            $res = sqlDAL::readSql($sql, "", array(), true);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $video;
        }

        public static function getTotalVideosSizeFromUser($users_id) {
            global $global, $config;
            $users_id = intval($users_id);
            $sql = "SELECT sum(filesize) as total FROM videos WHERE 1=1 ";

            if (!empty($users_id)) {
                $sql .= " AND users_id = '$users_id'";
            }

            $res = sqlDAL::readSql($sql, "", array(), true);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return intval($video['total']);
        }

        public static function getTotalVideosFromUser($users_id) {
            global $global, $config;
            $users_id = intval($users_id);
            $sql = "SELECT count(*) as total FROM videos WHERE 1=1 ";

            if (!empty($users_id)) {
                $sql .= " AND users_id = '$users_id'";
            }

            $res = sqlDAL::readSql($sql, "", array(), true);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return intval($video['total']);
        }

        public static function getVideoFromFileName($fileName, $ignoreGroup = false, $ignoreTags = false) {
            global $global;
            if (empty($fileName)) {
                return false;
            }
            $parts = explode("/", $fileName);
            if (!empty($parts[0])) {
                $fileName = $parts[0];
            }
            $fileName = self::getCleanFilenameFromFile($fileName);
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

        public static function getVideoFromFileNameLight($fileName) {
            global $global;
            $fileName = self::getCleanFilenameFromFile($fileName);
            if (empty($fileName)) {
                return false;
            }
            $sql = "SELECT * FROM videos WHERE filename = ? LIMIT 1";
            //var_dump($sql, $fileName);
            $res = sqlDAL::readSql($sql, "s", array($fileName), true);
            if ($res != false) {
                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                return $video;
            }
            return false;
        }

        public static function getVideoFromCleanTitle($clean_title) {
            // even increasing the max_allowed_packet it only goes away when close and reopen the connection
            global $global;
            $sql = "SELECT id  FROM videos  WHERE clean_title = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", array($clean_title));
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!empty($video) && $res) {
                return self::getVideo($video['id'], "", true, false, false, true);
                //$video['groups'] = UserGroups::getVideoGroups($video['id']);
            } else {
                return false;
            }
        }

        static function getRelatedMovies($videos_id, $limit = 10) {
            global $global;
            $video = self::getVideoLight($videos_id);
            if (empty($video)) {
                return false;
            }
            $sql = "SELECT * FROM videos v WHERE v.id != {$videos_id} AND v.status='a' AND (categories_id = {$video['categories_id']} ";
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                $sql .= " OR (";
                $sql .= "v.id IN (select videos_id FROM tags_has_videos WHERE tags_id IN "
                        . " (SELECT tags_id FROM tags_has_videos WHERE videos_id = {$videos_id}))";
                $sql .= ")";
            }

            $sql .= ") ";

            $sql .= AVideoPlugin::getVideoWhereClause();

            $sql .= "ORDER BY RAND() LIMIT {$limit} ";
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);

            sqlDAL::close($res);
            $rows = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    $row['images'] = self::getImageFromFilename($row['filename']);
                    if (empty($row['externalOptions'])) {
                        $row['externalOptions'] = json_encode(array('videoStartSeconds' => '00:00:00'));
                    }
                    $rows[] = $row;
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $rows;
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
        public static function getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $is_serie = null) {
            global $global, $config, $advancedCustom, $advancedCustomUser;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            if (!empty($_POST['sort']['suggested'])) {
                $suggestedOnly = true;
            }
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                    TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
                }
            }
            $status = str_replace("'", "", $status);

            $sql = "SELECT u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, v.modified as videoModified, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes, "
                    . " (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes "
                    . " FROM videos as v "
                    . " LEFT JOIN categories c ON categories_id = c.id "
                    . " LEFT JOIN users u ON v.users_id = u.id "
                    . " WHERE 2=2 ";

            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }

            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
                $uid = intval(User::getId());
                $sql .= " AND v.users_id = '{$uid}'";
            } elseif (!empty($showOnlyLoggedUserVideos)) {
                $uid = intval($showOnlyLoggedUserVideos);
                $sql .= " AND v.users_id = '{$uid}'";
            } elseif (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $uid = intval($user['id']);
                $sql .= " AND v.users_id = '{$uid}' ";
            }

            if (isset($_REQUEST['is_serie']) && empty($is_serie)) {
                $is_serie = intval($_REQUEST['is_serie']);
            }

            if (isset($is_serie)) {
                if (empty($is_serie)) {
                    $sql .= " AND v.serie_playlists_id IS NULL ";
                } else {
                    $sql .= " AND v.serie_playlists_id IS NOT NULL ";
                }
            }

            if (!empty($videosArrayId) && is_array($videosArrayId)) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }

            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }

            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                TimeLogStart("video::getAllVideos::getAllVideosExcludeVideosIDArray");
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
                TimeLogEnd("video::getAllVideos::getAllVideosExcludeVideosIDArray", __LINE__);
            }
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'videoOnly') {
                    $sql .= " AND (v.type = 'video')";
                } elseif ($_SESSION['type'] == 'audio') {
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
            } elseif ($status == "publicOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_GET['catName'])) {
                $catName = $global['mysqli']->real_escape_string($_GET['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' ))";
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_GET['modified'])) {
                $_GET['modified'] = str_replace("'", "", $_GET['modified']);
                $sql .= " AND v.modified >= '{$_GET['modified']}'";
            }

            if (!empty($_POST['searchPhrase'])) {
                $searchFieldsNames = array('v.title', 'v.description', 'c.name', 'c.description');
                if ($advancedCustomUser->videosSearchAlsoSearchesOnChannelName) {
                    $searchFieldsNames[] = 'u.channelName';
                }
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }

            $sql .= AVideoPlugin::getVideoWhereClause();

            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 ";
                $sql .= " ORDER BY RAND() ";
                $sort = @$_POST['sort'];
                unset($_POST['sort']);
                $sql .= BootGrid::getSqlFromPost(array(), empty($_POST['sort']['likes']) ? "v." : "", "", true);
                if (strpos(strtolower($sql), 'limit') === false) {
                    $sql .= " LIMIT 60 ";
                }
                $_POST['sort'] = $sort;
            } elseif (!isset($_POST['sort']['trending']) && !isset($_GET['sort']['trending'])) {
                if (!empty($_POST['sort']['created']) && !empty($_POST['sort']['likes'])) {
                    $_POST['sort']['v.created'] = $_POST['sort']['created'];
                    unset($_POST['sort']['created']);
                }
                $sql .= BootGrid::getSqlFromPost(array(), empty($_POST['sort']['likes']) ? "v." : "", "", true);
            } else {
                unset($_POST['sort']['trending'], $_GET['sort']['trending']);
                $rows = array();
                if (!empty($_REQUEST['current']) && $_REQUEST['current'] == 1) {
                    $rows = VideoStatistic::getVideosWithMoreViews($status, $showOnlyLoggedUserVideos, $showUnlisted, $suggestedOnly);
                }
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = $row['id'];
                }
                if (!empty($ids)) {
                    $sql .= " ORDER BY FIND_IN_SET(v.id, '" . implode(",", $ids) . "') DESC, likes DESC ";
                } else {
                    $sql .= " ORDER BY likes DESC ";
                }
                $sql .= ObjectYPT::getSqlLimit();
            }
            if (strpos(strtolower($sql), 'limit') === false) {
                if (!empty($_GET['limitOnceToOne'])) {
                    $sql .= " LIMIT 1";
                    unset($_GET['limitOnceToOne']);
                } else {
                    $_REQUEST['rowCount'] = getRowCount();
                    if (!empty($_REQUEST['rowCount'])) {
                        $sql .= " LIMIT {$_REQUEST['rowCount']}";
                    } else {
                        _error_log("getAllVideos without limit " . json_encode(debug_backtrace()));
                        if (empty($global['limitForUnlimitedVideos'])) {
                            $global['limitForUnlimitedVideos'] = 100;
                        }
                        if ($global['limitForUnlimitedVideos'] > 0) {
                            $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                        }
                    }
                }
            }

            //echo $sql;//exit;
            //_error_log("getAllVideos($status, $showOnlyLoggedUserVideos , $ignoreGroup , ". json_encode($videosArrayId).")" . $sql);
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($fullData) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            $videos = array();
            if ($res != false) {
                require_once 'userGroups.php';
                TimeLogStart("video::getAllVideos foreach");
                foreach ($fullData as $row) {
                    $row = self::getInfo($row, $getStatistcs);
                    $videos[] = $row;
                }
                $rowCount = getRowCount();
                $tolerance = $rowCount / 100;
                if ($tolerance < 0.2) {
                    $tolerance = 0.2;
                } else if ($tolerance > 2) {
                    $tolerance = 2;
                }
                TimeLogEnd("video::getAllVideos foreach", __LINE__, $tolerance);
                //$videos = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $videos;
        }

        private static function getInfo($row, $getStatistcs = false) {
            $name = "_getVideoInfo_{$row['id']}";
            $cache = ObjectYPT::getSessionCache($name, 3600);
            if (!empty($cache)) {
                $externalOptions = $cache->externalOptions;
                $obj = object_to_array($cache);
                if (!empty($externalOptions)) {
                    if (is_object($externalOptions)) {
                        $obj['externalOptions'] = $externalOptions;
                    } else if (is_string($externalOptions)) {
                        $obj['externalOptions'] = _json_decode($externalOptions);
                    }
                    $obj['externalOptions'] = json_encode($obj['externalOptions']);
                }
                if (empty($obj['externalOptions'])) {
                    $obj['externalOptions'] = json_encode(array('videoStartSeconds' => '00:00:00'));
                }
                return $obj;
            }

            $row = cleanUpRowFromDatabase($row);
            if (!self::canEdit($row['id'])) {
                if (!empty($row['video_password'])) {
                    $row['video_password'] = 1;
                } else {
                    $row['video_password'] = 0;
                }
            }
            if ($getStatistcs) {
                TimeLogStart("video::getInfo getStatistcs");
                $previewsMonth = date("Y-m-d 00:00:00", strtotime("-30 days"));
                $previewsWeek = date("Y-m-d 00:00:00", strtotime("-7 days"));
                $today = date('Y-m-d 23:59:59');
                $row['statistc_all'] = VideoStatistic::getStatisticTotalViews($row['id']);
                $row['statistc_today'] = VideoStatistic::getStatisticTotalViews($row['id'], false, date('Y-m-d 00:00:00'), $today);
                $row['statistc_week'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsWeek, $today);
                $row['statistc_month'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsMonth, $today);
                $row['statistc_unique_user'] = VideoStatistic::getStatisticTotalViews($row['id'], true);
                TimeLogEnd("video::getInfo getStatistcs", __LINE__, 0.5);
            }
            TimeLogStart("video::getInfo otherInfo");
            $otherInfocachename = "otherInfo{$row['id']}";
            $otherInfo = object_to_array(ObjectYPT::getCache($otherInfocachename), 600);
            if (empty($otherInfo)) {
                $otherInfo = array();
                $otherInfo['category'] = xss_esc_back($row['category']);
                $otherInfo['groups'] = UserGroups::getVideoGroups($row['id']);
                $otherInfo['tags'] = self::getTags($row['id']);
                $otherInfo['title'] = UTF8encode($row['title']);
                $otherInfo['description'] = UTF8encode($row['description']);
                $otherInfo['descriptionHTML'] = self::htmlDescription($otherInfo['description']);
                //$otherInfo['relatedVideos'] = self::getRelatedMovies($row['id']);
                if (empty($row['filesize'])) {
                    $otherInfo['filesize'] = Video::updateFilesize($row['id']);
                }
                ObjectYPT::setCache($otherInfocachename, $otherInfo);
            }
            foreach ($otherInfo as $key => $value) {
                $row[$key] = $value;
            }
            $row['hashId'] = idToHash($row['id']);
            $row['link'] = self::getLinkToVideo($row['id'], $row['clean_title']);
            $row['embedlink'] = self::getLinkToVideo($row['id'], $row['clean_title'], true);
            $row['progress'] = self::getVideoPogressPercent($row['id']);
            $row['isFavorite'] = self::isFavorite($row['id']);
            $row['isWatchLater'] = self::isWatchLater($row['id']);
            $row['favoriteId'] = self::getFavoriteIdFromUser(User::getId());
            $row['watchLaterId'] = self::getWatchLaterIdFromUser(User::getId());

            if (empty($row['externalOptions'])) {
                $row['externalOptions'] = json_encode(array('videoStartSeconds' => '00:00:00'));
            }
            TimeLogEnd("video::getInfo otherInfo", __LINE__, 0.5);

            TimeLogStart("video::getInfo getAllVideosArray");
            $row = array_merge($row, AVideoPlugin::getAllVideosArray($row['id']));
            TimeLogEnd("video::getInfo getAllVideosArray", __LINE__);
            ObjectYPT::setCache($name, $row);
            return $row;
        }

        public static function htmlDescription($description) {
            if (strip_tags($description) != $description) {
                return $description;
            } else {
                return nl2br(textToLink(htmlentities($description)));
            }
        }

        public static function isFavorite($videos_id) {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::isVideoOnFavorite($videos_id, User::getId());
            }
            return false;
        }

        public static function isSerie($videos_id) {
            $v = new Video("", "", $videos_id);
            return !empty($v->getSerie_playlists_id());
        }

        public static function isWatchLater($videos_id) {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::isVideoOnWatchLater($videos_id, User::getId());
            }
            return false;
        }

        public static function getFavoriteIdFromUser($users_id) {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::getFavoriteIdFromUser($users_id);
            }
            return false;
        }

        public static function getWatchLaterIdFromUser($users_id) {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::getWatchLaterIdFromUser($users_id);
            }
            return false;
        }

        public static function updateFilesize($videos_id) {
            global $config;
            if ($config->currentVersionLowerThen('8.5')) {
                return false;
            }
            TimeLogStart("Video::updateFilesize {$videos_id}");
            ini_set('max_execution_time', 300); // 5
            set_time_limit(300);
            $video = new Video("", "", $videos_id);
            $filename = $video->getFilename();
            if (empty($filename) || !($video->getType() == "video" || $video->getType() == "audio" || $video->getType() == "zip" || $video->getType() == "image")) {
                //_error_log("updateFilesize: Not updated, this filetype is ".$video->getType());
                return false;
            }
            $filesize = getUsageFromFilename($filename);
            if (empty($filesize)) {
                $obj = AVideoPlugin::getObjectDataIfEnabled("DiskUploadQuota");
                if (!empty($obj->deleteVideosWith0Bytes)) {
                    try {
                        _error_log("updateFilesize: DELETE videos_id=$videos_id filename=$filename filesize=$filesize");
                        return $video->delete();
                    } catch (Exception $exc) {
                        _error_log("updateFilesize: ERROR " . $exc->getTraceAsString());
                        return false;
                    }
                }
            }
            if ($video->getFilesize() == $filesize) {
                //_error_log("updateFilesize: No need to update videos_id=$videos_id filename=$filename filesize=$filesize");
                return $filesize;
            }
            $video->setFilesize($filesize);
            TimeLogEnd("Video::updateFilesize {$videos_id}", __LINE__);
            if ($video->save(false, true)) {
                _error_log("updateFilesize: videos_id=$videos_id filename=$filename filesize=$filesize");
                return $filesize;
            } else {
                _error_log("updateFilesize: ERROR videos_id=$videos_id filename=$filename filesize=$filesize");
                return false;
            }
        }

        /**
         * Same as getAllVideos() method but a lighter query
         * @global type $global
         * @global type $config
         * @param type $showOnlyLoggedUserVideos
         * @return boolean
         */
        public static function getAllVideosLight($status = "viewable", $showOnlyLoggedUserVideos = false, $showUnlisted = false, $suggestedOnly = false) {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            $sql = "SELECT v.* "
                    . " FROM videos as v "
                    . " WHERE 1=1 ";
            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }
            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
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
            $sql .= AVideoPlugin::getVideoWhereClause();

            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 ";
                $sql .= " ORDER BY RAND() ";
            }
            if (strpos(strtolower($sql), 'limit') === false) {
                if (empty($global['limitForUnlimitedVideos'])) {
                    $global['limitForUnlimitedVideos'] = empty($global['rowCount']) ? 1000 : $global['rowCount'];
                }
                if ($global['limitForUnlimitedVideos'] > 0) {
                    $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                }
            }
            //echo $sql;
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($fullData) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getAllVideosLight($status, $showOnlyLoggedUserVideos, $showUnlisted, $suggestedOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            $videos = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    if (empty($row['filesize'])) {
                        $row['filesize'] = Video::updateFilesize($row['id']);
                    }
                    $videos[] = $row;
                }
                //$videos = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $videos = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $videos;
        }

        public static function getTotalVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false) {
            global $global, $config, $advancedCustomUser;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            if (!empty($_POST['sort']['suggested'])) {
                $suggestedOnly = true;
            }
            $status = str_replace("'", "", $status);
            $cn = "";
            if (!empty($_GET['catName'])) {
                $cn .= ", c.clean_name as cn";
            }
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                    TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
                }
            }

            $sql = "SELECT v.users_id, v.type, v.id, v.title,v.description, c.name as category {$cn} "
                    . "FROM videos v "
                    . "LEFT JOIN categories c ON categories_id = c.id "
                    . " LEFT JOIN users u ON v.users_id = u.id "
                    . " WHERE 1=1 ";

            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }
            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
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

            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
                $sql .= " AND v.users_id = '" . User::getId() . "'";
            } elseif (is_int($showOnlyLoggedUserVideos)) {
                $sql .= " AND v.users_id = '{$showOnlyLoggedUserVideos}'";
            }

            if (isset($_REQUEST['is_serie'])) {
                $is_serie = intval($_REQUEST['is_serie']);
                if (empty($is_serie)) {
                    $sql .= " AND v.serie_playlists_id IS NULL ";
                } else {
                    $sql .= " AND v.serie_playlists_id IS NOT NULL ";
                }
            }

            if (!empty($_GET['catName'])) {
                $catName = $global['mysqli']->real_escape_string($_GET['catName']);
                $sql .= " AND c.clean_name = '{$catName}'";
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }
            if (!$ignoreGroup) {
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $uid = intval($user['id']);
                $sql .= " AND v.users_id = '{$uid}' ";
            }

            $sql .= AVideoPlugin::getVideoWhereClause();

            if (!empty($_POST['searchPhrase'])) {
                $searchFieldsNames = array('v.title', 'v.description', 'c.name', 'c.description');
                if ($advancedCustomUser->videosSearchAlsoSearchesOnChannelName) {
                    $searchFieldsNames[] = 'u.channelName';
                }
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = array('v.title');
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }

            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 ";
            }
            $res = sqlDAL::readSql($sql);
            $numRows = sqlDal::num_rows($res);
            sqlDAL::close($res);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($numRows) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getTotalVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            return $numRows;
        }

        public static function getTotalVideosInfo($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array()) {
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

        public static function getTotalVideosInfoAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false) {
            global $global, $advancedCustom;
            $path = getCacheDir() . "getTotalVideosInfo/";
            make_path($path);
            $cacheFileName = "{$path}_{$status}_{$showOnlyLoggedUserVideos}_{$ignoreGroup}_" . implode($videosArrayId) . "_{$getStatistcs}";
            $return = array();
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getTotalVideosInfo($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTotalVideosInfoAsync.php' "
                        . " '$status' '$showOnlyLoggedUserVideos' '$ignoreGroup', '" . json_encode($videosArrayId) . "', "
                        . " '$getStatistcs', '$cacheFileName'");
                //_error_log("getTotalVideosInfoAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        public static function getViewableStatus($showUnlisted = false) {
            $viewable = array('a', 'k', 'f');
            if ($showUnlisted) {
                $viewable[] = "u";
            }
            $videos_id = getVideos_id();
            if (!empty($videos_id)) {
                $post = $_POST;
                if (self::isOwner($videos_id) || Permissions::canModerateVideos()) {
                    $viewable[] = "u";
                }
                $_POST = $post;
            }
            return $viewable;
        }

        public static function getVideoConversionStatus($filename) {
            global $global;
            require_once $global['systemRootPath'] . 'objects/user.php';
            if (!User::isLogged()) {
                die("Only logged users can upload");
            }

            $object = new stdClass();

            foreach (self::$types as $value) {
                $progressFilename = self::getStoragePathFromFileName($filename) . "progress_{$value}.txt";
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

        private static function parseProgress($content) {
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

        public function delete($allowOfflineUser = false) {
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
                _error_log('Error (delete on video) : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                return false;
            } else {
                $this->removeVideoFiles();
            }
            return $resp;
        }

        public function removeVideoFiles() {
            $filename = $this->getFilename();
            if (empty($filename)) {
                return false;
            }
            $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
            $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
            $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
            $YPTStorage = AVideoPlugin::loadPluginIfEnabled('YPTStorage');
            if (!empty($aws_s3)) {
                $aws_s3->removeFiles($filename);
            }
            if (!empty($bb_b2)) {
                $bb_b2->removeFiles($filename);
            }
            if (!empty($ftp)) {
                $ftp->removeFiles($filename);
            }
            if (!empty($YPTStorage) && !empty($this->getSites_id())) {
                $YPTStorage->removeFiles($filename, $this->getSites_id());
            }
            $this->removeFiles($filename);
            self::deleteThumbs($filename);
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
            $file = self::getStoragePath() . "original_{$filename}";
            $this->removeFilePath($file);

            $files = self::getStoragePath() . "{$filename}";
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

        private static function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . "/" . $object)) {
                            self::rrmdir($dir . "/" . $object);
                        } else {
                            unlink($dir . "/" . $object);
                        }
                    }
                }
                rmdir($dir);
            }
        }

        public function setDescription($description) {
            global $global, $advancedCustom;
            if (empty($advancedCustom->disableHTMLDescription)) {
                $articleObj = AVideoPlugin::getObjectData('Articles');
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
                $new_description = $parts[0];
            } else {
                $new_description = strip_tags(br2nl($description));
            }
            AVideoPlugin::onVideoSetDescription($this->id, $this->description, $new_description);
            //$new_description= preg_replace('/[\xE2\x80\xAF\xBA\x96]/', '', $new_description);

            if (function_exists('mb_convert_encoding')) {
                $new_description = mb_convert_encoding($new_description, 'UTF-8', 'UTF-8');
            }

            $this->description = $new_description;
            //var_dump($this->description, $description, $parts);exit;
        }

        public function setCategories_id($categories_id) {
            if (!Category::userCanAddInCategory($categories_id)) {
                return false;
            }

            // to update old cat as well when auto..
            if (!empty($this->categories_id)) {
                $this->old_categories_id = $this->categories_id;
            }
            AVideoPlugin::onVideoSetCategories_id($this->id, $this->categories_id, $categories_id);
            $this->categories_id = $categories_id;
        }

        public static function getCleanDuration($duration = "") {
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

        private static function addZero($str) {
            if (intval($str) < 10) {
                return '0' . intval($str);
            }
            return $str;
        }

        public static function getItemPropDuration($duration = '') {
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            $duration = 'PT' . intval($parts[0]) . 'H' . intval($parts[1]) . 'M' . intval($parts[2]) . 'S';
            if ($duration == "PT0H0M0S") {
                $duration = "PT0H0M1S";
            }
            return $duration;
        }

        public static function getItemDurationSeconds($duration = '') {
            if ($duration == "EE:EE:EE") {
                return 0;
            }
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            return intval($parts[0] * 60 * 60) + intval($parts[1] * 60) + intval($parts[2]);
        }

        public static function getDurationFromFile($file) {
            global $global;
            // get movie duration HOURS:MM:SS.MICROSECONDS
            if (!file_exists($file)) {
                _error_log('{"status":"error", "msg":"getDurationFromFile ERROR, File (' . $file . ') Not Found"}');
                return "EE:EE:EE";
            }
            // Initialize getID3 engine
            $getID3 = new getID3;
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($file);
            return static::getCleanDuration(@$ThisFileInfo['playtime_string']);
        }

        public static function getResolution($file) {
            global $videogetResolution;
            if (!isset($videogetResolution)) {
                $videogetResolution = array();
            }
            if (isset($videogetResolution[$file])) {
                return $videogetResolution[$file];
            }
            if (
                    AVideoPlugin::isEnabledByName("Blackblaze_B2") ||
                    AVideoPlugin::isEnabledByName("AWS_S3") ||
                    AVideoPlugin::isEnabledByName("FTP_Storage") ||
                    AVideoPlugin::isEnabledByName("YPTStorage") || !file_exists($file)) {
                $videogetResolution[$file] = 0;
                return 0;
            }
            global $global;
            if (preg_match("/.m3u8$/i", $file) && AVideoPlugin::isEnabledByName('VideoHLS') && method_exists(new VideoHLS(), 'getHLSHigestResolutionFromFile')) {
                $videogetResolution[$file] = VideoHLS::getHLSHigestResolutionFromFile($file);
            } else {
                $getID3 = new getID3;
                $ThisFileInfo = $getID3->analyze($file);
                $videogetResolution[$file] = intval(@$ThisFileInfo['video']['resolution_y']);
            }
            return $videogetResolution[$file];
        }

        public static function getHLSDurationFromFile($file) {
            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return 0;
            }
            return VideoHLS::getHLSDurationFromFile($file);
        }

        public function updateHLSDurationIfNeed() {
            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return false;
            }
            return VideoHLS::updateHLSDurationIfNeed($this);
        }

        public function updateDurationIfNeed($fileExtension = ".mp4") {
            global $global;
            $source = self::getSourceFile($this->filename, $fileExtension, true);
            $file = $source['path'];

            if (!empty($this->id) && $this->duration == "EE:EE:EE" && file_exists($file)) {
                $this->duration = Video::getDurationFromFile($file);
                _error_log("Duration Updated: " . json_encode($this));

                $sql = "UPDATE videos SET duration = ?, modified = now() WHERE id = ?";
                $res = sqlDAL::writeSql($sql, "si", array($this->duration, $this->id));
                return $this->id;
            } else {
                _error_log("Do not need update duration: ");
                return false;
            }
        }

        public function getFilename() {
            return $this->filename;
        }

        public function getStatus() {
            return $this->status;
        }

        public function getId() {
            return $this->id;
        }

        public function getVideoDownloadedLink() {
            return $this->videoDownloadedLink;
        }

        public function setVideoDownloadedLink($videoDownloadedLink) {
            AVideoPlugin::onVideoSetVideoDownloadedLink($this->id, $this->videoDownloadedLink, $videoDownloadedLink);
            $this->videoDownloadedLink = $videoDownloadedLink;
        }

        public static function isLandscape($pathFileName) {
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

        public function userCanManageVideo() {
            global $advancedCustomUser;
            if (Permissions::canAdminVideos()) {
                return true;
            }
            if (empty($this->users_id) || !User::canUpload()) {
                return false;
            }

            // if you not admin you can only manager yours video
            $users_id = $this->users_id;
            if ($advancedCustomUser->userCanChangeVideoOwner) {
                $video = new Video("", "", $this->id); // query again to make sure the user is not changing the owner
                $users_id = $video->getUsers_id();
            }

            if ($users_id != User::getId()) {
                return false;
            }
            return true;
        }

        public function getVideoGroups() {
            return $this->videoGroups;
        }

        public function setVideoGroups($userGroups) {
            if (is_array($userGroups)) {
                AVideoPlugin::onVideoSetVideoGroups($this->id, $this->videoGroups, $userGroups);
                $this->videoGroups = $userGroups;
            }
        }

        /**
         *
         * @param type $user_id
         * text
         * label Default Primary Success Info Warning Danger
         */
        public static function getTags($video_id, $type = "") {
            global $advancedCustom, $videos_getTags;

            if (empty($videos_getTags)) {
                $videos_getTags = array();
            }
            $name = "{$video_id}_{$type}";
            if (!empty($videos_getTags[$name])) {
                return $videos_getTags[$name];
            }

            $videos_getTags[$name] = self::getTags_($video_id, $type);
            return $videos_getTags[$name];
        }

        public static function getTagsHTMLLabelArray($video_id) {
            global $_getTagsHTMLLabelArray;

            if (!isset($_getTagsHTMLLabelArray)) {
                $_getTagsHTMLLabelArray = array();
            }

            if (isset($_getTagsHTMLLabelArray[$video_id])) {
                return $_getTagsHTMLLabelArray[$video_id];
            }

            $tags = Video::getTags($video_id);
            $_getTagsHTMLLabelArray[$video_id] = array();
            foreach ($tags as $value2) {
                if (empty($value2->label) || ($value2->label !== __("Paid Content") && $value2->label !== __("Group") && $value2->label !== __("Plugin"))) {
                    continue;
                }

                $tooltip = '';
                if (!empty($value2->tooltip)) {
                    $icon = $value2->text;
                    if (!empty($value2->tooltipIcon)) {
                        $icon = $value2->tooltipIcon;
                    }
                    $tooltip = '  data-toggle="tooltip" title="' . htmlentities($icon . ' ' . $value2->tooltip) . '" data-html="true"';
                }

                $_getTagsHTMLLabelArray[$video_id][] = '<span class="label label-' . $value2->type . '" ' . $tooltip . '>' . $value2->text . '</span>';
            }
            return $_getTagsHTMLLabelArray[$video_id];
        }

        public static function getTags_($video_id, $type = "") {
            global $advancedCustom, $advancedCustomUser;
            if (empty($advancedCustom)) {
                $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
            }
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
            }
            $currentPage = getCurrentPage();
            $rowCount = getRowCount();
            $_REQUEST['current'] = 1;
            $_REQUEST['rowCount'] = 1000;

            $video = new Video("", "", $video_id);
            $tags = array();
            if (empty($type) || $type === "paid") {
                $objTag = new stdClass();
                $objTag->label = __("Paid Content");
                if (!empty($advancedCustom->paidOnlyShowLabels)) {
                    if (!empty($video->getOnly_for_paid())) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyLabel;
                    } else {
                        $objTag->type = "success";
                        $objTag->text = '<i class="fas fa-lock-open"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                    }
                } else {
                    $ppv = AVideoPlugin::getObjectDataIfEnabled("PayPerView");
                    if ($video->getStatus() === self::$statusFansOnly) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-star" ></i>';
                        $objTag->tooltip = __("Fans Only");
                    } elseif ($advancedCustomUser->userCanProtectVideosWithPassword && !empty($video->getVideo_password())) {
                        $objTag->type = "danger";
                        $objTag->text = '<i class="fas fa-lock" ></i>';
                        $objTag->tooltip = __("Password Protected");
                    } elseif (!empty($video->getOnly_for_paid())) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyLabel;
                    } elseif ($ppv && PayPerView::isVideoPayPerView($video_id)) {
                        if (!empty($ppv->showPPVLabel)) {
                            $objTag->type = "warning";
                            $objTag->text = "PPV";
                            $objTag->tooltip = __("Pay Per View");
                        } else {
                            $objTag->type = "warning";
                            $objTag->text = '<i class="fas fa-lock"></i>';
                            $objTag->tooltip = __("Private");
                        }
                    } elseif (!Video::isPublic($video_id)) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = __("Private");
                    } else {
                        $objTag->type = "success";
                        $objTag->text = '<i class="fas fa-lock-open"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                    }
                }
                $tags[] = $objTag;
                $objTag = new stdClass();
            }

            /**
              a = active
              i = inactive
              e = encoding
              x = encoding error
              d = downloading
              u = unlisted
             */
            if (empty($type) || $type === "status") {
                $objTag = new stdClass();
                $objTag->label = __("Status");
                $status = $video->getStatus();
                $objTag->text = __(Video::$statusDesc[$status]);
                switch ($status) {
                    case Video::$statusActive:
                        $objTag->type = "success";
                        break;
                    case Video::$statusActiveAndEncoding:
                        $objTag->type = "success";
                        break;
                    case Video::$statusInactive:
                        $objTag->type = "warning";
                        break;
                    case Video::$statusEncoding:
                        $objTag->type = "info";
                        break;
                    case Video::$statusDownloading:
                        $objTag->type = "info";
                        break;
                    case Video::$statusUnlisted:
                        $objTag->type = "info";
                        break;
                    case Video::$statusRecording:
                        $objTag->type = "danger isRecording isRecordingIcon";
                        break;
                    default:
                        $objTag->type = "danger";
                        break;
                }
                $objTag->text = $objTag->text;
                $tags[] = $objTag;
                $objTag = new stdClass();
            }

            if (empty($type) || $type === "userGroups") {
                $groups = UserGroups::getVideoGroups($video_id);
                $objTag = new stdClass();
                $objTag->label = __("Group");
                if (empty($groups)) {
                    $status = $video->getStatus();
                    if ($status == 'u') {
                        $objTag->type = "info";
                        $objTag->text = '<i class="far fa-eye-slash"></i>';
                        $objTag->tooltip = __("Unlisted");
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    } else {
                        //$objTag->type = "success";
//$objTag->text = __("Public");
                    }
                } else {
                    foreach ($groups as $value) {
                        $objTag = new stdClass();
                        $objTag->label = __("Group");
                        $objTag->type = "info";
                        $objTag->text = '<i class="fas fa-users"></i>';
                        $objTag->tooltip = $value['group_name'];
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    }
                }
            }

            if (empty($type) || $type === "category") {
                require_once 'category.php';
                $sort = null;
                if (!empty($_POST['sort']['title'])) {
                    $sort = $_POST['sort'];
                    unset($_POST['sort']);
                }
                $category = Category::getCategory($video->getCategories_id());
                if (!empty($sort)) {
                    $_POST['sort'] = $sort;
                }
                $objTag = new stdClass();
                $objTag->label = __("Category");
                if (!empty($category)) {
                    $objTag->type = "default";
                    $objTag->text = $category['name'];
                    $tags[] = $objTag;
                    $objTag = new stdClass();
                }
            }

            if (empty($type) || $type === "source") {
                $url = $video->getVideoDownloadedLink();
                $parse = parse_url($url);
                $objTag = new stdClass();
                $objTag->label = __("Source");
                if (!empty($parse['host'])) {
                    $objTag->type = "danger";
                    $objTag->text = $parse['host'];
                    $tags[] = $objTag;
                    $objTag = new stdClass();
                } else {
                    $objTag->type = "info";
                    $objTag->text = __("Local File");
                    $tags[] = $objTag;
                    $objTag = new stdClass();
                }
            }
            $array2 = AVideoPlugin::getVideoTags($video_id);
            if (is_array($array2)) {
                $tags = array_merge($tags, $array2);
            }
            //var_dump($tags);

            $_REQUEST['current'] = $currentPage;
            $_REQUEST['rowCount'] = $rowCount;

            return $tags;
        }

        public static function deleteTagsAsync($video_id) {
            global $global;
            if (empty($video_id)) {
                return false;
            }

            $name = "getVideoTags{$video_id}";
            ObjectYPT::deleteCache($name);

            _session_start();
            unset($_SESSION['getVideoTags'][$video_id]);
            $path = getCacheDir() . "getTagsAsync/";
            if (!is_dir($path)) {
                return false;
            }

            $cacheFileName = "{$path}_{$video_id}_";

            $files = glob("{$cacheFileName}*");
            foreach ($files as $file) {
                unlink($file);
            }
        }

        public static function getTagsAsync($video_id, $type = "video") {
            global $global, $advancedCustom;
            $path = getCacheDir() . "getTagsAsync/";
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
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > 300) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTags.php' '$video_id' '$type' '{$cacheFileName}'");
                //_error_log("getTags: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return (array) $return;
        }

        public function getCategories_id() {
            return $this->categories_id;
        }

        public function getType() {
            return $this->type;
        }

        public static function fixCleanTitle($clean_title, $count, $videoId, $original_title = "") {
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
        public static function isOwner($videos_id, $users_id = 0) {
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

        public static function isOwnerFromCleanTitle($clean_title, $users_id = 0) {
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
        public static function getOwner($videos_id) {
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
        public static function canEdit($videos_id, $users_id = 0) {
            if (empty($videos_id)) {
                return false;
            }
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

            if (Permissions::canAdminVideos()) {
                return true;
            }

            return self::isOwner($videos_id, $users_id);
        }

        public static function getRandom($excludeVideoId = false) {
            return static::getVideo("", "viewable", false, $excludeVideoId);
        }

        public static function getVideoQueryFileter() {
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

        public function getTitle() {
            return $this->title;
        }

        public function getClean_title() {
            return $this->clean_title;
        }

        public function getDescription() {
            return $this->description;
        }

        public function getExistingVideoFile() {
            $source = self::getHigestResolutionVideoMP4Source($this->getFilename(), true);
            if (empty($source)) {
                _error_log("getExistingVideoFile:: resources are empty " . $this->getFilename());
                return false;
            }
            $size = filesize($source['path']);
            if ($size <= 20) {// it is a dummy file
                $url = $source['url'];
                _error_log("getExistingVideoFile:: dummy file, download it " . json_encode($source));
                $filename = getTmpDir("getExistingVideoFile") . md5($url);
                copyfile_chunked($url, $filename);
                wget($url, $filename);
                return $filename;
            }
            return $source['path'];
        }

        public function getTrailer1() {
            return $this->trailer1;
        }

        public function getTrailer2() {
            return $this->trailer2;
        }

        public function getTrailer3() {
            return $this->trailer3;
        }

        public function getRate() {
            return $this->rate;
        }

        public function setTrailer1($trailer1) {
            if (filter_var($trailer1, FILTER_VALIDATE_URL)) {
                $new_trailer1 = $trailer1;
            } else {
                $new_trailer1 = "";
            }
            AVideoPlugin::onVideoSetTrailer1($this->id, $this->trailer1, $new_trailer1);
            $this->trailer1 = $new_trailer1;
        }

        public function setTrailer2($trailer2) {
            if (filter_var($trailer2, FILTER_VALIDATE_URL)) {
                $new_trailer2 = $trailer2;
            } else {
                $new_trailer2 = "";
            }
            AVideoPlugin::onVideoSetTrailer2($this->id, $this->trailer2, $new_trailer2);
            $this->trailer2 = $new_trailer2;
        }

        public function setTrailer3($trailer3) {
            if (filter_var($trailer3, FILTER_VALIDATE_URL)) {
                $new_trailer3 = $trailer3;
            } else {
                $new_trailer3 = "";
            }
            AVideoPlugin::onVideoSetTrailer3($this->id, $this->trailer3, $new_trailer3);
            $this->trailer3 = $new_trailer3;
        }

        public function setRate($rate) {
            AVideoPlugin::onVideoSetRate($this->id, $this->rate, floatval($rate));
            $this->rate = floatval($rate);
        }

        public function getYoutubeId() {
            return $this->youtubeId;
        }

        public function setYoutubeId($youtubeId) {
            AVideoPlugin::onVideoSetYoutubeId($this->id, $this->youtubeId, $youtubeId);
            $this->youtubeId = $youtubeId;
        }

        public function setTitle($title) {
            if ($title === "Video automatically booked" && !empty($this->title)) {
                return false;
            }
            $new_title = strip_tags($title);
            if (strlen($new_title) > 190) {
                $new_title = substr($new_title, 0, 187) . '...';
            }
            AVideoPlugin::onVideoSetTitle($this->id, $this->title, $new_title);
            $this->title = $new_title;
        }

        public function setFilename($filename, $force = false) {
            if ($force || empty($this->filename)) {
                AVideoPlugin::onVideoSetFilename($this->id, $this->filename, $filename, $force);
                $this->filename = $filename;
            } else {
                _error_log('setFilename: fail ' . $filename . " {$this->id}");
            }
            return $this->filename;
        }

        public function getNext_videos_id() {
            return $this->next_videos_id;
        }

        public function setNext_videos_id($next_videos_id) {
            AVideoPlugin::onVideoSetNext_videos_id($this->id, $this->next_videos_id, $next_videos_id);
            $this->next_videos_id = $next_videos_id;
        }

        public function queue($types = array()) {
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

            if (empty($types) && AVideoPlugin::isEnabledByName("VideoHLS")) {
                $postFields['inputHLS'] = 1;
            } elseif (!empty($types)) {
                foreach ($types as $key => $value) {
                    $postFields[$key] = $value;
                }
            }

            _error_log("SEND To QUEUE: ($target) " . json_encode($postFields));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $target);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $r = curl_exec($curl);
            $obj->response = $r;
            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);
                //echo "cURL error ({$errno}):\n {$error_message}";
                $obj->msg = "cURL error ({$errno}):\n {$error_message}";
            } else {
                $obj->error = false;
            }
            _error_log("QUEUE CURL: ($target) " . json_encode($obj));
            curl_close($curl);
            return $obj;
        }

        public function getVideoLink() {
            return $this->videoLink;
        }

        public function setVideoLink($videoLink) {
            AVideoPlugin::onVideoSetVideoLink($this->id, $this->videoLink, $videoLink);
            $this->videoLink = $videoLink;
        }

        public function getCan_download() {
            return $this->can_download;
        }

        public function getCan_share() {
            return $this->can_share;
        }

        public function setCan_download($can_download) {
            $new_can_download = (empty($can_download) || $can_download === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetCan_download($this->id, $this->can_download, $new_can_download);
            $this->can_download = $new_can_download;
        }

        public function setCan_share($can_share) {
            $new_can_share = (empty($can_share) || $can_share === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetCan_share($this->id, $this->can_share, $new_can_share);
            $this->can_share = $new_can_share;
        }

        public function getOnly_for_paid() {
            return $this->only_for_paid;
        }

        public function setOnly_for_paid($only_for_paid) {
            $new_only_for_paid = (empty($only_for_paid) || $only_for_paid === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetOnly_for_paid($this->id, $this->only_for_paid, $new_only_for_paid);
            $this->only_for_paid = $new_only_for_paid;
        }

        /**
         *
         * @param type $filename
         * @param type $type
         * @return type .jpg .gif .webp _thumbs.jpg _Low.mp4 _SD.mp4 _HD.mp4
         */
        public static function getSourceFile($filename, $type = ".jpg", $includeS3 = false) {
            global $global, $advancedCustom, $videosPaths, $VideoGetSourceFile;
            //if(!isValidFormats($type)){
            //return array();
            //}

            self::_moveSourceFilesToDir($filename);
            $paths = self::getPaths($filename);
            if ($type == '_thumbsSmallV2.jpg' && empty($advancedCustom->usePreloadLowResolutionImages)) {
                return array('path' => $global['systemRootPath'] . 'view/img/loading-gif.png', 'url' => getCDN() . 'view/img/loading-gif.png');
            }

            $cacheName = md5($filename . $type . $includeS3);
            if (0 && isset($VideoGetSourceFile[$cacheName]) && is_array($VideoGetSourceFile[$cacheName])) {
                if (!preg_match("/token=/", $VideoGetSourceFile[$cacheName]['url'])) {
                    return $VideoGetSourceFile[$cacheName];
                }
            }

            // check if there is a webp image
            if ($type === '.gif' && (empty($_SERVER['HTTP_USER_AGENT']) || get_browser_name($_SERVER['HTTP_USER_AGENT']) !== 'Safari')) {
                $path = "{$paths['path']}{$filename}.webp";
                if (file_exists($path)) {
                    $type = ".webp";
                }
            }
            if (empty($videosPaths[$filename][$type][intval($includeS3)])) {
                $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
                if (!empty($aws_s3)) {
                    $aws_s3_obj = $aws_s3->getDataObject();
                    if (!empty($aws_s3_obj->useS3DirectLink)) {
                        $includeS3 = true;
                    }
                } elseif (!empty($bb_b2)) {
                    $bb_b2_obj = $bb_b2->getDataObject();
                    if (!empty($bb_b2_obj->useDirectLink)) {
                        $includeS3 = true;
                    }
                } elseif (!empty($ftp)) {
                    $includeS3 = true;
                }
                $token = "";
                $secure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
                if ((preg_match("/.*\\.mp3$/", $type) || preg_match("/.*\\.mp4$/", $type) || preg_match("/.*\\.webm$/", $type) || $type == ".m3u8" || $type == ".pdf" || $type == ".zip")) {
                    $vars = array();
                    if (!empty($secure)) {
                        $vars[] = $secure->getToken($filename);
                    }
                    if (!empty($vars)) {
                        $token = "?" . implode("&", $vars);
                    }
                }


                $paths = self::getPaths($filename);

                $source = array();
                $source['path'] = $paths['path'] . "{$filename}{$type}";

                if ($type == ".m3u8") {
                    $source['path'] = self::getStoragePath() . "{$filename}/index{$type}";
                }
                $cleanFileName = self::getCleanFilenameFromFile($filename);
                $video = Video::getVideoFromFileNameLight($cleanFileName);
                if (empty($video)) {
                    _error_log("Video::getSourceFile($filename, $type, $includeS3) ERROR video not found ($cleanFileName)");
                    $VideoGetSourceFile[$cacheName] = false;
                    return false;
                }
                $canUseCDN = canUseCDN($video['id']);

                if (!empty($video['sites_id']) && (preg_match("/.*\\.mp3$/", $type) || preg_match("/.*\\.mp4$/", $type) || preg_match("/.*\\.webm$/", $type) || $type == ".m3u8" || $type == ".pdf" || $type == ".zip") && @filesize($source['path']) < 20) {
                    $site = new Sites($video['sites_id']);
                    $siteURL = getCDNOrURL($site->getUrl(), 'CDN_YPTStorage', $video['sites_id']);
                    $source['url'] = "{$siteURL}{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$siteURL}videos/{$filename}/index{$type}{$token}";
                    }
                } elseif (!empty($advancedCustom->videosCDN) && $canUseCDN) {
                    $advancedCustom->videosCDN = rtrim($advancedCustom->videosCDN, '/') . '/';
                    $source['url'] = "{$advancedCustom->videosCDN}{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$advancedCustom->videosCDN}videos/{$filename}/index{$type}{$token}";
                    }
                } else {
                    $source['url'] = getCDN() . "{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = getCDN() . "videos/{$filename}/index{$type}{$token}";
                    }
                }
                /* need it because getDurationFromFile */
                if ($includeS3 && ($type == ".mp4" || $type == ".webm" || $type == ".mp3" || $type == ".ogg" || $type == ".pdf" || $type == ".zip")) {
                    if (file_exists($source['path']) && filesize($source['path']) < 1024) {
                        if (!empty($aws_s3)) {
                            $source = $aws_s3->getAddress("{$filename}{$type}");
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_S3');
                        } elseif (!empty($bb_b2)) {
                            $source = $bb_b2->getAddress("{$filename}{$type}");
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_B2');
                        } elseif (!empty($ftp)) {
                            $source = $ftp->getAddress("{$filename}{$type}");
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_FTP');
                        }
                    }
                }
                if (!file_exists($source['path']) || ($type !== ".m3u8" && !is_dir($source['path']) && (filesize($source['path']) < 1000 && filesize($source['path']) != 10))) {
                    if ($type != "_thumbsV2.jpg" && $type != "_thumbsSmallV2.jpg" && $type != "_portrait_thumbsV2.jpg" && $type != "_portrait_thumbsSmallV2.jpg") {
                        $VideoGetSourceFile[$cacheName] = array('path' => false, 'url' => false);
                        //if($type=='.jpg'){echo '----'.PHP_EOL;var_dump($type, $source);echo '----'.PHP_EOL;};
                        //echo PHP_EOL.'---'.PHP_EOL;var_dump($source, $type, !file_exists($source['path']), ($type !== ".m3u8" && !is_dir($source['path']) && (filesize($source['path']) < 1000 && filesize($source['path']) != 10)));echo PHP_EOL.'+++'.PHP_EOL;
                        return $VideoGetSourceFile[$cacheName];
                    }
                }

                $videosPaths[$filename][$type][intval($includeS3)] = $source;
            } else {
                $source = $videosPaths[$filename][$type][intval($includeS3)];
            }
            if (substr($type, -4) === ".jpg" || substr($type, -4) === ".png" || substr($type, -4) === ".gif" || substr($type, -4) === ".webp") {
                $x = uniqid();
                if (file_exists($source['path'])) {
                    $x = filemtime($source['path']);
                } elseif (!empty($video)) {
                    $x = strtotime($video['modified']);
                }
                $source['url'] .= "?{$x}";
            }

            //ObjectYPT::setCache($name, $source);
            $VideoGetSourceFile[$cacheName] = $source;
            return $VideoGetSourceFile[$cacheName];
        }

        private static function _moveSourceFilesToDir($videoFilename) {
            $videoFilename = self::getCleanFilenameFromFile($videoFilename);
            if (preg_match('/^(hd|low|sd|(res[0-9]{3,4}))$/', $videoFilename)) {
                return false;
            }
            $paths = self::getPaths($videoFilename);
            $lock = "{$paths['path']}.move_v1.lock";
            if (file_exists($lock)) {
                return true;
            }
            $videosDir = self::getStoragePath();
            make_path($paths['path']);
            $files = _glob($videosDir, '/' . $videoFilename . '[._][a-z0-9_]+/i');
            //var_dump($paths['path'], is_dir($paths['path']), $files);exit;
            foreach ($files as $oldname) {
                if (is_dir($oldname)) {
                    continue;
                }
                $newname = str_replace($videosDir, $paths['path'], $oldname);
                rename($oldname, $newname);
            }
            return file_put_contents($lock, time());
        }

        public static function getPaths($videoFilename, $createDir = false) {
            global $global, $__getPaths;
            if (!isset($__getPaths)) {
                $__getPaths = array();
            }
            if (!empty($__getPaths[$videoFilename])) {
                return $__getPaths[$videoFilename];
            }
            $cleanVideoFilename = self::getCleanFilenameFromFile($videoFilename);
            $videosDir = self::getStoragePath();
            if (is_dir("{$videosDir}{$videoFilename}")) {
                $path = addLastSlash("{$videosDir}{$videoFilename}");
                //} else if (preg_match('/index\.m3u8$/', $videoFilename)) {
                //    $path = addLastSlash($videosDir);                
            } else {
                $path = addLastSlash("{$videosDir}{$cleanVideoFilename}");
            }
            $path = fixPath($path);
            if ($createDir) {
                make_path(addLastSlash($path));
            }
            $relative = addLastSlash("videos/{$cleanVideoFilename}");
            $url = getCDN() . "{$relative}";
            $__getPaths[$videoFilename] = array('filename' => $cleanVideoFilename, 'path' => $path, 'url' => $url, 'relative' => $relative);
            return $__getPaths[$videoFilename];
        }

        public static function getPathToFile($videoFilename, $createDir = false) {
            $videosDir = self::getStoragePath();
            $videoFilename = str_replace($videosDir, '', $videoFilename);
            $paths = Video::getPaths($videoFilename, $createDir);
            if (preg_match('/index.m3u8$/', $videoFilename)) {
                $paths['path'] = rtrim($paths['path'], DIRECTORY_SEPARATOR);
                $videoFilename = str_replace($paths['filename'], '', $videoFilename);
            }
            return "{$paths['path']}{$videoFilename}";
        }

        public static function getURLToFile($videoFilename, $createDir = false) {
            $videosDir = self::getStoragePath();
            $videoFilename = str_replace($videosDir, '', $videoFilename);
            $paths = Video::getPaths($videoFilename, $createDir);
            return "{$paths['url']}{$videoFilename}";
        }

        public static function getURLToFileIfExists($videoFilename) {
            $paths = Video::getPaths($videoFilename);
            if (!file_exists("{$paths['path']}{$videoFilename}")) {
                return false;
            }
            return "{$paths['url']}{$videoFilename}";
        }

        public static function getNewVideoFilename($prefix = '', $time = '') {
            $uid = substr(uniqid(), -4);
            if (empty($time)) {
                $time = time();
            }
            $prefix = preg_replace('/[^a-z0-9]/i', '', $prefix);
            if (empty($prefix)) {
                $prefix = 'v';
            }
            $date = date('ymdHis', $time);
            $videoFilename = strtolower("{$prefix}_{$date}_{$uid}");
            return self::getPaths($videoFilename);
        }

        public static function isNewVideoFilename($filename) {
            $filename = self::getCleanFilenameFromFile($filename);
            return preg_match('/_([0-9]{12})_([0-9a-z]{4})$/i', $filename);
        }

        public static function getNewVideoFilenameWithPrefixFromFilename($filename) {
            $video = self::getVideoFromFileNameLight($filename);
            if (empty($video)) {
                return self::getNewVideoFilename();
            }
            return self::getNewVideoFilename($video['type']);
        }

        public static function updateDirectoryFilename($directory) {
            if (!is_dir($directory)) {
                _error_log('Video::updateDirectoryFilename directory not found ' . "[{$directory}]");
                return false;
            }
            $video = self::getVideoFromFileNameLight($directory);
            if (empty($video)) {
                _error_log('Video::updateDirectoryFilename video not found for directory ' . "[{$directory}]");
                return false;
            }

            if (isAnyStorageEnabled()) {
                $newFilename = self::getPaths($video['filename']);
                $id = $video['id'];
            } else {
                $newFilename = self::getNewVideoFilename($video['type'], strtotime($video['created']));
                $v = new Video('', '', $video['id']);
                $v->setFilename($newFilename['filename'], true);
                $id = $v->save(false, true);
            }

            if ($id) {
                $renamed = rename($directory, $newFilename['path']);
                if (empty($renamed)) { // rename dir fail rollback
                    _error_log('Video::updateDirectoryFilename rename dir fail, we will rollback changes ' . "[olddir={$directory}] [newdir={$newFilename['path']}]");
                    $v = new Video('', '', $video['id']);
                    $v->setFilename($video['filename'], true);
                    $id = $v->save(false, true);
                    return false;
                } else {
                    _error_log('Video::updateDirectoryFilename video folder renamed from ' . "[olddir={$directory}] [newdir={$newFilename['path']}]");
                    self::updateFilesInDirectoryFilename($newFilename['path']);
                }
            }

            return array('videos_id' => $video['id'], 'filename' => $newFilename['filename'], 'oldDir' => $directory, 'newDir' => $newFilename['path']);
        }

        public static function updateFilesInDirectoryFilename($directory) {
            if (!is_dir($directory)) {
                _error_log('Video::updateFilesInDirectoryFilename directory not found ' . "[{$directory}]");
                return false;
            }
            $video = self::getVideoFromFileNameLight($directory);
            if (empty($video)) {
                _error_log('Video::updateFilesInDirectoryFilename video not found for directory ' . "[{$directory}]");
                return false;
            }
            $newFilename = $video['filename'];
            $files = glob("{$directory}*.{jpg,png,gif,webp,vtt,srt,mp4,webm,mp3,ogg,notfound}", GLOB_BRACE);
            _error_log('Video::updateFilesInDirectoryFilename total files found ' . count($files));
            foreach ($files as $value) {
                $oldFilename = self::getCleanFilenameFromFile($value);
                $newFilenamePath = str_replace($oldFilename, $newFilename, $value);
                $renamed = rename($value, $newFilenamePath);
                if (empty($renamed)) { // rename dir fail rollback
                    _error_log('Video::updateFilesInDirectoryFilename rename file fail ' . "[olddir={$value}] [newdir={$newFilenamePath}]");
                } else {
                    _error_log('Video::updateFilesInDirectoryFilename video file renamed from ' . "[olddir={$value}] [newdir={$newFilenamePath}]");
                }
            }
        }

        public function getVideoIdHash() {
            $obj = new stdClass();
            $obj->videos_id = $this->id;
            return encryptString(json_encode($obj));
        }

        public static function getVideoIdFromHash($hash) {
            $string = decryptString($hash);
            if (!empty($string)) {
                $json = json_decode($string);
                if (!empty($json) && !empty($json->videos_id)) {
                    return $json->videos_id;
                }
            }
            return false;
        }

        public static function getCleanFilenameFromFile($filename) {
            global $global;
            if (empty($filename)) {
                return "";
            }
            $filename = fixPath($filename);
            $filename = str_replace(getVideosDir(), '', $filename);
            if (preg_match('/videos[\/\\\]([^\/\\\]+)[\/\\\].*index.m3u8$/', $filename, $matches)) {
                return $matches[1];
            }
            $search = array('_Low', '_SD', '_HD', '_thumbsV2', '_thumbsSmallV2', '_thumbsSprit', '_roku', '_portrait', '_portrait_thumbsV2', '_portrait_thumbsSmallV2', '_spectrum', '_tvg', '.notfound');

            if (!empty($global['langs_codes_values_withdot']) && is_array($global['langs_codes_values_withdot'])) {
                $search = array_merge($search, $global['langs_codes_values_withdot']);
            }

            if (empty($global['avideo_resolutions']) || !is_array($global['avideo_resolutions'])) {
                $global['avideo_resolutions'] = array(240, 360, 480, 540, 720, 1080, 1440, 2160);
            }

            foreach ($global['avideo_resolutions'] as $value) {
                $search[] = "_{$value}";

                $search[] = "res{$value}";
            }

            $cleanName = str_replace($search, '', $filename);
            $path_parts = pathinfo($cleanName);
            if (empty($path_parts['extension'])) {
                //_error_log("Video::getCleanFilenameFromFile could not find extension of ".$filename);
                if (!empty($path_parts['filename'])) {
                    return $path_parts['filename'];
                } else {
                    return $filename;
                }
            } else if (strlen($path_parts['extension']) > 4) {
                return $cleanName;
            } else if ($path_parts['filename'] == 'index' && $path_parts['extension'] == 'm3u8') {
                $parts = explode(DIRECTORY_SEPARATOR, $cleanName);
                if (!empty($parts[0])) {
                    return $parts[0];
                }
                return $parts[1];
            } else {
                return $path_parts['filename'];
            }
        }

        public static function getSpecificResolution($filename, $desired_resolution) {
            $filename = self::getCleanFilenameFromFile($filename);
            $cacheName = "getSpecificResolution($filename)";
            $return = ObjectYPT::getCache($cacheName, 0);
            if (!empty($return)) {
                return object_to_array($return);
            }
            $name0 = "Video:::getSpecificResolution($filename)";
            TimeLogStart($name0);
            $name1 = "Video:::getSpecificResolution::getVideosURL_V2($filename)";
            TimeLogStart($name1);
            $sources = getVideosURL_V2($filename);
            if (!is_array($sources)) {
                _error_log("Video:::getSpecificResolution::getVideosURL_V2($filename) does not return an array " . json_encode($sources));
                return array();
            }
            TimeLogEnd($name1, __LINE__);
            $return = array();
            foreach ($sources as $key => $value) {
                if ($value['type'] === 'video') {
                    $parts = explode("_", $key);
                    $resolution = intval(@$parts[1]);
                    if (empty($resolution)) {
                        $name2 = "Video:::getSpecificResolution::getResolution({$value["path"]})";
                        TimeLogStart($name2);
                        $resolution = self::getResolution($value["path"]);
                        TimeLogEnd($name2, __LINE__);
                    }
                    if (!isset($return['resolution']) || $resolution == $desired_resolution) {
                        $return = $value;
                        $return['resolution'] = $resolution;
                        $return['resolution_text'] = getResolutionText($return['resolution']);
                        $return['resolution_label'] = getResolutionLabel($return['resolution']);
                        $return['resolution_string'] = trim($resolution . "p {$return['resolution_label']}");
                    }
                }
            }
            TimeLogEnd($name0, __LINE__);
            ObjectYPT::setCache($cacheName, $return);
            return $return;
        }

        public static function getHigestResolution($filename) {
            global $global;
            $filename = self::getCleanFilenameFromFile($filename);
            $cacheName = "getHigestResolution($filename)";
            $return = ObjectYPT::getCache($cacheName, 0);
            if (!empty($return)) {
                return object_to_array($return);
            }
            $name0 = "Video:::getHigestResolution($filename)";
            TimeLogStart($name0);
            $name1 = "Video:::getHigestResolution::getVideosURL_V2($filename)";
            TimeLogStart($name1);
            $sources = getVideosURL_V2($filename);
            if (!is_array($sources)) {
                _error_log("Video:::getHigestResolution::getVideosURL_V2($filename) does not return an array " . json_encode($sources));
                return array();
            }
            TimeLogEnd($name1, __LINE__);
            $return = array();
            foreach ($sources as $key => $value) {
                if ($value['type'] === 'video') {
                    $parts = explode("_", $key);
                    $resolution = intval(@$parts[1]);
                    if (empty($resolution)) {
                        $name2 = "Video:::getHigestResolution::getResolution({$value["path"]})";
                        TimeLogStart($name2);
                        $resolution = self::getResolutionFromFilename($value["path"]); // this is faster
                        if ($resolution && empty($global['onlyGetResolutionFromFilename'])) {
                            _error_log("Video:::getHigestResolution:: could not get the resolution from file name [{$value["path"]}], trying a slower method");
                            $resolution = self::getResolution($value["path"]);
                        }
                        TimeLogEnd($name2, __LINE__);
                    }
                    if (!isset($return['resolution']) || $resolution > $return['resolution']) {
                        $return = $value;
                        $return['resolution'] = $resolution;
                        $return['resolution_text'] = getResolutionText($return['resolution']);
                        $return['resolution_label'] = getResolutionLabel($return['resolution']);
                        $return['resolution_string'] = trim($resolution . "p {$return['resolution_label']}");
                    }
                }
            }
            TimeLogEnd($name0, __LINE__);
            ObjectYPT::setCache($cacheName, $return);
            return $return;
        }

        public static function getResolutionFromFilename($filename) {
            $resolution = false;
            if (preg_match("/_([0-9]+).(mp4|webm)/i", $filename, $matches)) {
                if (!empty($matches[1])) {
                    $resolution = intval($matches[1]);
                }
            } elseif (preg_match('/res([0-9]+)\/index.m3u8/i', $filename, $matches)) {
                if (!empty($matches[1])) {
                    $resolution = intval($matches[1]);
                }
            }

            //var_dump($filename, $resolution);exit;
            return $resolution;
        }

        public static function getHigestResolutionVideoMP4Source($filename, $includeS3 = false) {
            global $global;
            $types = array('', '_HD', '_SD', '_Low');
            $resolutions = $global['avideo_resolutions'];
            rsort($resolutions);
            foreach ($resolutions as $value) {
                $types[] = "_{$value}";
            }
            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".mp4", $includeS3);
                if (!empty($source['url'])) {
                    return $source;
                }
            }
            return false;
        }

        public static function getHigherVideoPathFromID($videos_id) {
            global $global;
            if (empty($videos_id)) {
                return false;
            }
            $paths = self::getVideosPathsFromID($videos_id);

            $types = array(0, 2160, 1440, 1080, 720, 'HD', 'SD', 'Low', 540, 480, 360, 240);

            if (!empty($paths['mp4'])) {
                foreach ($types as $value) {
                    if (!empty($paths['mp4'][$value])) {
                        if (is_string($paths['mp4'][$value])) {
                            return $paths['mp4'][$value];
                        } else {
                            return $paths['mp4'][$value]["url"];
                        }
                    }
                }
            }
            if (!empty($paths['webm'])) {
                foreach ($types as $value) {
                    if (!empty($paths['webm'][$value])) {
                        if (is_string($paths['webm'][$value])) {
                            return $paths['webm'][$value];
                        } else {
                            return $paths['webm'][$value]["url"];
                        }
                    }
                }
            }
            if (!empty($paths['m3u8'])) {
                if (!empty($paths['m3u8'])) {
                    if (is_string($paths['m3u8']["url"])) {
                        return $paths['m3u8']["url"];
                    } elseif (is_string($paths['m3u8'][$value])) {
                        return $paths['m3u8'][$value];
                    } else {
                        return $paths['m3u8'][$value]["url"];
                    }
                }
            }
            if (!empty($paths['mp3'])) {
                return $paths['mp3'];
            }
            return false;
        }

        public static function getVideosPathsFromID($videos_id) {
            if (empty($videos_id)) {
                return false;
            }
            $video = new Video("", "", $videos_id);
            return self::getVideosPaths($video->getFilename(), true);
        }

        public static function getVideosPaths($filename, $includeS3 = false) {
            global $global;
            $types = array('', '_Low', '_SD', '_HD');

            foreach ($global['avideo_resolutions'] as $value) {
                $types[] = "_{$value}";
            }

            $videos = array();

            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
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
            $source = self::getSourceFile($filename, ".zip", $includeS3);
            if (!empty($source['url'])) {
                $videos['zip'] = $source['url'];
            }
            $source = self::getSourceFile($filename, ".mp3", $includeS3);
            if (!empty($source['url'])) {
                $videos['mp3'] = $source['url'];
            }
            return $videos;
        }

        public static function getStoragePath() {
            global $global;
            $path = "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR;
            return $path;
        }

        public static function getStoragePathFromFileName($filename) {
            $cleanFileName = self::getCleanFilenameFromFile($filename);
            $path = self::getStoragePath() . "{$cleanFileName}/";
            make_path($path);
            return $path;
        }

        public static function getStoragePathFromVideosId($videos_id) {
            $v = new Video("", "", $videos_id);
            return self::getStoragePathFromFileName($v->getFilename());
        }

        public static function getImageFromFilename($filename, $type = "video", $async = false) {
            global $advancedCustom;
            // I dont know why but I had to remove it to avoid ERR_RESPONSE_HEADERS_TOO_BIG
            header_remove('Set-Cookie');
            if (!$async) {
                return self::getImageFromFilename_($filename, $type);
            } else {
                return self::getImageFromFilenameAsync($filename, $type);
            }
        }

        public static function getPoster($videos_id) {
            $images = self::getImageFromID($videos_id);
            if (!empty($images->poster)) {
                return $images->poster;
            }
            if (!empty($images->posterPortrait)) {
                return $images->poster;
            }
            return false;
        }

        public static function getRokuImage($videos_id) {
            global $global;
            $images = self::getImageFromID($videos_id);
            $imagePath = $images->posterLandscapePath;
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->posterLandscapeThumbs;
            }
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->poster;
            }
            $rokuImage = str_replace(".jpg", "_roku.jpg", $imagePath);
            if (convertImageToRoku($images->posterLandscapePath, $rokuImage)) {
                return str_replace($global['systemRootPath'], $global['webSiteRootURL'], $rokuImage);
            }
            return "" . getCDN() . "view/img/notfound.jpg";
        }

        public static function clearImageCache($filename, $type = "video") {
            $cacheFileName = "getImageFromFilename_" . $filename . $type . (get_browser_name() == 'Safari' ? "s" : "");
            return ObjectYPT::deleteCache($cacheFileName);
        }

        public static function getImageFromFilename_($filename, $type = "video") {
            if (empty($filename)) {
                return array();
            }
            global $_getImageFromFilename_;
            if (empty($_getImageFromFilename_)) {
                $_getImageFromFilename_ = array();
            }

            $cacheFileName = "getImageFromFilename_" . $filename . $type . (get_browser_name() == 'Safari' ? "s" : "");
            if (!empty($_getImageFromFilename_[$cacheFileName])) {
                $obj = $_getImageFromFilename_[$cacheFileName];
            } else {
                $cache = ObjectYPT::getCache($cacheFileName, 0);
                if (!empty($cache)) {
                    return $cache;
                }
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
                $spectrumSource = self::getSourceFile($filename, "_spectrum.jpg");
                if (empty($jpegSource)) {
                    return array();
                }
                $obj->poster = $jpegSource['url'];
                $obj->posterPortrait = $jpegPortraitSource['url'];
                $obj->posterPortraitPath = $jpegPortraitSource['path'];
                $obj->posterPortraitThumbs = $jpegPortraitThumbs['url'];
                $obj->posterPortraitThumbsSmall = $jpegPortraitThumbsSmall['url'];
                $obj->thumbsGif = $gifSource['url'];
                $obj->gifPortrait = $gifPortraitSource['url'];
                $obj->thumbsJpg = $thumbsSource['url'];
                $obj->thumbsJpgSmall = $thumbsSmallSource['url'];
                $obj->spectrumSource = $spectrumSource['url'];

                $obj->posterLandscape = $jpegSource['url'];
                $obj->posterLandscapePath = $jpegSource['path'];
                $obj->posterLandscapeThumbs = $thumbsSource['url'];
                $obj->posterLandscapeThumbsSmall = $thumbsSmallSource['url'];

                if (file_exists($gifSource['path'])) {
                    $obj->thumbsGif = $gifSource['url'];
                }
                if (file_exists($jpegPortraitSource['path'])) {
                    // create thumbs
                    if (!file_exists($jpegPortraitThumbs['path']) && filesize($jpegPortraitSource['path']) > 1024) {
                        _error_log("Resize JPG {$jpegPortraitSource['path']}, {$jpegPortraitThumbs['path']}");
                        if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                            im_resizeV3($jpegPortraitSource['path'], $jpegPortraitThumbs['path'], $advancedCustom->thumbsWidthPortrait, $advancedCustom->thumbsHeightPortrait);
                        } else {
                            im_resizeV2($jpegPortraitSource['path'], $jpegPortraitThumbs['path'], $advancedCustom->thumbsWidthPortrait, $advancedCustom->thumbsHeightPortrait);
                        }
                    }
                    // create thumbs
                    if (!file_exists($jpegPortraitThumbsSmall['path']) && filesize($jpegPortraitSource['path']) > 1024) {
                        _error_log("Resize JPG {$jpegPortraitSource['path']}, {$jpegPortraitThumbsSmall['path']}");
                        if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                            im_resizeV3($jpegPortraitSource['path'], $jpegPortraitThumbsSmall['path'], $advancedCustom->thumbsWidthPortrait, $advancedCustom->thumbsHeightPortrait);
                        } else {
                            im_resizeV2($jpegPortraitSource['path'], $jpegPortraitThumbsSmall['path'], $advancedCustom->thumbsWidthPortrait, $advancedCustom->thumbsHeightPortrait, 5);
                        }
                    }
                } else {
                    if ($type == "article") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/article_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/article_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/article_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/article_portrait.png";
                    } elseif ($type == "pdf") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/pdf_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/pdf_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/pdf_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/pdf_portrait.png";
                    } /* else if ($type == "image") {
                      $obj->posterPortrait = "".getCDN()."view/img/image_portrait.png";
                      $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/image_portrait.png";
                      $obj->posterPortraitThumbs = "".getCDN()."view/img/image_portrait.png";
                      $obj->posterPortraitThumbsSmall = "".getCDN()."view/img/image_portrait.png";
                      } */ elseif ($type == "zip") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/zip_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/zip_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/zip_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/zip_portrait.png";
                    } else {
                        $obj->posterPortrait = "" . getCDN() . "view/img/notfound_portrait.jpg";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/notfound_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/notfound_portrait.jpg";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/notfound_portrait.jpg";
                    }
                }

                if (file_exists($jpegSource['path'])) {
                    $obj->poster = $jpegSource['url'];
                    $obj->thumbsJpg = $thumbsSource['url'];
                    // create thumbs
                    if (!file_exists($thumbsSource['path']) && filesize($jpegSource['path']) > 1024) {
                        _error_log("Resize JPG {$jpegSource['path']}, {$thumbsSource['path']}");
                        if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                            im_resizeV3($jpegSource['path'], $thumbsSource['path'], $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape);
                        } else {
                            im_resizeV2($jpegSource['path'], $thumbsSource['path'], $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape);
                        }
                    }
                    // create thumbs
                    if (!file_exists($thumbsSmallSource['path']) && filesize($jpegSource['path']) > 1024) {
                        _error_log("Resize Small JPG {$jpegSource['path']}, {$thumbsSmallSource['path']}");
                        if (!empty($advancedCustom->useFFMPEGToGenerateThumbs)) {
                            im_resizeV3($jpegSource['path'], $thumbsSmallSource['path'], $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape);
                        } else {
                            im_resizeV2($jpegSource['path'], $thumbsSmallSource['path'], $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape, 5);
                        }
                    }
                } else {
                    if ($type == "article") {
                        $obj->poster = "" . getCDN() . "view/img/article.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/article.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/article.png";
                    } elseif ($type == "pdf") {
                        $obj->poster = "" . getCDN() . "view/img/pdf.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/pdf.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/pdf.png";
                    } elseif ($type == "image") {
                        $obj->poster = "" . getCDN() . "view/img/image.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/image.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/image.png";
                    } elseif ($type == "zip") {
                        $obj->poster = "" . getCDN() . "view/img/zip.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/zip.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/zip.png";
                    } elseif (($type !== "audio") && ($type !== "linkAudio")) {
                        if (file_exists($spectrumSource['path'])) {
                            $obj->poster = $spectrumSource['url'];
                            $obj->thumbsJpg = $spectrumSource['url'];
                            $obj->thumbsJpgSmall = $spectrumSource['url'];
                        } else {
                            $obj->poster = "" . getCDN() . "view/img/notfound.jpg";
                            $obj->thumbsJpg = "" . getCDN() . "view/img/notfoundThumbs.jpg";
                            $obj->thumbsJpgSmall = "" . getCDN() . "view/img/notfoundThumbsSmall.jpg";
                        }
                    } else {
                        $obj->poster = "" . getCDN() . "view/img/audio_wave.jpg";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/audio_waveThumbs.jpg";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/audio_waveThumbsSmall.jpg";
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

                ObjectYPT::setCache($cacheFileName, $obj);
                $_getImageFromFilename_[$cacheFileName] = $obj;
            }

            return $obj;
        }

        public static function getImageFromFilenameAsync($filename, $type = "video") {
            global $global, $advancedCustom;
            $return = array();
            $path = getCacheDir() . "getImageFromFilenameAsync/";
            make_path($path);
            $cacheFileName = "{$path}_{$filename}_{$type}";
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return array();
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getImageFromFilename_($filename, $type);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getImageFromFilenameAsync.php' '$filename' '$type' '{$cacheFileName}'");
                //_error_log("getImageFromFilenameAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        public static function getImageFromID($videos_id, $type = "video") {
            $video = new Video("", "", $videos_id);
            return self::getImageFromFilename($video->getFilename());
        }

        public function getViews_count() {
            return intval($this->views_count);
        }

        public static function get_clean_title($videos_id) {
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

        public static function get_id_from_clean_title($clean_title) {
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

        public function getChannelName() {
            return User::_getChannelName($this->getUsers_id());
        }

        public function getChannelLink() {
            return User::getChannelLink($this->getUsers_id());
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
        public static function getLinkToVideo($videos_id, $clean_title = "", $embed = false, $type = "URLFriendly", $get = array()) {
            if (!empty($_GET['evideo'])) {
                $v = self::decodeEvideo();
                if (!empty($v['video']['videoLink'])) {
                    if ($embed) {
                        return parseVideos($v['video']['videoLink']);
                    } else {
                        return $v['video']['videoLink'];
                    }
                }
            }

            global $global, $advancedCustomUser, $advancedCustom;
            if (!is_object($advancedCustomUser)) {
                $advancedCustomUser = AVideoPlugin::getDataObject('CustomizeUser');
            }
            if (empty($videos_id) && !empty($clean_title)) {
                $videos_id = self::get_id_from_clean_title($clean_title);
            }
            $video = new Video("", "", $videos_id);

            if ($advancedCustomUser->addChannelNameOnLinks) {
                $get['channelName'] = $video->getChannelName();
            }

            unset($get['v'], $get['videoName'], $get['videoName'], $get['isMediaPlaySite'], $get['parentsOnly']);
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

                if (empty($clean_title)) {
                    $clean_title = $video->getClean_title();
                }
                $clean_title = urlencode($clean_title);
                $subDir = "video";
                $subEmbedDir = "videoEmbed";
                if ($video->getType() == 'article') {
                    $subDir = "article";
                    $subEmbedDir = "articleEmbed";
                }
                if (!empty($advancedCustom->makeVideosIDHarderToGuess)) {
                    $encryptedVideos_id = '.' . idToHash($videos_id);
                    $videos_id = $encryptedVideos_id;
                }
                if ($embed) {
                    if (empty($advancedCustom->useVideoIDOnSEOLinks)) {
                        return "{$global['webSiteRootURL']}{$subEmbedDir}/{$clean_title}{$get_http}";
                    } else {
                        return "{$global['webSiteRootURL']}{$subEmbedDir}/{$videos_id}/{$clean_title}{$get_http}";
                    }
                } else {
                    if (empty($advancedCustom->useVideoIDOnSEOLinks)) {
                        return "{$global['webSiteRootURL']}{$cat}{$subDir}/{$clean_title}{$get_http}";
                    } else {
                        return "{$global['webSiteRootURL']}{$subDir}/{$videos_id}/{$clean_title}{$get_http}";
                    }
                }
            } else {
                if (!empty($advancedCustom->makeVideosIDHarderToGuess)) {
                    $encryptedVideos_id = '.' . idToHash($videos_id);
                    $videos_id = $encryptedVideos_id;
                }
                if ($embed) {
                    return "{$global['webSiteRootURL']}vEmbed/{$videos_id}{$get_http}";
                } else {
                    return "{$global['webSiteRootURL']}v/{$videos_id}{$get_http}";
                }
            }
        }

        public static function getPermaLink($videos_id, $embed = false, $get = array()) {
            return self::getLinkToVideo($videos_id, "", $embed, "permalink", $get);
        }

        public static function getURLFriendly($videos_id, $embed = false, $get = array()) {
            return self::getLinkToVideo($videos_id, "", $embed, "URLFriendly", $get);
        }

        public static function getPermaLinkFromCleanTitle($clean_title, $embed = false, $get = array()) {
            return self::getLinkToVideo("", $clean_title, $embed, "permalink", $get);
        }

        public static function getURLFriendlyFromCleanTitle($clean_title, $embed = false, $get = array()) {
            return self::getLinkToVideo("", $clean_title, $embed, "URLFriendly", $get);
        }

        public static function getLink($videos_id, $clean_title, $embed = false, $get = array()) {
            global $advancedCustom;
            if (!empty($advancedCustom->usePermalinks)) {
                $type = "permalink";
            } else {
                $type = "URLFriendly";
            }

            return self::getLinkToVideo($videos_id, $clean_title, $embed, $type, $get);
        }

        public static function getTotalVideosThumbsUpFromUser($users_id, $startDate, $endDate) {
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

        public static function deleteThumbs($filename, $doNotDeleteSprit = false) {
            if (empty($filename)) {
                return false;
            }
            global $global;

            $filePath = Video::getPathToFile($filename);
            // Streamlined for less coding space.
            $files = glob("{$filePath}*_thumbs*.jpg");
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if ($doNotDeleteSprit && strpos($file, '_thumbsSprit.jpg') !== false) {
                        continue;
                    }
                    @unlink($file);
                }
            }
            ObjectYPT::deleteCache($filename);
            ObjectYPT::deleteCache($filename . "article");
            ObjectYPT::deleteCache($filename . "pdf");
            ObjectYPT::deleteCache($filename . "video");
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, "article");
            Video::clearImageCache($filename, "pdf");
            Video::clearImageCache($filename, "audio");
            clearVideosURL($filename);
            return true;
        }

        public static function deleteGifAndWebp($filename) {
            if (empty($filename)) {
                return false;
            }
            global $global;

            $filePath = Video::getPathToFile($filename);
            @unlink("{$filePath}.gif");
            @unlink("{$filePath}.webp");
            ObjectYPT::deleteCache($filename);
            ObjectYPT::deleteCache($filename . "article");
            ObjectYPT::deleteCache($filename . "pdf");
            ObjectYPT::deleteCache($filename . "video");
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, "article");
            Video::clearImageCache($filename, "pdf");
            Video::clearImageCache($filename, "audio");
            clearVideosURL($filename);
            return true;
        }

        public static function clearCache($videos_id) {
            _error_log("Video:clearCache($videos_id)");
            $video = new Video("", "", $videos_id);
            $filename = $video->getFilename();
            if (empty($filename)) {
                _error_log("Video:clearCache filename not found");
                return false;
            }
            self::deleteThumbs($filename, true);
            ObjectYPT::deleteCache("otherInfo{$videos_id}");
            ObjectYPT::deleteCache($filename);
            ObjectYPT::deleteCache("getVideosURL_V2$filename");
            ObjectYPT::deleteCache($filename . "article");
            ObjectYPT::deleteCache($filename . "pdf");
            ObjectYPT::deleteCache($filename . "video");
            ObjectYPT::deleteCache(md5($filename . ".m3u8"));
            ObjectYPT::deleteCache(md5($filename . ".mp4"));
            ObjectYPT::deleteCache(md5($filename . ".m3u81"));
            ObjectYPT::deleteCache(md5($filename . ".mp41"));
            ObjectYPT::deleteCache("getSourceFile($filename)1");
            ObjectYPT::deleteCache("getSourceFile($filename)0");
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, "article");
            Video::clearImageCache($filename, "pdf");
            Video::clearImageCache($filename, "audio");
            Video::deleteTagsAsync($videos_id);
            clearVideosURL($filename);
            AVideoPlugin::deleteVideoTags($videos_id);
            ObjectYPT::setLastDeleteALLCacheTime();
            return true;
        }

        public static function clearCacheFromFilename($fileName) {
            if ($fileName == '.zip') {
                return false;
            }
            _error_log("Video:clearCacheFromFilename($fileName)");
            $video = self::getVideoFromFileNameLight($fileName);
            if (empty($video['id'])) {
                return false;
            }
            return self::clearCache($video['id']);
        }

        public static function getVideoPogress($videos_id, $users_id = 0) {
            if (empty($users_id)) {
                if (!User::isLogged()) {
                    return 0;
                }
                $users_id = User::getId();
            }

            return VideoStatistic::getLastVideoTimeFromVideo($videos_id, $users_id);
        }

        public static function getLastVideoTimePosition($videos_id, $users_id = 0) {
            return self::getVideoPogress($videos_id, $users_id);
        }

        public static function getVideoPogressPercent($videos_id, $users_id = 0) {
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

        public function getRrating() {
            return $this->rrating;
        }

        public function setRrating($rrating) {
            $rrating = strtolower($rrating);
            if (!in_array($rrating, self::$rratingOptions)) {
                $rrating = '';
            }
            AVideoPlugin::onVideoSetRrating($this->id, $this->rrating, $rrating);
            $this->rrating = $rrating;
        }

        public static function getVideoType($filename) {
            global $_getVideoType;

            if (!isset($_getVideoType)) {
                $_getVideoType = array();
            }
            if (isset($_getVideoType[$filename])) {
                return $_getVideoType[$filename];
            }

            $obj = new stdClass();
            $paths = self::getVideosPaths($filename);

            $obj->mp4 = !empty($paths['mp4']) ? true : false;
            $obj->webm = !empty($paths['webm']) ? true : false;
            $obj->m3u8 = !empty($paths['m3u8']) ? true : false;
            $obj->pdf = !empty($paths['pdf']) ? true : false;
            $obj->mp3 = !empty($paths['mp3']) ? true : false;

            $_getVideoType[$filename] = $obj;
            return $obj;
        }

        public static function getVideoTypeLabels($filename) {
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

        /**
         * Based on Roku Type
         * @param type $filename
         * @return string
         */
        public static function getVideoTypeText($filename) {
            $obj = self::getVideoType($filename);
            $labels = "";
            if (empty($obj->mp4) && empty($obj->webm) && empty($obj->m3u8) && empty($obj->pdf) && empty($obj->mp3)) {
                return __('Other');
            }
            if ($obj->mp4) {
                return 'MP4';
            }
            if ($obj->webm) {
                return 'WEBM';
            }
            if ($obj->m3u8) {
                return 'HLS';
            }
            if ($obj->pdf) {
                return 'PDF';
            }
            if ($obj->mp3) {
                return 'MP3';
            }
            return $labels;
        }

        public static function isPublic($videos_id) {
            // check if the video is not public
            $rows = UserGroups::getVideoGroups($videos_id);

            if (empty($rows)) {
                return true;
            }
            return false;
        }

        public static function userGroupAndVideoGroupMatch($users_id, $videos_id) {
            if (empty($videos_id)) {
                return false;
            }

            $ppv = AVideoPlugin::loadPluginIfEnabled("PayPerView");
            if ($ppv) {
                $ppv->userCanWatchVideo($users_id, $videos_id);
            }
            // check if the video is not public
            $rows = UserGroups::getVideoGroups($videos_id);
            if (empty($rows)) {
                return true;
            }

            if (empty($users_id)) {
                return false;
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

        public function getExternalOptions() {
            return $this->externalOptions;
        }

        public function setExternalOptions($externalOptions) {
            AVideoPlugin::onVideoSetExternalOptions($this->id, $this->externalOptions, $externalOptions);
            $this->externalOptions = $externalOptions;
        }

        public function setVideoStartSeconds($videoStartSeconds) {
            $externalOptions = _json_decode($this->getExternalOptions());
            AVideoPlugin::onVideoSetVideoStartSeconds($this->id, $this->videoStartSeconds, $videoStartSeconds);
            $externalOptions->videoStartSeconds = intval($videoStartSeconds);
            $this->setExternalOptions(json_encode($externalOptions));
        }

        public function setVideoEmbedWhitelist($embedWhitelist) {
            $externalOptions = _json_decode($this->getExternalOptions());
            $externalOptions->embedWhitelist = $embedWhitelist;
            $this->setExternalOptions(json_encode($externalOptions));
        }

        public function getVideoEmbedWhitelist() {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (empty($externalOptions->embedWhitelist)) {
                return '';
            }
            return $externalOptions->embedWhitelist;
        }

        static public function getEmbedWhitelist($videos_id) {
            $v = new Video('', '', $videos_id);
            return $v->getVideoEmbedWhitelist();
        }

        public function getSerie_playlists_id() {
            return $this->serie_playlists_id;
        }

        public function setSerie_playlists_id($serie_playlists_id) {
            AVideoPlugin::onVideoSetSerie_playlists_id($this->id, $this->serie_playlists_id, $serie_playlists_id);
            $this->serie_playlists_id = $serie_playlists_id;
        }

        public static function getVideoFromSeriePlayListsId($serie_playlists_id) {
            global $global, $config;
            $serie_playlists_id = intval($serie_playlists_id);
            $sql = "SELECT * FROM videos WHERE serie_playlists_id = '$serie_playlists_id' LIMIT 1";
            $res = sqlDAL::readSql($sql, "", array(), true);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $video;
        }

        /**
         * if will show likes, comments, share, etc
         * @return boolean
         */
        public static function showYoutubeModeOptions() {
            global $video;
            if (!empty($_GET['evideo'])) {
                $v = self::decodeEvideo();
                if (empty($v['video']['views_count'])) {
                    return false;
                } else {
                    return true;
                }
            }
            if (empty($video) || $video['type'] === 'notfound') {
                return false;
            }
            return true;
        }

        public static function decodeEvideo() {
            $evideo = false;
            if (!empty($_GET['evideo'])) {
                $evideo = _json_decode(decryptString($_GET['evideo']));
            }
            $video = array();
            if (!empty($evideo)) {
                $video['id'] = 0;
                $video['type'] = 'embed';
                $video['rotation'] = 0;
                $video['videoLink'] = $evideo->videoLink;
                $video['title'] = $evideo->title;
                $video['clean_title'] = preg_replace('/[!#$&\'()*+,\\/:;=?@[\\] ]+/', '-', trim(strtolower(cleanString($evideo->title))));
                if (empty($evideo->description) && !empty($evideo->videos_id)) {
                    $divId = uniqid();
                    $video['description'] = '<div id="' . $divId . '"></div>
                    <script>
                        $(document).ready(function () {
                            $.ajax({
                                url: "' . $evideo->webSiteRootURL . 'plugin/API/get.json.php?APIName=video&videos_id=' . $evideo->videos_id . '",
                                success: function (response) {
                                    if(!response.error && response.response.rows[0] && response.response.rows[0].description){
                                        $("#' . $divId . '").html(response.response.rows[0].description);
                                    }

                                }
                            });
                        });
                    </script>';
                } else {
                    $video['description'] = @$evideo->description;
                }

                $video['duration'] = @$evideo->duration;
                $video['creator'] = @$evideo->creator;
                $video['likes'] = "";
                $video['dislikes'] = "";
                $video['category'] = "embed";
                $video['views_count'] = intval(@$evideo->views_count);
            }
            return array('evideo' => $evideo, 'video' => $video);
        }

        private static function getBlockedUsersIdsArray($users_id = 0) {
            if (empty($users_id)) {
                $users_id = intval(User::getId());
            }
            if (empty($users_id)) {
                return array();
            }
            if (!User::isLogged()) {
                return array();
            }
            $report = AVideoPlugin::getDataObjectIfEnabled("ReportVideo");
            if (empty($report)) {
                return array();
            }
            return ReportVideo::getAllReportedUsersIdFromUser($users_id);
        }

        public static function getIncludeType($video) {
            $vType = $video['type'];
            if ($vType == 'linkVideo') {
                if (!preg_match('/m3u8/', $video['videoLink'])) {
                    $vType = isHTMLPage($video['videoLink']) ? 'embed' : 'video';
                } else {
                    $vType = 'video';
                }
            } elseif ($vType == 'live') {
                $vType = '../../plugin/Live/view/liveVideo';
            } elseif ($vType == 'linkAudio') {
                $vType = 'audio';
            }
            if (!in_array($vType, Video::$typeOptions)) {
                $vType = 'video';
            }
            return $vType;
        }

        private static function getFullTextSearch($columnsArray, $search, $connection = "OR") {
            global $global;
            $search = $global['mysqli']->real_escape_string(xss_esc($search));
            if (empty($columnsArray) || empty($search)) {
                return "";
            }
            $sql = "(";
            $matches = array();
            foreach ($columnsArray as $value) {
                $matches[] = " (MATCH({$value}) AGAINST ('{$search}' IN NATURAL LANGUAGE MODE)) ";
            }
            $sql .= implode(" OR ", $matches);
            $sql .= ")";
            return "{$connection} {$sql}";
        }

        public static function getChangeVideoStatusButton($videos_id) {

            $video = new Video('', '', $videos_id);
            $status = $video->getStatus();

            $activeBtn = '<button onclick="changeVideoStatus(' . $videos_id . ', \'u\');" style="color: #090" type="button" '
                    . 'class="btn btn-default btn-xs getChangeVideoStatusButton_a" data-toggle="tooltip" title="' . str_replace("'", "\\'", __("This video is Active and Listed, click here to unlist it")) . '"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
            $inactiveBtn = '<button onclick="changeVideoStatus(' . $videos_id . ', \'a\');" style="color: #A00" type="button" '
                    . 'class="btn btn-default btn-xs getChangeVideoStatusButton_i"  data-toggle="tooltip" title="' . str_replace("'", "\\'", __("This video is inactive, click here to activate it")) . '"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></button>';
            $unlistedBtn = '<button onclick="changeVideoStatus(' . $videos_id . ', \'i\');" style="color: #BBB" type="button" '
                    . 'class="btn btn-default btn-xs getChangeVideoStatusButton_u"  data-toggle="tooltip" title="' . str_replace("'", "\\'", __("This video is unlisted, click here to inactivate it")) . '"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';

            return "<span class='getChangeVideoStatusButton getChangeVideoStatusButton_{$videos_id} status_{$status}'>{$activeBtn}{$inactiveBtn}{$unlistedBtn}</span>";
        }

        static function canVideoBePurchased($videos_id) {
            global $global;
            $obj = new stdClass();
            $obj->plugin = '';
            $obj->buyURL = '';
            $obj->canVideoBePurchased = false;
            // check for Subscription plugin
            if (AVideoPlugin::isEnabledByName('Subscription')) {
                $sub = new Subscription();
                $plans = $sub->getPlansFromVideo($videos_id);
                if (!empty($plans)) {
                    $obj->plugin = 'Subscription';
                    $obj->buyURL = "{$global['webSiteRootURL']}plugin/Subscription/showPlans.php?videos_id={$videos_id}";
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }

            // check for PPV plugin
            if (AVideoPlugin::isEnabledByName('PayPerView')) {
                if (PayPerView::isVideoPayPerView($videos_id) || $obj->onlyPlayVideosWithPayPerViewActive) {
                    $url = "{$global['webSiteRootURL']}plugin/PayPerView/page/buy.php";
                    if (isSerie()) {
                        $redirectUri = getSelfURI();
                    } else {
                        $redirectUri = getRedirectToVideo($videos_id);
                    }
                    if (!empty($redirectUri)) {
                        $url = addQueryStringParameter($url, 'redirectUri', $redirectUri);
                    }
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);
                    $obj->plugin = 'PayPerView';
                    $obj->buyURL = $url;
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }

            // check for fansSubscription
            if (AVideoPlugin::isEnabledByName('FansSubscriptions')) {
                if (FansSubscriptions::hasPlansFromVideosID($videos_id)) {
                    $url = "{$global['webSiteRootURL']}plugin/FansSubscriptions/View/buy.php";
                    if (isSerie()) {
                        $redirectUri = getSelfURI();
                    } else {
                        $redirectUri = getRedirectToVideo($videos_id);
                    }
                    if (!empty($redirectUri)) {
                        $url = addQueryStringParameter($url, 'redirectUri', $redirectUri);
                    }
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);
                    $obj->plugin = 'FansSubscriptions';
                    $obj->buyURL = $url;
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }
            return false;
        }

        static function getCreatorHTML($users_id, $html = '', $small=false) {
            global $global;
            if($small){
                $template = $global['systemRootPath'] . 'view/videoCreatorSmall.html';
            }else{
                $template = $global['systemRootPath'] . 'view/videoCreator.html';
            }
            $content = local_get_contents($template);
            $name = User::getNameIdentificationById($users_id);

            $search = array(
                '{photo}',
                '{channelLink}',
                '{name}',
                '{icon}',
                '{subscriptionButton}',
                '{html}');

            $replace = array(
                User::getPhoto($users_id),
                User::getChannelLink($users_id),
                strip_tags($name),
                User::getEmailVerifiedIcon($users_id),
                Subscribe::getButton($users_id),
                $html
            );

            $btnHTML = str_replace($search, $replace, $content);
            return $btnHTML;
        }

        static function getVideosListItem($videos_id, $divID='', $style='') {
            global $global, $advancedCustom;
            if(empty($divID)){
                $divID = "divVideo-{$videos_id}";
            }
            $objGallery = AVideoPlugin::getObjectData("Gallery");
            $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
            $template = $global['systemRootPath'] . 'view/videosListItem.html';
            $content = local_get_contents($template);
            $value = Video::getVideoLight($videos_id);
            $link = Video::getLink($value['id'], $value['clean_title'], "", $get);
            if (!empty($_GET['page']) && $_GET['page'] > 1) {
                $link = addQueryStringParameter($link, 'page', $_GET['page']);
            }

            $title = $value['title'];

            $images = Video::getImageFromFilename($value['filename'], $value['type']);

            if (!is_object($images)) {
                $images = new stdClass();
                $images->thumbsGif = "";
                $images->poster = getCDN() . "view/img/notfound.jpg";
                $images->thumbsJpg = getCDN() . "view/img/notfoundThumbs.jpg";
                $images->thumbsJpgSmall = getCDN() . "view/img/notfoundThumbsSmall.jpg";
            }
            $imgJPGLow = $images->thumbsJpgSmall;
            $imgJPGHight = $images->thumbsJpg;
            $imgGif = $images->thumbsGif;
            $imgGifHTML = '';

            if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
                $imgGif = $images->gifPortrait;
                $imgJPGHight = $images->posterPortrait;
            }
            if (!empty($imgGif)) {
                $imgGifHTML = '<img src="' . getCDN() . 'view/img/loading-gif.png" data-src="' . $imgGif . '" style="position: absolute; top: 0; display: none;" alt="' . $title . '" id="thumbsGIF' . $videos_id . '" class="thumbsGIF img-responsive" height="130" />';
            }

            $timeHTML = '';
            if (isToShowDuration($value['type'])) {
                $timeHTML = '<time class="duration" datetime="' . Video::getItemPropDuration($value['duration']) . '">' . Video::getCleanDuration($value['duration']) . '</time>';
            }

            $loggedUserHTML = '';
            if (User::isLogged() && !empty($program)) {
                $value['favoriteId'] = self::getFavoriteIdFromUser(User::getId());
                $value['watchLaterId'] = self::getWatchLaterIdFromUser(User::getId());
                if ($value['isWatchLater']) {
                    $watchLaterBtnAddedStyle = "";
                    $watchLaterBtnStyle = "display: none;";
                } else {
                    $watchLaterBtnAddedStyle = "display: none;";
                    $watchLaterBtnStyle = "";
                }
                if ($value['isFavorite']) {
                    $favoriteBtnAddedStyle = "";
                    $favoriteBtnStyle = "display: none;";
                } else {
                    $favoriteBtnAddedStyle = "display: none;";
                    $favoriteBtnStyle = "";
                }
                $loggedUserHTML = '<div class="galleryVideoButtons">';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', false, ' . $value['watchLaterId'] . ');return false;" '
                        . 'class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded' . $value['id'] . '" '
                        . 'title="' . __("Added On Watch Later") . '" style="color: #4285f4;' . $watchLaterBtnAddedStyle . '" ><i class="fas fa-check"></i></button> ';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', true, ' . $value['watchLaterId'] . ');return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn' . $value['id'] . '" title="' . __("Watch Later") . '" style="' . $watchLaterBtnStyle . '" ><i class="fas fa-clock"></i></button>';
                $loggedUserHTML .= '<br>';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', false, ' . $value['favoriteId'] . ');return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded' . $value['id'] . '" title="' . __("Added On Favorite") . '" style="color: #4285f4; ' . $favoriteBtnAddedStyle . '"><i class="fas fa-check"></i></button>  ';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', true, ' . $value['favoriteId'] . ');return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn' . $value['id'] . '" title="' . __("Favorite") . '" style="' . $favoriteBtnStyle . '" ><i class="fas fa-heart" ></i></button>    ';
                $loggedUserHTML .= '</div>';
            }
            $progress = self::getVideoPogressPercent($value['id']);;
            
            $category = new Category($value['categories_id']);
            
            $categoryLink = $category->getLink();
            $categoryIcon = $category->getIconClass();
            $category = $category->getName();
            $tagsHTML = '';
            $tagsWhitelist = array(__("Paid Content"), __("Group"), __("Plugin"));
            if (!empty($objGallery->showTags)) {
                foreach ($value['tags'] as $value2) {
                    if (!empty($value2->label) && in_array($value2->label, $tagsWhitelist)) {
                        $tagsHTML .= '<span class="label label-' . $value2->type . '">' . $value2->text . '</span>';
                    }
                }
            }
            $viewsHTML = '';

            if (empty($advancedCustom->doNotDisplayViews)) {
                if (AVideoPlugin::isEnabledByName('LiveUsers')) {
                    $viewsHTML = '<div class="text-muted pull-right" style="display:flex;font-size: 1.2em;">' . getLiveUsersLabelVideo($value['id'], $value['views_count']) . '</div>';
                } else {
                    $viewsHTML = '<div class="text-muted pull-right"><i class="fas fa-eye"></i> ' . number_format($value['views_count'], 0) . '</strong></div>';
                }
            }
            $creator = self::getCreatorHTML($value['users_id'], '', true);


            $search = array(
                '{style}',
                '{divID}',
                '{link}',
                '{title}',
                '{imgJPGLow}',
                '{imgJPGHight}',
                '{imgGifHTML}',
                '{timeHTML}',
                '{loggedUserHTML}',
                '{progress}',
                '{categoryLink}',
                '{categoryIcon}',
                '{category}',
                '{tagsHTML}',
                '{viewsHTML}',
                '{creator}');

            $replace = array(
                $style,
                $divID,
                $link,
                $title,
                $imgJPGLow,
                $imgJPGHight,
                $imgGifHTML,
                $timeHTML,
                $loggedUserHTML,
                $progress,
                $categoryLink,
                $categoryIcon,
                $category,
                $tagsHTML,
                $viewsHTML,
                $creator
            );

            $btnHTML = str_replace($search, $replace, $content);
            return $btnHTML;
        }

    }

}
// just to convert permalink into clean_title
if (!empty($_GET['v']) && empty($_GET['videoName'])) {
    $_GET['videoName'] = Video::get_clean_title($_GET['v']);
}
