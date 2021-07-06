<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class PayPalYPT_log extends ObjectYPT {

    protected $id, $agreement_id, $users_id, $json, $recurring_payment_id, $value, $token;

    static function getSearchFieldsNames() {
        return array('agreement_id', 'json', 'recurring_payment_id', 'token');
    }

    static function getTableName() {
        return 'PayPalYPT_log';
    }

    static function getAllUsers() {
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
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setAgreement_id($agreement_id) {
        $this->agreement_id = $agreement_id;
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function setJson($json) {
        if(!is_string($json)){
            $json = _json_encode($json);
        }
        $this->json = $json;
    }

    function setRecurring_payment_id($recurring_payment_id) {
        $this->recurring_payment_id = $recurring_payment_id;
    }

    function setValue($value) {
        $this->value = floatval($value);
    }

    function setToken($token) {
        $this->token = $token;
    }

    function getId() {
        return intval($this->id);
    }

    function getAgreement_id() {
        return $this->agreement_id;
    }

    function getUsers_id() {
        return intval($this->users_id);
    }

    function getJson() {
        return $this->json;
    }

    function getRecurring_payment_id() {
        return $this->recurring_payment_id;
    }

    function getValue() {
        return floatval($this->value);
    }

    function getToken() {
        return $this->token;
    }
    
    static function getFromToken($token) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  token = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", array($token), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getFromRecurringPaymentId($recurring_payment_id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  recurring_payment_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", array($recurring_payment_id), true);
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
