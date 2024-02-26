<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';


class PHPBB3 extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "PHPBB3 Plugin, allow you to autologin in a phpBB forum";
        $desc .= "<br>Run this command inside your PHPBB directory <code>wget {$global['webSiteRootURL']}plugin/PHPBB3/avideoLogin.txt.php -O avideoLogin.php && php avideoLogin.php</code>";
        return $desc;
    }

    public function getName() {
        return "PHPBB3";
    }

    public function getUUID() {
        return "PHPBB3-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        global $global;       
        /*
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
        }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->phpBBURL = "";
        $obj->topMenu = true;
        $obj->topMenuLabel = 'Forum';
        $obj->topMenuIcon = '<i class="far fa-comments"></i>';
        $obj->leftMenu = true;
        $obj->leftMenuLabel = 'Forum';
        $obj->leftMenuIcon = '<i class="far fa-comments"></i>';
        $obj->showOnlyForLoggedUsers = true;
        /*
        $obj->textSample = "text";
        $obj->checkboxSample = true;
        $obj->numberSample = 5;
        
        $o = new stdClass();
        $o->type = array(0=>__("Default"))+array(1,2,3);
        $o->value = 0;
        $obj->selectBoxSample = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->textareaSample = $o;
        */
        return $obj;
    }
    
    static function getURL(){
        $obj = AVideoPlugin::getObjectData('PHPBB3');
        $url = $obj->phpBBURL;
        $url .= '?'.getCredentialsURL();
        return $url;
    }
    
    static function showButton(){
        $obj = AVideoPlugin::getObjectData('PHPBB3');
        if(empty($obj->phpBBURL)){
            return false;
        }
        if(!empty($obj->showOnlyForLoggedUsers) && !User::isLogged()){
            return false;
        }
        return true;
    }
    
    public function getHTMLMenuRight() {
        $obj = AVideoPlugin::getObjectData('PHPBB3');
        if(empty($obj->topMenuLabel)){
            return '';
        }
        if(!self::showButton()){
            return '';
        }
        echo '<li><button onclick="avideoModalIframeFull(\''.self::getURL().'\');" class="btn btn-default navbar-btn" data-toggle="tooltip" title="" data-placement="bottom" data-original-title="'.__($obj->topMenuLabel).'">'.$obj->topMenuIcon.' '.__($obj->topMenuLabel).'</button></li>';
    }
    
    public function getHTMLMenuLeft() {
        $obj = AVideoPlugin::getObjectData('PHPBB3');
        if(empty($obj->topMenuLabel)){
            return '';
        }
        if(!self::showButton()){
            return '';
        }
        echo '<li><a href="#" onclick="avideoModalIframeFull(\''.self::getURL().'\'); return false;" class="nav-link " >'.$obj->topMenuIcon.' '.__($obj->topMenuLabel).' </li>';
    
    }

}
