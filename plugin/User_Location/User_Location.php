<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';

class User_Location extends PluginAbstract {

    public function getDescription() {
        $ret = "Detects user location for various purposes";
        $ret .= "<br>This site or product includes IP2Location LITE data available from http://www.ip2location.com.";
        return $ret;
    }

    public function getName() {
        return "User_Location";
    }

    public function getUUID() {
        return "45432a78-d0c6-47f3-8ac4-8fd05f507386";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->autoChangeLanguage = true;
        return $obj;
    }    
    
    static  function getThisUserLocation() {
        return IP2Location::getLocation(getRealIpAddr());
    }
    
    public function getStart() {
        global $global;
        $obj = $this->getDataObject();
        if($obj->autoChangeLanguage){
            $User_Location = self::getThisUserLocation();
            if(empty($_SESSION['User_Location'])){
                $_SESSION['language'] = strtolower($User_Location['country_code']);
                $file = "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
                if(file_exists($file)){
                    include_once $file;
                }else{
                    $_SESSION['language'] = 'us';
                }
            }
            $_SESSION['User_Location'] = $global['User_Location'] = $User_Location;
            //var_dump($global['User_Location'], $_GET['lang'], $_SESSION['language']);exit;
        }
        return false;
    }


}