<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';


class IP2Location extends ObjectYPT {

    protected $ip_from, $ip_to, $country_code, $country_name, $region_name, $city_name;

    static function getSearchFieldsNames() {
        return array('country_code', 'country_name', 'region_name');
    }

    static function getTableName() {
        return 'ip2location_db3';
    }
    
    
    static function getLocation($ip) {
        // samples
        // brazil 2.20.147.123
        // spain 2.22.54.123
        // japan 2.16.40.123
        // USA 	2.16.13.123
        //$ip = '2.16.40.123';
        if(empty($_SESSION['IP2Location'][$ip])){
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE INET_ATON(?) <= ip_to LIMIT 1";
            // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
            $res = sqlDAL::readSql($sql,"s",array($ip)); 
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res) {
                $row = $data;
            } else {
                $row = false;
            }
            $row['ip'] = $ip;
            $_SESSION['IP2Location'][$ip] = $row;
        }//var_dump($_SESSION['IP2Location'][$ip]);exit;
        return $_SESSION['IP2Location'][$ip];
    }

}
