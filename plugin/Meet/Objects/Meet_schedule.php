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
        return ['password', 'topic', 'name', 'meet_code'];
    }

    public static function getTableName()
    {
        return 'meet_schedule';
    }

    public static function getAllUsers()
    {
        global $global;
        if (empty($global)) {
            $global = [];
        }
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
     * @return string
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
        $this->topic = xss_esc($topic);
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
     * @return string
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
        // I need the id so it will be in the recorded filename
        return $this->users_id . '-' . cleanURLName($this->name);
    }

    public function getMeet_code()
    {
        return $this->meet_code;
    }

    public function getMeetLink()
    {
        global $global;
        return $global['webSiteRootURL'] . 'meet/' . $this->getId() . '/' . urlencode($this->getName());
    }

    public function getMeetShortLink()
    {
        global $global;
        return $global['webSiteRootURL'] . 'meet/' . $this->getId();
    }

    static public function getSQLTime($time, $sort = true)
    {
        $sort = @$_POST['sort'];
        unset($_POST['sort']);
        $sql = '';
        $dateStarts = ' COALESCE(CONVERT_TZ(starts, timezone, @@session.time_zone), starts) ';
        if ($time == "today") {
            //$sql .= " AND {$dateStarts} = CURDATE() ";
            //select records where the datetime is from today until 12 AM the next day
            $sql .= " AND {$dateStarts} BETWEEN DATE_SUB(NOW(), INTERVAL 4 HOUR) AND DATE_ADD(DATE(NOW()), INTERVAL 24 HOUR) ";
            $_POST['sort']['starts'] = "ASC";
        } elseif ($time == "upcoming") {
            $sql .= " AND {$dateStarts} > CURDATE() ";
            $_POST['sort']['starts'] = "ASC";
        } elseif ($time == "past") {
            $sql .= " AND {$dateStarts} < CURDATE() ";
            $_POST['sort']['starts'] = "DESC";
        }
        if ($sort) {
            $sql .= self::getSqlFromPost();
        } else {
            $sql .= self::getSqlSearchFromPost();
        }
        $_POST['sort'] = $sort;
        //var_dump($sql, debug_backtrace());
        return $sql;
    }

    public static function getAllFromUsersId($users_id, $time = "", $canAttend = false, $hideIfHasPassword = false)
    {
        global $global;
        if (empty($global)) {
            $global = [];
        }
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
            $sql .= " OR ((public = 2 OR public = 1) AND (password IS NULL OR password = '') ";
            if (!empty($userGroupsIds)) {
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (" . implode(",", $userGroupsIds) . "))>0) ";
            }
            $sql .= " )  ";
        }
        if ($hideIfHasPassword) {
            $sql .= " AND (password = '' OR password IS NULL) ";
        }
        $sql .= " )  ";

        $identification = User::getNameIdentificationById($users_id);
        if (!empty($time)) {
            $sql .= self::getSQLTime($time);
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
            $liveIsEnabled = AVideoPlugin::isEnabledByName('Live');

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

                $row['starts_timezone'] = "{$row['starts']} " . __('Timezone') . ": {$row['timezone']}";

                $row['starts_in'] = humanTimingAfterwards($row['starts'], 2, $row['timezone']);

                $row['RTMPLink'] = false;
                $row['LinkToLive'] = false;

                if ($liveIsEnabled) {
                    $row['RTMPLink'] = Live::getRTMPLink($users_id);
                    $row['LinkToLive'] = Live::getLinkToLiveFromUsers_id($users_id);
                    $row['LinkToLiveEmbed'] = addQueryStringParameter($row['LinkToLive'], 'embed', 1);
                }

                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getTotalFromUsersId($users_id, $time = "", $canAttend = false, $hideIfHasPassword = false)
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
            $sql .= " OR ((public = 2 OR public = 1) AND (password IS NULL OR password = '') ";
            if (!empty($userGroupsIds)) {
                $sql .= " OR (public = 0 AND (SELECT count(id) FROM meet_schedule_has_users_groups WHERE meet_schedule_id=ms.id AND users_groups_id IN (" . implode(",", $userGroupsIds) . "))>0) ";
            }
            $sql .= " )  ";
        }
        if ($hideIfHasPassword) {
            $sql .= " AND (password = '' OR password IS NULL) ";
        }
        $sql .= " )  ";
        if (!empty($time)) {
            $sql .= self::getSQLTime($time, false);
        } else {
            $sql .= self::getSqlSearchFromPost();
        }
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public static function getAll($time = "")
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if (!empty($time)) {
            $sql .= self::getSQLTime($time);
        } else {
            $sql .= self::getSqlFromPost();
        }
        //echo $sql;
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
                $row['starts_timezone'] = "{$row['starts']} " . __('Timezone') . ": {$row['timezone']}";
                $row['starts_in'] = humanTimingAfterwards($row['starts'], 2, $row['timezone']);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getTotal($time = "")
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        if (!empty($time)) {
            $sql .= self::getSQLTime($time, false);
        } else {
            $sql .= self::getSqlSearchFromPost();
        }
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
        if ($this->getUsers_id() == User::getId()) {
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

    static function getFromName($name, $refreshCache = false)
    {
        global $global;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? ORDER BY id DESC LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "s", [$name]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
}
