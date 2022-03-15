<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Meet_schedule extends ObjectYPT
{
    protected $id;
    protected $users_id;
    protected $status;
    protected $public;
    protected $live_stream;
    protected $password;
    protected $topic;
    protected $starts;
    protected $finish;
    protected $name;
    protected $meet_code;
    protected $timezone;

    public static function getSearchFieldsNames()
    {
        return ['password','topic','name','meet_code'];
    }

    public static function getTableName()
    {
        return 'meet_schedule';
    }

    public static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE (canCreateMeet = 1 OR isAdmin = 1) AND status = 'a' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Public = 2
     * Logged Users Only = 1
     * Specific User Groups = 0
     * @return type
     */
    public function setPublic($public)
    {
        $this->public = intval($public);
    }

    public function setLive_stream($live_stream)
    {
        $this->live_stream = intval($live_stream);
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    public function setStarts($starts)
    {
        $this->starts = $starts;
    }

    public function setFinish($finish)
    {
        $this->finish = $finish;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setMeet_code($meet_code)
    {
        $this->meet_code = $meet_code;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getUsers_id()
    {
        return intval($this->users_id);
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Public = 2
     * Logged Users Only = 1
     * Specific User Groups = 0
     * @return type
     */
    public function getPublic()
    {
        return intval($this->public);
    }

    public function getLive_stream()
    {
        return intval($this->live_stream);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getStarts()
    {
        return $this->starts;
    }

    public function getFinish()
    {
        return $this->finish;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCleanName()
    {
        return cleanURLName($this->name);
    }

    public function getMeet_code()
    {
        return $this->meet_code;
    }

    public function getMeetLink()
    {
        global $global;
        return $global['webSiteRootURL'] . 'meet/'.$this->getId().'/' . urlencode($this->getName());
    }

    public function getMeetShortLink()
    {
        global $global;
        return $global['webSiteRootURL'] . 'meet/'.$this->getId();
    }

    public static function getAllFromUsersId($users_id, $time="", $canAttend=false, $hideIfHasPassword=false)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $users_id = intval($users_id);
        if (empty($users_id)) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " ms WHERE (users_id = $users_id ";

        if ($canAttend) {
            $userGroups = UserGroups::getUserGroups($users_id);
            $userGroupsIds = [];
            foreach ($userGroups as $value) {
                $userGroupsIds[] = $value['id'];
            }
            $sql .= " OR (public = 2 OR public = 1 ";
            if (!empty($userGroupsIds)) {
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (". implode(",", $userGroupsIds)."))>0) ";
            }
            $sql .= " )  ";
        }
        if ($hideIfHasPassword) {
            $sql .= " AND (password = '' OR password IS NULL) ";
        }
        $sql .= " )  ";

        $identification = User::getNameIdentificationById($users_id);
        if (!empty($time)) {
            unset($_POST['sort']);
            if ($time=="today") {
                $sql .= " AND date(starts) = CURDATE() ";
                $_POST['sort']['starts']="ASC";
                $sql .= self::getSqlFromPost();
            } elseif ($time=="upcoming") {
                $sql .= " AND date(starts) > CURDATE() ";
                $_POST['sort']['starts']="ASC";
                $sql .= self::getSqlFromPost();
            } elseif ($time=="past") {
                $sql .= " AND date(starts) < CURDATE() ";
                $_POST['sort']['starts']="DESC";
                $sql .= self::getSqlFromPost();
            }
            unset($_POST['sort']);
        } else {
            $sql .= self::getSqlFromPost();
        }
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        unset($_GET['order']);
        if ($res != false) {
            $domain = Meet::getDomainURL();
            foreach ($fullData as $row) {
                $row['identification'] = $identification;
                $row['link'] = Meet::getMeetLink($row['id']);
                if (empty($row['public'])) {
                    $row['userGroups'] = Meet_schedule_has_users_groups::getAllFromSchedule($row['id']);
                } else {
                    $row['userGroups'] = [];
                }
                $row['invitation'] = Meet::getInvitation($row['id']);
                $row['joinURL'] = "";
                if (Meet::canJoinMeet($row['id'])) {
                    $row['joinURL'] = Meet::getJoinURL();
                    $row['roomID'] = Meet::getRoomID($row['id']);
                    $row['jwt'] = Meet::getToken($row['id'], User::getId());
                    $row['domain'] = $domain;
                    $row['iframeURL'] = Meet::getIframeURL($row['id']);
                }

                $row['starts_timezone'] = "{$row['starts']} ".__('Timezone').": {$row['timezone']}";

                $row['starts_in'] = humanTimingAfterwards($row['starts'], 2, $row['timezone']);

                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getTotalFromUsersId($users_id, $time="", $canAttend=false, $hideIfHasPassword=false)
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $users_id = intval($users_id);
        if (empty($users_id)) {
            return false;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE (users_id = $users_id ";

        if ($canAttend) {
            $userGroups = UserGroups::getUserGroups($users_id);
            $userGroupsIds = [];
            foreach ($userGroups as $value) {
                $userGroupsIds[] = $value['id'];
            }
            $sql .= " OR (public = 2 OR public = 1 ";
            if (!empty($userGroupsIds)) {
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (". implode(",", $userGroupsIds)."))>0) ";
            }
            $sql .= " )  ";
        }
        if ($hideIfHasPassword) {
            $sql .= " AND (password = '' OR password IS NULL) ";
        }
        $sql .= " )  ";
        if (!empty($time)) {
            unset($_POST['sort']);
            if ($time=="today") {
                $sql .= " AND date(starts) = CURDATE() ";
                $sql .= " ORDER BY starts ASC ";
            } elseif ($time=="upcoming") {
                $sql .= " AND date(starts) > CURDATE() ";
                $sql .= " ORDER BY starts ASC ";
            } elseif ($time=="past") {
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

    public static function getAll($time="")
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if (!empty($time)) {
            unset($_POST['sort']);
            if ($time=="today") {
                $sql .= " AND date(starts) = CURDATE() ";
                $_POST['sort']['starts']="ASC";
                $sql .= self::getSqlFromPost();
            } elseif ($time=="upcoming") {
                $sql .= " AND date(starts) > CURDATE() ";
                $_POST['sort']['starts']="ASC";
                $sql .= self::getSqlFromPost();
            } elseif ($time=="past") {
                $sql .= " AND date(starts) < CURDATE() ";
                $_POST['sort']['starts']="DESC";
                $sql .= self::getSqlFromPost();
            }
            unset($_POST['sort']);
        } else {
            $sql .= self::getSqlFromPost();
        }
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        unset($_GET['order']);
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['link'] = Meet::getMeetLink($row['id']);
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                if (empty($row['public'])) {
                    $row['userGroups'] = Meet_schedule_has_users_groups::getAllFromSchedule($row['id']);
                } else {
                    $row['userGroups'] = [];
                }

                $row['isModerator'] = Meet::isModerator($row['id']);
                $row['invitation'] = Meet::getInvitation($row['id']);
                $row['joinURL'] = "";
                if (Meet::canJoinMeet($row['id'])) {
                    $row['joinURL'] = Meet::getJoinURL();
                    $row['roomID'] = Meet::getRoomID($row['id']);
                }
                $row['starts_timezone'] = "{$row['starts']} ".__('Timezone').": {$row['timezone']}";
                $row['starts_in'] = humanTimingAfterwards($row['starts'], 2, $row['timezone']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getTotal($time="")
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        if (!empty($time)) {
            unset($_POST['sort']);
            if ($time=="today") {
                $sql .= " AND date(starts) = CURDATE() ";
                $sql .= " ORDER BY starts ASC ";
            } elseif ($time=="upcoming") {
                $sql .= " AND date(starts) > CURDATE() ";
                $sql .= " ORDER BY starts ASC ";
            } elseif ($time=="past") {
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

    public function canManageSchedule()
    {
        if (User::isAdmin()) {
            return true;
        }
        if (empty($this->getUsers_id())) {
            return false;
        }
        if ($this->getUsers_id()==User::getId()) {
            return true;
        }
        return false;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    private function _setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function save()
    {
        if (empty($this->finish)) {
            $this->finish = 'null';
        }

        $this->_setTimeZone(date_default_timezone_get());

        return parent::save();
    }
}
