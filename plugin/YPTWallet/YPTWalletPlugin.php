<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author daniel
 */
abstract class YPTWalletPlugin {
    //put your code here
    private $invoiceNumber, $value, $currency;

    abstract function getAprovalButton();
    abstract function getEmptyDataObject();

    function getValue(){
        return $this->value;
    }

    function setValue($value){
        $this->value = floatval($value);
    }

    function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
    }

    function getCurrency() {
        return $this->currency;
    }

    function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function getRecurrentAprovalButton() {
    }
    
    public function getRecurrentAprovalButtonV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0) {
    }

}
