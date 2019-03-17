<?php
/**
-2 : PAYMENT_EXPIRED
-1 : PAYMENT_ERROR (Happens when Paid BTC amount is not matching expected value)
 0 : UNPAID
 1 : IN_PROCESS
 2 : PAID
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/YPTWallet/YPTWalletPlugin.php';

class YPTWalletBlockonomics extends YPTWalletPlugin{
    
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
        include $global['systemRootPath'].'plugin/YPTWallet/plugins/YPTWalletBlockonomics/confirmButton.php';
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        return $obj;
    }

}
