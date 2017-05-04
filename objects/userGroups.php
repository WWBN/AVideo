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
        // cannot delete default category
        if ($this->id == 1) {
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



}
