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
    
}
