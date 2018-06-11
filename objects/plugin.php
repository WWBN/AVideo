<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Plugin extends ObjectYPT {

    protected $id, $status, $object_data, $name, $uuid, $dirName;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'plugins';
    }

    function getId() {
        return $this->id;
    }

    function getStatus() {
        return $this->status;
    }

    function getObject_data() {
        return $this->object_data;
    }

    function getName() {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setObject_data($object_data) {
        $this->object_data = $object_data;
    }

    function setName($name) {
        $this->name = $name;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getDirName() {
        return $this->dirName;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setDirName($dirName) {
        $this->dirName = $dirName;
    }

    static function getPluginByName($name) {
        global $global, $getPluginByName;
        if(empty($getPluginByName)){
            $getPluginByName = array();
        }
        if(empty($getPluginByName[$name])){
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE name = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", array($name));
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!empty($data)) {
                $getPluginByName[$name] = $data;
            } else {
                $getPluginByName[$name] = false;
            }
        }
        return $getPluginByName[$name];
    }

    static function getPluginByUUID($uuid) {
        global $global,$getPluginByUUID;
        if(empty($getPluginByUUID)){
            $getPluginByUUID = array();
        }
        if(empty($getPluginByUUID[$uuid])){
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE uuid = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", array($uuid));
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!empty($data)) {
                $getPluginByUUID[$uuid] = $data;
            } else {
                $getPluginByUUID[$uuid] = false;
            }
        }
        return $getPluginByUUID[$uuid];
    }

    function loadFromUUID($uuid) {
        $this->uuid = $uuid;
        $row = static::getPluginByUUID($uuid);
        if (!empty($row)) {
            $this->load($row['id']);
        }
    }

    static function isEnabledByName($name) {
        $row = static::getPluginByName($name);
        if ($row) {
            return $row['status'] == 'active';
        }
        return false;
    }

    static function isEnabledByUUID($uuid) {
        $row = static::getPluginByUUID($uuid);
        if ($row) {
            return $row['status'] == 'active';
        }
        return false;
    }

    static function getAvailablePlugins() {
        global $global,$getAvailablePlugins;
        if(empty($getAvailablePlugins)){
            $dir = $global['systemRootPath'] . "plugin";
            $getAvailablePlugins = array();
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, array(".", ".."))) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                        $p = YouPHPTubePlugin::loadPlugin($value);
                        if (!is_object($p) || $p->hidePlugin()) {
                            error_log("Plugin Not Found: {$value}");
                            continue;
                        }
                        $obj = new stdClass();
                        $obj->name = $p->getName();
                        $obj->dir = $value;
                        $obj->uuid = $p->getUUID();
                        $obj->description = $p->getDescription();
                        $obj->installedPlugin = static::getPluginByUUID($obj->uuid);
                        $obj->enabled = (!empty($obj->installedPlugin['status']) && $obj->installedPlugin['status'] === "active") ? true : false;
                        $obj->id = (!empty($obj->installedPlugin['id'])) ? $obj->installedPlugin['id'] : 0;
                        $obj->data_object = $p->getDataObject();
                        $obj->databaseScript = !empty(static::getDatabaseFile($value));
                        $obj->pluginMenu = $p->getPluginMenu();
                        $obj->tags = $p->getTags();
                        $getAvailablePlugins[] = $obj;
                    }
                }
            }
        }
        return $getAvailablePlugins;
    }

    static function getDatabaseFile($pluginName) {
        $filename = static::getDatabaseFileName($pluginName);
        if (!$filename) {
            return false;
        }
        return url_get_contents($filename);
    }

    static function getDatabaseFileName($pluginName) {
        global $global;
        $dir = $global['systemRootPath'] . "plugin";
        $filename = $dir . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . "install.sql";
        if (!file_exists($filename)) {
            return false;
        }
        return $filename;
    }

    static function getAllEnabled() {
        global $global, $getAllEnabledRows;
        if(empty($getAllEnabledRows)){
            $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='active' ";
            $res = $global['mysqli']->query($sql);
            $getAllEnabledRows = array();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $getAllEnabledRows[] = $row;
                }           

                uasort($getAllEnabledRows, 'cmpPlugin');
            } else {
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
        return $getAllEnabledRows;
    }

    static function getEnabled($uuid) {
        global $global,$getEnabled;
        if(empty($getEnabled)){
            $getEnabled = array();
        }
        if(empty($getEnabled[$uuid])){
            $getEnabled[$uuid] = array();
            $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='active' AND uuid = '".$uuid."' ;";
            $res = sqlDAL::readSql($sql); 
            $pluginRows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if($pluginRows!=false){
                foreach($pluginRows as $row){
                    $getEnabled[$uuid][] = $row;
                }
            }
        }
        return $getEnabled[$uuid];
    }
    
    function save() {
        global $global;
        $this->object_data = $global['mysqli']->real_escape_string($this->object_data);
        return parent::save();
    }

}
