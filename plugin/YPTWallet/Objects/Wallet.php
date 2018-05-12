<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class Wallet extends ObjectYPT {

    protected $id, $balance, $users_id, $crypto_wallet_address;


    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'wallet';
    }
    
    function getBalance() {
        if(empty($this->balance)){
            return 0.0;
        }
        return floatval($this->balance);
    }
    
    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
    }
    
    function getUsers_id() {
        return $this->users_id;
    }

    function setBalance($balance) {
        $this->balance = floatval($balance);
    }

    function setUsers_id($users_id) {
        $this->loadFromUser($users_id);
        $this->users_id = $users_id;
    }
    
    function getCrypto_wallet_address() {
        return $this->crypto_wallet_address;
    }

    function setCrypto_wallet_address($crypto_wallet_address) {
        $this->crypto_wallet_address = $crypto_wallet_address;
    }
    
    protected function loadFromUser($users_id) {
        $row = self::getFromUser($users_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
    
    static function getFromUser($users_id) {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = $users_id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
        } else {
            $row = false;
        }
        return $row;
    }
    
    public function save() {
        $this->balance = floatval($this->balance);
        return parent::save();
    }

    

}
