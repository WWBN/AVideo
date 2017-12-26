<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'objects/user.php';
class Like {
    private $id;
    private $like;
    private $videos_id;
    private $users_id;

    function __construct($like, $videos_id) {
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        $this->videos_id = $videos_id;
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
        if (empty($this->users_id) || empty($this->videos_id)) {
            header('Content-Type: application/json');
            die('{"error":"You must have user and videos set to get a like"}');
        }
        $sql = "SELECT * FROM likes WHERE users_id = $this->users_id AND videos_id = $this->videos_id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        return ($res) ? $res->fetch_assoc() : false;
    }

    private function save() {
        global $global;
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        if (!empty($this->id)) {
            $sql = "UPDATE likes SET `like` = '{$this->like}', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO likes ( `like`,users_id, videos_id, created, modified) VALUES ('{$this->like}', {$this->users_id}, {$this->videos_id}, now(), now())";
        }
        //echo $sql;exit;
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    static function getLikes($videos_id) {
        global $global;

        $obj = new stdClass();
        $obj->videos_id = $videos_id;
        $obj->likes = 0;
        $obj->dislikes = 0;
        $obj->myVote = self::getMyVote($videos_id);

        $sql = "SELECT count(*) as total FROM likes WHERE videos_id = {$videos_id} AND `like` = 1 "; // like
        $res = $global['mysqli']->query($sql);
        if (!$res) {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $row = $res->fetch_assoc();
        $obj->likes = intval($row['total']);

        $sql = "SELECT count(*) as total FROM likes WHERE videos_id = {$videos_id} AND `like` = -1 "; // dislike
        $res = $global['mysqli']->query($sql);
        if (!$res) {
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $row = $res->fetch_assoc();
        $obj->dislikes = intval($row['total']);
        return $obj;
    }
    
    static function getTotalLikes() {
        global $global;

        $obj = new stdClass();
        $obj->likes = 0;
        $obj->dislikes = 0;

        $sql = "SELECT count(*) as total FROM likes WHERE `like` = 1 "; // like
        $res = $global['mysqli']->query($sql);
        if (!$res) {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $row = $res->fetch_assoc();
        $obj->likes = intval($row['total']);

        $sql = "SELECT count(*) as total FROM likes WHERE `like` = -1 "; // dislike
        $res = $global['mysqli']->query($sql);
        if (!$res) {
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $row = $res->fetch_assoc();
        $obj->dislikes = intval($row['total']);
        return $obj;
    }

    static function getMyVote($videos_id) {
        global $global;
        if (!User::isLogged()) {
            return 0;
        }
        $id = User::getId();
        $sql = "SELECT `like` FROM likes WHERE videos_id = {$videos_id} AND users_id = {$id} "; // like
        $res = $global['mysqli']->query($sql);
        if ($row = $res->fetch_assoc()) {
            return intval($row['like']);
        }
        return 0;
    }

}
