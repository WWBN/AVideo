<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';


class AD_Overlay_Code extends ObjectYPT {

    protected $id, $users_id, $code;

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
        $this->code = $code;
    }



}
