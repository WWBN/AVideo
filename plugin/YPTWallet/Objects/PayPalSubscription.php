<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'].'plugin/YPTWallet/Objects/Wallet.php';

class PayPalSubscription extends ObjectYPT {

    protected $id, $wallet_id, $token;


    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'paypal_subscription';
    }
    
    function getWallet_id() {
        return $this->wallet_id;
    }

    function getToken() {
        return $this->token;
    }

    function setWallet_id($wallet_id) {
        $this->wallet_id = $wallet_id;
    }
    
    function setUsers_id($users_id) {
        $wallet = Wallet::getOrCreateFromUser($users_id);
        $wallet_id = $wallet['id'];
        $this->wallet_id = $wallet_id;
    }

    function setToken($token) {
        $this->token = $token;
    }
    
    static function getFromToken($token){
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " s LEFT JOIN wallet w ON w.id =  wallet_id WHERE  token = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($token));
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
