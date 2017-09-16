<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class PlayList extends Object {

    protected $id, $name, $users_id, $status;

    protected static function getSearchFieldsNames() {
        return array('name');
    }

    protected static function getTableName() {
        return 'playlists';
    }

    /**
     *
     * @global type $global
     * @param type $publicOnly
     * @param type $userId if not present check session
     * @param type $isVideoIdPresent pass the ID of the video checking
     * @return boolean
     */
    static function getAllFromUser($userId, $publicOnly = true) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        if ($publicOnly) {
            $sql .= " AND status = 'public' ";
        }
        if (!empty($userId)) {
            $sql .= " AND users_id = {$userId} ";
        }
        $sql .= self::getSqlFromPost();

        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['videos'] = static::getVideosFromPlaylist($row['id']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getVideosFromPlaylist($playlists_id) {
        global $global;
        $sql = "SELECT * FROM  playlists_has_videos "
                . "LEFT JOIN videos as v ON videos_id = v.id "
                . " WHERE playlists_id = {$playlists_id} ";

        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getVideosIdFromPlaylist($playlists_id) {
        $videosId = array();
        $rows = static::getVideosFromPlaylist($playlists_id);
        foreach ($rows as $value) {
            $videosId[] = $value['id'];
        }
        return $videosId;
    }

    public function save() {
        if (!User::isLogged()) {
            return false;
        }
        $users_id = User::getId();
        $this->setUsers_id($users_id);
        return parent::save();
    }

    public function addVideo($video_id, $add) {
        global $global;
        if(empty($add) || $add == "false"){
            $sql = "DELETE FROM playlists_has_videos WHERE playlists_id = {$this->id} AND videos_id = {$video_id} ";
        }else{
            $sql = "INSERT INTO playlists_has_videos ( playlists_id, videos_id ) VALUES ({$this->id}, {$video_id}) ";
        }
        //echo $sql;
        return $global['mysqli']->query($sql);
    }

    public function delete() {
        if(empty($this->id)){
            return false;
        }
        global $global;
        $sql = "DELETE FROM playlists WHERE id = {$this->id} ";
        //echo $sql;
        return $global['mysqli']->query($sql);
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
        $this->name = $name;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

}
