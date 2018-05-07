<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginFacebook extends PluginAbstract {

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
        return "LoginFacebook";
    }

    public function getUUID() {
        return "010d53de-101a-4eb6-9aeb-dcffecdb6cb3";
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();                
        $obj->id = "";
        $obj->key = "";
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'login', 'facebook');
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-primary btn-block"; 
        $obj->icon = "fab fa-facebook-square"; 
        $obj->type = "Facebook"; 
        $obj->linkToDevelopersPage = "https://developers.facebook.com/apps";         
        return $obj;
    }
    
}
