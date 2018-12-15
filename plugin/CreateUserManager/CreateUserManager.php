<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/CreateUserManager/Objects/UserManager.php';

class CreateUserManager extends PluginAbstract {

    public function getDescription() {
        return "Select some users to be able to add and remove other users from user groups";
    }

    public function getName() {
        return "CreateUserManager";
    }

    public function getUUID() {
        return "cc570956-dc62-46e3-ace9-86c6e8f9c81b";
    }  

    public function getPluginVersion() {
        return "1.0";   
    }
        
    public function getPluginMenu(){
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CreateUserManager/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/CreateUserManager/menuRight.php';
    }
    
    static function deleteGroupsFromUser($users_id){
        if (!self::isManager()) {
            return false;
        }

        global $global;
        if (!empty($users_id)) {
            $sql = "DELETE FROM users_has_users_groups WHERE users_id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql,"i",array($users_id));
    }
    
    static function updateUserGroups($users_id, $array_groups_id){
        if (!self::isManager()) {
            return false;
        }
        if (!is_array($array_groups_id)) {
            return false;
        }
        self::deleteGroupsFromUser($users_id);
        global $global;
        $sql = "INSERT INTO users_has_users_groups ( users_id, users_groups_id) VALUES (?,?)";
        foreach ($array_groups_id as $value) {
            $value = intval($value);
            sqlDAL::writeSql($sql,"ii",array($users_id,$value));
        }

        return true;
    }
    
    static function isManager(){
        if (!User::isLogged()) {
            return false;
        }
        if (User::isAdmin()) {
            return true;
        }
        
        if(UserManager::getFromUsersId(User::getId())){
            return true;
        }
        return false;
    }

}
