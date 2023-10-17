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

    static function hashName($name){
        if(preg_match('/^hashName_/', $name)){
            return $name;
        }
        return 'hashName_'.preg_replace('/[^0-9a-z]/i', '_', $name);
    }

    public static function _getCache($name, $domain, $ishttps, $user_location, $loggedType, $ignoreMetadata=false)
    {
        global $global;
        $name = self::hashName($name);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE name = ? ";
        $formats = 's';
        $values = [$name];
        $sql .= "  AND ishttps = ? AND domain = ? AND user_location = ? ";
        $formats = 'siss';
        $values = [$name, $ishttps, $domain, $user_location];
        if(empty($ignoreMetadata)){
            $sql .= " AND loggedType = ? ";
            $formats .= 's';
            $values[] = $loggedType;
        }
        $sql .= " ORDER BY id DESC LIMIT 1";
        
        //var_dump($sql, $formats, $values );
        //_error_log(json_encode(array($sql, $values )));
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, $formats, $values);
        $data = sqlDAL::fetchAssoc($res);
        //var_dump( $data);
        sqlDAL::close($res);
        if ($res) {
            if (!empty($data) && !empty($data['content'])) {
                $originalContent = $data['content'];
                $data['content'] = self::decodeContent($data['content']);
                //var_dump($originalContent );
                //var_dump($data['content']);
                if($data['content'] === null){
                    _error_log("Fail decode content [{$name}]".$originalContent);
                    //_error_log(json_encode(debug_backtrace()));exit;
                    //var_dump(debug_backtrace());exit;
                    //var_dump("Fail decode content [{$name}]", $originalContent);exit;
                }
            }
            $row = $data;
        } else {
            $row = false;
        }
        //var_dump($row);
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
        
        $name = self::hashName($name);
        $c->setContent($value);
        $c->setName($name);
        $c->setDomain($domain);
        $c->setIshttps($ishttps);
        $c->setUser_location($user_location);
        $c->setLoggedType($loggedType);
        $c->setExpires(date('Y-m-d H:i:s', strtotime('+ 1 month')));
        return $c->save();
    }

    public static function setBulkCache($cacheArray, $metadata) {
        if (empty($cacheArray)) {
            return false;
        }
        global $global;
        
        $placeholders = [];
        $formats = [];
        $values = [];
        $tz = date_default_timezone_get();
        $time = time();
        foreach ($cacheArray as $name => $cache) {
            $name = self::hashName($name);
            $content = !is_string($cache) ? _json_encode($cache) : $cache;
            if (empty($content)) continue;

            $formats[] = "ssssssssi";
            $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $expires = date('Y-m-d H:i:s', strtotime('+ 1 month'));
            /*
            echo PHP_EOL.'--name=';
            var_dump($name);
            echo PHP_EOL.'--content=';
            var_dump($content);
            */
            // Add values to the values array
            //_error_log("setBulkCache [{$name}]");
            array_push($values, $name, $content, $metadata['domain'], $metadata['ishttps'], $metadata['user_location'], $metadata['loggedType'], $expires, $tz, $time);
        }

        $sql = "INSERT INTO CachesInDB (name, content, domain, ishttps, user_location, loggedType, expires, timezone, created_php_time, created, modified) 
                VALUES " . implode(", ", $placeholders) . "
                ON DUPLICATE KEY UPDATE 
                content = VALUES(content),
                expires = VALUES(expires),
                created_php_time = VALUES(created_php_time),
                modified = NOW()";

        // Assuming you have a PDO connection $pdo
        $result = sqlDAL::writeSql($sql, implode('', $formats), $values);
        //_error_log("setBulkCache writeSql ".json_encode($result ));
        //var_dump($result, $sql, implode('', $formats), $values);exit;
        return $result;
    }

    public static function _deleteCache($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }
        
        if (!static::isTableInstalled()) {
            return false;
        }
        $name = self::hashName($name);
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
        if (!static::isTableInstalled()) {
            return false;
        }
        $name = self::hashName($name);
        //$sql = "DELETE FROM " . static::getTableName() . " ";
        //$sql .= " WHERE name LIKE '{$name}%'";
        $sql = "DELETE FROM CachesInDB WHERE MATCH(name) AGAINST('{$name}*' IN BOOLEAN MODE);";
        
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }

    
    public static function _deleteCacheWith($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }
        if (!static::isTableInstalled()) {
            return false;
        }
        $name = self::hashName($name);
        $name = str_replace('hashName_', '', $name);
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name LIKE '%{$name}%'";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }

    public static function _deleteAllCache()
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "TRUNCATE TABLE " . static::getTableName() . " ";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }

    public static function encodeContent($content)
    {
        $original = $content;
        if (!is_string($content)) {
            $content = _json_encode($content);
        }
        $prefix = substr($content, 0, 10);
        if ($prefix!== CachesInDB::$prefix) {
            //$content = base64_encode($content);
            $content = CachesInDB::$prefix.$content;
        }
        return $content;
    }

    public static function decodeContent($content)
    {
        $prefix = substr($content, 0, strlen(CachesInDB::$prefix));
        if ($prefix === CachesInDB::$prefix) {
            $content = str_replace(CachesInDB::$prefix, '', $content);
            //$content = base64_decode($content);
        }
        return $content;
    }
}
