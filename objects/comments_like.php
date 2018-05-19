<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'objects/user.php';
class CommentsLike {
    private $id;
    private $like;
    private $comments_id;
    private $users_id;

    function __construct($like, $comments_id) {
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        $this->comments_id = $comments_id;
        $this->users_id = User::getId();
        $this->load();
        // if click again in the same vote, remove the vote
        if ($this->like == $like) {
            $like = 0;
        }
        $this->setLike($like);
        $this->save();
    }

    private function setLike($like) {
        $like = intval($like);
        if(!in_array($like, array(0,1,-1))){
            $like = 0;
        }
        $this->like = $like;
    }

    private function load() {
        $like = $this->getLike();
        if (empty($like)) {
            return false;
        }
        foreach ($like as $key => $value) {
            $this->$key = $value;
        }
    }

    private function getLike() {
        global $global;
        if (empty($this->users_id) || empty($this->comments_id)) {
            header('Content-Type: application/json');
            die('{"error":"You must have user and videos set to get a like"}');
        }
        $sql = "SELECT * FROM comments_likes WHERE users_id = ? AND comments_id = ? LIMIT 1";
        $res = $global['mysqli']->query($sql);
        $res = sqlDAL::readSql($sql,"ii",array($this->users_id,$this->comments_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return ($res) ? $result : false;
    }

    private function save() {
        global $global;
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        $formats = "";
        $values = array();
        if (!empty($this->id)) {
            $sql = "UPDATE comments_likes SET `like` = ?, modified = now() WHERE id = ?";
            $formats = "ii";
            $values = array($this->like,$this->id);
        } else {
            $sql = "INSERT INTO comments_likes ( `like`,users_id, comments_id, created, modified) VALUES (?, ?, ?, now(), now())";
            $formats = "iii";
            $values = array($this->like,$this->users_id,$this->comments_id);
        }
        //echo $sql;exit;
        $resp = sqlDAL::writeSql($sql,$formats,$values);
        if ($global['mysqli']->errno!=0) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    static function getLikes($comments_id) {
        global $global;

        $obj = new stdClass();
        $obj->comments_id = $comments_id;
        $obj->likes = 0;
        $obj->dislikes = 0;
        $obj->myVote = self::getMyVote($comments_id);

        $sql = "SELECT count(*) as total FROM comments_likes WHERE comments_id = ? AND `like` = 1 "; // like
        $res = sqlDAL::readSql($sql,"i",array($comments_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!$res) {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $obj->likes = intval($result['total']);

        $sql = "SELECT count(*) as total FROM comments_likes WHERE comments_id = ? AND `like` = -1 "; // dislike
        $res = sqlDAL::readSql($sql,"i",array($comments_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!$res) {
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $obj->dislikes = intval($result['total']);
        return $obj;
    }
    
    static function getTotalLikes() {
        global $global;

        $obj = new stdClass();
        $obj->likes = 0;
        $obj->dislikes = 0;

        $sql = "SELECT count(*) as total FROM comments_likes WHERE `like` = 1 "; // like
        $res = sqlDAL::readSql($sql);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!$res) {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $obj->likes = intval($result['total']);

        $sql = "SELECT count(*) as total FROM comments_likes WHERE `like` = -1 "; // dislike
        $res = sqlDAL::readSql($sql);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!$res) {
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $obj->dislikes = intval($result['total']);
        return $obj;
    }

    static function getMyVote($comments_id) {
        global $global;
        if (!User::isLogged()) {
            return 0;
        }
        $id = User::getId();
        $sql = "SELECT `like` FROM comments_likes WHERE comments_id = ? AND users_id = ? "; // like
        $res = sqlDAL::readSql($sql,"ii",array($comments_id,$id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            return intval($result['like']);
        }
        return 0;
    }

}
