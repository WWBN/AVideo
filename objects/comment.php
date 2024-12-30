<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class Comment {
    protected $properties = [];

    protected $id;
    protected $comment;
    protected $videos_id;
    protected $users_id;
    protected $comments_id_pai;
    protected $pin;

    public function __construct($comment, $videos_id, $id = 0) {
        if (empty($id)) {
            // get the comment data from comment
            $this->comment = xss_esc($comment);
            $this->videos_id = $videos_id;
            $this->users_id = User::getId();
        } else {
            // get data from id
            $this->load($id);
        }
    }

    public function getPin() {
        return $this->pin;
    }

    public function setPin($pin): void {
        $this->pin = intval($pin);
    }

    public function getId() {
        return $this->id;
    }

    public function setComments_id_pai($comments_id_pai) {
        $this->comments_id_pai = $comments_id_pai;
    }

    public function getComments_id_pai() {
        return $this->comments_id_pai;
    }

    public function getUsers_id() {
        return $this->users_id;
    }

    public function setComment($comment) {
        $this->comment = xss_esc($comment);
    }

    public function getCommentText() {
        return $this->comment;
    }

    public function getVideos_id() {
        return $this->videos_id;
    }

    public function load($id, $refreshCache = false) {
        $row = self::getComment($id);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public function save() {
        global $global;
        if (!User::isLogged()) {
            forbiddenPage('Permission denied');
        }
        //$this->comment = htmlentities($this->comment);

        if (empty($this->comment)) {
            return false;
        }

        if (empty($this->comments_id_pai)) {
            $this->comments_id_pai = 'NULL';
        }

        $this->pin = intval($this->pin);

        if (empty($this->videos_id) && !empty($this->comments_id_pai)) {
            $comment = new Comment("", 0, $this->comments_id_pai);
            $this->videos_id = $comment->getVideos_id();
        }
        if(!empty($_REQUEST['comment_users_id']) && User::isAdmin()){
            $users_id = intval($_REQUEST['comment_users_id']);
        }else{
            $users_id = User::getId();
        }
        if (!empty($this->id)) {
            if (!self::userCanAdminComment($this->id)) {
                return false;
            }
            $sql = "UPDATE comments SET "
                    . " comment = ?, pin = ?, modified = now() WHERE id = ?";
            $resp = sqlDAL::writeSql($sql, "sii", [xss_esc($this->comment), $this->pin, $this->id]);
        } else {
            $sql = "INSERT INTO comments ( comment,users_id, videos_id, comments_id_pai, created, modified) VALUES "
                    . " (?, ?, ?, {$this->comments_id_pai}, now(), now())";
            $resp = sqlDAL::writeSql($sql, "sii", [xss_esc($this->comment), $users_id, $this->videos_id]);
        }
        /**
         * @var array $global
         * @var object $global['mysqli']
         */
        if ((empty($resp)) && ($global['mysqli']->errno != 0)) {
            die('Error (comment save) : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        if (empty($this->id)) {
            // log_error("note: insert_id works? ".$global['mysqli']->insert_id); // success!
            $id = $global['mysqli']->insert_id;
            $this->id = $id;
        } else {
            $id = $this->id;
        }
        if (empty($this->comments_id_pai) || $this->comments_id_pai == 'NULL') {
            AVideoPlugin::afterNewComment($this->id);
        } else {
            AVideoPlugin::afterNewResponse($this->id);
        }
        
        $cacheHandler = new VideoCacheHandler($this->videos_id);
        $cacheHandler->deleteCache();
        return $id;
    }

    public function delete() {
        if (!self::userCanAdminComment($this->id)) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM comments WHERE id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql, "i", [$this->id]);
    }

    static function getComment($id, $refreshCache = false) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM comments WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id], $refreshCache);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return ($res != false) ? $result : false;
    }

    public static function getAllComments($videoId = 0, $comments_id_pai = 'NULL', $video_owner_users_id = 0, $includeResponses = false) {
        global $global;
        $format = '';
        $values = [];
        $sql = "SELECT c.*, u.name as name, u.user as user, "
                . " (SELECT count(id) FROM comments_likes as l where l.comments_id = c.id AND `like` = 1 ) as likes, "
                . " (SELECT count(id) FROM comments_likes as l where l.comments_id = c.id AND `like` = -1 ) as dislikes ";
        $users_id = User::getId();

        if (!empty($users_id)) {
            $sql .= ", (SELECT `like` FROM comments_likes as l where l.comments_id = c.id AND users_id = ? ) as myVote ";
            $format .= "i";
            $values[] = $users_id;
        } else {
            $sql .= ", 0 as myVote ";
        }

        $sql .= " FROM comments c LEFT JOIN users as u ON u.id = users_id LEFT JOIN videos as v ON v.id = videos_id";
        $sql .= " WHERE 1=1 AND u.status = 'a' ";

        if (!empty($videoId)) {
            $sql .= " AND videos_id = ? ";
            $format .= "i";
            $values[] = $videoId;
        } elseif (!Permissions::canAdminComment() && empty($comments_id_pai)) {
            if (!User::isLogged()) {
                die("can not see comments");
            }
            $users_id = User::getId();
            if(!empty($users_id)){
                $sql .= " AND (v.users_id = ? OR c.users_id = ?) ";
                $format .= "ii";
                $values[] = $users_id;
                $values[] = $users_id;
            }
        }

        if (_empty($comments_id_pai)) {
            $sql .= " AND (comments_id_pai IS NULL ";
            if (empty($videoId) && User::isLogged()) {
                $users_id = User::getId();
                if(!empty($users_id)){
                    $sql .= " OR c.users_id = ? ";
                    $format .= "i";
                    $values[] = $users_id;
                }
            }
            $sql .= ") ";
        } else {
            $sql .= " AND comments_id_pai = ? ";
            $format .= "s";
            $values[] = $comments_id_pai;
        }

        if (!empty($video_owner_users_id)) {
            $sql .= " AND v.users_id = ? ";
            $format .= "i";
            $values[] = $video_owner_users_id;
        }

        $sql .= BootGrid::getSqlFromPost(['name']);
        if($comments_id_pai == 9){
            //echo PHP_EOL.PHP_EOL.'>>>>'.PHP_EOL.PHP_EOL;var_dump($comments_id_pai,$video_owner_users_id, $sql, $values);echo PHP_EOL.'<<<<'.PHP_EOL.PHP_EOL;exit;
        }
        //echo PHP_EOL.PHP_EOL.'>>>>'.PHP_EOL.PHP_EOL;var_dump($comments_id_pai,$video_owner_users_id, $sql, $values);echo PHP_EOL.'<<<<'.PHP_EOL.PHP_EOL;//exit;
        $res = sqlDAL::readSql($sql, $format, $values);
        $allData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $comment = [];
        if ($res != false) {
            foreach ($allData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $row['comment'] = str_replace('\n', "\n", $row['comment']);
                $row['commentPlain'] = xss_esc_back($row['comment']);
                $row['commentHTML'] = markDownToHTML(str_replace('`', "'", $row['commentPlain']));
                $row['commentHTML'] = linkifyTimestamps($row['commentHTML']);
                $row['responses'] = array();
                if($includeResponses){
                    $row['responses'] = self::getAllComments($videoId, $row['id'], $video_owner_users_id, $includeResponses);
                }
                $comment[] = $row;
            }
            //$comment = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            /**
             * @var array $global
             * @var object $global['mysqli']
             */
            $comment = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $comment;
    }

    public static function getTotalComments($videoId = 0, $comments_id_pai = 'NULL', $video_owner_users_id = 0) {
        global $global;
        $format = '';
        $values = [];
        $sql = "SELECT c.id FROM comments c LEFT JOIN users as u ON u.id = users_id LEFT JOIN videos as v ON v.id = videos_id WHERE 1=1  ";

        if (!empty($videoId)) {
            $sql .= " AND videos_id = ? ";
            $format .= "i";
            $values[] = $videoId;
        } elseif (!Permissions::canAdminComment() && empty($comments_id_pai)) {
            if (!User::isLogged()) {
                die("can not see comments");
            }
            $users_id = User::getId();
            $sql .= " AND (v.users_id = ? OR c.users_id = ?) ";
            $format .= "ii";
            $values[] = $users_id;
            $values[] = $users_id;
        }

        if ($comments_id_pai === 'NULL' || empty($comments_id_pai)) {
            $sql .= " AND (comments_id_pai IS NULL ";
            if (empty($videoId) && User::isLogged()) {
                $users_id = User::getId();
                $sql .= " OR c.users_id = ? ";
                $format .= "i";
                $values[] = $users_id;
            }
            $sql .= ") ";
        } else {
            $sql .= " AND comments_id_pai = ? ";
            $format .= "s";
            $values[] = $comments_id_pai;
        }

        if (!empty($video_owner_users_id)) {
            $sql .= " AND v.users_id = ? ";
            $format .= "i";
            $values[] = $video_owner_users_id;
        }

        $sql .= BootGrid::getSqlSearchFromPost(['name']);

        $res = sqlDAL::readSql($sql, $format, $values);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);

        return $countRow;
    }

    public static function userCanAdminComment($comments_id) {
        if (!User::isLogged()) {
            return false;
        }
        if (Permissions::canAdminComment()) {
            return true;
        }
        $obj = new Comment("", 0, $comments_id);
        if ($obj->users_id == User::getId()) {
            return true;
        }
        $video = new Video("", "", $obj->videos_id);
        if ($video->getUsers_id() == User::getId()) {
            return true;
        }
        return false;
    }

    public static function userCanEditComment($comments_id) {
        if (!User::isLogged()) {
            return false;
        }
        if (Permissions::canAdminComment()) {
            return true;
        }
        $obj = new Comment("", 0, $comments_id);
        if ($obj->users_id == User::getId()) {
            return true;
        }
        return false;
    }

    public static function getTotalCommentsThumbsUpFromUser($users_id, $startDate, $endDate) {
        global $global;
        $sql = "SELECT id from comments  WHERE users_id = ?";
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $r = ['thumbsUp' => 0, 'thumbsDown' => 0];
        if ($res != false) {
            foreach ($fullData as $row) {
                $format = "i";
                $values = [$row['id']];
                $sql = "SELECT id from comments_likes WHERE comments_id = ? AND `like` = 1  ";
                if (!empty($startDate)) {
                    $sql .= " AND `created` >= ? ";
                    $format .= "s";
                    $values[] = $startDate;
                }
                if (!empty($endDate)) {
                    $sql .= " AND `created` <= ? ";
                    $format .= "s";
                    $values[] = $endDate;
                }
                $res = sqlDAL::readSql($sql, $format, $values);
                $countRow = sqlDAL::num_rows($res);
                sqlDAL::close($res);
                $r['thumbsUp'] += $countRow;
                $format = "i";
                $values = [$row['id']];
                $sql = "SELECT id from comments_likes WHERE comments_id = ? AND `like` = -1  ";
                if (!empty($startDate)) {
                    $sql .= " AND `created` >= ? ";
                    $format .= "s";
                    $values[] = $startDate;
                }
                if (!empty($endDate)) {
                    $sql .= " AND `created` <= ? ";
                    $format .= "s";
                    $values[] = $endDate;
                }
                $sql .= " LIMIT 10000 ";
                $res = sqlDAL::readSql($sql, $format, $values);
                $countRow = sqlDAL::num_rows($res);
                sqlDAL::close($res);
                $r['thumbsDown'] += $countRow;
            }
        }
        return $r;
    }

    static function addExtraInfo($commentsArray) {
        foreach ($commentsArray as $key2 => $value2) {
            $commentsArray[$key2] = self::addExtraInfo2($value2);
        }
        return $commentsArray;
    }

    static function addExtraInfo2InRows($rows) {
        foreach ($rows as $key => $value) {
            $rows[$key] = Comment::addExtraInfo2($value);
            if(!empty($rows[$key]['responses'])){
                $rows[$key]['responses'] = self::addExtraInfo2InRows($rows[$key]['responses']);
            }
        }
        return $rows;
    }

    static function addExtraInfo2($row) {
        if(empty($row['commentHTML'])){
            $row['comment'] = str_replace('\n', "\n", $row['comment']);
            $row['commentPlain'] = xss_esc_back($row['comment']);
            $row['commentHTML'] = markDownToHTML(str_replace('`', "'", $row['commentPlain']));
        }

        $row['identification'] = User::getNameIdentificationById($row['users_id']);
        $row['commentWithLinks'] = self::fixCommentText(textToLink($row['commentHTML']));
        $row['commentWithLinks'] = linkifyTimestamps($row['commentWithLinks']);
        $row['humanTiming'] = humanTiming(strtotime($row['created']));
        $row['channelLink'] = User::getChannelLink($row['users_id']);
        $row['photo'] = User::getPhoto($row['users_id']);
        $row['comment'] = '<div class="pull-left">'
                . '<img src="' . $row['photo'] . '" alt="User Photo" class="img img-responsive img-circle" style="max-width: 50px;"/></div>'
                . '<div class="commentDetails"><div class="commenterName"><strong><a href="' . $row['channelLink'] . '">' . $row['identification'] . '</a></strong> '
                . '<small>' . $row['humanTiming'] . '</small></div>' . $row['commentWithLinks'] . '</div>';
        $row['total_replies'] = Comment::getTotalComments($row['videos_id'], $row['id']);
        $row['video'] = Video::getVideo($row['videos_id']);
        unset($row['video']['description']);
        $row['poster'] = Video::getImageFromFilename($row['video']['filename']);
        $row['userCanAdminComment'] = Comment::userCanAdminComment($row['id']);
        $row['userCanEditComment'] = Comment::userCanEditComment($row['id']);

        $row['userPhotoURL'] = $row['photo'];
        $row['userName'] = $row['identification'];
        return $row;
    }

    static function fixCommentText($subject) {
        $search = ['\n'];
        $replace = ["<br/>"];
        return stripslashes(str_replace($search, $replace, $subject));
    }
}
