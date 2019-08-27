<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';

class User_Location extends PluginAbstract {

    public function getDescription() {
        global $global;
        $ret = "Detects user location for various purposes";
        $ret .= "<br>This site or product includes IP2Location LITE data available from http://www.ip2location.com.";
        $ret .= "<br><strong>Before use this plugin unzip the install.zip file and install the IPs tables<strong>";
        $ret .= "<br><pre>cd {$global['systemRootPath']}plugin/User_Location/install && unzip install.zip</pre>";
        return $ret;
    }

    public function getName() {
        return "User_Location";
    }

    public function getUUID() {
        return "45432a78-d0c6-47f3-8ac4-8fd05f507386";
    }

    public function getPluginVersion() {
        return "1.0";   
    }
    
    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->autoChangeLanguage = true;
        return $obj;
    }    
    
    static  function getThisUserLocation() {
        if(!empty($_SESSION['User_Location'])){
            return $_SESSION['User_Location'];
        }
        return IP2Location::getLocation(getRealIpAddr());
    }
    
    public function getStart() {
        global $global;
        $obj = $this->getDataObject();
        $User_Location = self::getThisUserLocation();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if($obj->autoChangeLanguage){
            if(empty($_SESSION['User_Location']) && !empty($User_Location['country_code'])){
                $_SESSION['language'] = strtolower($User_Location['country_code']);
                $file = "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
                if(file_exists($file)){
                    include_once $file;
                }else{
                    $_SESSION['language'] = 'us';
                }
            }
        }
        $_SESSION['User_Location'] = $global['User_Location'] = $User_Location;
        return false;
    }


}
