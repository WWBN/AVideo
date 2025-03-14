<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/YPTWallet/YPTWalletPlugin.php';

class YPTWalletBTC extends YPTWalletPlugin{

    public function getAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletBTC/confirmButton.php';
    }


    public function getRecurrentAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletBTC/confirmRecurrentButton.php';
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        //$obj->RedirectURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletBTC/redirect_url.php";
        //$obj->CancelURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletBTC/cancel_url.php";
        return $obj;
    }

}
