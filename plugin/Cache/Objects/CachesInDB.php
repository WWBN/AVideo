<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class CachesInDB extends ObjectYPT
{
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
            if (empty($global['mysqli'])) {
                $global['mysqli'] = new stdClass();
            }
            if($global['mysqli']->errno == 1146){
                $error = array($global['mysqli']->error);
                $file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';
                sqlDal::executeFile($file);
                if (!static::isTableInstalled()) {
                    $error[] = $global['mysqli']->error;
                    die("We could not create table ".static::getTableName().'<br> '.implode('<br>', $error));
                }
            }
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
    private static function prepareCacheItem($name, $cache, $metadata, $tz, $time) {
        $formattedCacheItem = [];
        
        $name = self::hashName($name);
        $content = !is_string($cache) ? json_encode($cache) : $cache;
        if (empty($content)) {
            return null;
        }
    
        $expires = date('Y-m-d H:i:s', strtotime('+1 month'));
    
        // Format for the prepared statement
        $formattedCacheItem['format'] = "ssssssssi";
        $formattedCacheItem['values'] = [
            $name,
            $content,
            $metadata['domain'],
            $metadata['ishttps'],
            $metadata['user_location'],
            $metadata['loggedType'],
            $expires,
            $tz,
            $time
        ];
    
        return $formattedCacheItem;
    }
    
    public static function setBulkCache($cacheArray, $metadata, $batchSize = 50) {
        if (empty($cacheArray)) {
            return false;
        }
    
        global $global;
        $cacheBatches = array_chunk($cacheArray, $batchSize, true);
        $tz = date_default_timezone_get();
        $time = time();
        $result = true;
    
        foreach ($cacheBatches as $batch) {$placeholders = [];
            $formats = [];
            $values = [];
    
            foreach ($batch as $name => $cache) {
                $cacheItem = self::prepareCacheItem($name, $cache, $metadata, $tz, $time);
                if ($cacheItem === null) continue;
    
                $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $formats[] = $cacheItem['format'];
                $values = array_merge($values, $cacheItem['values']);
            }
    
            $sql = "INSERT INTO " . static::getTableName() . " (name, content, domain, ishttps, user_location, loggedType, expires, timezone, created_php_time, created, modified)
             VALUES " . implode(", ", $placeholders) . " 
             ON DUPLICATE KEY UPDATE 
             content = VALUES(content),
             expires = VALUES(expires),
             created_php_time = VALUES(created_php_time),
             modified = NOW()";
    
            // Start transaction
            mysqlBeginTransaction();
    
            try {
                $result &= sqlDAL::writeSql($sql, implode('', $formats), $values);
                mysqlCommit();
            } catch (\Throwable $th) {
                mysqlRollback();
                _error_log($th->getMessage() . ' '.$sql, AVideoLog::$ERROR);
                return false;
            }
        }
    
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
        $sql = "DELETE FROM " . static::getTableName() . " WHERE MATCH(name) AGAINST('{$name}*' IN BOOLEAN MODE);";
        
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
        if ($prefix!== CacheDB::$prefix) {
            //$content = base64_encode($content);
            $content = CacheDB::$prefix.$content;
        }
        return $content;
    }

    public static function decodeContent($content)
    {
        $prefix = substr($content, 0, strlen(CacheDB::$prefix));
        if ($prefix === CacheDB::$prefix) {
            $content = str_replace(CacheDB::$prefix, '', $content);
            //$content = base64_decode($content);
        }
        return $content;
    }
}
