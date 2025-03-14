<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/BlockonomicsYPT/Objects/BlockonomicsOrder.php';

class BlockonomicsYPT extends PluginAbstract {

    static $BASE_URL = "https://www.blockonomics.co/";

    static function getPRICE_URL() {
        $obj = AVideoPlugin::getObjectData("YPTWallet");
        $currency = $obj->currency;
        return self::$BASE_URL . 'api/price?currency=' . strtoupper($currency);
    }

    public function getDescription() {
        global $global;
        $obj = $this->getDataObject();
        $return = "<a href='https://www.blockonomics.co'>Blockonomics</a> is a decentralized and permissionless bitcoin payment solution.";
        $return .= "<br>HTTP Callback URL: <br><code>{$global['webSiteRootURL']}plugin/BlockonomicsYPT/callback.php?secret={$obj->Secret}</code>";
        return $return;
    }

    public function getTags()
    {
        return array(
            PluginTags::$DEPRECATED
        );
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
        $obj->Secret = md5($global['systemRootPath'] . $global['salt']);
        $obj->ExpireInSeconds = 600;
        return $obj;
    }

    public function setUpPayment($total_cost = '1.00') {
        global $global;
        $total_cost = floatval($total_cost);
        $obj = $this->getDataObject();
        $objWallet = AVideoPlugin::getObjectData("YPTWallet");
        $currency = $objWallet->currency;
        //return here if total is empty
        if (empty($total_cost)) {
            echo json_encode(array("error" => "Total Is Empty"));
            return false;
        }

        if (!User::isLogged()) {
            echo json_encode(array("error" => "Must be logged in"));
            return false;
        }

        //Generate new address for this invoice
        $new_address = $this->getNewAddress($obj->APIKey);

        if (empty($new_address)) {
            _error_log('Blockonomics ERROR 1: ' . json_last_error_msg(), AVideoLog::$ERROR);
            return false;
        }

        //Getting price
        $options = array('http' => array('method' => 'GET'));
        $context = stream_context_create($options);
        $contents = url_get_contents(self::getPRICE_URL(), $context, 0, true);
        $price = _json_decode($contents);
        //Total Cart value in bits
        $bits = intval(1.0e8 * $total_cost / $price->price);

        // save on database
        $b = new BlockonomicsOrder(0);
        $b->setAddr($new_address);
        $b->setBits($bits);
        $b->setBits_payed(0);
        $b->setStatus("-1");
        $b->setTxid("");
        $b->setUsers_id(User::getId());
        $b->setTotal_value($total_cost);
        $b->setCurrency($currency);

        return $b->save();
    }

    public function getNewAddress($api_key) {
        $url = 'https://www.blockonomics.co/api/new_address';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        $header = "Authorization: Bearer " . $api_key;
        $headers = array();
        $headers[] = $header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
            _error_log("Blockonomics Error 2:" . curl_error($ch));
            return false;
        }

        $responseObj = _json_decode($contents);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200) {
            return $responseObj->address;
        } else {
            //echo "<div class='alert alert-danger'>{$responseObj->message}</div>";
            _error_log("Blockonomics Error 3: [{$status}] {$responseObj->message}");
        }
        return false;
    }

}
