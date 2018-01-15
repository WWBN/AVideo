<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginGoogle extends PluginAbstract {

    public function getDescription() {
        global $global;
        $obj = $this->getLogin();
        $name = $obj->type;
        $str = "Login with {$name} OAuth Integration";
        $str .= "<br><a href='{$obj->linkToDevelopersPage}'>Get {$name} ID and Key</a>"
        . "<br>Valid OAuth redirect URIs: <strong>{$global['webSiteRootURL']}objects/login.json.php?type=$name</strong>"
        . "<br>For mobile a Valid OAuth redirect URIs: <strong>{$global['webSiteRootURL']}plugin/MobileManager/oauth2.php?type=$name</strong>";
        return $str;
    }

    public function getName() {
        return "LoginGoogle";
    }

    public function getUUID() {
        return "15240814-8c31-4f15-a355-48715fac13f3";
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
                
        $obj->id = "";
        $obj->key = "";
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'login', 'google');
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-danger btn-block"; 
        $obj->icon = "fa fa-google"; 
        $obj->type = "Google"; 
        $obj->linkToDevelopersPage = "https://console.developers.google.com/apis/credentials";         
        return $obj;
    }
    
}
