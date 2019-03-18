<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/YPTWallet/YPTWalletPlugin.php';

class YPTWalletPayPal extends YPTWalletPlugin{
    
    public function getAprovalLink() {
        global $global;
        $plugin = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
        $payment = $plugin->setUpPayment(
                $this->getInvoiceNumber(), 
                $this->getRedirectURL(), 
                $this->getCancelURL(), 
                $this->getValue(), 
                $this->getCurrency());
        if (!empty($payment)) {
            return $payment->getApprovalLink();
        }
        return false;
    }

    public function getAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletPayPal/confirmButton.php';
    }
    

    public function getRecurrentAprovalButton() {
        global $global;
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletPayPal/confirmRecurrentButton.php';
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->RedirectURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/redirect_url.php";
        $obj->CancelURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/cancel_url.php";
        return $obj;
    }

}
