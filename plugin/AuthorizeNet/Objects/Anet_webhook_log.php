<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Anet_webhook_log extends ObjectYPT
{

    protected $id, $uniq_key, $event_type, $trans_id, $payload_json, $processed, $error_text, $status, $created_php_time, $modified_php_time, $users_id;

    static function getSearchFieldsNames()
    {
        return array('uniq_key', 'event_type', 'trans_id', 'error_text', 'status');
    }

    static function getTableName()
    {
        return 'anet_webhook_log';
    }

    function setId($id)
    {
        $this->id = intval($id);
    }
    function setUniq_key($uniq_key)
    {
        $this->uniq_key = $uniq_key;
    }
    function setEvent_type($event_type)
    {
        $this->event_type = $event_type;
    }
    function setTrans_id($trans_id)
    {
        $this->trans_id = $trans_id;
    }
    function setPayload_json($payload_json)
    {
        $this->payload_json = $payload_json;
    }
    function setProcessed($processed)
    {
        $this->processed = intval($processed);
    }
    function setError_text($error_text)
    {
        $this->error_text = $error_text;
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
    function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    function getId()
    {
        return intval($this->id);
    }
    function getUniq_key()
    {
        return $this->uniq_key;
    }
    function getEvent_type()
    {
        return $this->event_type;
    }
    function getTrans_id()
    {
        return $this->trans_id;
    }
    function getPayload_json()
    {
        return $this->payload_json;
    }
    function getProcessed()
    {
        return intval($this->processed);
    }
    function getError_text()
    {
        return $this->error_text;
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
    function getUsers_id()
    {
        return intval($this->users_id);
    }

    public static function alreadyProcessed($uniq_key)
    {
        if (empty($uniq_key)) {
            return false;
        }
        $obj = self::getFromUniqKey($uniq_key);
        if (!empty($obj) && !empty($obj['processed'])) {
            return true;
        }
        return false;
    }

    public static function getFromUniqKey($uniq_key)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE uniq_key = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$uniq_key]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            return $data;
        }
        return false;
    }

    public static function createIfNotExists($uniq_key, $event_type, $payload_json, $users_id = 0)
    {
        if (self::alreadyProcessed($uniq_key)) {
            return false;
        }

        if(empty($users_id)){
            $users_id = User::getId();
        }

        $obj = new self();
        $obj->setUniq_key($uniq_key);
        $obj->setEvent_type($event_type);
        $obj->setPayload_json(_json_encode($payload_json));
        $obj->setUsers_id($users_id);
        $obj->setProcessed(0);
        $obj->setCreated_php_time(time());
        $obj->setModified_php_time(time());

        return $obj->save();
    }
}
