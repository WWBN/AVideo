<?php
require_once $global['systemRootPath'].'objects/plugin.php';
class YouPHPTubePlugin{
    public static function getHeadCode(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getHeadCode();
        }
        return $str;
    }
    public static function getFooterCode(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getFooterCode();
        }
        return $str;
    }
    public static function getHTMLBody(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getHTMLBody();
        }
        return $str;
    }
    public static function getHTMLMenuLeft(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getHTMLMenuLeft();
        }
        return $str;
    }
    public static function getHTMLMenuRight() {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getHTMLMenuRight();
        }
        return $str;
    }
        
    public static function getFirstPage() {
        $plugins = Plugin::getAllEnabled();
        $firstPage = false;
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $fp = $p->getFirstPage();
            if(!empty($fp)){
               $firstPage = $fp; 
            }
        }
        return $firstPage;
    }
    
    static function loadPlugin($name){
        global $global;
        $file = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        if(file_exists($file)){
            require_once $file;
            eval("\$p = new {$name}();");
            return $p;
        }
        return false;
    }
    
    static function loadPluginIfEnabled($name){
        $p = static::loadPlugin($name);
        if($p){
            $uuid = $p->getUUID();
            if(static::isEnabled($uuid)){
                return $p;
            }
        }
        return false;
    }
    
    static function getObjectData($name){
        $p = static::loadPlugin($name);
        if($p){
            return $p->getDataObject();
        }
        return false;
    }
    
    static function getObjectDataIfEnabled($name){
        $p = static::loadPlugin($name);
        if($p){
            $uuid = $p->getUUID();
            if(static::isEnabled($uuid)){
                return static::getObjectData($name);
            }
        }
        return false;
    }
    
    static function xsendfilePreVideoPlay(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->xsendfilePreVideoPlay();
        }
        return $str;
    }
    
    static function getVideosManagerListButton(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getVideosManagerListButton();
        }
        return $str;
    }
    
    
    public static function isEnabled($uuid){
        return !empty(Plugin::getEnabled($uuid));
    }
    
}