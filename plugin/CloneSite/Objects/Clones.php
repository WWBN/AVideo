<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class Clones extends ObjectYPT {

    protected $id, $url, $status, $key, $last_clone_request;

    static function getSearchFieldsNames() {
        return array('url');
    }

    static function getTableName() {
        return 'clone_SitesAllowed';
    }
    
    static function getFromURL($url){
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  url = ? LIMIT 1";
        $res = sqlDAL::readSql($sql,"s",array($url)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    function updateLastCloneRequest() {
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
    
    function loadFromURL($url) {
        $row = self::getFromURL($url);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
    
    static function thisURLCanCloneMe($url, $key){
        $resp = new stdClass();
        $resp->canClone = false;
        $resp->clone = null;
        $resp->msg = "";
        
        $clone = new Clones(0);
        $clone->loadFromURL($url);
        if(empty($clone->getId())){
            $resp->msg = "The URL {$url} was just added in our server, ask the Server Manager to approve this URL on plugins->Clone Site->Clones Manager (The Blue Button) and Activate your client";
            self::addURL($url, $key);
            return $resp;
        }
        if($clone->getKey() !== $key){
            $resp->msg = "Invalid Key";
            return $resp;
        }
        if($clone->getStatus() !== 'a'){
            $resp->msg = "The URL {$url} is inactive in our Clone Server";
            return $resp;
        }
        $resp->clone = $clone;
        $resp->canClone = true;
        return $resp;
    }
    
    static function addURL($url, $key){
        $clone = new Clones(0);
        $clone->loadFromURL($url);
        if(empty($clone->getId())){
            $clone->setUrl($url);
            $clone->setKey($key);
            return $clone->save();
        }
        return false;
    }
    
    function save() {
        if(empty($this->status)){
            $this->status = 'i';
        }
        if(empty($this->last_clone_request)){
            $this->last_clone_request = 'null';
        }
        return parent::save();
    }
            
    function getId() {
        return $this->id;
    }

    function getUrl() {
        return $this->url;
    }

    function getStatus() {
        return $this->status;
    }

    function getKey() {
        return $this->key;
    }

    function getLast_clone_request() {
        return $this->last_clone_request;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setLast_clone_request($last_clone_request) {
        $this->last_clone_request = $last_clone_request;
    }

    function toogleStatus(){
        if(empty($this->id)){
           return false; 
        }
        if($this->status==='i'){
            $this->status='a';
        }else{
            $this->status='i';
        }
        return $this->save();             
    }


}
