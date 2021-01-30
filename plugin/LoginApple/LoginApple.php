<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginApple extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$LOGIN,
        );
    }
    public function getDescription() {
        global $global;
        
        $parse = parse_url($global['webSiteRootURL']);
        
        $obj = $this->getLogin();
        $name = $obj->type;
        $str = "Login with {$name} OAuth Integration";
        $str .= "<br><a href='{$obj->linkToDevelopersPage}'>Get {$name} ID and Key</a>"
        . "<br>Domains and Subdomains: <strong>". $parse['host']."</strong>"
        . "<br>Return URLs: <strong>{$global['webSiteRootURL']}objects/login.json.php?type=$name</strong>"
        . ", <strong>{$global['webSiteRootURL']}plugin/MobileManager/oauth2.php?type=$name</strong>";
        $help = "<br><small><a href='https://hybridauth.github.io/providers/apple.html' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $str.$help;
    }

    public function getName() {
        return "LoginApple";
    }

    public function getUUID() {
        return "03aapple-f4b8-4844-8366-75436025e8a7";
    }

    public function getPluginVersion() {
        return "1.0";   
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();                
        $obj->id = "";
        self::addDataObjectHelper('id', 'Apple ID', 'Your Apple ID');
        $obj->team_id = "";
        self::addDataObjectHelper('team_id', 'Team id', 'This is your Account ID at the top right of the account information (2nd line)');
        $obj->key_id = "";
        self::addDataObjectHelper('key_id', 'Key ID', 'Create a new key for your Sign-In Service. This gets you a key ID (under details), for example 6Q15R47JGG');
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->key_content = $o;
        self::addDataObjectHelper('key_content', 'Apple Private key', 'Content of private key, including BEGIN and END lines');
        return $obj;
    }
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = "btn btn-default btn-block"; 
        $obj->icon = "fab fa-apple"; 
        $obj->type = "Apple"; 
        $obj->linkToDevelopersPage = "https://developer.apple.com/account/resources/certificates/list";         
        return $obj;
    }
    
}
