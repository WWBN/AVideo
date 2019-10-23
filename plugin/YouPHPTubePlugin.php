<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

class YouPHPTubePlugin {
    
    static function YPTstart() {
        global $global;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $global['YouPHPTubePluginStart'] = $time;
    }

    static function YPTend($pluginName) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/user.php';
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $global['YouPHPTubePluginStart']), 4);
        if($total_time > 0.05){
            error_log("Warning: The plugin [{$pluginName}] takes {$total_time} seconds to complete. ");
            error_log($_SERVER["SCRIPT_FILENAME"]);
            
        }
    }

   
    public static function addRoutes() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addRoutes();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return false;
    }
    public static function addView($videos_id, $total) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addView($videos_id, $total);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return false;
    }

    public static function getHeadCode() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHeadCode();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getChartTabs() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                if(method_exists ($p,'getChartTabs')){
                    $str .= $p->getChartTabs();
                }else{
                    $checkStr = $p->getChartContent();
                    if (!empty($checkStr)) {
                        $str .= '<li><a data-toggle="tab" id="pluginMenuLink' . $p->getName() . '" href="#pluginMenu' . $p->getName() . '">' . $p->getName() . '</a></li>';
                    }
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getChartContent() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $checkStr = $p->getChartContent();
                if (!empty($checkStr)) {
                    $str .= '<div id="pluginMenu' . $p->getName() . '" class="tab-pane fade" style="padding: 10px;"><div class="row">' . $checkStr . '</div></div>';
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getGallerySection() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getGallerySection();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getHelpToc() {
        $plugins = Plugin::getAllEnabled();
        $str = "<h4>" . __("Table of content") . "</h4><ul>";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                if (!empty($t)) {
                    $str .= "<li><a href='#" . $value['name'] . " help'>" . $value['name'] . "</a></li>";
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str . "</ul>";
    }

    public static function getHelp() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                $str .= $t;
                if (!empty($t)) {
                    $str .= "<hr />";
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getFooterCode() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getFooterCode();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getJSFiles() {
        $plugins = Plugin::getAllEnabled();
        $allFiles = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $js = $p->getJSFiles();
                if(is_array($js)){
                    $allFiles = array_merge($allFiles, $js);
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $allFiles;
    }

    public static function getCSSFiles() {
        $plugins = Plugin::getAllEnabled();
        $allFiles = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $allFiles = array_merge($allFiles, $p->getCSSFiles());
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $allFiles;
    }

    public static function getHTMLBody() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLBody();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getHTMLMenuLeft() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuLeft();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getHTMLMenuRight() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuRight();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    private static function firstPage() {
        $name = "ThemeSwitcherMenu";
        if (Plugin::isEnabledByName($name)) {
            $p = static::loadPlugin($name);
            if (is_object($p)) {
                $page = $p->getPage();
                if (!empty($page)) {
                    $p2 = static::loadPlugin($page);

                    return $p2->getFirstPage();
                }
            }
        }
        return false;
    }

    public static function getFirstPage() {
        // if the menu set a different defaul page
        $fp = static::firstPage();
        if (!empty($fp)) {
            return $fp;
        }
        return static::getEnabledFirstPage();
    }

    public static function getEnabledFirstPage() {
        $plugins = Plugin::getAllEnabled();
        $firstPage = false;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (!is_object($p)) {
                continue;
            }
            $fp = $p->getFirstPage();
            if (!empty($fp)) {
                $firstPage = $fp;
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $firstPage;
    }

    static function loadPlugin($name) {
        global $global, $pluginIsLoaded;
        if (empty($pluginIsLoaded)) {
            $pluginIsLoaded = array();
        }
        $file = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        $crc = dechex(crc32($name));
        if (!isset($pluginIsLoaded[$crc])) {

            if (file_exists($file)) {
                require_once $file;
                $code = "\$p = new {$name}();";
                $codeResult = @eval($code . " return \$p;");
                if ($codeResult == false) {
                    error_log("[loadPlugin] eval failed for plugin " . $name);
                }
                $pluginIsLoaded[$crc] = $codeResult;
                return $codeResult;
            } else {
                // error_log("Plugin File Not found ".$file );
                $pluginIsLoaded[$crc] = "false"; // only for pass empty-function
            }
        } else {
            if (!empty($global['debug'])) {
                error_log("Plugin was already executed " . $file);
            }
        }
        if ($pluginIsLoaded[$crc] == "false") {
            return false;
        }
        return $pluginIsLoaded[$crc];
    }

    static function loadPluginIfEnabled($name) {
        $p = static::loadPlugin($name);
        if ($p) {
            $uuid = $p->getUUID();
            if (static::isEnabled($uuid)) {
                return $p;
            }
        }
        return false;
    }

    static function getObjectData($name) {
        $p = static::loadPlugin($name);
        if ($p) {
            return $p->getDataObject();
        }
        return false;
    }

    static function getObjectDataIfEnabled($name) {
        $p = static::loadPlugin($name);
        if ($p) {
            $uuid = $p->getUUID();
            if (static::isEnabled($uuid)) {
                return static::getObjectData($name);
            }
        }
        return false;
    }

    static function xsendfilePreVideoPlay() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->xsendfilePreVideoPlay();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    static function getVideosManagerListButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getVideosManagerListButton();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    static function getUsersManagerListButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getUsersManagerListButton();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    static function getWatchActionButton($videos_id) {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getWatchActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    static function getNetflixActionButton($videos_id) {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getNetflixActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }
    
    static function getGalleryActionButton($videos_id) {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getGalleryActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }
    
    public static function isEnabled($uuid) {
        return !empty(Plugin::getEnabled($uuid));
    }
    
    public static function exists($name) {
        global $global;
        $filename = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        return file_exists($filename);
    }

    static function isEnabledByName($name) {
        $p = static::loadPluginIfEnabled($name);
        return !empty($p);
    }

    static function getLogin() {
        $plugins = Plugin::getAllEnabled();
        $logins = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $l = $p->getLogin();
                if (is_string($l) && file_exists($l)) { // it is a login form
                    $logins[] = $l;
                } else if (!empty($l->type)) { // it is a hybridauth
                    $logins[] = array('parameters' => $l, 'loginObject' => $p);
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $logins;
    }

    public static function getStart() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getStart();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getEnd() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getEnd();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function afterNewVideo($videos_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewVideo($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function afterNewComment($comments_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewComment($comments_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function afterNewResponse($comments_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewResponse($comments_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getChannelButton() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannelButton();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }
    
    public static function getVideoManagerButton() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getVideoManagerButton();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getLivePanel() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getLivePanel();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getModeYouTube($videos_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeYouTube($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getChannel($user_id, $user) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannel($user_id, $user);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getLiveApplicationArray() {
        $plugins = Plugin::getAllEnabled();
        $array = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getLiveApplicationArray();
                $array = array_merge($array, $appArray);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $array;
    }

    public static function getPlayListButtons($playlist_id = "") {
        if (empty($playlist_id))
            return "";
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getPlayListButtons($playlist_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }
    
    public static function getMyAccount($users_id = "") {
        if (empty($users_id))
            return "";
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getMyAccount($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $str;
    }

    public static function getPluginUserOptions() {
        $plugins = Plugin::getAllEnabled();
        $userOptions = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $userOptions = array_merge($userOptions, $p->getUserOptions());
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $userOptions;
    }

    /**
     * 
     * @return type return a list of IDs of the user groups
     */
    public static function getDynamicUserGroupsId($users_id) {
        $plugins = Plugin::getAllEnabled();
        $array = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getDynamicUserGroupsId($users_id);
                $array = array_merge($array, $appArray);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $array;
    }

    public static function getUserOptions() {
        $userOptions = static::getPluginUserOptions();
        $str = "";
        foreach ($userOptions as $userOption => $id) {
            $str .= "
                <li class=\"list-group-item\">" . __($userOption) .
                    "<div class=\"material-switch pull-right\">
                        <input type=\"checkbox\" value=\"$id\" id=\"$id\"/>
                        <label for=\"$id\" class=\"label-success\"></label>
                    </div>
                </li>
            ";
        }
        return $str;
    }

    public static function addUserBtnJS() {
        $userOptions = static::getPluginUserOptions();
        $userOptions = array();
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                    $('#$id').prop('checked', false);\n";
        }
        return $js;
    }

    public static function updateUserFormJS() {
        $userOptions = static::getPluginUserOptions();
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                            \"$id\": $('#$id').is(':checked'),\n";
        }
        return $js;
    }

    public static function loadUsersFormJS() {
        $userOptions = static::getPluginUserOptions();
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                        $('#$id').prop('checked', (row.$id == \"1\" ? true : false));
\n";
        }
        return $js;
    }

    public static function navBarButtons() {
        $plugins = Plugin::getAllEnabled();
        $userOptions = array();
        $navBarButtons = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBarButtons();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $navBarButtons;
    }
    
    public static function navBar() {
        $plugins = Plugin::getAllEnabled();
        $userOptions = array();
        $navBarButtons = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBar();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $navBarButtons;
    }

    /**
     * excecute update function at plugin and 
     * update plugin version at database 
     */
    public static function updatePlugin($name) {
        $p = static::loadPlugin($name);
        $currentVersion = $p->getPluginVersion();
        $uuid = $p->getUUID();
        error_log("YouPHPTubePlugin::updatePlugin name=($name) uuid=($uuid) ");
        if (method_exists($p, 'updateScript')) {
            error_log("YouPHPTubePlugin::updatePlugin method_exists ");
            if ($p->updateScript())
                Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
            else
                return false;
        }else {
            error_log("YouPHPTubePlugin::updatePlugin method NOT exists ");
            Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
        }
        return true;
    }
    
    public static function getCurrentVersion($name){
        $p = static::loadPlugin($name);
        $uuid = $p->getUUID();
        return Plugin::getCurrentVersionByUuid($uuid);
    }
    
    /**
     * 
     * @param type $name
     * @param type $version
     * @return type
     * -1 if your plugin is lower,
     * 0 if they are equal, and
     * 1 if your plugin is greater.
     */
    public static function compareVersion($name, $version){
        $currentVersion = self::getCurrentVersion($name);
        return version_compare($currentVersion, $version);
    }

    public static function getSwitchButton($name) {
        global $global;
        $p = static::loadPlugin($name);
        $btn = "";
        if(!empty($p)){
            $uid = uniqid();
           $btn = '<div class="material-switch">
                    <input class="pluginSwitch" data-toggle="toggle" type="checkbox" id="subsSwitch'.$uid.'" value="1" ' . (self::isEnabledByName($name) ? "checked" : "") . ' >
                    <label for="subsSwitch'.$uid.'" class="label-primary"></label>
                </div><script>
                $(document).ready(function () {
                $("#subsSwitch'.$uid.'").change(function (e) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: "'.$global['webSiteRootURL'].'objects/pluginSwitch.json.php",
                        data: {"uuid": "'.$p->getUUID().'", "name": "'.$name.'", "dir": "'.$name.'", "enable": $(this).is(":checked")},
                        type: "post",
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });
                });</script>' ;
        }
        return $btn;
    }
    
    public static function getAllVideosExcludeVideosIDArray(){
        $plugins = Plugin::getAllEnabled();
        $array = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $array = array_merge($array, $p->getAllVideosExcludeVideosIDArray());
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $array;
    }
    
    public static function userCanWatchVideo($users_id, $videos_id){
        $plugins = Plugin::getAllEnabled();
        $resp = true;
        $video = new Video("", "", $videos_id);
        if(empty($video)){
            return false;
        }
        // check if the video is for paid plans only
        if($video->getOnly_for_paid()){
            $resp = false;
        }
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $can = $p->userCanWatchVideo($users_id, $videos_id);
                if(!empty($can)){
                    $resp = $can>0?true:false;
                    if($resp){
                        return true;
                    }
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $resp;
    }
    
    public static function userCanWatchVideoWithAds($users_id, $videos_id){
        $plugins = Plugin::getAllEnabled();
        $resp = true;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $can = $p->userCanWatchVideoWithAds($users_id, $videos_id);
                if(!empty($can)){
                    $resp = $can>0?true:false;
                    if($resp){
                        return true;
                    }else{
                        //error_log("userCanWatchVideoWithAds: users_id = $users_id, videos_id = $videos_id {$value['dirName']} said no");
                    }
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $resp;
    }
    
    public static function showAds($videos_id){
        $plugins = Plugin::getAllEnabled();
        $resp = true;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $resp = $resp && $p->showAds($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $resp;
    }
    
    /**
     * In case some plugin needs to play a video that is not allowed for some reason.
     * A plugin can replace the getVideo method from the youtubeMode page
     * @return type
     */
    public static function getVideo(){
        $plugins = Plugin::getAllEnabled();
        $resp = null;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $video = $p->getVideo();
                if(!empty($video)){
                    return $video;
                }
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $resp;
    }
    
    
    public static function onUserSignIn($users_id){
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSignIn($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }
    
    public static function onUserSignup($users_id){
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSignup($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }
    
    public static function onLiveStream($users_id){
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onLiveStream($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }
    
    public static function thumbsOverlay($videos_id){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->thumbsOverlay($videos_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function profileTabName($users_id){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->profileTabName($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function profileTabContent($users_id){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->profileTabContent($users_id);
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getVideoTags($videos_id){
        if(empty($videos_id)){
            return array();
        }
        if(empty($_SESSION['getVideoTags'][$videos_id])){
            $plugins = Plugin::getAllEnabled();
            $array = array();
            foreach ($plugins as $value) {
                self::YPTstart();
                $p = static::loadPlugin($value['dirName']);
                if (is_object($p)) {
                    $array = array_merge($array, $p->getVideoTags($videos_id));
                }
                self::YPTend("{$value['dirName']}::".__FUNCTION__);
            }
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['getVideoTags'][$videos_id] = $array;
            session_write_close();
        } 
        return $_SESSION['getVideoTags'][$videos_id];
    }
    
    public static function getVideoWhereClause(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getVideoWhereClause();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosAddNew(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosAddNew();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function saveVideosAddNew($post, $videos_id){
        $plugins = Plugin::getAllEnabled();
        $r = true;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r = $p->saveVideosAddNew($post, $videos_id) && $r;
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosReset(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosReset();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosEdit(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosEdit();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosEditField(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosEditField();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosJavaScripts(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosJavaScripts();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosTab(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosTab();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getManagerVideosBody(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosBody();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getAllVideosArray($videos_id){
        $plugins = Plugin::getAllEnabled();
        $r = array();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r = array_merge($r, $p->getAllVideosArray($videos_id));
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
    
    public static function getUploadMenuButton(){
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getUploadMenuButton();
            }
            self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
        return $r;
    }
}
