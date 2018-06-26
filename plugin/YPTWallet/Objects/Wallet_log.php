<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'].'plugin/YPTWallet/Objects/Wallet.php';

class WalletLog extends ObjectYPT {

    protected $id, $value, $description, $wallet_id, $json_data, $status, $type;


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
    
    function getStatus() {
        return $this->status;
    }

    function getType() {
        return $this->type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setType($type) {
        $this->type = $type;
    }
            
    static function getAllFromWallet($wallet_id, $dontReturnEmpty = true, $status="") {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if(!empty($wallet_id)){
            $sql .= " AND wallet_id=$wallet_id ";
        }
        
        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }
        
        if(!empty($status)){
            $sql .= " AND status = '$status' ";
        }
        
        $sql .= self::getSqlFromPost();
        $obj = YouPHPTubePlugin::getObjectData("YPTWallet");
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['valueText'] = "{$obj->currency_symbol} ".number_format($row['value'], $obj->decimalPrecision)." {$obj->currency}";
                $row['wallet'] = Wallet::getFromWalletId($row['wallet_id']);
                $row['user'] = $row['wallet']['user'];
                $row['balance'] = $row['wallet']['balance'];
                $row['crypto_wallet_address'] = "";
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function getTotalFromWallet($wallet_id, $dontReturnEmpty = true, $status="") {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if(!empty($wallet_id)){
            $sql .= " AND wallet_id=$wallet_id ";
        }
        
        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }
        
        if(!empty($status)){
            $sql .= " AND status = '$status' ";
        }
        
        $sql .= self::getSqlSearchFromPost();
        $res = $global['mysqli']->query($sql);
        if(!$res){
            return 0;
        }

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
    
    static function addLog($wallet_id, $value, $description="", $json_data="{}", $status="success", $type=""){
        $log = new WalletLog(0);
        $log->setWallet_id($wallet_id);
        $log->setValue($value);
        $log->setDescription($description);
        $log->setJson_data($json_data);
        $log->setStatus($status);        
        $log->setType($type);
        return $log->save();
    }
    
    function save() {
        global $global;
        $this->description = $global['mysqli']->real_escape_string($this->description);
        return parent::save();
    }


}
