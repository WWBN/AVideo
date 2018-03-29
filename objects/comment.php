<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
class Comment {
    private $id;
    private $comment;
    private $videos_id;
    private $users_id;
    private $comments_id_pai;

    function __construct($comment, $videos_id, $id = 0) {
        if (empty($id)) {
            // get the comment data from comment
            $this->comment = $comment;
            $this->videos_id = $videos_id;
            $this->users_id = User::getId();
        } else {
            // get data from id
            $this->load($id);
        }

    }
    
    function setComments_id_pai($comments_id_pai) {
        $this->comments_id_pai = $comments_id_pai;
    }
    
    function setComment($comment) {
        $this->comment = $comment;
    }

    function getVideos_id() {
        return $this->videos_id;
    }
    
    private function load($id) {
        $comment = $this->getComment($id);
        $this->id = $comment['id'];
        $this->comment = $comment['comment'];
        $this->videos_id = $comment['videos_id'];
        $this->users_id = $comment['users_id'];
    }

    function save() {
        global $global;
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        $this->comment = htmlentities($this->comment);
        $this->comment = $global['mysqli']->real_escape_string($this->comment);
        
        if(empty($this->comment)){
            return false;
        }
        
        if(empty($this->comments_id_pai)){
            $this->comments_id_pai = 'NULL';
        }
        
        if(empty($this->videos_id) && !empty($this->comments_id_pai)){
            $comment = new Comment("", 0, $this->comments_id_pai);
            $this->videos_id = $comment->getVideos_id();
        }
        
        if (!empty($this->id) ) {
            if(!self::userCanAdminComment($this->id)){
                return false;
            }
            $sql = "UPDATE comments SET "
                    . " comment = '{$this->comment}', modified = now() WHERE id = {$this->id}";
        } else {
            $id = User::getId();
            $sql = "INSERT INTO comments ( comment,users_id, videos_id, comments_id_pai, created, modified) VALUES "
                    . " ('{$this->comment}', {$id}, {$this->videos_id}, {$this->comments_id_pai}, now(), now())";
        }
        $resp = $global['mysqli']->query($sql);
        if(empty($resp)){
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }


    function delete() {
        if(!self::userCanAdminComment($this->id)){
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM comments WHERE id = {$this->id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    private function getComment($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM comments WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        return ($res) ? $res->fetch_assoc() : false;
    }

    static function getAllComments($videoId = 0, $comments_id_pai = 'NULL') {
        global $global;
        $sql = "SELECT c.*, u.name as name, u.user as user, "
                . " (SELECT count(id) FROM comments_likes as l where l.comments_id = c.id AND `like` = 1 ) as likes, "
                . " (SELECT count(id) FROM comments_likes as l where l.comments_id = c.id AND `like` = -1 ) as dislikes ";
        
        if (User::isLogged()) {
            $sql .= ", (SELECT `like` FROM comments_likes as l where l.comments_id = c.id AND users_id = " . User::getId() . " ) as myVote ";
        } else {
            $sql .= ", 0 as myVote ";
        }
        
        $sql .= " FROM comments c LEFT JOIN users as u ON u.id = users_id LEFT JOIN videos as v ON v.id = videos_id WHERE 1=1 ";
        
        if (!empty($videoId)) {
            $sql .= " AND videos_id = {$videoId} ";
        }else if(!User::isAdmin() && empty ($comments_id_pai)){
            if(!User::isLogged()){
                die("can not see comments");
            }
            $users_id = User::getId();
            $sql .= " AND (v.users_id = {$users_id} OR c.users_id = $users_id) ";
        }
        
        if($comments_id_pai==='NULL' || empty ($comments_id_pai)){
            $sql .= " AND (comments_id_pai IS NULL ";
            if(empty($videoId) && User::isLogged()){
                $users_id = User::getId();
                $sql .= " OR c.users_id = $users_id ";
            }
            $sql .= ") ";
        }else{
            $sql .= " AND comments_id_pai = {$comments_id_pai} ";
        }

        $sql .= BootGrid::getSqlFromPost(array('name'));

        $res = $global['mysqli']->query($sql);
        $comment = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['commentPlain'] = $row['comment'];
                $row['commentHTML'] = nl2br($row['comment']);
                $comment[] = $row;
            }
            //$comment = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $comment = false;
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $comment;
    }

    static function getTotalComments($videoId = 0, $comments_id_pai = 'NULL') {
        global $global;
        $sql = "SELECT c.id FROM comments c LEFT JOIN users as u ON u.id = users_id LEFT JOIN videos as v ON v.id = videos_id WHERE 1=1  ";

        if (!empty($videoId)) {
            $sql .= " AND videos_id = {$videoId} ";
        }else if(!User::isAdmin() && empty ($comments_id_pai)){
            if(!User::isLogged()){
                die("can not see comments");
            }
            $users_id = User::getId();
            $sql .= " AND (v.users_id = {$users_id} OR c.users_id = $users_id) ";
        }
        
        if($comments_id_pai==='NULL' || empty ($comments_id_pai)){
            $sql .= " AND (comments_id_pai IS NULL ";
            if(empty($videoId) && User::isLogged()){
                $users_id = User::getId();
                $sql .= " OR c.users_id = $users_id ";
            }
            $sql .= ") ";
        }else{
            $sql .= " AND comments_id_pai = {$comments_id_pai} ";
        }
        
        $sql .= BootGrid::getSqlSearchFromPost(array('name'));

        $res = $global['mysqli']->query($sql);

        return $res->num_rows;
    }
    
    static function userCanAdminComment($comments_id){
        if(!User::isLogged()){
            return false;
        }
        if(User::isAdmin()){
            return true;
        }
        $obj = new Comment("", 0, $comments_id);
        if($obj->users_id == User::getId()){
            return true;
        }
        $video = new Video("", "", $obj->videos_id);
        if($video->getUsers_id() == User::getId()){
            return true;
        }
        return false;
    }
    
    static function getTotalCommentsThumbsUpFromUser($users_id, $startDate, $endDate) {
        global $global;
        $sql = "SELECT id from comments  WHERE users_id = {$users_id}  ";

        $res = $global['mysqli']->query($sql);
        
        $r = array('thumbsUp'=>0, 'thumbsDown'=>0 );
        
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $sql = "SELECT id from comments_likes WHERE comments_id = {$row['id']} AND `like` = 1  ";
                if (!empty($startDate)) {
                    $sql .= " AND `created` >= '{$startDate}' ";
                }

                if (!empty($endDate)) {
                    $sql .= " AND `created` <= '{$endDate}' ";
                }
                $res2 = $global['mysqli']->query($sql);
                
                $r['thumbsUp']+=$res2->num_rows;
                
                $sql = "SELECT id from comments_likes WHERE comments_id = {$row['id']} AND `like` = -1  ";
                if (!empty($startDate)) {
                    $sql .= " AND `created` >= '{$startDate}' ";
                }

                if (!empty($endDate)) {
                    $sql .= " AND `created` <= '{$endDate}' ";
                }
                $res2 = $global['mysqli']->query($sql);
                $r['thumbsDown']+=$res2->num_rows;
            }
        } 
        
        return $r;
    }
    
    

}
