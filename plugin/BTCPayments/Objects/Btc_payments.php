<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Btc_payments extends ObjectYPT
{

    protected $id, $btc_invoices_id, $transaction_identification,
    $amount_received_btc, $confirmations, $created_php_time, $modified_php_time, $json, $store;

    static function getSearchFieldsNames()
    {
        return array('transaction_identification', 'json', 'store');
    }

    static function getTableName()
    {
        return 'btc_payments';
    }

    static function getAllBtc_invoices()
    {
        global $global;
        $table = "btc_invoices";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $rows;
    }
    static function getAllTransaction()
    {
        global $global;
        $table = "transaction";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $rows;
    }

    function setId($id)
    {
        $this->id = intval($id);
    }
    function setBtc_invoices_id($btc_invoices_id)
    {
        $this->btc_invoices_id = intval($btc_invoices_id);
    }
    function setTransaction_identification($transaction_identification)
    {
        $this->transaction_identification = $transaction_identification;
    }
    function setAmount_received_btc($amount_received_btc)
    {
        $this->amount_received_btc = $amount_received_btc;
    }
    function setConfirmations($confirmations)
    {
        $this->confirmations = intval($confirmations);
    }
    function setCreated_php_time($created_php_time)
    {
        $this->created_php_time = $created_php_time;
    }
    function setModified_php_time($modified_php_time)
    {
        $this->modified_php_time = $modified_php_time;
    }
    function setJson($json)
    {

        if(!is_string($json)){
            $json = json_encode($json);
        }
        $this->json = $json;
    }
    function setStore($store)
    {
        $this->store = $store;
    }

    function getId()
    {
        return intval($this->id);
    }
    function getBtc_invoices_id()
    {
        return intval($this->btc_invoices_id);
    }
    function getTransaction_identification()
    {
        return $this->transaction_identification;
    }
    function getAmount_received_btc()
    {
        return $this->amount_received_btc;
    }
    function getConfirmations()
    {
        return intval($this->confirmations);
    }
    function getCreated_php_time()
    {
        return $this->created_php_time;
    }
    function getModified_php_time()
    {
        return $this->modified_php_time;
    }
    function getJson()
    {
        return $this->json;
    }
    function getStore()
    {
        return $this->store;
    }

    static function getFromInvoice_identification($btc_invoices_id)
    {
        global $global;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  btc_invoices_id = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "i", [$btc_invoices_id],true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getIdOr0($btc_invoices_id){
        $invoice = self::getFromInvoice_identification($btc_invoices_id);
        if(!empty($invoice)){
            return intval($invoice['id']);
        }
        return 0;
    }
}
