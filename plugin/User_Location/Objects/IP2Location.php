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
        if (empty($_SESSION['IP2Location'][$ip])) {
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE INET_ATON(?) <= ip_to LIMIT 1";
            // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
            $res = sqlDAL::readSql($sql, "s", array($ip));
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

    static function getCountries() {
        global $global;
        
        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cachefile = "{$cacheDir}CountriesArray.cache";  
        $content = array();
        if(!file_exists($cachefile)){
            $sql = "SELECT distinct(country_name) as cn FROM  " . static::getTableName() . " WHERE country_name != '-' ORDER BY country_name ";
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    $rows[] = $row['cn'];
                }
                $content = json_encode($rows);
                file_put_contents($cachefile, $content);
            } else {
                return array();
            }
        }else{
            $content = file_get_contents($cachefile);
        }
          
        return json_decode($content);
    }
    
    static function getRegions($country_name) {
        global $global;
        
        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $country_name_code = md5($country_name);
        
        $cachefile = "{$cacheDir}RegionsArray{$country_name_code}.cache";  
        $content = array();
        if(!file_exists($cachefile)){
            $sql = "SELECT distinct(region_name) as n FROM  " . static::getTableName() . " WHERE country_name = '{$country_name}' ORDER BY region_name ";

            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    $rows[] = $row['n'];
                }
                $content = json_encode($rows);
                file_put_contents($cachefile, $content);
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }else{
            $content = file_get_contents($cachefile);
        }
          
        return json_decode($content);
    }
    
    static function getCities($country_name, $region_name) {
        global $global;
        
        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $country_name_code = md5($country_name);
        $region_name_code = md5($region_name);
        
        $cachefile = "{$cacheDir}RegionsArray{$country_name_code}{$region_name_code}.cache";  
        $content = array();
        if(!file_exists($cachefile)){
            $sql = "SELECT distinct(city_name) as n FROM  " . static::getTableName() . " WHERE country_name = '{$country_name}' AND region_name = '{$region_name}' ORDER BY city_name ";

            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $rows = array();
            if ($res != false) {
                foreach ($fullData as $row) {
                    $rows[] = $row['n'];
                }
                $content = json_encode($rows);
                file_put_contents($cachefile, $content);
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }else{
            $content = file_get_contents($cachefile);
        }
          
        return json_decode($content);
    }

}
