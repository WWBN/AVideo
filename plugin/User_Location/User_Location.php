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

        if (!ObjectYPT::isTableInstalled("ip2location_db1_ipv6")) {
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
        $o = new stdClass();
        $o->type = array('browser' => __("Detect language from Browser"), 'ip' => __("Detect language from IP"));
        $o->value = 'browser';
        $obj->useLanguageFrom = $o;
        return $obj;
    }

    static function getSessionLocation() {
        $ip = getRealIpAddr();
        if (!empty($_SESSION['User_Location'][$ip]['country_name'])) {
            if ($_SESSION['User_Location'][$ip]['country_name'] == "United States of America") {
                $_SESSION['User_Location'][$ip]['country_name'] = "United States";
            }
            return $_SESSION['User_Location'][$ip];
        }
        return false;
    }

    static function setSessionLocation($value) {
        $ip = getRealIpAddr();
        $_SESSION['User_Location'][$ip] = $value;
        //_error_log("User_Location: $ip ". json_encode($_SESSION['User_Location'][$ip]));
    }

    static function getThisUserLocation() {
        $location = self::getSessionLocation();
        if (!empty($location['country_code'])) {
            return $location;
        }
        return self::getLocationFromIP(getRealIpAddr());
    }

    static function getLocationFromIP($ip) {
        return IP2Location::getLocation($ip);
    }

    static function getLanguageFromBrowser() {
        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return false;
        }
        $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        return str_replace('-', '_', $parts[0]);
    }

    static function setLanguage($lang) {
        global $global;
        $lang = flag2Lang($lang);
        if (!empty($_SESSION['language'])) {
            $file = "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
            if (file_exists($file)) {
                include_once $file;
                return true;
            }else{
                _error_log('setLanguage: File does not exists 1 '.$file);
            }
        }

        $file = "{$global['systemRootPath']}locale/{$lang}.php";
        _session_start();
        if (file_exists($file)) {
            $_SESSION['language'] = $lang;
            include_once $file;
            return true;
        } else {
            _error_log('setLanguage: File does not exists 2 '.$file);
            $lang = strtolower($lang);
            $file = "{$global['systemRootPath']}locale/{$lang}.php";
            if (file_exists($file)) {
                $_SESSION['language'] = $lang;
                include_once $file;
                return true;
            }else{
                _error_log('setLanguage: File does not exists 3 '.$file);
            }
        }
        return false;
    }

    static function setLanguageFromBrowser() {
        return self::setLanguage(self::getLanguageFromBrowser());
    }

    static function setLanguageFromIP() {
        $User_Location = self::getThisUserLocation();
        return self::setLanguage($User_Location['country_code']);
    }

    public function getStart() {
        global $global, $config;
        $obj = $this->getDataObject();
        $User_Location = self::getThisUserLocation();
        if ($obj->autoChangeLanguage) {
            if ($obj->useLanguageFrom->value == 'browser') {
                $changed = self::setLanguageFromBrowser();
                if (!$changed) {
                    $changed = self::setLanguageFromIP();
                }
            } else {
                $changed = self::setLanguageFromIP();
                if (!$changed) {
                    $changed = self::setLanguageFromBrowser();
                }
            }
            if (!$changed) {
                _error_log('getStart language: got from config '.$file);
                $_SESSION['language'] = $config->getLanguage();
            }
        }
        $global['User_Location'] = $User_Location;
        self::setSessionLocation($global['User_Location']);
        return false;
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/User_Location/pluginMenu.html';
        return file_get_contents($filename);
    }

}
