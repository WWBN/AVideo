<?php

global $global, $config, $refreshCacheFromPlaylist;
$refreshCacheFromPlaylist = false; // this is because it was creating playlists multiple times

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

class PlayList extends ObjectYPT {

    protected $id;
    protected $name;
    protected $users_id;
    protected $status;
    protected $showOnTV;
    public static $validStatus = array('public', 'private', 'unlisted', 'favorite', 'watch_later');

    public static function getSearchFieldsNames() {
        return array('pl.name');
    }

    public static function getTableName() {
        return 'playlists';
    }

    protected static function getFromDbFromName($name) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? users_id = " . User::getId() . " LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($name));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function loadFromName($name) {
        if (!User::isLogged()) {
            return false;
        }
        $this->setName($name);
        $row = self::getFromDbFromName($this->getName());
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    public static function getAllFromPlaylistsID($playlists_id) {
        if (empty($playlists_id)) {
            return false;
        }
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
        $videosP = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
        $videosP = PlayList::sortVideos($videosP, $videosArrayId);
        foreach ($videosP as $key => $value2) {
            if (!empty($value2['serie_playlists_id'])) {
                $videosP[$key]['icon'] = '<i class=\'fas fa-layer-group\'></i>';
            } else {
                $videosP[$key]['icon'] = '<i class=\'fas fa-film\'></i>';
            }
        }

        return $videosP;
    }

    /**
     *
     * @global type $global
     * @param type $publicOnly
     * @param type $userId if not present check session
     * @param type $isVideoIdPresent pass the ID of the video checking
     * @return boolean
     */
    public static function getAllFromUser($userId, $publicOnly = true, $status = false, $playlists_id = 0, $try = 0) {
        global $global, $config, $refreshCacheFromPlaylist;
        $playlists_id = intval($playlists_id);
        $formats = "";
        $values = array();
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl "
                . " LEFT JOIN users u ON u.id = users_id WHERE 1=1 ";
        if (!empty($playlists_id)) {
            $sql .= " AND pl.id = '{$playlists_id}' ";
        }
        if (!empty($status)) {
            $status = str_replace("'", "", $status);
            $sql .= " AND pl.status = '{$status}' ";
        } elseif ($publicOnly) {
            $sql .= " AND pl.status = 'public' ";
        }
        if (!empty($userId)) {
            $sql .= " AND users_id = ? ";
            $formats .= "i";
            $values[] = $userId;
        }
        $sql .= self::getSqlFromPost("pl.");
        //echo $sql, $userId;exit;
        $res = sqlDAL::readSql($sql, $formats, $values, $refreshCacheFromPlaylist);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        $favorite = array();
        $watch_later = array();
        $favoriteCount = 0;
        $watch_laterCount = 0;
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $row['name_translated'] = __($row['name']);
                $row['videos'] = static::getVideosFromPlaylist($row['id']);
                $row['isFavorite'] = false;
                $row['isWatchLater'] = false;
                if ($row['status'] === "favorite") {
                    $row['isFavorite'] = true;
                    $favoriteCount++;
                    $favorite = $row;
                } elseif ($row['status'] === "watch_later") {
                    $watch_laterCount++;
                    $row['isWatchLater'] = true;
                    $watch_later = $row;
                } else {
                    $rows[] = $row;
                }
            }
            if (!empty($userId)) {
                if ($try == 0 && ($favoriteCount > 1 || $watch_laterCount > 1)) {
                    self::fixDuplicatePlayList($userId);
                    $refreshCacheFromPlaylist = true;
                    return self::getAllFromUser($userId, $publicOnly, $status, $playlists_id, $try + 1);
                }
                if (empty($_POST['current']) && empty($status) && $config->currentVersionGreaterThen("6.4")) {
                    if (empty($favorite)) {
                        $pl = new PlayList(0);
                        $pl->setName("Favorite");
                        $pl->setStatus("favorite");
                        $pl->setUsers_id($userId);
                        $id = $pl->save();
                        $refreshCacheFromPlaylist = true;
                        $row['id'] = $id;
                        $row['name'] = $pl->getName();
                        $row['status'] = $pl->getStatus();
                        $row['users_id'] = $pl->getUsers_id();
                        $favorite = $row;
                    }
                    if (empty($watch_later)) {
                        $pl = new PlayList(0);
                        $pl->setName("Watch Later");
                        $pl->setStatus("watch_later");
                        $pl->setUsers_id($userId);
                        $id = $pl->save();
                        $refreshCacheFromPlaylist = true;
                        $row['id'] = $id;
                        $row['name'] = $pl->getName();
                        $row['status'] = $pl->getStatus();
                        $row['users_id'] = $pl->getUsers_id();
                        $watch_later = $row;
                    }
                }
            }
            if (!empty($favorite)) {
                array_unshift($rows, $favorite);
            }
            if (!empty($watch_later)) {
                array_unshift($rows, $watch_later);
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    /**
     *
     * @global type $global
     * @param type $publicOnly
     * @param type $userId if not present check session
     * @param type $isVideoIdPresent pass the ID of the video checking
     * @return boolean
     */
    public static function getAllFromUserLight($userId, $publicOnly = true, $status = false, $playlists_id = 0, $onlyWithVideos = false) {
        global $global, $config, $refreshCacheFromPlaylist;
        $playlists_id = intval($playlists_id);
        $formats = "";
        $values = array();
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl "
                . " LEFT JOIN users u ON u.id = users_id WHERE 1=1 ";
        if (!empty($playlists_id)) {
            $sql .= " AND pl.id = '{$playlists_id}' ";
        }
        if (!empty($status)) {
            $status = str_replace("'", "", $status);
            $sql .= " AND pl.status = '{$status}' ";
        } elseif ($publicOnly) {
            if (User::getId() != $userId) {
                $sql .= " AND pl.status = 'public' ";
            }
        }
        if (!empty($userId)) {
            $sql .= " AND users_id = ? ";
            $formats .= "i";
            $values[] = $userId;
        }
        $sql .= self::getSqlFromPost("pl.");
        //echo $sql, $userId;exit;
        $res = sqlDAL::readSql($sql, $formats, $values, $refreshCacheFromPlaylist);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                if ($onlyWithVideos) {
                    $videos = self::getVideosIDFromPlaylistLight($row['id']);
                    if (empty($videos)) {
                        continue;
                    }
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function fixDuplicatePlayList($user_id) {
        if (empty($user_id)) {
            return false;
        }
        _error_log("PlayList::fixDuplicatePlayList Process user_id = {$user_id} favorite");
        $sql = "SELECT * FROM  playlists WHERE users_id = ? AND status = 'favorite' ORDER BY created ";
        $res = sqlDAL::readSql($sql, "i", array($user_id), true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $key => $row) {
                if ($key === 0) {
                    continue;
                }

                if (!empty(PlayList::getVideosIDFromPlaylistLight($row['id']))) {
                    _error_log("PlayList::fixDuplicatePlayList favorite PlayList NOT empty {$row['id']}");
                    continue;
                }

                $sql = "DELETE FROM playlists ";
                $sql .= " WHERE id = ?";

                _error_log("PlayList::fixDuplicatePlayList favorite {$row['id']}");
                sqlDAL::writeSql($sql, "i", array($row['id']));
            }
        }

        _error_log("PlayList::fixDuplicatePlayList Process user_id = {$user_id} watch_later");
        $sql = "SELECT * FROM  playlists WHERE users_id = ? AND status = 'watch_later' ORDER BY created ";
        $res = sqlDAL::readSql($sql, "i", array($user_id), true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $key => $row) {
                if ($key === 0) {
                    continue;
                }
                if (!empty(PlayList::getVideosIDFromPlaylistLight($row['id']))) {
                    _error_log("PlayList::fixDuplicatePlayList watch_later PlayList NOT empty {$row['id']}");
                    continue;
                }
                $sql = "DELETE FROM playlists ";
                $sql .= " WHERE id = ?";
                _error_log("PlayList::fixDuplicatePlayList watch_later {$row['id']}");
                ob_flush();
                sqlDAL::writeSql($sql, "i", array($row['id']));
            }
        }
    }

    public static function getAllFromUserVideo($userId, $videos_id, $publicOnly = true, $status = false) {
        if (empty($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id][$userId][intval($publicOnly)][intval($status)])) {
            $rows = self::getAllFromUser($userId, $publicOnly, $status);
            foreach ($rows as $key => $value) {
                $rows[$key]['name_translated'] = __($rows[$key]['name']);
                $videos = self::getVideosIdFromPlaylist($value['id']);
                $rows[$key]['isOnPlaylist'] = in_array($videos_id, $videos);
            }
            _session_start();
            $_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id][$userId][intval($publicOnly)][intval($status)] = $rows;
        } else {
            $rows = $_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id][$userId][intval($publicOnly)][intval($status)];
        }

        return $rows;
    }

    private static function removeCache($videos_id) {
        $close = false;
        _session_start();
        unset($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id]);
        unset($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id]);
    }

    public static function getVideosIDFromPlaylistLight($playlists_id) {
        global $global, $getVideosIDFromPlaylistLight;

        if (!isset($getVideosIDFromPlaylistLight)) {
            $getVideosIDFromPlaylistLight = array();
        }

        if (isset($getVideosIDFromPlaylistLight[$playlists_id])) {
            return $getVideosIDFromPlaylistLight[$playlists_id];
        }

        $sql = "SELECT * FROM playlists_has_videos p WHERE playlists_id = ?  ORDER BY `order` ";
        /*
          cleanSearchVar();
          $sort = @$_POST['sort'];
          $_POST['sort'] = array();
          $sql .= self::getSqlFromPost();
          $_POST['sort'] = $sort;
          reloadSearchVar();
         *
         */
        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $getVideosIDFromPlaylistLight[$playlists_id] = $rows;
        return $rows;
    }

    public static function getVideosFromPlaylist($playlists_id) {
        $sql = "SELECT *,v.created as cre, p.`order` as video_order, v.externalOptions as externalOptions "
                . ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " LEFT JOIN users u ON u.id = v.users_id "
                . " WHERE playlists_id = ? AND v.status != 'i' ";
        cleanSearchVar();
        $sort = @$_POST['sort'];
        $_POST['sort'] = array();
        $_POST['sort']['p.`order`'] = 'ASC';
        $sql .= self::getSqlFromPost();
        reloadSearchVar();
        $_POST['sort'] = $sort;
        $cacheName = "getVideosFromPlaylist{$playlists_id}" . DIRECTORY_SEPARATOR . md5($sql);
        $rows = self::getCache($cacheName, 0, true);
        if (empty($rows)) {
            global $global;

            $res = sqlDAL::readSql($sql, "i", array($playlists_id));
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
            $SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
            if ($res != false) {
                foreach ($fullData as $row) {
                    $row = cleanUpRowFromDatabase($row);
                    if (!empty($_GET['isChannel'])) {
                        $row['tags'] = Video::getTags($row['id']);
                        $row['pluginBtns'] = AVideoPlugin::getPlayListButtons($playlists_id);
                        $row['humancreate'] = humanTiming(strtotime($row['cre']));
                    }
                    $images = Video::getImageFromFilename($row['filename'], $row['type']);
                    if (!file_exists($images->posterLandscapePath) && !empty($row['serie_playlists_id'])) {
                        $images = self::getRandomImageFromPlayList($row['serie_playlists_id']);
                    }
                    $row['images'] = $images;
                    $row['videos'] = Video::getVideosPaths($row['filename'], true);
                    $row['progress'] = Video::getVideoPogressPercent($row['videos_id']);
                    $row['title'] = UTF8encode($row['title']);
                    $row['description'] = UTF8encode($row['description']);
                    $row['tags'] = Video::getTags($row['videos_id']);
                    if (AVideoPlugin::isEnabledByName("VideoTags")) {
                        $row['videoTags'] = Tags::getAllFromVideosId($row['videos_id']);
                        $row['videoTagsObject'] = Tags::getObjectFromVideosId($row['videos_id']);
                    }
                    if ($SubtitleSwitcher) {
                        $row['subtitles'] = getVTTTracks($row['filename'], true);
                        foreach ($row['subtitles'] as $value) {
                            $row['subtitlesSRT'][] = convertSRTTrack($value);
                        }
                    }
                    $rows[] = $row;
                }

                $cache = self::setCache($cacheName, $rows);
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        } else {
            $rows = object_to_array($rows);
        }
        return $rows;
    }

    public static function getRandomImageFromPlayList($playlists_id) {
        global $global;
        $sql = "SELECT v.* "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE playlists_id = ? AND v.status != 'i' ORDER BY RAND()
                LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            $images = Video::getImageFromFilename($row['filename'], $row['type']);

            if (!file_exists($images->posterLandscapePath) && !empty($row['serie_playlists_id'])) {
                return self::getRandomImageFromPlayList($row['serie_playlists_id']);
            }
            return $images;
        }
        return false;
    }

    public static function isAGroupOfPlayLists($playlists_id) {

        $rows = self::getAllSubPlayLists($playlists_id);

        return count($rows);
    }

    public static function getAllSubPlayLists($playlists_id, $NOTSubPlaylists = 0) {
        global $getAllSubPlayLists;
        if (empty($playlists_id)) {
            return false;
        }
        if (!isset($getAllSubPlayLists)) {
            $getAllSubPlayLists = array();
        }
        if (!isset($getAllSubPlayLists[$playlists_id])) {
            $getAllSubPlayLists[$playlists_id] = array();
        }
        if (isset($getAllSubPlayLists[$playlists_id][$NOTSubPlaylists])) {
            return $getAllSubPlayLists[$playlists_id][$NOTSubPlaylists];
        }
        $sql = "SELECT v.* "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE playlists_id = ? AND v.status != 'i' ";

        if ($NOTSubPlaylists) {
            $sql .= ' AND serie_playlists_id IS NULL ';
        } else {
            $sql .= ' AND serie_playlists_id IS NOT NULL ';
        }

        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        $getAllSubPlayLists[$playlists_id][$NOTSubPlaylists] = $rows;
        return $rows;
    }

    public static function getAllNOTSubPlayLists($playlists_id) {
        return self::getAllSubPlayLists($playlists_id, 1);
    }

    public static function isVideoOnFavorite($videos_id, $users_id) {
        return self::isVideoOn($videos_id, $users_id, 'favorite');
    }

    public static function isVideoOnWatchLater($videos_id, $users_id) {
        return self::isVideoOn($videos_id, $users_id, 'watch_later');
    }

    private static function isVideoOn($videos_id, $users_id, $status) {
        global $global;
        $status = str_replace("'", "", $status);

        $sql = "SELECT pl.id FROM  " . static::getTableName() . " pl "
                . " LEFT JOIN users u ON u.id = users_id "
                . " LEFT JOIN  playlists_has_videos p ON pl.id = playlists_id"
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE  videos_id = ? AND pl.users_id = ? AND pl.status = '{$status}' LIMIT 1 ";
        //echo $videos_id," - " ,$users_id, $sql;
        $res = sqlDAL::readSql($sql, "ii", array($videos_id, $users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            $row = cleanUpRowFromDatabase($row);
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getFavoriteIdFromUser($users_id) {
        global $refreshCacheFromPlaylist;
        $favorite = self::getIdFromUser($users_id, "favorite");
        if (empty($favorite)) {
            $pl = new PlayList(0);
            $pl->setName("Favorite");
            $pl->setUsers_id($users_id);
            $pl->setStatus("favorite");
            $pl->save();
            $refreshCacheFromPlaylist = true;
            $favorite = self::getIdFromUser($users_id, "favorite");
        }
        return $favorite;
    }

    public static function getWatchLaterIdFromUser($users_id) {
        global $refreshCacheFromPlaylist;
        $watch_later = self::getIdFromUser($users_id, "watch_later");

        if (empty($watch_later)) {
            $pl = new PlayList(0);
            $pl->setName("Watch Later");
            $pl->setUsers_id($users_id);
            $pl->setStatus("watch_later");
            $pl->save();
            $refreshCacheFromPlaylist = true;
            $watch_later = self::getIdFromUser($users_id, "watch_later");
        }
        return $watch_later;
    }

    private static function getIdFromUser($users_id, $status) {
        global $global;

        $status = str_replace("'", "", $status);
        $sql = "SELECT * FROM  " . static::getTableName() . " pl  WHERE"
                . " users_id = ? AND pl.status = '{$status}' LIMIT 1 ";
        $res = sqlDAL::readSql($sql, "i", array($users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res && !empty($data)) {
            $row = $data['id'];
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getVideosIdFromPlaylist($playlists_id) {
        global $getVideosIdFromPlaylist;
        if (empty($getVideosIdFromPlaylist)) {
            $getVideosIdFromPlaylist = array();
        }
        if (isset($getVideosIdFromPlaylist[$playlists_id])) {
            return $getVideosIdFromPlaylist[$playlists_id];
        }
        $videosId = array();
        $rows = static::getVideosIDFromPlaylistLight($playlists_id);
        foreach ($rows as $value) {
            $videosId[] = $value['videos_id'];
        }

        $getVideosIdFromPlaylist[$playlists_id] = $videosId;
        return $videosId;
    }

    public static function sortVideos($videosList, $listIdOrder) {
        $list = array();
        foreach ($listIdOrder as $value) {
            $found = false;
            foreach ($videosList as $key => $value2) {
                if ($value2['id'] == $value) {
                    $list[] = $value2;
                    unset($videosList[$key]);
                    $found = true;
                }
            }
            if (!$found) {
                $v = new Video("", "", $value);
                if (empty($v->getFilename())) {
                    continue;
                }
                $list[] = array('id' => $value);
            }
        }
        return $list;
    }

    public function save() {
        if (!User::isLogged()) {
            return false;
        }
        $this->clearEmptyLists();
        $users_id = User::getId();
        $this->setUsers_id($users_id);
        $this->showOnTV = intval($this->showOnTV);
        $playlists_id = parent::save();
        if (!empty($playlists_id)) {
            self::deleteCacheDir($playlists_id);
        }
        return $playlists_id;
    }

    /**
     * This is just to fix errors from the update 6.4 to 6.5, where empty playlists were created before the update
     * @return type
     */
    private function clearEmptyLists() {
        $sql = "DELETE FROM " . static::getTableName() . " WHERE status = ''";

        return sqlDAL::writeSql($sql);
    }

    public function addVideo($videos_id, $add, $order = 0) {
        global $global;
        $formats = "";
        $values = array();
        if (empty($add) || $add === "false") {
            $sql = "DELETE FROM playlists_has_videos WHERE playlists_id = ? AND videos_id = ? ";
            $formats = "ii";
            $values[] = $this->id;
            $values[] = $videos_id;
        } else {
            $this->addVideo($videos_id, false);
            $sql = "INSERT INTO playlists_has_videos ( playlists_id, videos_id , `order`) VALUES (?, ?, ?) ";
            $formats = "iii";
            $values[] = $this->id;
            $values[] = $videos_id;
            $values[] = $order;
        }
        $result = sqlDAL::writeSql($sql, $formats, $values);
        self::deleteCacheDir($this->id);
        self::removeCache($videos_id);
        return $result;
    }

    private static function deleteCacheDir($playlists_id){
        $tmpDir = ObjectYPT::getCacheDir();
        $cacheDir = $tmpDir . "getvideosfromplaylist{$playlists_id}" . DIRECTORY_SEPARATOR;
        rrmdir($cacheDir);
        exec('rm -R ' . $cacheDir);
    }
    
    public function delete() {
        if (empty($this->id)) {
            return false;
        }
        global $global;
        $sql = "DELETE FROM playlists WHERE id = ? ";
        //echo $sql;
        $result = sqlDAL::writeSql($sql, "i", array($this->id));

        self::deleteCacheDir($this->id);
        return $result;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getModified() {
        return $this->modified;
    }

    public function getUsers_id() {
        return $this->users_id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        if (strlen($name) > 45) {
            $name = substr($name, 0, 42) . '...';
        }
        $this->name = xss_esc($name);
        //var_dump($name,$this->name);exit;
    }

    public function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    public function setStatus($status) {
        if (!in_array($status, self::$validStatus)) {
            $status = 'public';
        }
        $this->status = $status;
    }

    public static function canSee($playlist_id, $users_id) {
        $obj = new PlayList($playlist_id);
        $status = $obj->getStatus();
        if ($status !== 'public' && $status !== 'unlisted' && $users_id != $obj->getUsers_id()) {
            return false;
        }
        return true;
    }

    public static function getEPG() {
        global $config, $global;
        $encoder = $config->_getEncoderURL();
        $url = "{$encoder}view/videosListEPG.php?date_default_timezone=" . urlencode(date_default_timezone_get());

        $content = url_get_contents($url);
        return _json_decode($content);
    }

    public function getShowOnTV() {
        return intval($this->showOnTV);
    }

    public function setShowOnTV($showOnTV) {
        if (strtolower($showOnTV) === "false") {
            $showOnTV = 0;
        } elseif (strtolower($showOnTV) === "true") {
            $showOnTV = 1;
        }
        $this->showOnTV = intval($showOnTV);
    }

    public static function getAllToShowOnTV() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT u.*, pl.* FROM  playlists pl "
                . " LEFT JOIN users u ON users_id = u.id "
                . " WHERE showOnTV=1 ";

        $sql .= self::getSqlFromPost();
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function showPlayListSelector($playListArray) {
        $collections = array();
        $videos = array();
        foreach ($playListArray as $value) {
            if ($value['type'] === 'serie' && !empty($value['serie_playlists_id'])) {
                $collections[] = $value;
            } else {
                $videos[] = $value;
            }
        }
        $countCollections = count($collections);
        $countVideos = count($videos);
        if (!empty($countCollections)) {
            if ($countCollections === 1 && empty($countVideos)) {
                return false;
            }
            return $collections;
        }
        return false;
    }

}
