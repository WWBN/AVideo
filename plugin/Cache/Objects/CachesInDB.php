<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class CachesInDB extends ObjectYPT {

    static $loggedType_NOT_LOGGED = 'n';
    static $loggedType_LOGGED = 'l';
    static $loggedType_ADMIN = 'a';
    static $prefix = 'ypt_cache_';
    protected $id, $content, $domain, $ishttps, $loggedType, $user_location, $expires, $timezone, $name;

    static function getSearchFieldsNames() {
        return array('domain', 'ishttps', 'user_location', 'timezone', 'name');
    }

    static function getTableName() {
        return 'CachesInDB';
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setContent($content) {
        $content = self::encodeContent($content);
        $this->content = $content;
    }

    function setDomain($domain) {
        $this->domain = $domain;
    }

    function setIshttps($ishttps) {
        $this->ishttps = $ishttps;
    }

    function setLoggedType($loggedType) {
        $this->loggedType = $loggedType;
    }

    function setUser_location($user_location) {
        $this->user_location = $user_location;
    }

    function setExpires($expires) {
        $this->expires = $expires;
    }

    function setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    function setName($name) {
        $this->name = $name;
    }

    function getId() {
        return intval($this->id);
    }

    function getContent() {
        $this->content = self::decodeContent($this->content);
        return $this->content;
    }

    function getDomain() {
        return $this->domain;
    }

    function getIshttps() {
        return $this->ishttps;
    }

    function getLoggedType() {
        return $this->loggedType;
    }

    function getUser_location() {
        return $this->user_location;
    }

    function getExpires() {
        return $this->expires;
    }

    function getTimezone() {
        return $this->timezone;
    }

    function getName() {
        return $this->name;
    }

    static function _getCache($name, $domain, $ishttps, $user_location, $loggedType) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  ishttps = ? AND loggedType = ? AND name = ? AND domain = ? AND user_location = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "issss", array($ishttps, $loggedType, $name, $domain, $user_location), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            if(!empty($data) && !empty($data['content'])){
                $data['content'] = self::decodeContent($data['content']);
            }
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function _setCache($name, $value, $domain, $ishttps, $user_location, $loggedType) {
        if(!is_string($value)){
            $value = _json_encode($value);
        }
        if(empty($value)){
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

    public static function _deleteCache($name) {
        global $global;
        if(empty($name)){
            return false;
        }
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name = ?";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql, "s", array($name));
    }

    public static function _deleteAllCache() {
        global $global;
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE id > 0";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
    }
    
    public static function encodeContent($content){
        if(!is_string($content)){
            $content = _json_encode($content);
        }
        $prefix = substr($content, 0, 10);
        if($prefix!== CachesInDB::$prefix){
            $base64 = base64_encode($content);
            $content = CachesInDB::$prefix.$base64;
        }
        return $content;
    }
    
    public static function decodeContent($content){
        $prefix = substr($content, 0, 10);
        if($prefix === CachesInDB::$prefix){
            $content = str_replace(CachesInDB::$prefix, '', $content);
            $content = base64_decode($content);
        }
        return $content;
    }

}
