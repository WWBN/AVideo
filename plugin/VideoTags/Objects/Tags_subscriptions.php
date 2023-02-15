<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Tags_subscriptions extends ObjectYPT {

    protected $id, $tags_id, $users_id, $notify;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'tags_subscriptions';
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setTags_id($tags_id) {
        $this->tags_id = intval($tags_id);
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function getId() {
        return intval($this->id);
    }

    function getTags_id() {
        return intval($this->tags_id);
    }

    function getUsers_id() {
        return intval($this->users_id);
    }
    
    public function getNotify() {
        if(empty($notify)){
            return 0;
        }else{
            return 1;
        }
    }

    public function setNotify($notify): void {
        if(empty($notify)){
            $this->notify = 0;
        }else{
            $this->notify = 1;
        }
    }
    
    static function getTotalFromTag($tags_id){
        global $global;
        $tags_id = intval($tags_id);
        $users_id = intval($users_id);
        $sql = "SELECT count(id) as total FROM " . static::getTableName() . " WHERE tags_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$tags_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            return $data['total'];
        } 
        return 0;
    }
    
    static function getFromTagAndUser($tags_id, $users_id){
        global $global;
        $tags_id = intval($tags_id);
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE tags_id = ? AND users_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "ii", [$tags_id, $users_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    static function getAllFromUsers_id($users_id){
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT t.*, ts.* FROM " . static::getTableName() . " ts LEFT JOIN tags t ON ts.tags_id = t.id WHERE users_id = ?";
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }
    
    static function getAllTagsIdsFromUsers_id($users_id){
        $fullData = self::getAllFromUsers_id($users_id);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row['tags_id'];
            }
        }
        return $rows;
    }
    
    static function getAllFromTags_id($tags_id){
        global $global;
        $tags_id = intval($tags_id);
        $sql = "SELECT u.name, u.user, u.email, ts.* FROM " . static::getTableName() . " ts LEFT JOIN users u ON ts.users_id = u.id WHERE tags_id = ?";
        $res = sqlDAL::readSql($sql, "i", [$tags_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }
    
    static function subscribe($tags_id, $users_id, $notify = 0){
        $row = self::getFromTagAndUser($tags_id, $users_id);
        $id = 0;
        if(!empty($row)){
            // already subscribed
            $id = $row['id'];
        }
        $tag = new Tags_subscriptions($id);
        $tag->setTags_id($tags_id);
        $tag->setUsers_id($users_id);
        $tag->setNotify($notify);
        return $tag->save();
    }
    
    static function unsubscribe($tags_id, $users_id){
        $row = self::getFromTagAndUser($tags_id, $users_id);
        if(empty($row)){
            // already unsubscribed
            return true;
        }
        $tag = new Tags_subscriptions($row['id']);
        return $tag->delete();
        
    }

}
