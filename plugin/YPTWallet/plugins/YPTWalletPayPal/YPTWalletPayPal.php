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

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->ClientID = "ASUkHFpWX0T8sr8EiGdLZ05m-RAb8l-hdRxoq-OXWmua2i7EUfqFkMZvSoGgH2LhK7zAqt29IiS2oRTn";
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->RedirectURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/redirect_url.php";
        $obj->CancelURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/cancel_url.php";
        $obj->disableSandbox = false;
        return $obj;
    }

}
