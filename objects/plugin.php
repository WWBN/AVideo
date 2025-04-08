<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__.'/../videos/configuration.php';
}
require_once __DIR__.'/Object.php';
require_once __DIR__.'/mysql_dal.php';
require_once __DIR__.'/user.php';

class Plugin extends ObjectYPT
{
    protected $id;
    protected $status;
    protected $object_data;
    protected $name;
    protected $uuid;
    protected $dirName;
    protected $pluginversion;

    public static function getSearchFieldsNames()
    {
        return ['name'];
    }

    public static function getTableName()
    {
        return 'plugins';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getObject_data()
    {
        return $this->object_data;
    }

    public function getPluginVersion()
    {
        return $this->pluginversion;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setObject_data($object_data)
    {
        $this->object_data = $object_data;
    }

    public function setName($name)
    {
        $name = preg_replace("/[^A-Za-z0-9 _-]/", '', $name);
        $this->name = $name;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getDirName()
    {
        return $this->dirName;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        $this->loadFromUUID($uuid);
    }

    public function setDirName($dirName)
    {
        $dirName = preg_replace("/[^A-Za-z0-9 _-]/", '', $dirName);
        $this->dirName = $dirName;
    }

    public function setPluginversion($pluginVersion)
    {
        $this->pluginversion = $pluginVersion;
    }

    public static function setCurrentVersionByUuid($uuid, $currentVersion)
    {
        _error_log("plugin::setCurrentVersionByUuid $uuid, $currentVersion");
        $p = static::getPluginByUUID($uuid, true);
        if (!$p) {
            _error_log("plugin::setCurrentVersionByUuid error on get plugin");
            return false;
        }
        //pluginversion isn't an object property so we must explicity update it using this function
        $sql = "update " . static::getTableName() . " set pluginversion='$currentVersion' where uuid='$uuid'";

        $name = "plugin$uuid";
        ObjectYPT::deleteCache($name);
        $res = sqlDal::writeSql($sql);
    }

    public static function getCurrentVersionByUuid($uuid)
    {
        $p = static::getPluginByUUID($uuid);
        if (!$p) {
            return false;
        }
        //pluginversion isn't an object property so we must explicity update it using this function
        $sql = "SELECT pluginversion FROM " . static::getTableName() . " WHERE uuid=? LIMIT 1 ";
        $res = sqlDAL::readSql($sql, "s", [$uuid]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            return $data['pluginversion'];
        }
        return false;
    }

    public static function getPluginByName($name)
    {
        global $global, $getPluginByName;
        if (empty($getPluginByName)) {
            $getPluginByName = [];
        }
        if (empty($getPluginByName[$name])) {
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE name = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", [$name]);
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

    public static function getPluginByUUID($uuid, $refreshCache=false)
    {
        global $global, $getPluginByUUID, $pluginJustInstalled;
        $name = "plugin$uuid";
        if (!isset($getPluginByUUID)) {
            $getPluginByUUID = [];
        }
        if (!isset($pluginJustInstalled)) {
            $pluginJustInstalled = [];
        }
        if (empty($getPluginByUUID[$uuid])) {
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE uuid = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", [$uuid], $refreshCache);
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!empty($data)) {
                if (empty($data['pluginversion'])) {
                    $data['pluginversion'] = "1.0";
                }
                if (AVideoPlugin::isPluginOnByDefault($uuid)) {
                    $data['status'] = 'active';
                }
                $getPluginByUUID[$uuid] = $data;
            } else {
                $name = AVideoPlugin::getPluginsNameOnByDefaultFromUUID($uuid);
                if (!sqlDAL::wasSTMTError() && $name !== false && empty($pluginJustInstalled[$uuid])) {
                    $pluginJustInstalled[$uuid] = 1;
                    _error_log("plugin::getPluginByUUID {$name} {$uuid} this plugin is On By Default we will install it ($sql)");
                    self::deleteByUUID($uuid);
                    self::deleteByName($name);
                    unset($getPluginByUUID[$uuid]);
                    $getPluginByUUID[$uuid] = self::getOrCreatePluginByName($name, 'active');
                } else {
                    $getPluginByUUID[$uuid] = false;
                }
            }
        }
        return $getPluginByUUID[$uuid];
    }

    public function loadFromUUID($uuid)
    {
        $uuid = preg_replace("/[^A-Za-z0-9 _-]/", '', $uuid);
        $this->uuid = $uuid;
        $row = static::getPluginByUUID($uuid);
        if (!empty($row)) {
            $this->load($row['id']);
        }
    }

    public static function isEnabledByName($name)
    {
        $row = static::getPluginByName($name);
        if ($row) {
            return $row['status'] == 'active' && AVideoPlugin::isPluginTablesInstalled($name, true);
        }
        return false;
    }

    public static function isEnabledByUUID($uuid)
    {
        $row = static::getPluginByUUID($uuid);
        if ($row) {
            return $row['status'] == 'active' && AVideoPlugin::isPluginTablesInstalled($row['name'], true);
        }
        return false;
    }

    public static function getAvailablePlugins($comparePluginVersion = false)
    {
        global $global, $getAvailablePlugins;
        $pluginsMarketplace = [];
        if ($comparePluginVersion) {
            $pluginsMarketplace = ObjectYPT::getSessionCache('getAvailablePlugins', 600); // 10 min cache
            if (empty($pluginsMarketplace)) {
                //$pluginsMarketplace = _json_decode(url_get_contents("https://tutorials.wwbn.net/info?version=1", "", 2));
                $pluginsMarketplace = _json_decode(url_get_contents("https://youphp.tube/marketplace/plugins.json.php", "", 2));
                if (!empty($pluginsMarketplace)) {
                    ObjectYPT::setSessionCache('getAvailablePlugins', $pluginsMarketplace);
                }
            }
        }
        if (empty($getAvailablePlugins)) {
            $dir = $global['systemRootPath'] . "plugin";
            $getAvailablePlugins = [];
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, [".", ".."])) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                        $p = AVideoPlugin::loadPlugin($value);
                        if (!is_object($p) || $p->hidePlugin()) {
                            if ($value !== "Statistics") { // avoid error while this plugin is not ready
                                if (!is_object($p)) {
                                    _error_log("Plugin Not Found 1: {$value}");
                                }else{
                                    _error_log("Plugin Not Found 1 hide: {$value}");
                                }
                            }else{
                                _error_log("Plugin Not loaded: {$value}");
                            }
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
                        $obj->data_object_helper = $p->getDataObjectHelper();
                        $obj->data_object_info = $p->getDataObjectInfo();
                        $obj->databaseScript = !empty(static::getDatabaseFile($value));
                        $obj->pluginMenu = $p->getPluginMenu();
                        $obj->tags = $p->getTags();
                        $obj->pluginversion = $p->getPluginVersion();
                        $obj->pluginversionMarketPlace = (!empty($pluginsMarketplace->{$obj->name}) ? $pluginsMarketplace->{$obj->name}->version : 0);
                        $obj->pluginversionCompare = (!empty($obj->pluginversionMarketPlace) ? version_compare($obj->pluginversion, $obj->pluginversionMarketPlace) : 0);
                        $obj->permissions = $obj->enabled ? Permissions::getPluginPermissions($obj->id) : [];
                        if (User::isAdmin()) {
                            $obj->isPluginTablesInstalled = AVideoPlugin::isPluginTablesInstalled($obj->name, false);
                        }
                        if ($obj->pluginversionCompare < 0) {
                            $obj->tags[] = "update";
                        }
                        $getAvailablePlugins[] = $obj;
                    }else{

                    }
                }
            }
        }
        return $getAvailablePlugins;
    }

