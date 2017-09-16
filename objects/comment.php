<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
class Comment {
    private $id;
    private $comment;
    private $videos_id;
    private $users_id;

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

    private function load($id) {
        $comment = $this->getComment($id);
        $this->id = $comment['id'];
        $this->comment = $comment['comment'];
        $this->videos_id = $comment['video_id'];
        $this->users_id = $comment['user_id'];
    }

    function save() {
        global $global;
        if(!User::isLogged()){
            header('Content-Type: application/json');
            die('{"error":"'.__("Permission denied").'"}');
        }
        $this->comment = htmlentities($this->comment);
        $this->comment = $global['mysqli']->real_escape_string($this->comment);
        if (!empty($this->id)) {
            $sql = "UPDATE comments SET comment = '{$this->comment}', modified = now() WHERE id = {$this->id}";
        } else {
            $id = User::getId();
            $sql = "INSERT INTO comments ( comment,users_id, videos_id, created, modified) VALUES ('{$this->comment}', {$id}, {$this->videos_id}, now(), now())";
        }
        $resp = $global['mysqli']->query($sql);
        if(empty($resp)){
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }


    function delete() {
        if(!User::isAdmin()){
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

    static function getAllComments($videoId = 0) {
        global $global;
        $sql = "SELECT c.*, u.name as name, u.user as user FROM comments c LEFT JOIN users as u ON u.id = users_id  WHERE 1=1 ";

        if (!empty($videoId)) {
            $sql .= " AND videos_id = {$videoId} ";
        }

        $sql .= BootGrid::getSqlFromPost(array('name'));

        $res = $global['mysqli']->query($sql);
        $comment = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $comment[] = $row;
            }
            //$comment = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $comment = false;
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $comment;
    }

    static function getTotalComments($videoId = 0) {
        global $global;
        $sql = "SELECT id FROM comments WHERE 1=1  ";

        if (!empty($videoId)) {
            $sql .= " AND videos_id = {$videoId} ";
        }
        $sql .= BootGrid::getSqlSearchFromPost(array('name'));

        $res = $global['mysqli']->query($sql);

        return $res->num_rows;
    }

}
