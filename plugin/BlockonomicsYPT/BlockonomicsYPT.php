<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/BlockonomicsYPT/Objects/BlockonomicsOrder.php';

class BlockonomicsYPT extends PluginAbstract {
    static $BASE_URL = "https://www.blockonomics.co/";

    static function getNEW_ADDRESS_URL(){
        $obj = YouPHPTubePlugin::getObjectData("BlockonomicsYPT");
        return self::$BASE_URL.'api/new_address?match_callback='.$obj->Secret;
    }
    static function getPRICE_URL(){
        $obj = YouPHPTubePlugin::getObjectData("YPTWallet");
        $currency = $obj->currency;
        return self::$BASE_URL.'api/price?currency='.strtoupper($currency);
    }
    public function getDescription() {
        global $global;
        $obj = $this->getDataObject();
        $return = "<a href='https://www.blockonomics.co'>Blockonomics</a> is a decentralized and permissionless bitcoin payment solution.";
        $return .= "<br>HTTP Callback URL: <br><code>{$global['webSiteRootURL']}plugin/BlockonomicsYPT/callback.php?secret={$obj->Secret}</code>";
        return $return;
    }

    public function getName() {
        return "BlockonomicsYPT";
    }

    public function getUUID() {
        return "bitcoin9-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->APIKey = "NUdcVWq0Juz29qnJH9hebgdvEY0qiSIpWZo5nCWXask";
        $obj->Secret = md5($global['systemRootPath'].$global['salt']);
        $obj->ExpireInSeconds = 600;
        return $obj;
    }

    
    public function setUpPayment($total_cost = '1.00') {
        global $global; 
        $total_cost = floatval($total_cost);
        $obj = $this->getDataObject();
        $objWallet = YouPHPTubePlugin::getObjectData("YPTWallet");
        $currency = $objWallet->currency;
        //return here if total is empty
        if (empty($total_cost)) {
            echo $json_response = json_encode(array("error" => "Total Is Empty"));
            return;
        }
        
        if(!User::isLogged()){
            echo $json_response = json_encode(array("error" => "Must be logged in"));
            return;
        }
        
        $data = '';
        $options = array(
            'http' => array(
                'header' => 'Authorization: Bearer ' . $obj->APIKey,
                'method' => 'POST',
                'content' => $data
            )
        );
        //Generate new address for this invoice
        $context = stream_context_create($options);
        $contents = file_get_contents(self::getNEW_ADDRESS_URL(), false, $context);
        $new_address = json_decode($contents);
        //Getting price
        $options = array('http' => array('method' => 'GET'));
        $context = stream_context_create($options);
        $contents = file_get_contents(self::getPRICE_URL(), false, $context);
        $price = json_decode($contents);
        //Total Cart value in bits
        $bits = intval(1.0e8 * $total_cost / $price->price);
        
        // save on database
        $b = new BlockonomicsOrder(0);
        $b->setAddr($new_address->address);
        $b->setBits($bits);
        $b->setBits_payed(0);
        $b->setStatus("-1");
        $b->setTxid("");
        $b->setUsers_id(User::getId());
        $b->setTotal_value($total_cost);
        $b->setCurrency($currency);
        
        return $b->save();
    }

}