    public static function getAvailablePluginsBasic()
    {
        global $global, $getAvailablePlugins;
        if (empty($getAvailablePlugins)) {
            $dir = $global['systemRootPath'] . "plugin";
            $getAvailablePlugins = [];
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, [".", ".."])) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                        $p = AVideoPlugin::loadPlugin($value);
                        if (!is_object($p) || $p->hidePlugin()) {
                            if ($value !== "Statistics") { // avoid error while this plugin is not ready
                                _error_log("Plugin Not Found 2: {$value}");
                            }
                            continue;
                        }
                        $row = self::getPluginByUUID($p->getUUID());
                        if(empty($row)){
                            continue;
                        }
                        $obj = new stdClass();
                        $obj->name = $p->getName();
                        $obj->pluginversion = $p->getPluginVersion();
                        $obj->status = $row['status'];

                        $pinfoFile = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR . 'pinfo.json';
                        if (file_exists($pinfoFile)) {
                            $obj->pinfo = json_decode(file_get_contents($pinfoFile));
                        } else {
                            $obj->pinfo = false;
                        }
                        $getAvailablePlugins[$p->getUUID()] = $obj;
                    }
                }
            }
        }
        return $getAvailablePlugins;
    }

    public static function getDatabaseFile($pluginName)
    {
        $filename = static::getDatabaseFileName($pluginName);
        if (!$filename) {
            return false;
        }
        return url_get_contents($filename);
    }

    public static function getDatabaseFileName($pluginName)
    {
        global $global;

        $pluginName = AVideoPlugin::fixName($pluginName);
        $dir = $global['systemRootPath'] . "plugin";
        $filename = $dir . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . "install.sql";
        if (!file_exists($filename)) {
            return false;
        }
        return $filename;
    }

    public static function getAllEnabled($try = 0)
    {
        global $global, $getAllEnabledRows;
        if (empty($getAllEnabledRows)) {
            $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='active' ";

            $defaultEnabledUUIDs = AVideoPlugin::getPluginsOnByDefault(true);
            $defaultEnabledNames = AVideoPlugin::getPluginsOnByDefault(false);
            $sql .= " OR uuid IN ('" . implode("','", $defaultEnabledUUIDs) . "')";

            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $getAllEnabledRows = [];
            foreach ($fullData as $row) {
                $getAllEnabledRows[] = $row;
                if (($key = array_search($row['uuid'], $defaultEnabledUUIDs)) !== false) {
                    unset($defaultEnabledUUIDs[$key], $defaultEnabledNames[$key]);
                }
            }

            $addedNewPlugin = false;
            foreach ($defaultEnabledUUIDs as $key => $value) {
                $obj = new Plugin(0);
                $obj->loadFromUUID($defaultEnabledUUIDs[$key]);
                $obj->setName($defaultEnabledNames[$key]);
                $obj->setDirName($defaultEnabledNames[$key]);
                $obj->setStatus("active");
                if ($obj->save()) {
                    $addedNewPlugin = true;
                }
            }

            if ($addedNewPlugin && empty($try)) {
                //ObjectYPT::deleteALLCache();
                return self::getAllEnabled(1);
            }

            uasort($getAllEnabledRows, 'cmpPlugin');
        }
        return $getAllEnabledRows;
    }

    public static function getAllDisabled()
    {
        global $global, $getAllDisabledRows;
        if (empty($getAllDisabledRows)) {
            $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='inactive' ";
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $getAllDisabledRows = [];
            foreach ($fullData as $row) {
                $getAllDisabledRows[] = $row;
            }
            uasort($getAllDisabledRows, 'cmpPlugin');
        }
        return $getAllDisabledRows;
    }

    public static function getEnabled($uuid)
    {
        global $global, $getEnabled;
        if(!class_exists('sqlDAL')){
            return array();
        }
        if (empty($getEnabled)) {
            $getEnabled = [];
        }

        if (in_array($uuid, AVideoPlugin::getPluginsOnByDefault())) {
            // make sure the OnByDefault plugins are enabled
            return self::getOrCreatePluginByName(AVideoPlugin::getPluginsNameOnByDefaultFromUUID($uuid));
        }

        if (empty($getEnabled[$uuid])) {
            $getEnabled[$uuid] = [];
            $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='active' AND uuid = '" . $uuid . "' ;";
            $res = sqlDAL::readSql($sql);
            $pluginRows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if ($pluginRows !== false) {
                foreach ($pluginRows as $row) {
                    $getEnabled[$uuid][] = $row;
                }
            }
        }

        return $getEnabled[$uuid];
    }

    public static function deleteByUUID($uuid)
    {
        global $global;
        $uuid = ($uuid);
        if (!empty($uuid)) {
            _error_log("Plugin:deleteByUUID {$uuid}");
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE uuid = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "s", [$uuid]);
        }
        return false;
    }

    public static function deleteByName($name)
    {
        global $global;
        $name = ($name);
        if (!empty($name)) {
            _error_log("Plugin:deleteByName {$name}");
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE name = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "s", [$name]);
        }
        return false;
    }

    public static function getOrCreatePluginByName($name, $statusIfCreate = 'inactive')
    {
        global $global;
        if (self::getPluginByName($name) === false) {
            $pluginFile = $global['systemRootPath'] . "plugin/{$name}/{$name}.php";
            if (file_exists($pluginFile)) {
                require_once $pluginFile;
                $code = "\$p = new {$name}();";
                eval($code);
                $plugin = new Plugin(0);
                $plugin->setUuid($p->getUUID());
                $plugin->setDirName($name);
                $plugin->setName($name);
                $plugin->setObject_data(json_encode($p->getDataObject()));
                $plugin->setStatus($statusIfCreate);
                $plugin->setPluginversion($p->getPluginVersion());
                $plugin->save();
            }
        }
        return self::getPluginByName($name);
    }

    public function save()
    {
        if (empty($this->uuid)) {
            return false;
        }
        global $global;
        $this->object_data = ($this->object_data);
        if (empty($this->object_data)) {
            $this->object_data = 'null';
        }
        self::deletePluginCache($this->uuid);
        //ObjectYPT::deleteALLCache();
        return parent::save();
    }

    public static function deletePluginCache($uuid)
    {
        $name = "plugin{$uuid}";
        ObjectYPT::deleteCache($name);
        ObjectYPT::deleteCache("plugin::getAllEnabled");
    }

    public static function encryptIfNeed($object_data)
    {
        $isString = false;
        if (!is_object($object_data)) {
            $object_data = _json_decode($object_data);
            $isString = true;
        }
        if (!empty($object_data)) {
            foreach ($object_data as $key => $value) {
                if (!empty($value->type) && !empty($value->value) && is_string($value->type) && strtolower($value->type) === "encrypted") {
                    if (!self::isEncrypted($value->value)) {
                        $obj2 = new stdClass();
                        $obj2->dateEncrypted = time();
                        $obj2->value = $value->value;
                        $object_data->$key->value = encryptString($obj2);
                    }
                }
            }
            if ($isString) {
                $object_data = json_encode($object_data);
            }
            return $object_data;
        } else {
            return '';
        }
    }

    public static function decryptIfNeed($object_data)
    {
        $isString = false;
        if (!is_object($object_data)) {
            $object_data = _json_decode($object_data);
            $isString = true;
        }
        if (!empty($object_data)) {
            foreach ($object_data as $key => $value) {
                if (!empty($value->type) && !empty($value->value) && strtolower($value->type) === "encrypted") {
                    $isEncrypted = self::isEncrypted($value->value);
                    if ($isEncrypted) {
                        $object_data->$key->value = $isEncrypted;
                    }
                }
            }
            if ($isString) {
                $object_data = json_encode($object_data);
            }
            return $object_data;
        } else {
            return '';
        }
    }

    public static function isEncrypted($object_data_element_value)
    {
        if (!empty($object_data_element_value)) {
            $object_data_element_value_json = decryptString($object_data_element_value);
            $object_data_element_value_json = _json_decode($object_data_element_value_json);
            if (!empty($object_data_element_value_json) && !empty($object_data_element_value_json->dateEncrypted)) {
                return $object_data_element_value_json->value;
            }
        }
        return false;
    }
}

