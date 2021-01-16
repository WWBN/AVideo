<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Categories_has_users_groups extends ObjectYPT {

    protected $id,$categories_id,$users_groups_id,$status;
    
    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'categories_has_users_groups';
    }
    
    static function getAllCategories() {
        global $global;
        $table = "categories";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

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
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
static function getAllUsers_groups() {
        global $global;
        $table = "users_groups";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

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
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setCategories_id($categories_id) {
        $this->categories_id = intval($categories_id);
    } 
 
    function setUsers_groups_id($users_groups_id) {
        $this->users_groups_id = intval($users_groups_id);
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getCategories_id() {
        return intval($this->categories_id);
    }  
 
    function getUsers_groups_id() {
        return intval($this->users_groups_id);
    }  
 
    function getStatus() {
        return $this->status;
    }  
    
    
    public static function getAll(){
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT c.*, ug.*, cug.* FROM  " . static::getTableName() . " cug "
                . " LEFT JOIN categories c ON cug.categories_id = c.id "
                . " LEFT JOIN users_groups ug ON cug.users_groups_id = ug.id "
                . " WHERE 1=1 ";

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
    
    public static function getAllFromCategory($categories_id){
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $categories_id = intval($categories_id);
        if(empty($categories_id)){
            return false;
        }
        $sql = "SELECT c.*, ug.*, cug.* FROM  " . static::getTableName() . " cug "
                . " LEFT JOIN categories c ON cug.categories_id = c.id "
                . " LEFT JOIN users_groups ug ON cug.users_groups_id = ug.id "
                . " WHERE cug.categories_id = {$categories_id} ";

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

        
}
