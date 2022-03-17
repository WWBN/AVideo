<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class Clones extends ObjectYPT
{
    protected $id;
    protected $url;
    protected $status;
    protected $key;
    protected $last_clone_request;

    public static function getSearchFieldsNames()
    {
        return ['url'];
    }

    public static function getTableName()
    {
        return 'clone_SitesAllowed';
    }

    public static function getFromURL($url)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  url = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$url]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function updateLastCloneRequest()
    {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET last_clone_request = now() ";
            $sql .= " WHERE id = {$this->id}";
        } else {
            return false;
        }
        $insert_row = sqlDAL::writeSql($sql);

        if ($insert_row) {
            $id = $this->id;
            return $id;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    public function loadFromURL($url)
    {
        $row = self::getFromURL($url);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    public static function thisURLCanCloneMe($url, $key)
    {
        $resp = new stdClass();
        $resp->canClone = false;
        $resp->clone = null;
        $resp->msg = "";

        $clone = new Clones(0);
        $clone->loadFromURL($url);
        if (empty($clone->getId())) {
            $resp->msg = "The URL {$url} was just added in our server, ask the Server Manager to approve this URL on plugins->Clone Site->Clones Manager (The Blue Button) and Activate your client";
            self::addURL($url, $key);
            return $resp;
        }
        if ($clone->getKey() !== $key) {
            $resp->msg = "Invalid Key";
            return $resp;
        }
        if ($clone->getStatus() !== 'a') {
            $resp->msg = "The URL {$url} is inactive in our Clone Server";
            return $resp;
        }
        $resp->clone = $clone;
        $resp->canClone = true;
        return $resp;
    }

    public static function addURL($url, $key)
    {
        $clone = new Clones(0);
        $clone->loadFromURL($url);
        if (empty($clone->getId())) {
            $clone->setUrl($url);
            $clone->setKey($key);
            return $clone->save();
        }
        return false;
    }

    public function save()
    {
        if (empty($this->status)) {
            $this->status = 'i';
        }
        if (empty($this->last_clone_request)) {
            $this->last_clone_request = 'null';
        }
        return parent::save();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getLast_clone_request()
    {
        return $this->last_clone_request;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setLast_clone_request($last_clone_request)
    {
        $this->last_clone_request = $last_clone_request;
    }

    public function toogleStatus()
    {
        if (empty($this->id)) {
            return false;
        }
        if ($this->status==='i') {
            $this->status='a';
        } else {
            $this->status='i';
        }
        return $this->save();
    }
}
