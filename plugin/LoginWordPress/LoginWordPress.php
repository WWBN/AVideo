<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginWordPress extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$LOGIN,
        );
    }
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
        return "LoginWordPress";
    }

    public function getUUID() {
        return "wp-8c31-4f15-a355-48715fac13f3";
    }

    public function getPluginVersion() {
        return "1.0";   
    }    
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
                
        $obj->id = "";
        $obj->key = "";
        $obj->buttonLabel = "";
        return $obj;
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-primary btn-block"; 
        $obj->icon = "fab fa-wordpress"; 
        $obj->type = "WordPress"; 
        $obj->linkToDevelopersPage = "https://developer.wordpress.com/apps/";         
        return $obj;
    }
    
}
