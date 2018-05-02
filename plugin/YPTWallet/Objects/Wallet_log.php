<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'].'plugin/YPTWallet/Objects/Wallet.php';

class WalletLog extends ObjectYPT {

    protected $id, $value, $description, $wallet_id, $json_data;


    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'wallet_log';
    }
    
    function getValue() {
        return $this->value;
    }

    function getDescription() {
        return $this->description;
    }

    function getWallet_id() {
        return $this->wallet_id;
    }

    function getJson_data() {
        return $this->json_data;
    }

    function setValue($value) {
        $this->value = $value;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setWallet_id($wallet_id) {
        $this->wallet_id = $wallet_id;
    }

    function setJson_data($json_data) {
        $this->json_data = $json_data;
    }
        
    static function getAllFromWallet($wallet_id, $dontReturnEmpty = true) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE wallet_id=$wallet_id ";

        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }
        
        $sql .= self::getSqlFromPost();
        $obj = YouPHPTubePlugin::getObjectData("YPTWallet");
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['valueText'] = "{$obj->currency_symbol} ".number_format($row['value'], $obj->decimalPrecision)." {$obj->currency}";
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function getTotalFromWallet($wallet_id, $dontReturnEmpty = true) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE wallet_id=$wallet_id ";
        
        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }
        
        $sql .= self::getSqlSearchFromPost();

        $res = $global['mysqli']->query($sql);

        return $res->num_rows;
    }
    
    static function getAllFromUser($users_id, $dontReturnEmpty = true) {
        
        $wallet = Wallet::getFromUser($users_id);
        
        return self::getAllFromWallet($wallet['id'], $dontReturnEmpty);
    }
    
    static function getTotalFromUser($users_id, $dontReturnEmpty = true) {
        
        $wallet = Wallet::getFromUser($users_id);
        
        return self::getTotalFromWallet($wallet['id'], $dontReturnEmpty);
    }
    
    static function addLog($wallet_id, $value, $description="", $json_data="{}"){
        $log = new WalletLog(0);
        $log->setWallet_id($wallet_id);
        $log->setValue($value);
        $log->setDescription($description);
        $log->setJson_data($json_data);
        $log->save();
    }


}
