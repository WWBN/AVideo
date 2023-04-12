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

    static function changeLang($force = false) {
        global $global;
        _session_start();
        if (!empty($force) || empty($_SESSION['language'])) {
            $obj = AVideoPlugin::getDataObject('User_Location');
            if ($obj->autoChangeLanguage) {
                $lang = self::getLanguage();
                if (!empty($lang)) {
                    if (!empty($_REQUEST['debug'])) {
                        _error_log("changeLang line=" . __LINE__ . " " . json_encode(debug_backtrace()));
                    }
                    setLanguage($lang);
                } else {
                    if (!empty($_REQUEST['debug'])) {
                        _error_log("changeLang line=" . __LINE__ . " " . json_encode(debug_backtrace()));
                    }
                }
            } else {
                if (!empty($_REQUEST['debug'])) {
                    _error_log("changeLang line=" . __LINE__ . " " . json_encode(debug_backtrace()));
                }
            }
        } else {
            if (!empty($_REQUEST['debug'])) {
                _error_log("changeLang [{$_SESSION['language']}] line=" . __LINE__ . " " . json_encode(debug_backtrace()));
            }
        }
    }

    static function getLanguage() {
        global $global;
        $global['User_Location_lang'] = false;
        if (empty($global['User_Location_lang'])) {
            $obj = AVideoPlugin::getDataObject('User_Location');
            if ($obj->useLanguageFrom->value == 'browser') {
                $global['User_Location_lang'] = getLanguageFromBrowser();
            } else {
                $User_Location = self::getThisUserLocation();
                $global['User_Location_lang'] = $User_Location['country_code'];
            }
        }
        return $global['User_Location_lang'];
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/User_Location/pluginMenu.html';
        return file_get_contents($filename);
    }

}
