<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class BlockonomicsOrder extends ObjectYPT {

    protected $id, $addr, $txid, $status, $bits, $bits_payed, $users_id, $total_value, $currency, $created;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'blockonomics_order';
    }

    function getId() {
        return $this->id;
    }

    function getAddr() {
        return $this->addr;
    }

    function getTxid() {
        return $this->txid;
    }

    function getStatus() {
        return $this->status;
    }

    function getCreated() {
        return $this->created;
    }

    function getBits() {
        return floatval($this->bits);
    }

    function getFormatedBits() {
        return $this->bits / 1.0e8;
    }

    function getBits_payed() {
        return floatval($this->bits_payed);
    }

    function getFormatedBits_payed() {
        return $this->bits_payed / 1.0e8;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function setAddr($addr) {
        $this->addr = $addr;
    }

    function setTxid($txid) {
        $this->txid = $txid;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setBits($bits) {
        $this->bits = floatval($bits);
    }

    function setBits_payed($bits_payed) {
        $this->bits_payed = floatval($bits_payed);
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function getTotal_value() {
        return $this->total_value;
    }

    function getCurrency() {
        return $this->currency;
    }

    function setTotal_value($total_value) {
        $this->total_value = $total_value;
    }

    function setCurrency($currency) {
        $this->currency = $currency;
    }

    function loadFromAddress($id) {
        $row = self::getFromAddressFromDb($id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static function getFromAddressFromDb($id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  addr = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql, "s", array($id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function save() {
        return parent::save();
    }

}
