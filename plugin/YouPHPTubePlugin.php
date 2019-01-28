<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

class YouPHPTubePlugin {

    public static function addRoutes() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addRoutes();
            }
        }
        return false;
    }
    public static function addView($videos_id, $total) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addView($videos_id, $total);
            }
        }
        return false;
    }

    public static function getHeadCode() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHeadCode();
            }
        }
        return $str;
    }

    public static function getChartTabs() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
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
        }
        return $str;
    }

    public static function getChartContent() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $checkStr = $p->getChartContent();
                if (!empty($checkStr)) {
                    $str .= '<div id="pluginMenu' . $p->getName() . '" class="tab-pane fade" style="padding: 10px;"><div class="row">' . $checkStr . '</div></div>';
                }
            }
        }
        return $str;
    }

    public static function getGallerySection() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getGallerySection();
            }
        }
        return $str;
    }

    public static function getHelpToc() {
        $plugins = Plugin::getAllEnabled();
        $str = "<h4>" . __("Table of content") . "</h4><ul>";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                if (!empty($t)) {
                    $str .= "<li><a href='#" . $value['name'] . " help'>" . $value['name'] . "</a></li>";
                }
            }
        }
        return $str . "</ul>";
    }

    public static function getHelp() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                $str .= $t;
                if (!empty($t)) {
                    $str .= "<hr />";
                }
            }
        }
        return $str;
    }

    public static function getFooterCode() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getFooterCode();
            }
        }
        return $str;
    }

    public static function getJSFiles() {
        $plugins = Plugin::getAllEnabled();
        $allFiles = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $allFiles = array_merge($allFiles, $p->getJSFiles());
            }
        }
        return $allFiles;
    }

    public static function getCSSFiles() {
        $plugins = Plugin::getAllEnabled();
        $allFiles = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $allFiles = array_merge($allFiles, $p->getCSSFiles());
            }
        }
        return $allFiles;
    }

    public static function getHTMLBody() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLBody();
            }
        }
        return $str;
    }

    public static function getHTMLMenuLeft() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuLeft();
            }
        }
        return $str;
    }

    public static function getHTMLMenuRight() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuRight();
            }
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
            $p = static::loadPlugin($value['dirName']);
            if (!is_object($p)) {
                continue;
            }
            $fp = $p->getFirstPage();
            if (!empty($fp)) {
                $firstPage = $fp;
            }
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
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->xsendfilePreVideoPlay();
            }
        }
        return $str;
    }

    static function getVideosManagerListButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getVideosManagerListButton();
            }
        }
        return $str;
    }

    static function getWatchActionButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getWatchActionButton();
            }
        }
        return $str;
    }

    static function getNetflixActionButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getNetflixActionButton();
            }
        }
        return $str;
    }
    
    static function getGalleryActionButton() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getGalleryActionButton();
            }
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
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $l = $p->getLogin();
                if (is_string($l) && file_exists($l)) { // it is a login form
                    $logins[] = $l;
                } else if (!empty($l->type)) { // it is a hybridauth
                    $logins[] = array('parameters' => $l, 'loginObject' => $p);
                }
            }
        }
        return $logins;
    }

    public static function getStart() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getStart();
            }
        }
    }

    public static function getEnd() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getEnd();
            }
        }
    }

    public static function afterNewVideo($videos_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewVideo($videos_id);
            }
        }
    }

    public static function afterNewComment($comments_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewComment($comments_id);
            }
        }
    }

    public static function afterNewResponse($comments_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewResponse($comments_id);
            }
        }
    }

    public static function getChannelButton() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannelButton();
            }
        }
    }
    
    public static function getVideoManagerButton() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getVideoManagerButton();
            }
        }
    }

    public static function getLivePanel() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getLivePanel();
            }
        }
    }

    public static function getModeYouTube($videos_id) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeYouTube($videos_id);
            }
        }
    }

    public static function getChannel($user_id, $user) {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannel($user_id, $user);
            }
        }
    }

    public static function getLiveApplicationArray() {
        $plugins = Plugin::getAllEnabled();
        $array = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getLiveApplicationArray();
                $array = array_merge($array, $appArray);
            }
        }
        return $array;
    }

    public static function getPlayListButtons($playlist_id = "") {
        if (empty($playlist_id))
            return "";
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getPlayListButtons($playlist_id);
            }
        }
        return $str;
    }
    
    public static function getMyAccount($users_id = "") {
        if (empty($users_id))
            return "";
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getMyAccount($users_id);
            }
        }
        return $str;
    }

    public static function getPluginUserOptions() {
        $plugins = Plugin::getAllEnabled();
        $userOptions = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $userOptions = array_merge($userOptions, $p->getUserOptions());
            }
        }
        return $userOptions;
    }

    /**
     * 
     * @return type return a list of IDs of the user groups
     */
    public static function getDynamicUserGroupsId() {
        $plugins = Plugin::getAllEnabled();
        $array = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getDynamicUserGroupsId();
                $array = array_merge($array, $appArray);
            }
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
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBarButtons();
            }
        }
        return $navBarButtons;
    }
    
    public static function navBar() {
        $plugins = Plugin::getAllEnabled();
        $userOptions = array();
        $navBarButtons = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBar();
            }
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
        if (method_exists($p, 'updateScript')) {
            if ($p->updateScript())
                Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
            else
                return false;
        }else {
            Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
        }
        return true;
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

}
