<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Meet_schedule extends ObjectYPT {

    protected $id,$users_id,$status,$public,$live_stream,$password,$topic,$starts,$finish,$name,$meet_code;

    static function getSearchFieldsNames() {
        return array('password','topic','name','meet_code');
    }

    static function getTableName() {
        return 'meet_schedule';
    }

    static function getAllUsers() {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE (canCreateMeet = 1 OR isAdmin = 1) AND status = 'a' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Public = 2
     * Logged Users Only = 1
     * Specific User Groups = 0
     * @return type
     */
    function setPublic($public) {
        $this->public = intval($public);
    }

    function setLive_stream($live_stream) {
        $this->live_stream = intval($live_stream);
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setTopic($topic) {
        $this->topic = $topic;
    }

    function setStarts($starts) {
        $this->starts = $starts;
    }

    function setFinish($finish) {
        $this->finish = $finish;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setMeet_code($meet_code) {
        $this->meet_code = $meet_code;
    }

    function getId() {
        return intval($this->id);
    }

    function getUsers_id() {
        return intval($this->users_id);
    }

    function getStatus() {
        return $this->status;
    }

    /**
     * Public = 2
     * Logged Users Only = 1
     * Specific User Groups = 0
     * @return type
     */
    function getPublic() {
        return intval($this->public);
    }

    function getLive_stream() {
        return intval($this->live_stream);
    }

    function getPassword() {
        return $this->password;
    }

    function getTopic() {
        return $this->topic;
    }

    function getStarts() {
        return $this->starts;
    }

    function getFinish() {
        return $this->finish;
    }

    function getName() {
        return $this->name;
    }

    function getCleanName(){
        return cleanURLName($this->name);
    }

    function getMeet_code() {
        return $this->meet_code;
    }

    public function getMeetLink() {
        global $global;
        return $global['webSiteRootURL'] . 'meet/'.$this->getId().'/' . urlencode($this->getName());
    }

    public function getMeetShortLink() {
        global $global;
        return $global['webSiteRootURL'] . 'meet/'.$this->getId();
    }

    static function getAllFromUsersId($users_id, $time="", $canAttend=false) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $users_id = intval($users_id);
        if(empty($users_id)){
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " ms WHERE users_id = $users_id ";

        if($canAttend){
            $userGroups = UserGroups::getUserGroups($users_id);
            $userGroupsIds = array();
            foreach ($userGroups as $value){
                $userGroupsIds[] = $value['id'];
            }
            $sql .= " OR (public = 2 OR public = 1 ";
            if(!empty($userGroupsIds)){
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (". implode(",", $userGroupsIds)."))>0) ";
            }
            $sql .= " )  ";
        }

        $identification = User::getNameIdentificationById($users_id);
        if(!empty($time)){
            unset($_POST['sort']);
            if($time=="today"){
               $sql .= " AND date(starts) = CURDATE() ";
               $_POST['sort']['starts']="ASC";
               $sql .= self::getSqlFromPost();
            }else if($time=="upcoming"){
               $sql .= " AND date(starts) > CURDATE() ";
               $_POST['sort']['starts']="ASC";
               $sql .= self::getSqlFromPost();
            }else if($time=="past"){
               $sql .= " AND date(starts) < CURDATE() ";
               $_POST['sort']['starts']="DESC";
               $sql .= self::getSqlFromPost();
            }
            unset($_POST['sort']);
        }else{
            $sql .= self::getSqlFromPost();
        }
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        unset($_GET['order']);
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['identification'] = $identification;
                $row['link'] = Meet::getMeetLink($row['id']);
                if(empty($row['public'])){
                    $row['userGroups'] = Meet_schedule_has_users_groups::getAllFromSchedule($row['id']);
                }else{
                    $row['userGroups'] = array();
                }
                $row['invitation'] = Meet::getInvitation($row['id']);
                $row['joinURL'] = "";
                if(Meet::canJoinMeet($row['id'])){
                    $row['joinURL'] = Meet::getJoinURL();
                    $row['roomID'] = Meet::getRoomID($row['id']);
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getTotalFromUsersId($users_id, $time="", $canAttend=false) {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $users_id = intval($users_id);
        if(empty($users_id)){
            return false;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";

        if($canAttend){
            $userGroups = UserGroups::getUserGroups($users_id);
            $userGroupsIds = array();
            foreach ($userGroups as $value){
                $userGroupsIds[] = $value['id'];
            }
            $sql .= " OR (public = 2 OR public = 1 ";
            if(!empty($userGroupsIds)){
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (". implode(",", $userGroupsIds)."))>0) ";
            }
            $sql .= " )  ";
        }
        if(!empty($time)){
            unset($_POST['sort']);
            if($time=="today"){
               $sql .= " AND date(starts) = CURDATE() ";
               $sql .= " ORDER BY starts ASC ";
            }else if($time=="upcoming"){
               $sql .= " AND date(starts) > CURDATE() ";
               $sql .= " ORDER BY starts ASC ";
            }else if($time=="past"){
               $sql .= " AND date(starts) < CURDATE() ";
               $sql .= " ORDER BY starts DESC ";
            }
        }
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    static function getAll($time="") {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if(!empty($time)){
            unset($_POST['sort']);
            if($time=="today"){
               $sql .= " AND date(starts) = CURDATE() ";
               $_POST['sort']['starts']="ASC";
               $sql .= self::getSqlFromPost();
            }else if($time=="upcoming"){
               $sql .= " AND date(starts) > CURDATE() ";
               $_POST['sort']['starts']="ASC";
               $sql .= self::getSqlFromPost();
            }else if($time=="past"){
               $sql .= " AND date(starts) < CURDATE() ";
               $_POST['sort']['starts']="DESC";
               $sql .= self::getSqlFromPost();
            }
            unset($_POST['sort']);
        }else{
            $sql .= self::getSqlFromPost();
        }
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        unset($_GET['order']);
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['link'] = Meet::getMeetLink($row['id']);
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                if(empty($row['public'])){
                    $row['userGroups'] = Meet_schedule_has_users_groups::getAllFromSchedule($row['id']);
                }else{
                    $row['userGroups'] = array();
                }

                $row['isModerator'] = Meet::isModerator($row['id']);
                $row['invitation'] = Meet::getInvitation($row['id']);
                $row['joinURL'] = "";
                if(Meet::canJoinMeet($row['id'])){
                    $row['joinURL'] = Meet::getJoinURL();
                    $row['roomID'] = Meet::getRoomID($row['id']);
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getTotal($time="") {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        if(!empty($time)){
            unset($_POST['sort']);
            if($time=="today"){
               $sql .= " AND date(starts) = CURDATE() ";
               $sql .= " ORDER BY starts ASC ";
            }else if($time=="upcoming"){
               $sql .= " AND date(starts) > CURDATE() ";
               $sql .= " ORDER BY starts ASC ";
            }else if($time=="past"){
               $sql .= " AND date(starts) < CURDATE() ";
               $sql .= " ORDER BY starts DESC ";
            }
        }
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    function canManageSchedule(){
        if(User::isAdmin()){
            return true;
        }
        if(empty($this->getUsers_id())){
            return false;
        }
        if($this->getUsers_id()==User::getId()){
            return true;
        }
        return false;
    }

    function save() {
        if(empty($this->finish)){
            $this->finish = 'null';
        }
        return parent::save();
    }

}
