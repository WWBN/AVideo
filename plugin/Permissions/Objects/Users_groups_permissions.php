<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Users_groups_permissions extends ObjectYPT {

    protected $id, $name, $users_groups_id, $plugins_id, $type, $status;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'users_groups_permissions';
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

    static function getAllPlugins() {
        global $global;
        $table = "plugins";
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

    function setName($name) {
        $this->name = $name;
    }

    function setusers_groups_id($users_groups_id) {
        $this->users_groups_id = intval($users_groups_id);
    }

    function setPlugins_id($plugins_id) {
        $this->plugins_id = intval($plugins_id);
    }

    function setType($type) {
        $this->type = intval($type);
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getId() {
        return intval($this->id);
    }

    function getName() {
        return $this->name;
    }

    function getusers_groups_id() {
        return intval($this->users_groups_id);
    }

    function getPlugins_id() {
        return intval($this->plugins_id);
    }

    function getType() {
        return $this->type;
    }

    function getStatus() {
        return $this->status;
    }
    
    static function deleteAllFromGroup($groups_id){
        global $global;
        if (!self::isTableInstalled()) {
            return true;
        }
        $groups_id = intval($groups_id);
        if (!empty($groups_id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE users_groups_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($groups_id));
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion", AVideoLog::$ERROR);
        return false;
    }
    
    static function add($pluginName, $users_groups_id, $type){
        if (!self::isTableInstalled()) {
            return false;
        }
        $row = Plugin::getPluginByName($pluginName);
        if(!empty($row['id'])){
            $o = new Users_groups_permissions(0);
            $o->setusers_groups_id($users_groups_id);
            $o->setPlugins_id($row['id']);
            $o->setType($type);
            return $o->save();
        }
        return false;
    }
    
    
    static function getAllFromUserGorup($users_groups_id, $activeOnly = true) {
        if (!self::isTableInstalled()) {
            return array();
        }
        global $global, $getAllPermissionsFromUserGorup;
        $users_groups_id = intval($users_groups_id);
        if(empty($users_groups_id)){
            return array();
        }
        if(empty($getAllPermissionsFromUserGorup)){
            $getAllPermissionsFromUserGorup = array();
        }
        if(isset($getAllPermissionsFromUserGorup[$users_groups_id])){
            return $getAllPermissionsFromUserGorup[$users_groups_id];
        }
        $sql = "SELECT * FROM " . static::getTableName() . " ";
        $sql .= " WHERE users_groups_id = ?";
        if($activeOnly){
            $sql .= " and status = 'a' ";
        }
        $res = sqlDAL::readSql($sql, "i", array($users_groups_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $plugin = new Plugin($row['plugins_id']);
                if(empty($rows[$plugin->getName()])){
                    $rows[$plugin->getName()] = array();
                }
                $rows[$plugin->getName()][] = $row['type'];
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $getAllPermissionsFromUserGorup[$users_groups_id] = $rows;
        return $rows;
    }
    
    
    static function getAllFromPluginAndType($plugins_id, $type, $activeOnly = true) {
        if (!self::isTableInstalled()) {
            return array();
        }
        global $global, $getAllPermissionsFromPlugin;
        $plugins_id = intval($plugins_id);
        if(empty($plugins_id)){
            return array();
        }
        if(empty($getAllPermissionsFromUserGorup)){
            $getAllPermissionsFromUserGorup = array();
        }
        if(isset($getAllPermissionsFromUserGorup[$plugins_id])){
            return $getAllPermissionsFromUserGorup[$plugins_id];
        }
        $sql = "SELECT * FROM " . static::getTableName() . " ";
        $sql .= " WHERE plugins_id = ? AND `type` = ?";
        if($activeOnly){
            $sql .= " and status = 'a' ";
        }
        $res = sqlDAL::readSql($sql, "ii", array($plugins_id, $type));
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
        $getAllPermissionsFromUserGorup[$plugins_id] = $rows;
        return $rows;
    }
    
    static function getAllFromPlugin($plugins_id, $activeOnly = true) {
        if (!self::isTableInstalled()) {
            return array();
        }
        global $global, $getAllPermissionsFromPlugin;
        $plugins_id = intval($plugins_id);
        if(empty($plugins_id)){
            return array();
        }
        if(empty($getAllPermissionsFromUserGorup)){
            $getAllPermissionsFromUserGorup = array();
        }
        if(isset($getAllPermissionsFromUserGorup[$plugins_id])){
            return $getAllPermissionsFromUserGorup[$plugins_id];
        }
        $sql = "SELECT * FROM " . static::getTableName() . " ";
        $sql .= " WHERE plugins_id = ?";
        if($activeOnly){
            $sql .= " and status = 'a' ";
        }
        $res = sqlDAL::readSql($sql, "i", array($plugins_id));
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
        $getAllPermissionsFromUserGorup[$plugins_id] = $rows;
        return $rows;
    }
    
    static function getFromUserGroupAndPluginAndType($users_groups_id, $plugins_id, $type, $activeOnly = true) {
        if (!self::isTableInstalled()) {
            return array();
        }
        global $global, $getFromUserGroupAndPluginAndType;
        $plugins_id = intval($plugins_id);
        if(empty($plugins_id)){
            return array();
        }
        $name = "$users_groups_id, $plugins_id, $type";
        if(empty($getFromUserGroupAndPluginAndType)){
            $getAllPermissionsFromUserGorup = array();
        }
        if(isset($getFromUserGroupAndPluginAndType[$name])){
            return $getFromUserGroupAndPluginAndType[$name];
        }
        $sql = "SELECT * FROM " . static::getTableName() . " ";
        $sql .= " WHERE users_groups_id = ? AND plugins_id = ? AND `type` = ? LIMIT 1";
        if($activeOnly){
            $sql .= " and status = 'a' ";
        }
        //echo $sql;var_dump($users_groups_id, $plugins_id, $type);
        $res = sqlDAL::readSql($sql, "iii", array($users_groups_id, $plugins_id, $type), true);        
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        $getFromUserGroupAndPluginAndType[$name] = $row;
        return $row;
    }
    
    public function save() {
        if (!self::isTableInstalled()) {
            return true;
        }
        if(empty($this->status)){
            $this->status = 'a';
        }
        return parent::save();
    }

}
