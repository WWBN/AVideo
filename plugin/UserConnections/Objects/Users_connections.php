<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Users_connections extends ObjectYPT
{
    //'pending', 'approved', 'blocked'
    const STATUS_I_NEED_TO_APPROVE = 'n';
    const STATUS_PENDING = 'p';
    const STATUS_APPROVED = 'a';
    const STATUS_BLOCKED = 'b';
    const STATUS_INACTIVE = 'i';

    const STATUS_TEXT = array(
        self::STATUS_I_NEED_TO_APPROVE => 'Need to be approved',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_BLOCKED => 'Blocked',
        self::STATUS_INACTIVE => 'Inactive',
    );

    const STATUS_ICONS = array(
        self::STATUS_I_NEED_TO_APPROVE => 'fa-user-clock',
        self::STATUS_PENDING => 'fa-hourglass-half',
        self::STATUS_APPROVED => 'fa-check-circle',
        self::STATUS_BLOCKED => 'fa-ban',
        self::STATUS_INACTIVE => 'fa-times-circle',
    );

    const STATUS_LABELS = array(
        self::STATUS_I_NEED_TO_APPROVE => 'label-warning',
        self::STATUS_PENDING => 'label-info',
        self::STATUS_APPROVED => 'label-success',
        self::STATUS_BLOCKED => 'label-danger',
        self::STATUS_INACTIVE => 'label-default',
    );

    protected $id, $users_id1, $users_id2, $user1_status, $user2_status, $user1_mute, $user2_mute, $created_php_time, $modified_php_time, $json;

    static function getSearchFieldsNames()
    {
        return array('json');
    }

    static function getTableName()
    {
        return 'users_connections';
    }

    static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

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
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }


    function setId($id)
    {
        $this->id = intval($id);
    }

    function setUsers_id1($users_id1)
    {
        $this->users_id1 = intval($users_id1);
    }

    function setUsers_id2($users_id2)
    {
        $this->users_id2 = intval($users_id2);
    }

    function setUser1_status($user1_status)
    {
        $this->user1_status = $user1_status;
    }

    function setUser2_status($user2_status)
    {
        $this->user2_status = $user2_status;
    }

    function setUser1_mute($user1_mute)
    {
        $this->user1_mute = intval($user1_mute);
    }

    function setUser2_mute($user2_mute)
    {
        $this->user2_mute = intval($user2_mute);
    }

    function setCreated_php_time($created_php_time)
    {
        $this->created_php_time = $created_php_time;
    }

    function setModified_php_time($modified_php_time)
    {
        $this->modified_php_time = $modified_php_time;
    }

    function setJson($json)
    {
        $this->json = $json;
    }


    function getId()
    {
        return intval($this->id);
    }

    function getUsers_id1()
    {
        return intval($this->users_id1);
    }

    function getUsers_id2()
    {
        return intval($this->users_id2);
    }

    function getUser1_status()
    {
        return $this->user1_status;
    }

    function getUser2_status()
    {
        return $this->user2_status;
    }

    function getUser1_mute()
    {
        return intval($this->user1_mute);
    }

    function getUser2_mute()
    {
        return intval($this->user2_mute);
    }

    function getCreated_php_time()
    {
        return $this->created_php_time;
    }

    function getModified_php_time()
    {
        return $this->modified_php_time;
    }

    function getJson()
    {
        return $this->json;
    }

    static function getConnection($users_id1, $users_id2){
        global $global;
        $sql = "SELECT *
        FROM users_connections
        WHERE (users_id1 = ? AND users_id2 = ?)
        OR (users_id1 = ? AND users_id2 = ?)
        LIMIT 1";
        $res = sqlDAL::readSql($sql, 'iiii', [$users_id1, $users_id2, $users_id2, $users_id1], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function save(){
        if(empty($this->user1_status)){
            $this->user1_status = self::STATUS_APPROVED;
        }
        if(empty($this->user2_status)){
            $this->user2_status = self::STATUS_I_NEED_TO_APPROVE;
        }
        return parent::save();
    }

    static function getAllConnections($users_id, $validOnly = false){
        global $global;
        $sql = "SELECT *
            FROM users_connections
            WHERE 
                (users_id1 = ? OR users_id2 = ?)";
        $formats = 'ii';
        $values = [$users_id, $users_id];
        if($validOnly){
            $sql .= "AND user1_status = ? AND user2_status = ?";
            $formats .= 'ss';
            $values[] = self::STATUS_APPROVED;
            $values[] = self::STATUS_APPROVED;
        }

        //$sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['isValid'] = $row['user1_status'] === Users_connections::STATUS_APPROVED && $row['user2_status'] === Users_connections::STATUS_APPROVED;
                if($users_id == $row['users_id1']){
                    $row['friend_users_id'] = $row['users_id2'];
                    $row['my_users_id'] = $row['users_id1'];
                }else{
                    $row['friend_users_id'] = $row['users_id1'];
                    $row['my_users_id'] = $row['users_id2'];
                }
                $row['friend'] = User::getNameIdentificationById($row['friend_users_id']);
                $row['status'] = UserConnections::getMyConnectionStatusFromRow($users_id, $row);
                $row['status_friend'] = self::STATUS_TEXT[$row['status']['friend']];
                $row['status_mine'] = self::STATUS_TEXT[$row['status']['mine']];
                $row['general_status'] = UserConnections::getCurrentConnectionStatus($users_id, $row);
                $row['general_status_text'] = self::STATUS_TEXT[$row['general_status']];
                $row['general_status_html'] = self::getStatusLabel($row['general_status']);
                $rows[] = $row;
            }
        } 
        return $rows;
    }

    public static function getStatusLabel($status)
    {
        if (!array_key_exists($status, self::STATUS_TEXT)) {
            return '';
        }

        $statusText = self::STATUS_TEXT[$status];
        $statusIcon = self::STATUS_ICONS[$status];
        $statusLabel = self::STATUS_LABELS[$status];

        return "<span class='label $statusLabel'><i class='fa $statusIcon'></i> $statusText</span>";
    }
}
