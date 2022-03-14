<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_restreams extends ObjectYPT
{
    protected $id;
    protected $name;
    protected $stream_url;
    protected $stream_key;
    protected $status;
    protected $parameters;
    protected $users_id;

    public static function getSearchFieldsNames()
    {
        return ['name','stream_url','stream_key','parameters'];
    }

    public static function getTableName()
    {
        return 'live_restreams';
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setStream_url($stream_url)
    {
        $this->stream_url = $stream_url;
    }

    public function setStream_key($stream_key)
    {
        $this->stream_key = $stream_key;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }


    public function getId()
    {
        return intval($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStream_url()
    {
        return $this->stream_url;
    }

    public function getStream_key()
    {
        return $this->stream_key;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getUsers_id()
    {
        return intval($this->users_id);
    }


    public static function getAllFromUser($users_id, $status = 'a')
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $users_id = intval($users_id);
        if (empty($users_id)) {
            return false;
        }

        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = $users_id ";

        if (!empty($status)) {
            $sql .= " AND status = '$status' " ;
        }

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
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
}
