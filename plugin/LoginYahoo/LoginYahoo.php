<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginYahoo extends PluginAbstract {

    public function getDescription() {
        global $global;
        $obj = $this->getLogin();
        $name = $obj->type;
        $str = "Login with {$name} OAuth Integration";
        $str .= "<br><a href='{$obj->linkToDevelopersPage}'>Get {$name} ID and Key</a>"
        . "<br>Valid OAuth redirect URIs: <strong>{$global['webSiteRootURL']}objects/login.json.php?type=$name</strong>";
        return $str;
    }

    public function getName() {
        return "LoginYahoo";
    }

    public function getUUID() {
        return "03a225a1-f4b8-4844-8366-75436025e8a7";
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();                
        $obj->id = "";
        $obj->key = "";
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'login', 'yahoo');
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-primary btn-block"; 
        $obj->icon = "fa fa-yahoo"; 
        $obj->type = "Yahoo"; 
        $obj->linkToDevelopersPage = "https://developer.yahoo.com/oauth2/guide/flows_authcode/";         
        return $obj;
    }
    
}
