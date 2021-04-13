<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';

class User_Location extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        global $global, $mysqlDatabase;
        $ret = "Detects user location for various purposes";
        $ret .= "<br>This site or product includes IP2Location LITE data available from http://www.ip2location.com.";
        $ret .= "<br><strong>Before use this plugin unzip the install.zip file and install the IPs tables<strong>";
        $ret .= "<br><pre>cd {$global['systemRootPath']}plugin/User_Location/install && unzip install.zip</pre>";
        
        if(!ObjectYPT::isTableInstalled("ip2location_db1_ipv6")){
            $ret .= "<br><strong>For IPV6 support unzip the ip2location_db1_ipv6.zip file and install the IPs tables<strong>";
            $ret .= "<br><pre>cd {$global['systemRootPath']}plugin/User_Location/install && unzip ip2location_db1_ipv6.zip && mysql -u root -p {$mysqlDatabase} <  {$global['systemRootPath']}plugin/User_Location/install/ip2location_db1_ipv6.sql </pre>";
        }
        
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
    
    static function getSessionLocation(){
        $ip = getRealIpAddr();
        if(!empty($_SESSION['User_Location'][$ip]['country_name'])){
            if ($_SESSION['User_Location'][$ip]['country_name'] == "United States of America") {
                $_SESSION['User_Location'][$ip]['country_name'] = "United States";
            }
            return $_SESSION['User_Location'][$ip];
        }
        return false;
    }
    
    static function setSessionLocation($value){
        $ip = getRealIpAddr();
        $_SESSION['User_Location'][$ip] = $value;
        //_error_log("User_Location: $ip ". json_encode($_SESSION['User_Location'][$ip]));
    }
    
    static  function getThisUserLocation() {
        $location = self::getSessionLocation();
        if(!empty($location['country_code'])){
            return $location;
        }
        return IP2Location::getLocation(getRealIpAddr());
    }
    
    public function getStart() {
        global $global, $config;
        $obj = $this->getDataObject();
        $User_Location = self::getThisUserLocation();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if($obj->autoChangeLanguage){
            $location = self::getSessionLocation();
            if(empty($location) && !empty($User_Location['country_code'])){
                $_SESSION['language'] = strtolower($User_Location['country_code']);
                $file = "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
                if(file_exists($file)){
                    include_once $file;
                }else{
                    $_SESSION['language'] = $config->getLanguage();
                }
            }
        }
        $global['User_Location'] = $User_Location;
        self::setSessionLocation($global['User_Location']);
        return false;
    }


}
