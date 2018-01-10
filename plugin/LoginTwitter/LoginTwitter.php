<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginTwitter extends PluginAbstract {

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
        return "LoginTwitter";
    }

    public function getUUID() {
        return "bc50f9c1-85d8-4898-8092-82ee69041b3f";
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();                
        $obj->id = "";
        $obj->key = "";
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'login', 'twitter');
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-info btn-block"; 
        $obj->icon = "fa fa-twitter"; 
        $obj->type = "Twitter"; 
        $obj->linkToDevelopersPage = "https://apps.twitter.com/";         
        return $obj;
    }
    
}
