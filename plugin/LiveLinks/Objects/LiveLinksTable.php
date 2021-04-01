<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveLinksTable extends ObjectYPT {

    protected $id, $title, $description, $link, $start_date, $end_date, $type, $status, $users_id, $categories_id;

    static function getSearchFieldsNames() {
        return array('title', 'description');
    }

    static function getTableName() {
        return 'LiveLinks';
    }

    function save() {
        if (!User::isLogged()) {
            return false;
        }

        if (empty($this->users_id)) {
            $this->users_id = User::getId();
        }
        if (empty($this->categories_id)) {
            $this->categories_id = 'NULL';
        }
        Category::clearCacheCount();
        $id = parent::save();
        if(class_exists('Live') && $id){
            Live::deleteStatsCache(true);
            if($this->status=='a'){
                Live::notifySocketStats();
            }else{
                LiveLinks::notifySocketToRemoveLiveLinks($id);
            }
        }
        return $id;
    }

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getLink() {
        return $this->link;
    }

    function getStart_date() {
        return $this->start_date;
    }

    function getEnd_date() {
        return $this->end_date;
    }

    function getType() {
        return $this->type;
    }

    function getStatus() {
        return $this->status;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setLink($link) {
        $this->link = $link;
    }

    function setStart_date($start_date) {
        $this->start_date = $start_date;
    }

    function setEnd_date($end_date) {
        $this->end_date = $end_date;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function getCategories_id() {
        return $this->categories_id;
    }

    function setCategories_id($categories_id) {
        $this->categories_id = intval($categories_id);
    }

    public static function getAll($users_id = 0) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $users_id = intval($users_id);
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if (!empty($users_id)) {
            $sql .= " AND users_id = '{$users_id}' ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    public function delete(){
        global $global;
        if(!User::isLogged()){
            return false;
        }
        if (!empty($this->id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE id = ?";
            
            if (!User::isAdmin()) {
                $sql .= " AND users_id = ".User::getId();
            }
            
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($this->id));
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion", AVideoLog::$ERROR);
        return false;
    }

}
