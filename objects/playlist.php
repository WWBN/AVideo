<?php

global $global, $config;
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
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? users_id = ". User::getId()." LIMIT 1";
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
        if(!User::isLogged()){
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
    static function getAllFromUser($userId, $publicOnly = true, $status = false) {
        global $global, $config;
        $formats = "";
        $values = array();
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl "
                . " LEFT JOIN users u ON u.id = users_id WHERE 1=1 ";
        if (!empty($status)) {
            $status = str_replace("'","", $status);
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
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        $favorite = array();
        $watch_later = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['videos'] = static::getVideosFromPlaylist($row['id']);
                if ($row['status'] === "favorite") {
                    $favorite = $row;
                } else if ($row['status'] === "watch_later") {
                    $watch_later = $row;
                } else {
                    $rows[] = $row;
                }
            }
            if (empty($status) && $config->currentVersionGreaterThen("6.4")) {
                if (empty($favorite)) {
                    $pl = new PlayList(0);
                    $pl->setName("Favorite");
                    $pl->setStatus("favorite");
                    $pl->setUsers_id($userId);
                    $id = $pl->save();
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
                    $row['id'] = $id;
                    $row['name'] = $pl->getName();
                    $row['status'] = $pl->getStatus();
                    $row['users_id'] = $pl->getUsers_id();
                    $watch_later = $row;
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
    
    static function getAllFromUserVideo($userId, $videos_id, $publicOnly = true, $status = false) {
        $rows = self::getAllFromUser($userId, $publicOnly, $status);
        foreach ($rows as $key => $value) {
            $videos = self::getVideosIdFromPlaylist($value['id']);
            $rows[$key]['isOnPlaylist'] = in_array($videos_id, $videos);
        }
        return $rows;
    }

    static function getVideosFromPlaylist($playlists_id) {
        global $global;
        $sql = "SELECT *,v.created as cre, p.`order` as video_order, v.externalOptions as externalOptions "
                . ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes "
                . " FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " LEFT JOIN users u ON u.id = v.users_id "
                . " WHERE playlists_id = ? ORDER BY p.`order` ASC ";
        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                if (!empty($_GET['isChannel'])) {
                    $row['tags'] = Video::getTags($row['id']);
                    $row['pluginBtns'] = YouPHPTubePlugin::getPlayListButtons($playlists_id);
                    $row['humancreate'] = humanTiming(strtotime($row['cre']));
                }
                $row['progress'] = Video::getVideoPogressPercent($row['videos_id']);
                $row['title'] = UTF8encode($row['title']);
                $row['description'] = UTF8encode($row['description']);
                //unset($row['description']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
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
        $status = str_replace("'","", $status);

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
        $favorite = self::getIdFromUser($users_id, "favorite");
        if(empty($favorite)){
            $pl = new PlayList(0);
            $pl->setName("Favorite");
            $pl->setUsers_id($users_id);
            $pl->setStatus("favorite");
            $pl->save();
            $favorite = self::getIdFromUser($users_id, "favorite");
        }
        return $favorite;
    }

    static function getWatchLaterIdFromUser($users_id) {
        $watch_later = self::getIdFromUser($users_id, "watch_later");
        
        if(empty($watch_later)){
            $pl = new PlayList(0);
            $pl->setName("Watch Later");
            $pl->setUsers_id($users_id);
            $pl->setStatus("watch_later");
            $pl->save();
            $watch_later = self::getIdFromUser($users_id, "watch_later");
        }
        return $watch_later;
    }

    private static function getIdFromUser($users_id, $status) {
        global $global;

        $status = str_replace("'","", $status);
        $sql = "SELECT * FROM  " . static::getTableName() . " pl  WHERE"
                . " users_id = ? AND pl.status = '{$status}' LIMIT 1 ";
        $res = sqlDAL::readSql($sql, "i", array($users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data['id'];
        } else {
            $row = false;
        }
        return $row;
    }

    static function getVideosIdFromPlaylist($playlists_id) {
        $videosId = array();
        $rows = static::getVideosFromPlaylist($playlists_id);
        foreach ($rows as $value) {
            $videosId[] = $value['videos_id'];
        }
        return $videosId;
    }

    static function sortVideos($videosList, $listIdOrder) {
        $list = array();
        foreach ($listIdOrder as $value) {
            foreach ($videosList as $key => $value2) {
                if ($value2['id'] == $value) {
                    $list[] = $value2;
                    unset($videosList[$key]);
                }
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
        return parent::save();
    }

    /**
     * This is just to fix errors from the update 6.4 to 6.5, where empty playlists were created before the update
     * @return type
     */
    private function clearEmptyLists() {
        $sql = "DELETE FROM " . static::getTableName() . " WHERE status = ''";

        return sqlDAL::writeSql($sql);
    }

    public function addVideo($video_id, $add, $order = 0) {
        global $global;
        $formats = "";
        $values = array();
        if (empty($add) || $add === "false") {
            $sql = "DELETE FROM playlists_has_videos WHERE playlists_id = ? AND videos_id = ? ";
            $formats = "ii";
            $values[] = $this->id;
            $values[] = $video_id;
        } else {
            $this->addVideo($video_id, false);
            $sql = "INSERT INTO playlists_has_videos ( playlists_id, videos_id , `order`) VALUES (?, ?, ?) ";
            $formats = "iii";
            $values[] = $this->id;
            $values[] = $video_id;
            $values[] = $order;
        }
        return sqlDAL::writeSql($sql, $formats, $values);
    }

    public function delete() {
        if (empty($this->id)) {
            return false;
        }
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
