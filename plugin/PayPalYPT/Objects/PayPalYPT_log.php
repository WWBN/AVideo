<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class PayPalYPT_log extends ObjectYPT
{
    protected $id;
    protected $agreement_id;
    protected $users_id;
    protected $json;
    protected $recurring_payment_id;
    protected $value;
    protected $token;

    public static function getSearchFieldsNames()
    {
        return ['agreement_id', 'json', 'recurring_payment_id', 'token'];
    }

    public static function getTableName()
    {
        return 'PayPalYPT_log';
    }

    public static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setAgreement_id($agreement_id)
    {
        $this->agreement_id = $agreement_id;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    public function setJson($json)
    {
        if (!is_string($json)) {
            $json = _json_encode($json);
        }
        $this->json = $json;
    }

    public function setRecurring_payment_id($recurring_payment_id)
    {
        $this->recurring_payment_id = $recurring_payment_id;
    }

    public function setValue($value)
    {
        $this->value = floatval($value);
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getAgreement_id()
    {
        return $this->agreement_id;
    }

    public function getUsers_id()
    {
        return intval($this->users_id);
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getRecurring_payment_id()
    {
        return $this->recurring_payment_id;
    }

    public function getValue()
    {
        return floatval($this->value);
    }

    public function getToken()
    {
        return $this->token;
    }

    public static function getFromToken($token)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  token = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", [$token], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getFromRecurringPaymentId($recurring_payment_id)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  recurring_payment_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", [$recurring_payment_id], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getAllFromUser($users_id)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . "  WHERE users_id = ? ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $search = ['"get":{"json":"{', '}","success"'];
                $replace = ['"get":{"json":{', '},"success"'];
                $row['json'] = str_replace($search, $replace, $row['json']);
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function save()
    {
        global $global;
        $this->json = $global['mysqli']->real_escape_string($this->json);

        return parent::save();
    }
}
