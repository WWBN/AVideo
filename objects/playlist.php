<?php

global $global, $config, $refreshCacheFromPlaylist;
$refreshCacheFromPlaylist = false; // this is because it was creating playlists multiple times

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

class PlayList extends ObjectYPT {

    protected $properties = [];
    protected $id;
    protected $name;
    protected $users_id;
    protected $status;
    protected $showOnTV;
    protected $modified;
    public static $validStatus = ['public', 'private', 'unlisted', 'favorite', 'watch_later'];

    public static function getSearchFieldsNames() {
        return ['pl.name'];
    }

    public static function getTableName() {
        return 'playlists';
    }

    protected static function getFromDbFromName($name) {
        global $global;
        if (!User::isLogged()) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? AND users_id = " . User::getId() . " LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$name]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getFromDbFromId($id) {
        global $global;
        if (!User::isLogged()) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
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
            @$this->$key = $value;
            //$this->properties[$key] = $value;
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
            if (empty($videosP[$key]['type'])) {
                unset($videosP[$key]);
                continue;
            }
            if (!empty($value2['serie_playlists_id'])) {
                $videosP[$key]['icon'] = '<i class=\'fas fa-layer-group\'></i>';
            } else {
                $videosP[$key]['icon'] = '<i class=\'fas fa-film\'></i>';
            }
            $images = Video::getImageFromFilename($videosP[$key]['filename'], $videosP[$key]['type']);
            $videosP[$key]['images'] = $images;
            if ($videosP[$key]['type'] !== 'linkVideo') {
                $videosP[$key]['videos'] = Video::getVideosPaths($videosP[$key]['filename'], true);
            }
        }

