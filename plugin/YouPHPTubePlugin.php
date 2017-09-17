<?php
require_once $global['systemRootPath'].'objects/plugin.php';
class YouPHPTubePlugin{
    public static function getHeadCode(){
        return "";
    }
    public static function getHTMLBody(){
        return "";
    }
    public static function getHTMLMenuLeft(){
        return "";
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
    
    static function loadPlugin($name){
        global $global;
        require_once "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        eval("\$p = new {$name}();");
        return $p;
    }
    
}