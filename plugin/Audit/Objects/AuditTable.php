<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class AuditTable extends ObjectYPT {

    protected $id, $method, $class, $statement, $formats, $values, $ip, $users_id;
    
    static function getSearchFieldsNames() {
        return array('method','class','statement','ip','a.created', 'user');
    }

    static function getTableName() {
        return 'audit';
    }
        
    
    function audit($method, $class, $statement, $formats, $values, $users_id) {
        $this->method = $method;
        $this->class = $class;
        $this->statement = substr(str_replace("'", "", $statement),0,1000)."n";
        $this->formats = $formats;
        $this->values = str_replace(array("'","\\"), array("",""), $values);
        $this->ip = getRealIpAddr();
        $this->users_id = empty($users_id)?"NULL":$users_id;
        return $this->save();
    }
    
    static function getTotal() {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT a.id FROM  " . static::getTableName() . " a LEFT JOIN users u ON u.id = users_id  WHERE 1=1  ";
        $sql .= self::getSqlSearchFromPost();
        //echo $sql;
        $res = sqlDAL::readSql($sql); 
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }    

    static function getAll() {
        global $global;
        $sql = "SELECT u.*, a.* FROM  " . static::getTableName() . " a LEFT JOIN users u ON u.id = users_id WHERE 1=1 ";
        $sql .= self::getSqlFromPost("a.");
        //echo $sql;
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
