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
        $sql = "SELECT * FROM likes WHERE users_id = ? AND videos_id = ? LIMIT 1";
        $stmt = $global['mysqli']->prepare($sql);
        $stmt->bind_param('ii', $this->users_id, $this->videos_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        return ($res) ? $res->fetch_assoc() : false;
    }

    private function save() {
        global $global;
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        if (!empty($this->id)) {
            $sql = "UPDATE likes SET `like` = ?, modified = now() WHERE id = ?";
            $stmt = $global['mysqli']->prepare($sql);
            $stmt->bind_param('ii', $this->like, $this->id);
        } else {
            $sql = "INSERT INTO likes ( `like`,users_id, videos_id, created, modified) VALUES (?, ?, ?, now(), now())";
            $stmt = $global['mysqli']->prepare($sql);
            $stmt->bind_param('iii', $this->like, $this->users_id, $this->videos_id);
        }
        //echo $sql;exit;
        $stmt->execute();
        $resp = $stmt->get_result();
        $stmt->close();
        if ($global['mysqli']->errno!=0) {
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

        $sql = "SELECT count(*) as total FROM likes WHERE videos_id = ? AND `like` = 1 "; // like
        $stmt = $global['mysqli']->prepare($sql);
        $stmt->bind_param('i', $videos_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        if ($global['mysqli']->errno!=0) {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $row = $res->fetch_assoc();
        $obj->likes = intval($row['total']);

        $sql = "SELECT count(*) as total FROM likes WHERE videos_id = ? AND `like` = -1 "; // dislike
        $stmt = $global['mysqli']->prepare($sql);
        $stmt->bind_param('i', $videos_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        if ($global['mysqli']->errno!=0) {
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
        $sql = "SELECT `like` FROM likes WHERE videos_id = ? AND users_id = ? "; // like
        $stmt = $global['mysqli']->prepare($sql);
        $stmt->bind_param('ii', $videos_id,$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        if ($row = $res->fetch_assoc()) {
            return intval($row['like']);
        }
        return 0;
    }

}
