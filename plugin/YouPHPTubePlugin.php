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
    public static function getGallerySection(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getGallerySection();
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
        
    
    private static function firstPage(){
        $name = "ThemeSwitcherMenu";
        if(Plugin::isEnabledByName($name)){
            $p = static::loadPlugin($name);
            $page = $p->getPage();
            if(!empty($page)){
                $p2 = static::loadPlugin($page);
                return $p2->getFirstPage();
            }
        }
        return false;
    }
    
    public static function getFirstPage() {
        // if the menu set a different defaul page
        $fp = static::firstPage();
        if(!empty($fp)){
            return $fp;
        }
        return static::getEnabledFirstPage();
    }
    
    public static function getEnabledFirstPage(){
        $plugins = Plugin::getAllEnabled();
        $firstPage = false;
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            if(!is_object($p)){
                continue;
            }
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
    
    static function getWatchActionButton(){
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $str .= $p->getWatchActionButton();
        }
        return $str;
    }
    
    
    public static function isEnabled($uuid){
        return !empty(Plugin::getEnabled($uuid));
    }
    
    static function isEnabledByName($name){
        $p = static::loadPluginIfEnabled($name);
        return !empty($p);
    }
    
    static function getLogin(){
        $plugins = Plugin::getAllEnabled();
        $logins = array();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $l = $p->getLogin();
            if(is_string($l) && file_exists($l)){ // it is a login form
                $logins[] = $l;
            }else if(!empty($l->type)){ // it is a hybridauth
                $logins[] = array('parameters'=>$l, 'loginObject'=>$p);
            }
            
        }
        return $logins;
    }
    
    public static function getStart() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $p->getStart();
        }
    }
    
    public static function getEnd() {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = static::loadPlugin($value['dirName']);
            $p->getEnd();
        }
    }
    
}