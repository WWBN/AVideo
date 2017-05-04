<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class UserGroups {

    private $id;
    private $group_name;

    function __construct($id, $group_name = "") {
        if (empty($id)) {
            // get the category data from category and pass
            $this->group_name = $group_name;
        } else {
            // get data from id
            $this->load($id);
        }
    }

    private function load($id) {
        $user = self::getUserGroupsDb($id);
        if (empty($user))
            return false;
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
    }
    
    static private function getUserGroupsDb($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users_groups WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $user = $res->fetch_assoc();
        } else {
            $user = false;
        }
        return $user;
    }

    function save() {
        global $global;
        if (empty($this->isAdmin)) {
            $this->isAdmin = "false";
        }
        if (!empty($this->id)) {
            $sql = "UPDATE users_groups SET group_name = '{$this->group_name}', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO users_groups ( group_name, created, modified) VALUES ('{$this->group_name}',now(), now())";
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    function delete() {
        if (!User::isAdmin()) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM users_groups WHERE id = {$this->id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    private function getUserGroup($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users_groups WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $category = $res->fetch_assoc();
        } else {
            $category = false;
        }
        return $category;
    }

    static function getAllUsersGroups() {
        global $global;
        $sql = "SELECT * FROM users_groups WHERE 1=1 ";

        $sql .= BootGrid::getSqlFromPost(array('group_name'));

        $res = $global['mysqli']->query($sql);
        $arr = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $arr[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }

    static function getTotalUsersGroups() {
        global $global;
        $sql = "SELECT id FROM users_groups WHERE 1=1  ";

        $sql .= BootGrid::getSqlSearchFromPost(array('group_name'));

        $res = $global['mysqli']->query($sql);


        return $res->num_rows;
    }
    
    function getGroup_name() {
        return $this->group_name;
    }

    function setGroup_name($group_name) {
        $this->group_name = $group_name;
    }

    // for users
    
    static function updateUserGroups($users_id, $array_groups_id){
        if (!User::isAdmin()) {
            return false;
        }
        if(!is_array($array_groups_id)){
            return false;
        }
        self::deleteGroupsFromUser($users_id);
        global $global;
        
        foreach ($array_groups_id as $value) {
            $value = intval($value);
            $sql = "INSERT INTO users_has_users_groups ( users_id, users_groups_id) VALUES ({$users_id},{$value})";
            echo $sql;
            $global['mysqli']->query($sql);
        }
        
        return true;
    } 
    
    static function getUserGroups($users_id){
        $result = $global['mysqli']->query("SHOW TABLES LIKE 'users_has_users_groups'");
        if (empty($result->num_rows)) {
            return array();
        }
        if(empty($users_id)){
            return array();
        }
        global $global;
        $sql = "SELECT * FROM users_has_users_groups"
                . " LEFT JOIN users_groups ON users_groups_id = id WHERE users_id = $users_id ";

        $res = $global['mysqli']->query($sql);
        $arr = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $arr[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    } 
    
    static private function deleteGroupsFromUser($users_id){
        if (!User::isAdmin()) {
            return false;
        }

        global $global;
        if (!empty($users_id)) {
            $sql = "DELETE FROM users_has_users_groups WHERE users_id = {$users_id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    // for users end
    
    // for videos   
    
    static function updateVideoGroups($videos_id, $array_groups_id){
        if (!User::canUpload()) {
            return false;
        }
        if(!is_array($array_groups_id)){
            return false;
        }
        self::deleteGroupsFromVideo($videos_id);
        global $global;
        
        foreach ($array_groups_id as $value) {
            $value = intval($value);
            $sql = "INSERT INTO videos_group_view ( videos_id, users_groups_id) VALUES ({$videos_id},{$value})";
            $global['mysqli']->query($sql);
        }
        
        return true;
    } 
    
    static function getVideoGroups($videos_id){
        global $global;
        //check if table exists if not you need to update
        $res = $global['mysqli']->query('select 1 from `videos_group_view` LIMIT 1');
        if(!$res){
            if(User::isAdmin()){
                $_GET['error'] = "You need to Update YouPHPTube to version 2.3 <a href='{$global['webSiteRootURL']}update/'>Click here</a>";
            }
           return array();
        }
        
        $sql = "SELECT * FROM videos_group_view as v "
                . " LEFT JOIN users_groups as ug ON users_groups_id = ug.id WHERE videos_id = $videos_id ";

        $res = $global['mysqli']->query($sql);
        $arr = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $arr[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    } 
    
    static private function deleteGroupsFromVideo($videos_id){
        if (!User::canUpload()) {
            return false;
        }

        global $global;
        if (!empty($videos_id)) {
            $sql = "DELETE FROM videos_group_view WHERE videos_id = {$videos_id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

}
