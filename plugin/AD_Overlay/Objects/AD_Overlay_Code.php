<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class AD_Overlay_Code extends ObjectYPT {

    protected $id, $users_id, $code, $status;

    function loadFromUser($users_id) {
        $row = self::getFromDbFromUser($users_id);
        $this->setUsers_id($users_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static protected function getFromDbFromUser($users_id) {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getSearchFieldsNames() {
        return array('code');
    }

    static function getTableName() {
        return 'ad_overlay_codes';
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getCode() {
        return $this->code;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setCode($code) {
        $this->code = self::filterCode($code);
    }

    function getStatus() {
        return $this->status;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    static function filterCode($data) {
        global $global;
        //$data = preg_replace('/[\x00-\x1F\x7F]/u', '', $data);
        // normalize $data because of get_magic_quotes_gpc
        $dataNeedsStripSlashes = get_magic_quotes_gpc();
        if ($dataNeedsStripSlashes) {
            $data = stripslashes($data);
        }

        // normalize $data because of whitespace on beginning and end
        $data = trim($data);

        // strip tags
        //$data = strip_tags($data);

        // replace characters with their HTML entitites
        //$data = htmlentities($data);

        // mysql escape string   
        $data = $global['mysqli']->real_escape_string($data);

        return $data;
    }

}
