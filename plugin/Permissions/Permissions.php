<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Permissions/Objects/Users_groups_permissions.php';

class Permissions extends PluginAbstract {

    const PERMISSION_COMMENTS = 1;
    const PERMISSION_FULLACCESSVIDEOS = 10;
    const PERMISSION_INACTIVATEVIDEOS = 11;
    const PERMISSION_USERS = 20;
    const PERMISSION_USERGROUPS = 30;
    const PERMISSION_CACHE = 40;
    const PERMISSION_SITEMAP = 50;
    const PERMISSION_LOG = 60;

    public function getDescription() {
        $desc = "Permissions will allow you to add intermediate permisson to usergroups without need to make them Admin, "
                . " each plugin will have his own permission rules";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Permissions-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc.$help;
    }

    public function getName() {
        return "Permissions";
    }

    public function getUUID() {
        return "Permissions-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        global $global;
        /*
          if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
          sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
          }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframe(webSiteRootURL +\'plugin/Permissions/View/editor.php\');" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    static function getForm() {        
        global $global;
                
        $disabled = "";
        if (!Users_groups_permissions::isTableInstalled()) {
            $disabled = " disabled='disabled' ";
            echo "<div class=\"alert alert-danger\">"
            . "<span class=\"fa fa-info-circle\"></span> "
            . __("The Permissions Plugin is not installed. Please install it if you want to customize the permissions.")
            . "</div>";
        }        

        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $row = Plugin::getPluginByName($value['dirName']);
            $p = AVideoPlugin::loadPlugin($value['dirName']);
            if (is_object($p) && method_exists($p, 'getPermissionsOptions')) {
                $array = $p->getPermissionsOptions();
                foreach ($array as $value) {
                    if (!is_object($value)) {
                        continue;
                    }
                    echo "<div class=\"checkbox\">"
                    . "<label data-toggle=\"tooltip\" title=\"" . addcslashes($value->getDescription(), '"') . "\">"
                    . "<input ".$disabled." type=\"checkbox\" name=\"permissions[" . $value->getClassName() . "][]\" value=\"" . $value->getType() . "\" class=\"permissions " . $value->getClassName() . "\">" . $value->getName() . " "
                    . "</label>"
                    . " <button ".$disabled." type='button' class='btn btn-xs pull-right' data-toggle=\"tooltip\" title=\"" . $value->getClassName() . " Plugin\" onclick=\"pluginPermissionsBtn({$row['id']})\">(" . $value->getClassName() . ")</button>"
                    . "</div>";
                }
            }
        }
        return false;
    }

    static function hasPermission($type, $pluginName) {
        global $hasPermission;
        if (!User::isLogged()) {
            return false;
        }

        if (User::isAdmin()) {
            return true;
        }

        if (empty($hasPermission)) {
            $hasPermission = array();
        }
        if (empty($hasPermission[$pluginName])) {
            $hasPermission[$pluginName] = array();
        }
        if (isset($hasPermission[$pluginName][$type])) {
            return $hasPermission[$pluginName][$type];
        }
        $hasPermission[$pluginName][$type] = false;
        $groups = UserGroups::getUserGroups(User::getId());
        foreach ($groups as $value) {
            $permissions = Users_groups_permissions::getAllFromUserGorup($value['id']);
            if (!empty($permissions[$pluginName]) && in_array($type, $permissions[$pluginName])) {
                $hasPermission[$pluginName][$type] = true;
                return $hasPermission[$pluginName][$type];
            }
        }
        return $hasPermission[$pluginName][$type];
    }

    static function canAdminComment() {
        return self::hasPermission(Permissions::PERMISSION_COMMENTS, 'Permissions');
    }

    static function canAdminVideos() {
        return self::hasPermission(Permissions::PERMISSION_FULLACCESSVIDEOS, 'Permissions');
    }

    static function canModerateVideos() {
        return self::hasPermission(Permissions::PERMISSION_INACTIVATEVIDEOS, 'Permissions') || self::canAdminVideos();
    }

    static function canAdminUsers() {
        return self::hasPermission(Permissions::PERMISSION_USERS, 'Permissions');
    }

    static function canAdminUserGroups() {
        return self::hasPermission(Permissions::PERMISSION_USERGROUPS, 'Permissions');
    }

    static function canClearCache() {
        return self::hasPermission(Permissions::PERMISSION_CACHE, 'Permissions');
    }

    static function canGenerateSiteMap() {
        return self::hasPermission(Permissions::PERMISSION_SITEMAP, 'Permissions');
    }

    static function canSeeLogs() {
        return self::hasPermission(Permissions::PERMISSION_LOG, 'Permissions');
    }

    /**
     * 
      const COMMENTS = 1;
      const FULLACCESSVIDEOS = 10;
      const INACTIVATEVIDEOS = 11;
      const USERS = 20;
      const USERGROUPS = 30;
      const CACHE = 40;
      const SITEMAP = 50;
      const LOG = 60;
     */
    function getPermissionsOptions() {
        $permissions = array();
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_COMMENTS, __("Comments Admin"), __("Users with this option will be able to edit and delete comments in any video"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_FULLACCESSVIDEOS, __("Videos Admin"), __("Just like admin, this user will have permission to edit and delete videos from any user, including videos from admin"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_INACTIVATEVIDEOS, __("Videos Moderator"), __("This is a level below the (Videos Admin), this type of user can change the video publicity (Active, Inactive, Unlisted)"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_USERS, __("Users Admin"), __("This type of user can edit users, can add or remove users into user groups, but cannot make them admins"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_USERGROUPS, __("Users Groups Admin"), __("Can edit and delete user groups"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_CACHE, __("Cache Manager"), __("This will give the option to can clear cache (Site and first page)"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_SITEMAP, __("Sitemap"), __("This will give the option to generate SiteMap"), 'Permissions');
        $permissions[] = new PluginPermissionOption(Permissions::PERMISSION_LOG, __("Log"), __("This will give the option to see the log file menu"), 'Permissions');
        return $permissions;
    }
    
    static function getPluginPermissions($plugins_id) {
        global $getPluginPermissions;
        if(empty($getPluginPermissions)){
            $getPluginPermissions = array();
        }
        if(isset($getPluginPermissions[$plugins_id])){
            return $getPluginPermissions[$plugins_id];
        }
        $plugin = new Plugin($plugins_id);
        if(empty($plugin)){
            $getPluginPermissions[$plugins_id] = array();
            return $getPluginPermissions[$plugins_id];
        }
        $p = AVideoPlugin::loadPlugin($plugin->getName());
        if(empty($p)){
            $getPluginPermissions[$plugins_id] = array();
            return $getPluginPermissions[$plugins_id];
        }
        $options = $p->getPermissionsOptions();
        if(empty($options)){
            $getPluginPermissions[$plugins_id] = array();
            return $getPluginPermissions[$plugins_id];
        }
        $permissions = array();
        foreach ($options as $key => $value) {
            $obj = new stdClass();
            $obj->name = $options[$key]->getName();
            $obj->type = $options[$key]->getType();
            $obj->description = $options[$key]->getDescription();
            $obj->className = $options[$key]->getClassName();
            $obj->groups = Users_groups_permissions::getAllFromPluginAndType($plugins_id, $value->getType());
            $permissions[] = $obj;
        }
        $getPluginPermissions[$plugins_id] = $permissions;
        return $getPluginPermissions[$plugins_id];
    }
    
    
    static function getPluginPermissionsFromName($pluginName) {
        $row = Plugin::getPluginByName($pluginName);
        if(empty($row['id'])){
            return array();
        }
        $plugins_id = $row['id'];
        return self::getPluginPermissions($plugins_id);
    }
    
    static function setPermission($users_groups_id, $plugins_id, $type, $isEnabled) {
        //var_dump($users_groups_id, $plugins_id, $type, $isEnabled, $_POST);
        $row = Users_groups_permissions::getFromUserGroupAndPluginAndType($users_groups_id, $plugins_id, $type, false);
        //var_dump($users_groups_id, $plugins_id, $type, $isEnabled, $row);
        if(!empty($row['id'])){
            $ugp = new Users_groups_permissions($row['id']);
        }else{
            $ugp = new Users_groups_permissions();
            $ugp->setusers_groups_id($users_groups_id);
            $ugp->setPlugins_id($plugins_id);
            $ugp->setType($type);
        }
        $ugp->setStatus($isEnabled?'a':'i');
        return $ugp->save();
    }

}
