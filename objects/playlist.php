<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class PlayList extends ObjectYPT {

    protected $id, $name, $users_id, $status;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
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
        $formats = "";
        $values = array();
        $sql = "SELECT u.*, pl.* FROM  " . static::getTableName() . " pl "
                . " LEFT JOIN users u ON u.id = users_id WHERE 1=1 ";
        if ($publicOnly) {
            $sql .= " AND pl.status = 'public' ";
        }
        if (!empty($userId)) {
            $sql .= " AND users_id = ? ";
            $formats .= "i";
            $values[] = $userId;
        }
        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql,$formats,$values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
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
        $sql = "SELECT * FROM  playlists_has_videos p "
                . " LEFT JOIN videos as v ON videos_id = v.id "
                . " LEFT JOIN users u ON u.id = v.users_id "
                . " WHERE playlists_id = {$playlists_id} ORDER BY p.`order` ASC ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql,"i",array($playlists_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
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
            $videosId[] = $value['videos_id'];
        }
        return $videosId;
    }
    
    static function sortVideos($videosList, $listIdOrder){
        $list = array();
        foreach ($listIdOrder as $value) {
            foreach ($videosList as $key => $value2) {
                if($value2['id']==$value){
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
        $users_id = User::getId();
        $this->setUsers_id($users_id);
        return parent::save();
    }

    public function addVideo($video_id, $add, $order=0) {
        global $global;
        if(empty($add) || $add === "false"){
            $sql = "DELETE FROM playlists_has_videos WHERE playlists_id = {$this->id} AND videos_id = {$video_id} ";
        }else{
            $this->addVideo($video_id, false);
            $sql = "INSERT INTO playlists_has_videos ( playlists_id, videos_id , `order`) VALUES ({$this->id}, {$video_id}, {$order}) ";
        }
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
