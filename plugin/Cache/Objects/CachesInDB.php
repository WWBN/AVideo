<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class CachesInDB extends ObjectYPT
{
    public static $loggedType_NOT_LOGGED = 'n';
    public static $loggedType_LOGGED = 'l';
    public static $loggedType_ADMIN = 'a';
    public static $prefix = 'ypt_cache_';
    protected $id;
    protected $content;
    protected $domain;
    protected $ishttps;
    protected $loggedType;
    protected $user_location;
    protected $expires;
    protected $timezone;
    protected $name;

    public static function getSearchFieldsNames()
    {
        return ['domain', 'ishttps', 'user_location', 'timezone', 'name'];
    }

    public static function getTableName()
    {
        return 'CachesInDB';
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setContent($content)
    {
        $content = self::encodeContent($content);
        $this->content = $content;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setIshttps($ishttps)
    {
        $this->ishttps = $ishttps;
    }

    public function setLoggedType($loggedType)
    {
        $this->loggedType = $loggedType;
    }

    public function setUser_location($user_location)
    {
        $this->user_location = $user_location;
    }

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getContent()
    {
        $this->content = self::decodeContent($this->content);
        return $this->content;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getIshttps()
    {
        return $this->ishttps;
    }

    public function getLoggedType()
    {
        return $this->loggedType;
    }

    public function getUser_location()
    {
        return $this->user_location;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function _getCache($name, $domain, $ishttps, $user_location, $loggedType)
    {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  ishttps = ? AND loggedType = ? AND name = ? AND domain = ? AND user_location = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "issss", [$ishttps, $loggedType, $name, $domain, $user_location], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            if (!empty($data) && !empty($data['content'])) {
                $data['content'] = self::decodeContent($data['content']);
            }
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function _setCache($name, $value, $domain, $ishttps, $user_location, $loggedType)
    {
        if (!is_string($value)) {
            $value = _json_encode($value);
        }
        if (empty($value)) {
            return false;
        }

        $row = self::_getCache($name, $domain, $ishttps, $user_location, $loggedType);
        if (!empty($row)) {
            $c = new CachesInDB($row['id']);
        } else {
            $c = new CachesInDB(0);
        }
        $c->setContent($value);
        $c->setName($name);
        $c->setDomain($domain);
        $c->setIshttps($ishttps);
        $c->setUser_location($user_location);
        $c->setLoggedType($loggedType);
        $c->setExpires(date('Y-m-d H:i:s', strtotime('+ 1 month')));
        return $c->save();
    }

    public static function _deleteCache($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name = ?";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql, "s", [$name]);
    }

    public static function _deleteCacheStartingWith($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name LIKE '{$name}%'";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }

    public static function _deleteAllCache()
    {
        global $global;
        $sql = "TRUNCATE TABLE " . static::getTableName() . " ";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }

    public static function encodeContent($content)
    {
        if (!is_string($content)) {
            $content = _json_encode($content);
        }
        $prefix = substr($content, 0, 10);
        if ($prefix!== CachesInDB::$prefix) {
            $base64 = base64_encode($content);
            $content = CachesInDB::$prefix.$base64;
        }
        return $content;
    }

    public static function decodeContent($content)
    {
        $prefix = substr($content, 0, 10);
        if ($prefix === CachesInDB::$prefix) {
            $content = str_replace(CachesInDB::$prefix, '', $content);
            $content = base64_decode($content);
        }
        return $content;
    }
}
