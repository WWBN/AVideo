<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Btc_invoices extends ObjectYPT
{

    protected $id, $invoice_identification, $users_id, $amount_currency,
    $amount_btc, $currency, $status, $created_php_time, $modified_php_time, $json;

    static function getSearchFieldsNames()
    {
        return array('invoice_identification', 'currency', 'json');
    }

    static function getTableName()
    {
        return 'btc_invoices';
    }

    static function getAllInvoice()
    {
        global $global;
        $table = "invoice";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";
        $res = sqlDAL::readSql($sql);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $rows;
    }
    static function getAllUsers()
    {
        global $global;
        $table = "users";
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
    function setInvoice_identification($invoice_identification)
    {
        $this->invoice_identification = $invoice_identification;
    }
    function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }
    function setAmount_currency($amount_currency)
    {
        $this->amount_currency = $amount_currency;
    }
    function setAmount_btc($amount_btc)
    {
        $this->amount_btc = $amount_btc;
    }
    function setCurrency($currency)
    {
        $this->currency = $currency;
    }
    function setStatus($status)
    {
        $this->status = $status;
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

    function getId()
    {
        return intval($this->id);
    }
    function getInvoice_identification()
    {
        return $this->invoice_identification;
    }
    function getUsers_id()
    {
        return intval($this->users_id);
    }
    function getAmount_currency()
    {
        return $this->amount_currency;
    }
    function getAmount_btc()
    {
        return $this->amount_btc;
    }
    function getCurrency()
    {
        return $this->currency;
    }
    function getStatus()
    {
        return $this->status;
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

    static function getFromInvoice_identification($invoice_identification)
    {
        global $global;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  invoice_identification = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "s", [$invoice_identification],true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getIdOr0($invoice_identification){
        $invoice = self::getFromInvoice_identification($invoice_identification);
        if(!empty($invoice)){
            return intval($invoice['id']);
        }
        return 0;
    }

    public static function getAllFromUser($users_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT p.*, i.* FROM  " . static::getTableName() . " i LEFT JOIN btc_payments p ON i.id = btc_invoices_id WHERE i.users_id = ? ";

        $sql .= self::getSqlFromPost('i.');
        $res = sqlDAL::readSql($sql, 'i', [$users_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getTotalFromUser($users_id)
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE users_id = ? ";
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql, 'i', [$users_id]);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public function save($notifySocket = false){
        $save = parent::save();
        if($save && $notifySocket){
            sendSocketMessageToUsers_id(json_decode($this->json), $this->users_id,'BTCPayments');
        }
        return $save;
    }
}
