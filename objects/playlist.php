<?php

global $global, $config, $refreshCacheFromPlaylist;
$refreshCacheFromPlaylist = false; // this is because it was creating playlists multiple times

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

class PlayList extends ObjectYPT {

    protected $id, $name, $users_id, $status;
    static $validStatus = array('public', 'private', 'unlisted', 'favorite', 'watch_later');

    static function getSearchFieldsNames() {
        return array('pl.name');
    }

    static function getTableName() {
        return 'playlists';
    }

    static protected function getFromDbFromName($name) {
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

    function loadFromName($name) {
        if (!User::isLogged()) {
            return false;
        }
        $this->setName($name);
        $row = self::getFromDbFromName($this->getName());
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    /**
     *
     * @global type $global
     * @param type $publicOnly
     * @param type $userId if not present check session
     * @param type $isVideoIdPresent pass the ID of the video checking
     * @return boolean
     */
    static function getAllFromUser($userId, $publicOnly = true, $status = false, $playlists_id = 0, $try = 0) {
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
        } else
        if ($publicOnly) {
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
                $row['videos'] = static::getVideosFromPlaylist($row['id']);
                $row['isFavorite'] = false;
                $row['isWatchLater'] = false;
                if ($row['status'] === "favorite") {
                    $row['isFavorite'] = true;
                    $favoriteCount++;
                    $favorite = $row;
                } else if ($row['status'] === "watch_later") {
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

    static function fixDuplicatePlayList($user_id) {
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

    static function getAllFromUserVideo($userId, $videos_id, $publicOnly = true, $status = false) {
        if (empty($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id][$userId][intval($publicOnly)][intval($status)])) {
            $rows = self::getAllFromUser($userId, $publicOnly, $status);
            foreach ($rows as $key => $value) {
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

    static private function removeCache($videos_id) {
        $close = false;
        _session_start();
        unset($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id]);
        unset($_SESSION['user']['sessionCache']['getAllFromUserVideo'][$videos_id]);
    }

    static function getVideosIDFromPlaylistLight($playlists_id) {
        global $global;
        $sql = "SELECT * FROM playlists_has_videos p WHERE playlists_id = ? ";
        cleanSearchVar();
        $sort = @$_POST['sort'];
        $_POST['sort'] = array();
        $sql .= self::getSqlFromPost();
        $_POST['sort'] = $sort;
        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        reloadSearchVar();
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
        return $rows;
    }

    public static function setCache($name, $value) {
        parent::setCache($name, $value);
    }

    static function getVideosFromPlaylist($playlists_id) {
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
        $cacheName = "getVideosFromPlaylist{$playlists_id}".md5($sql);
        $rows = self::getCache($cacheName, 0);
        if (empty($rows)) {
            global $global;

            $res = sqlDAL::readSql($sql, "i", array($playlists_id));
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
            $SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
            if ($res != false) {
                $timeLog2 = __FILE__ . " - getVideosFromPlaylist: {$playlists_id}";
                TimeLogStart($timeLog2);
                foreach ($fullData as $row) {
                    if (!empty($_GET['isChannel'])) {
                        $row['tags'] = Video::getTags($row['id']);
                        $row['pluginBtns'] = AVideoPlugin::getPlayListButtons($playlists_id);
                        $row['humancreate'] = humanTiming(strtotime($row['cre']));
                    }
                    TimeLogEnd($timeLog2, __LINE__);
                    $images = Video::getImageFromFilename($row['filename'], $row['type']);
                    TimeLogEnd($timeLog2, __LINE__);
                    $row['images'] = $images;
                    $row['videos'] = Video::getVideosPaths($row['filename'], true);
                    TimeLogEnd($timeLog2, __LINE__);
                    $row['progress'] = Video::getVideoPogressPercent($row['videos_id']);
                    TimeLogEnd($timeLog2, __LINE__);
                    $row['title'] = UTF8encode($row['title']);
                    TimeLogEnd($timeLog2, __LINE__);
                    $row['description'] = UTF8encode($row['description']);
                    TimeLogEnd($timeLog2, __LINE__);
                    $row['tags'] = Video::getTags($row['videos_id']);
                    TimeLogEnd($timeLog2, __LINE__);
                    if (AVideoPlugin::isEnabledByName("VideoTags")) {
                        $row['videoTags'] = Tags::getAllFromVideosId($row['videos_id']);
                        $row['videoTagsObject'] = Tags::getObjectFromVideosId($row['videos_id']);
                    }
                    TimeLogEnd($timeLog2, __LINE__);
                    if ($SubtitleSwitcher) {
                        $row['subtitles'] = getVTTTracks($row['filename'], true);
                        foreach ($row['subtitles'] as $value) {
                            $row['subtitlesSRT'][] = convertSRTTrack($value);
                        }
                    }
                    TimeLogEnd($timeLog2, __LINE__);
                    unset($row['password']);
                    unset($row['recoverPass']);
                    //unset($row['description']);
                    $rows[] = $row;
                }

                self::setCache($cacheName, $rows);
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        } else {
            $rows = object_to_array($rows);
        }
        return $rows;
    }

    static function isVideoOnFavorite($videos_id, $users_id) {
        return self::isVideoOn($videos_id, $users_id, 'favorite');
    }

    static function isVideoOnWatchLater($videos_id, $users_id) {
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
        } else {
            $row = false;
        }
        return $row;
    }

    static function getFavoriteIdFromUser($users_id) {
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

    static function getWatchLaterIdFromUser($users_id) {
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

    static function getVideosIdFromPlaylist($playlists_id) {
        $videosId = array();
        $rows = static::getVideosIDFromPlaylistLight($playlists_id);
        foreach ($rows as $value) {
            $videosId[] = $value['videos_id'];
        }
        return $videosId;
    }

    static function sortVideos($videosList, $listIdOrder) {
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
        $playlists_id = parent::save();
        if (!empty($playlists_id)) {
            self::deleteCache("getVideosFromPlaylist{$playlists_id}");
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
        self::deleteCache("getVideosFromPlaylist{$this->id}");
        self::removeCache($videos_id);
        return sqlDAL::writeSql($sql, $formats, $values);
    }

    public function delete() {
        if (empty($this->id)) {
            return false;
        }
        self::deleteCache("getVideosFromPlaylist{$this->id}");
        global $global;
        $sql = "DELETE FROM playlists WHERE id = ? ";
        //echo $sql;
        return sqlDAL::writeSql($sql, "i", array($this->id));
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getModified() {
        return $this->modified;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getStatus() {
        return $this->status;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        if (strlen($name) > 45)
            $name = substr($name, 0, 42) . '...';
        $this->name = xss_esc($name);
        //var_dump($name,$this->name);exit;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setStatus($status) {
        if (!in_array($status, self::$validStatus)) {
            $status = 'public';
        }
        $this->status = $status;
    }

    static function canSee($playlist_id, $users_id) {
        $obj = new PlayList($playlist_id);
        $status = $obj->getStatus();
        if ($status !== 'public' && $status !== 'unlisted' && $users_id != $obj->getUsers_id()) {
            return false;
        }
        return true;
    }

}
