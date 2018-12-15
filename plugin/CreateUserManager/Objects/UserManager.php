<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class UserManager extends ObjectYPT {

    protected $id, $users_id, $status;
    
    static function getSearchFieldsNames() {
        return array('name', 'user', 'email');
    }

    static function getTableName() {
        return 'userManagers';
    }
    
    static function getTotalUsers() {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT a.id FROM  users u LEFT JOIN " . static::getTableName() . " a ON u.id = users_id  WHERE u.status = 'a'  ";
        $sql .= self::getSqlSearchFromPost();
        //echo $sql;
        $res = sqlDAL::readSql($sql); 
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }    

    static function getAllUsers() {
        global $global;
        $sql = "SELECT u.*, u.id as real_users_id, a.* FROM  users u LEFT JOIN " . static::getTableName() . " a  ON u.id = users_id WHERE u.status = 'a' ";
        $sql .= self::getSqlFromPost("u.");
        //echo $sql;
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $row['groups'] = UserGroups::getUserGroups($row['real_users_id']);
                $row['switch'] = '
                    <div class="material-switch pull-right">
                        <input onchange="switchManager('.$row['real_users_id'].');" data-toggle="toggle" type="checkbox" value="'.$row['real_users_id'].'" id="themeSwitch'.$row['real_users_id'].'" '.(($row['status']==='a')?"checked":"").'>
                        <label for="themeSwitch'.$row['real_users_id'].'" class="label-primary"></label>
                    </div>';
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function getFromUsersId($users_id) {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql,"i",array($users_id)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    
    function loadFromUsersId($users_id) {
        $row = self::getFromUsersId($users_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
    
    function switchUsers($users_id) {
        $loaded = $this->loadFromUsersId($users_id);
        $status = 'a';
        if(empty($loaded)){
            $this->setUsers_id($users_id);
        }else{
            if($this->getStatus()==='a'){
                $status = 'i';
                $this->setStatus('i');
            }else{
                $this->setStatus('a');
            }
        }
        $obj = new stdClass();
        $obj->id = $this->save();
        $obj->status = $status;
        return $obj;
    }
    
    function getUsers_id() {
        return $this->users_id;
    }

    function getStatus() {
        return $this->status;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }
    
    

}