class PluginTags
{
    public static $RECOMMENDED = ['success', 'Recommended', '<i class="fas fa-heart"></i>', 'RECOMMENDED'];
    public static $SECURITY = ['warning', 'Security', '<i class="fas fa-user-shield"></i>', 'SECURITY'];
    public static $LIVE = ['primary', 'Live', '<i class="fas fa-broadcast-tower"></i>', 'LIVE'];
    public static $MONETIZATION = ['primary', 'Monetization', '<i class="fas fa-dollar-sign"></i>', 'MONETIZATION'];
    public static $ADS = ['primary', 'ADS', '<i class="fas fa-camera-retro"></i>', 'ADS'];
    public static $STORAGE = ['primary', 'Storage', '<i class="fas fa-archive"></i>', 'STORAGE'];
    public static $GALLERY = ['primary', 'Gallery', '<i class="fas fa-images"></i>', 'GALLERY'];
    public static $NETFLIX = ['primary', 'Netflix', '<i class="fas fa-film"></i>', 'NETFLIX'];
    public static $LAYOUT = ['primary', 'Layout', '<i class="fas fa-sitemap"></i>', 'LAYOUT'];
    public static $LOGIN = ['primary', 'Login', '<i class="fas fa-lock"></i>', 'LOGIN'];
    public static $MOBILE = ['primary', 'Mobile', '<i class="fas fa-mobile-alt"></i>', 'MOBILE'];
    public static $PLAYER = ['primary', 'Player', '<i class="fas fa-play-circle"></i>', 'PLAYER'];
    public static $FREE = ['info', 'Free', '<i class="fas fa-check"></i>', 'FREE'];
    public static $PREMIUM = ['info', 'Premium', '<i class="fas fa-thumbs-up"></i>', 'PREMIUM'];
    public static $DEPRECATED = ['danger', 'Deprecated', '<i class="fas fa-times-circle"></i>', 'DEPRECATED'];
    public static $UNDERDEVELOPMENT = ['warning', 'Under Development', '<i class="fa-solid fa-terminal"></i>', 'UNDERDEVELOPMENT'];
}