        return $videosP;
    }

    /**
     *
     * @global array $global
     * @param string $publicOnly
     * @param string $userId if not present check session
     * @param string $isVideoIdPresent pass the ID of the video checking
     * @return array
     */
    public static function getAllFromUser($userId, $publicOnly = true, $status = false, $playlists_id = 0, $try = 0, $includeSeries = false) {
        global $global, $config, $refreshCacheFromPlaylist;
        $playlists_id = intval($playlists_id);
        $formats = '';
        $values = [];
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl ";

        if ($includeSeries) {
            $sql .= " LEFT JOIN videos v ON pl.id = serie_playlists_id  ";
        }
        $sql .= " LEFT JOIN users u ON u.id = pl.users_id WHERE 1=1 ";
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
            if ($includeSeries) {
                $sql .= " AND (pl.users_id = ? OR v.users_id = ?) ";
                $formats .= "ii";
                $values[] = $userId;
                $values[] = $userId;
            } else {
                $sql .= " AND (pl.users_id = ?) ";
                $formats .= "i";
                $values[] = $userId;
            }
        }
        $sql .= self::getSqlFromPost("pl.");
        $TimeLog1 = "playList getAllFromUser($userId)";
        TimeLogStart($TimeLog1);
        $res = sqlDAL::readSql($sql, $formats, $values, $refreshCacheFromPlaylist);
        $fullData = sqlDAL::fetchAllAssoc($res);
        TimeLogEnd($TimeLog1, __LINE__);
        sqlDAL::close($res);
        $rows = [];
        $favorite = [];
        $watch_later = [];
        $favoriteCount = 0;
        $watch_laterCount = 0;
        if ($res !== false) {
            TimeLogEnd($TimeLog1, __LINE__);
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
            TimeLogEnd($TimeLog1, __LINE__);
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

            TimeLogEnd($TimeLog1, __LINE__);
            if (!empty($favorite)) {
                array_unshift($rows, $favorite);
            }
            TimeLogEnd($TimeLog1, __LINE__);
            if (!empty($watch_later)) {
                array_unshift($rows, $watch_later);
            }
            TimeLogEnd($TimeLog1, __LINE__);
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            $rows = [];
        }
        return $rows;
    }

    /**
     *
     * @global array $global
     * @param string $publicOnly
     * @param string $userId if not present check session
     * @param string $isVideoIdPresent pass the ID of the video checking
     * @return array
     */
    public static function getAllFromUserLight($userId, $publicOnly = true, $status = false, $playlists_id = 0, $onlyWithVideos = false, $includeSeries = false) {
        global $global, $config, $refreshCacheFromPlaylist;
        $playlists_id = intval($playlists_id);
        $formats = '';
        $values = [];
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl ";
        if ($includeSeries) {
            $sql .= " LEFT JOIN videos v ON pl.id = serie_playlists_id  ";
        }
        $sql .= " LEFT JOIN users u ON u.id = pl.users_id WHERE 1=1 ";
        if (!empty($playlists_id)) {
            $sql .= " AND pl.id = '{$playlists_id}' ";
        }
        if (!empty($status)) {
            $status = str_replace("'", "", $status);
            $sql .= " AND pl.status = '{$status}' ";
        } elseif ($publicOnly) {
            if (User::getId() !== $userId) {
                $sql .= " AND pl.status = 'public' ";
            }
        }
        if (!empty($userId)) {
            if ($includeSeries) {
                $sql .= " AND (pl.users_id = ? OR v.users_id = ?) ";
                $formats .= "ii";
                $values[] = $userId;
                $values[] = $userId;
            } else {
                $sql .= " AND (pl.users_id = ?) ";
                $formats .= "i";
                $values[] = $userId;
            }
        }
        $sql .= self::getSqlFromPost("pl.");
        //echo $sql, $userId;exit;
        $res = sqlDAL::readSql($sql, $formats, $values, $refreshCacheFromPlaylist);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $row['videos'] = self::getVideosIDFromPlaylistLight($row['id']);
                if ($onlyWithVideos) {
                    if (empty($row['videos'])) {
                        continue;
                    }
                }
                $rows[] = $row;
            }
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            $rows = [];
        }
        return $rows;
    }

    public static function fixDuplicatePlayList($user_id) {
        if (empty($user_id)) {
            return false;
        }
        _error_log("PlayList::fixDuplicatePlayList Process user_id = {$user_id} favorite");
        $sql = "SELECT * FROM  playlists WHERE users_id = ? AND status = 'favorite' ORDER BY created ";
        $res = sqlDAL::readSql($sql, "i", [$user_id], true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
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
                sqlDAL::writeSql($sql, "i", [$row['id']]);
            }
        }

        _error_log("PlayList::fixDuplicatePlayList Process user_id = {$user_id} watch_later");
        $sql = "SELECT * FROM  playlists WHERE users_id = ? AND status = 'watch_later' ORDER BY created ";
        $res = sqlDAL::readSql($sql, "i", [$user_id], true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
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
                sqlDAL::writeSql($sql, "i", [$row['id']]);
            }
        }
    }

    public static function getAllFromUserVideo($userId, $videos_id, $publicOnly = true, $status = false) {
        $TimeLog1 = "playList getAllFromUser($userId, $videos_id)";
        TimeLogStart($TimeLog1);
        $cacheName = "getAllFromUserVideo_{$videos_id}".DIRECTORY_SEPARATOR."getAllFromUserVideo($userId, $videos_id)".intval($publicOnly).$status;
        //var_dump($playlists_id, $sql);exit;
        $rows = self::getCacheGlobal($cacheName);
        if(empty($rows)){
            $rows = self::getAllFromUser($userId, $publicOnly, $status);
            TimeLogEnd($TimeLog1, __LINE__);
            foreach ($rows as $key => $value) {
                $rows[$key]['name_translated'] = __($rows[$key]['name']);
                $videos = self::getVideosIdFromPlaylist($value['id']);
                $rows[$key]['isOnPlaylist'] = in_array($videos_id, $videos);
            }
            TimeLogEnd($TimeLog1, __LINE__);
            self::setCacheGlobal($cacheName, $rows);
        }else{
            $rows = object_to_array($rows);
        }
        TimeLogEnd($TimeLog1, __LINE__);

        return $rows;
    }

    private static function removeCache($videos_id) {
        $cacheName = "getAllFromUserVideo_{$videos_id}";
        self::deleteCacheFromPattern($name);
    }

    public static function getSuggested() {
        global $global;

        return Video::getAllVideosLight("viewableNotUnlisted", false, false, true, 'serie');
    }

    public static function getVideosIndexFromPlaylistLight($playlists_id, $videos_id) {
        if (!empty($videos_id)) {
            $pl = self::getVideosIDFromPlaylistLight($playlists_id);
            foreach ($pl as $key => $value) {
                if ($value['videos_id'] == $videos_id) {
                    return $key;
                }
            }
        }
        return 0;
    }

    public static function getVideosIDFromPlaylistLight($playlists_id) {
        global $global, $getVideosIDFromPlaylistLight;

        if (!isset($getVideosIDFromPlaylistLight)) {
            $getVideosIDFromPlaylistLight = [];
        }

        if (isset($getVideosIDFromPlaylistLight[$playlists_id])) {
            return $getVideosIDFromPlaylistLight[$playlists_id];
        }

        if (empty($playlists_id)) {
            $sql = "SELECT 0 as playlists_id, id as videos_id FROM videos p WHERE status = ?  ORDER BY `created` DESC ";
            $res = sqlDAL::readSql($sql, "s", [Video::$statusActive]);
        } else {
            $sql = "SELECT * FROM playlists_has_videos p WHERE playlists_id = ?  ORDER BY `order` ";
            $res = sqlDAL::readSql($sql, "i", [$playlists_id]);
        }

        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $key => $row) {
                if (empty($playlists_id)) {
                    $row['order'] = $key;
                }
                $rows[] = $row;
            }
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            $rows = [];
        }
        $getVideosIDFromPlaylistLight[$playlists_id] = $rows;
        return $rows;
    }

    public static function getVideosFromPlaylist($playlists_id) {
        $sql = "SELECT *,v.created as cre, p.`order` as video_order, v.externalOptions as externalOptions "
                //. ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " LEFT JOIN users u ON u.id = v.users_id "
                . " WHERE playlists_id = ? AND v.status != 'i' ";
        cleanSearchVar();
        $sort = @$_POST['sort'];
        $_POST['sort'] = [];
        $_POST['sort']['p.`order`'] = 'ASC';
        $sql .= self::getSqlFromPost();
        reloadSearchVar();
        $_POST['sort'] = $sort;
        $cacheName = "getVideosFromPlaylist{$playlists_id}" . DIRECTORY_SEPARATOR . md5($sql);
        //var_dump($playlists_id, $sql);exit;
        $rows = self::getCacheGlobal($cacheName);
        if (empty($rows)) {
            global $global;

            $res = sqlDAL::readSql($sql, "i", [$playlists_id]);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = [];
            $SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
            if ($res !== false) {
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
                    if (empty($row['externalOptions'])) {
                        $row['externalOptions'] = json_encode(['videoStartSeconds' => '00:00:00']);
                    }
                    $rows[] = $row;
                }

                $cache = self::setCacheGlobal($cacheName, $rows);
            } else {
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                $rows = [];
            }
        } else {
            $rows = object_to_array($rows);
        }
        return $rows;
    }

    public static function getRandomImageFromPlayList($playlists_id, $try = 0) {
        global $global;
        $sql = "SELECT v.* "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE playlists_id = ? AND v.status != 'i' ORDER BY RAND()
                LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$playlists_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            $images = Video::getImageFromFilename($row['filename'], $row['type']);

            if (!file_exists($images->posterLandscapePath) && !empty($row['serie_playlists_id'])) {
                if ($try > 5) {
                    return $images;
                } else {
                    return self::getRandomImageFromPlayList($row['serie_playlists_id'], $try + 1);
                }
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
            $getAllSubPlayLists = [];
        }
        if (!isset($getAllSubPlayLists[$playlists_id])) {
            $getAllSubPlayLists[$playlists_id] = [];
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

        $res = sqlDAL::readSql($sql, "i", [$playlists_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
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
        $res = sqlDAL::readSql($sql, "ii", [$videos_id, $users_id]);
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
            $pl->setName("Favorites");
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
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
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
        $cacheName = "getVideosFromPlaylist{$playlists_id}" . DIRECTORY_SEPARATOR . "getVideosIdFromPlaylist";
        $videosId = self::getCacheGlobal($cacheName);
        if (empty($videosId)) {
            $videosId = [];
            $rows = static::getVideosIDFromPlaylistLight($playlists_id);
            foreach ($rows as $value) {
                $videosId[] = $value['videos_id'];
            }

            $cache = self::setCacheGlobal($cacheName, $videosId);
        }
        return $videosId;
    }

    public static function getTotalDurationFromPlaylistInSeconds($playlists_id) {
        global $getTotalDurationFromPlaylist;
        if (empty($getTotalDurationFromPlaylist)) {
            $getTotalDurationFromPlaylist = [];
        }
        if (isset($getTotalDurationFromPlaylist[$playlists_id])) {
            return $getTotalDurationFromPlaylist[$playlists_id];
        }
        $getTotalDurationFromPlaylist[$playlists_id] = 0;
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
        if (!empty($videosArrayId)) {
            $sql = "SELECT sum(duration_in_seconds) as total FROM videos WHERE id IN ('" . implode("', '", $videosArrayId) . "') ";
            //var_dump($sql);exit;
            $res = sqlDAL::readSql($sql);
            $data = sqlDAL::fetchAssoc($res);
            if (!empty($data)) {
                $getTotalDurationFromPlaylist[$playlists_id] = intval($data['total']);
            }
        }
        return $getTotalDurationFromPlaylist[$playlists_id];
    }

    public static function sortVideos($videosList, $listIdOrder) {
        $list = [];
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
                $list[] = ['id' => $value];
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
     * @return string
     */
    private function clearEmptyLists() {
        $sql = "DELETE FROM " . static::getTableName() . " WHERE status = ''";

        return sqlDAL::writeSql($sql);
    }

    public function addVideo($videos_id, $add, $order = 0) {
        global $global;

        $this->id = intval($this->id);
        $videos_id = intval($videos_id);
        $order = intval($order);

        if (empty($this->id) || empty($videos_id)) {
            return false;
        }

        $formats = '';
        $values = [];
        if (_empty($add)) {
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

    private static function deleteCacheDir($playlists_id) {
        $tmpDir = ObjectYPT::getCacheDir();
        $name = "getvideosfromplaylist{$playlists_id}";
        $cacheDir = $tmpDir . $name . DIRECTORY_SEPARATOR;
        rrmdir($cacheDir);
        if (class_exists('CachesInDB')) {
            CachesInDB::_deleteCacheStartingWith($name);
        }
    }

    public function delete() {
        if (empty($this->id)) {
            return false;
        }
        global $global;
        $sql = "DELETE FROM playlists WHERE id = ? ";
        //echo $sql;
        $result = sqlDAL::writeSql($sql, "i", [$this->id]);

        self::deleteCacheDir($this->id);
        return $result;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getNameOrSerieTitle() {
        return PlayLists::getNameOrSerieTitle($this->id);
    }

    public function getModified() {
        return $this->modified;
    }

    public function getUsers_id() {
        return $this->users_id;
    }

    /**
     * @return string
     */
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
        if ($status !== 'public' && $status !== 'unlisted' && $users_id !== $obj->getUsers_id()) {
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
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $rows[] = $row;
            }
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            $rows = [];
        }
        return $rows;
    }

    public static function showPlayListSelector($playListArray) {
        $collections = [];
        $videos = [];
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
