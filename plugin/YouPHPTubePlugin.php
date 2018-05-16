<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

class YouPHPTubePlugin {

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
        $str = "<h4>".__("Table of content")."</h4><ul>";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                if(!empty($t)){
                    $str .= "<li><a href='#".$value['name']." help'>".$value['name']."</a></li>";
                }
            }
        }
        return $str."</ul>";
    }
    public static function getHelp() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                $str .= $t;
                if(!empty($t)){
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
                $allFiles = array_merge($allFiles,$p->getJSFiles());
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
                $allFiles = array_merge($allFiles,$p->getCSSFiles());
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
            $page = $p->getPage();
            if (!empty($page)) {
                $p2 = static::loadPlugin($page);

                if (is_object($p)) {
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
        global $global;
        $file = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        if (file_exists($file)) {
            require_once $file;
            eval("\$p = new {$name}();");
            return $p;
        }
        return false;
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

    public static function isEnabled($uuid) {
        return !empty(Plugin::getEnabled($uuid));
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
    public static function getLivePanel() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getLivePanel();
            }
        }
    }
    
    public static function getLiveApplicationArray(){
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

}
