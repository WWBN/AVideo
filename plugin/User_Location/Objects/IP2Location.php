<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class IP2Location extends ObjectYPT
{

    protected $ip_from, $ip_to, $country_code, $country_name, $region_name, $city_name;

    static function getSearchFieldsNames()
    {
        return array('country_code', 'country_name', 'region_name');
    }

    static function getTableName()
    {
        return 'ip2location_db3';
    }

    static function getLocation($ip)
    {
        if (!self::isTableInstalled() || !AVideoPlugin::isEnabledByName('User_Location')) {
            return false;
        }
        // samples
        // brazil 2.20.147.123
        // spain 2.22.54.123
        // japan 2.16.40.123
        // USA 	2.16.13.123
        //$ip = '2.16.40.123';
        if (!isset($_SESSION['IP2Location']) || !is_array($_SESSION['IP2Location'])) {
            $_SESSION['IP2Location'] = array();
        }
        if (empty($_SESSION['IP2Location'][$ip]['country_code'])) {
            $_SESSION['IP2Location'][$ip] = false;
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $sql = "SELECT * FROM ip2location_db3 WHERE INET_ATON(?) <= ip_to LIMIT 1";
                // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
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
            } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && ObjectYPT::isTableInstalled("ip2location_db1_ipv6")) {
                $ipno = self::Dot2LongIPv6($ip);
                $sql = "SELECT * FROM ip2location_db1_ipv6 WHERE ip_to >= $ipno order by ip_to limit 1 ";
                $res = sqlDAL::readSql($sql);
                $data = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if ($res) {
                    $row = $data;
                } else {
                    $row = false;
                }
                $row['ip'] = $ip;
                $_SESSION['IP2Location'][$ip] = $row;
            }
        } //var_dump($_SESSION['IP2Location'][$ip]);exit;
        if (!empty($_SESSION['IP2Location'][$ip]['country_name']) && $_SESSION['IP2Location'][$ip]['country_name'] == "United States of America") {
            $_SESSION['IP2Location'][$ip]['country_name'] = "United States";
        }
        //_error_log("IP2Location::getLocation({$ip}) " . get_browser_name() . " " . json_encode($_SESSION['IP2Location'][$ip]));
        return $_SESSION['IP2Location'][$ip];
    }

    // Function to convert IP address to IP number (IPv6)
    static function Dot2LongIPv6($IPaddr)
    {
        if (!function_exists("gmp_strval")) {
            _error_log("To query IPV6 you must install php-gmp (apt-get install php-gmp)");
            return 0;
        }
        $int = inet_pton($IPaddr);
        $bits = 15;
        $ipv6long = 0;
        while ($bits >= 0) {
            $bin = sprintf("%08b", (ord($int[$bits])));
            if ($ipv6long) {
                $ipv6long = $bin . $ipv6long;
            } else {
                $ipv6long = $bin;
            }
            $bits--;
        }
        $ipv6long = gmp_strval(gmp_init($ipv6long, 2), 10);
        return $ipv6long;
    }

    static function getCountries()
    {
        global $global;

        if (!static::isTableInstalled()) {
            return false;
        }
        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cachefile = "{$cacheDir}CountriesArray.cache";
        $content = array();
        if (!file_exists($cachefile)) {
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
        } else {
            $content = file_get_contents($cachefile);
        }

        return _json_decode($content);
    }

    static function getRegions($country_name)
    {
        global $global;

        if (!static::isTableInstalled()) {
            return false;
        }
        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $country_name_code = md5($country_name);

        $cachefile = "{$cacheDir}RegionsArray{$country_name_code}.cache";
        $content = array();
        if (!file_exists($cachefile)) {
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
            }
        } else {
            $content = file_get_contents($cachefile);
        }

        return _json_decode($content);
    }

    static function getCities($country_name, $region_name)
    {
        global $global;

        $cacheDir = $global['systemRootPath'] . 'videos/cache/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $country_name_code = md5($country_name);
        $region_name_code = md5($region_name);

        $cachefile = "{$cacheDir}RegionsArray{$country_name_code}{$region_name_code}.cache";
        $content = array();
        if (!file_exists($cachefile)) {
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
            }
        } else {
            $content = file_get_contents($cachefile);
        }

        return _json_decode($content);
    }

    static function getNorthAmericaCountries()
    {
        $countries = array(
            "Antigua and Barbuda",
            "Bahamas",
            "Barbados",
            "Belize",
            "Canada",
            "Costa Rica",
            "Cuba",
            "Dominica",
            "Dominican Republic",
            "El Salvador",
            "Grenada",
            "Guam",
            "Guatemala",
            "Haiti",
            "Honduras",
            "Jamaica",
            "Marshall Islands",
            "Mexico",
            "Nicaragua",
            "Northern Mariana Islands",
            "Palau",
            "Panama",
            "Puerto Rico",
            "Saint Barthelemy",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Pierre and Miquelon",
            "Saint Vincent and The Grenadines",
            "Samoa",
            "Trinidad and Tobago",
            "United States",
            "United States Minor Outlying Islands",
            "Virgin Islands, U.S.",
            "Greenland",
            "American Samoa",
            "Antarctica",
            "Saint Martin (French Part)",
        );
        return $countries;
    }

    static function getUSTerritories()
    {
        $countries = array(
            "American Samoa",
            "Guam",
            "Marshall Islands",
            "Micronesia, Federated States of",
            "Northern Mariana Islands",
            "Puerto Rico",
            "Samoa",
            "United States",
            "United States Minor Outlying Islands",
            "Virgin Islands, U.S.",
        );
        return $countries;
    }
    static function getEuropeanUnionCountries()
    {
        $countries = array(
            "Austria",
            "Belgium",
            "Bulgaria",
            "Croatia",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Estonia",
            "Finland",
            "France",
            "Germany",
            "Greece",
            "Hungary",
            "Ireland",
            "Italy",
            "Latvia",
            "Lithuania",
            "Luxembourg",
            "Malta",
            "Netherlands",
            "Poland",
            "Portugal",
            "Romania",
            "Slovakia",
            "Slovenia",
            "Spain",
            "Sweden",
        );
        return $countries;
    }
    static function getASEANCountries()
    {
        $countries = array(
            "Brunei Darussalam",
            "Cambodia",
            "Indonesia",
            "Laos",
            "Malaysia",
            "Myanmar",
            "Philippines",
            "Singapore",
            "Thailand",
            "Vietnam",
        );
        return $countries;
    }

    static function getSouthAmericanCountries() {
        $countries = array(
            "Argentina",
            "Bolivia",
            "Brazil",
            "Chile",
            "Colombia",
            "Ecuador",
            "Guyana",
            "Paraguay",
            "Peru",
            "Suriname",
            "Uruguay",
            "Venezuela",
        );
        return $countries;
    }

    static function getMiddleEasternCountries() {
        $countries = array(
            "Bahrain",
            "Cyprus",
            "Egypt",
            "Iran",
            "Iraq",
            "Israel",
            "Jordan",
            "Kuwait",
            "Lebanon",
            "Oman",
            "Palestine",
            "Qatar",
            "Saudi Arabia",
            "Syria",
            "Turkey",
            "United Arab Emirates",
            "Yemen",
        );
        return $countries;
    }
    static function getAfricanCountries() {
        $countries = array(
            "Algeria",
            "Angola",
            "Benin",
            "Botswana",
            "Burkina Faso",
            "Burundi",
            "Cabo Verde",
            "Cameroon",
            "Central African Republic",
            "Chad",
            "Comoros",
            "Congo, Democratic Republic of the",
            "Congo, Republic of the",
            "Djibouti",
            "Egypt",
            "Equatorial Guinea",
            "Eritrea",
            "Eswatini",
            "Ethiopia",
            "Gabon",
            "Gambia",
            "Ghana",
            "Guinea",
            "Guinea-Bissau",
            "Ivory Coast",
            "Kenya",
            "Lesotho",
            "Liberia",
            "Libya",
            "Madagascar",
            "Malawi",
            "Mali",
            "Mauritania",
            "Mauritius",
            "Morocco",
            "Mozambique",
            "Namibia",
            "Niger",
            "Nigeria",
            "Rwanda",
            "Sao Tome and Principe",
            "Senegal",
            "Seychelles",
            "Sierra Leone",
            "Somalia",
            "South Africa",
            "South Sudan",
            "Sudan",
            "Tanzania",
            "Togo",
            "Tunisia",
            "Uganda",
            "Zambia",
            "Zimbabwe",
        );
        return $countries;
    }
    
    static function getCaribbeanCountries() {
        $countries = array(
            "Antigua and Barbuda",
            "Bahamas",
            "Barbados",
            "Cuba",
            "Dominica",
            "Dominican Republic",
            "Grenada",
            "Haiti",
            "Jamaica",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent and the Grenadines",
            "Trinidad and Tobago",
        );
        return $countries;
    }
    static function getCentralAmericanCountries() {
        $countries = array(
            "Belize",
            "Costa Rica",
            "El Salvador",
            "Guatemala",
            "Honduras",
            "Nicaragua",
            "Panama",
        );
        return $countries;
    }
    
}
