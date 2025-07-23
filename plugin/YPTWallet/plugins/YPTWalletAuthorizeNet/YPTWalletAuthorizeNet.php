<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/YPTWallet/YPTWalletPlugin.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/AuthorizeNet.php';

class YPTWalletAuthorizeNet extends YPTWalletPlugin {

    public function getAprovalLink() {
        // Implement logic to generate Authorize.Net payment approval link if needed
        return false;
    }

    public function getAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletAuthorizeNet/confirmButton.php';
    }

    public function getRecurrentAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletAuthorizeNet/confirmRecurrentButton.php';
    }

    public function getRecurrentAprovalButtonV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $addFunds_Success='', $trialDays = 0) {
        $total = floatval($total);
        if(empty($total)){
            return '';
        }
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletAuthorizeNet/confirmRecurrentButtonV2.php';
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        // Add custom config if needed
        return $obj;
    }
}
