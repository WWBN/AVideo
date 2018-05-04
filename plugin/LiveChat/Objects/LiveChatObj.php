<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveChatObj extends ObjectYPT {

    protected $id, $text, $users_id, $status, $live_stream_code;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'LiveChat';
    }
    
    function getText() {
        return $this->text;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getStatus() {
        return $this->status;
    }

    function getLive_stream_code() {
        return $this->live_stream_code;
    }

    function setText($text) {
        $this->text = $text;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setLive_stream_code($live_stream_code) {
        $this->live_stream_code = $live_stream_code;
    }

    static function getFromChat($live_stream_code, $limit=10) {
        global $global;
        $sql = "SELECT u.*, lc.* FROM  " . static::getTableName() . " lc "
                . " LEFT JOIN users u ON users_id = u.id WHERE live_stream_code='$live_stream_code' ORDER BY lc.created DESC LIMIT $limit ";

        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['photo'] = User::getPhoto($row['users_id']);
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                unset($row['password']);
                unset($row['recoverPass']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        
        $rows = array_reverse($rows);
        
        return $rows;
    }
    
    public function save() {
        global $global;
        if(empty($this->users_id)){
            $this->users_id = 'null';
        }
        $this->text = $global['mysqli']->real_escape_string(trim($this->text));
        return parent::save();
    }


}
