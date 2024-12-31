<?php

function isSafari()
{
    global $global, $_isSafari;
    if (!isset($_isSafari)) {
        $_isSafari = false;
        $os = getOS();
        if (preg_match('/Mac|iPhone|iPod|iPad/i', $os)) {
            require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
            $detect = new Mobile_Detect();
            $_isSafari = $detect->is('Safari');
        }
    }
    return $_isSafari;
}

function fixQuotesIfSafari($str)
{
    if (!isSafari()) {
        return $str;
    }
    return fixQuotes($str);
}


function getLanguageFromBrowser()
{
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return false;
    }
    $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return str_replace('-', '_', $parts[0]);
}

function deviceIdToObject($deviceID)
{
    $parts = explode('-', $deviceID);
    $obj = new stdClass();
    $obj->browser = '';
    $obj->os = '';
    $obj->ip = '';
    $obj->user_agent = '';
    $obj->users_id = 0;

    foreach ($parts as $key => $value) {
        $parts[$key] = str_replace('_', ' ', $value);
    }

    switch ($parts[0]) {
        case 'ypt':
            $obj->browser = $parts[1];
            $obj->os = $parts[2];
            $obj->ip = $parts[3];
            $obj->user_agent = $parts[4];
            $obj->users_id = $parts[5];
            break;
        case 'unknowDevice':
            $obj->browser = $parts[0];
            $obj->os = 'unknow OS';
            $obj->ip = $parts[1];
            $obj->user_agent = 'unknow UA';
            $obj->users_id = $parts[2];
            break;
        default:
            break;
    }
    return $obj;
}

/**
 * It's separated by time, version, clock_seq_hi, clock_seq_lo, node, as indicated in the followoing rfc.
 *
 * From the IETF RFC4122:
 * 8-4-4-4-12
 * @return string
 */
function getDeviceID($useRandomString = true)
{
    $ip = md5(getRealIpAddr());
    $pattern = "/[^0-9a-z_.-]/i";
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        if(isCommandLineInterface()){
            $device = "commandLine";
        }else{
            $device = "unknowDevice-{$ip}";
        }
        $device .= '-' . intval(User::getId());
        return preg_replace($pattern, '-', $device);
    }

    if (empty($useRandomString)) {
        $device = 'ypt-' . get_browser_name() . '-' . getOS() . '-' . $ip . '-' . md5($_SERVER['HTTP_USER_AGENT']);
        $device = str_replace(
            ['[', ']', ' '],
            ['', '', '_'],
            $device
        );
        $device .= '-' . intval(User::getId());
        return preg_replace($pattern, '-', $device);
    }

    $cookieName = "yptDeviceID";
    if (empty($_COOKIE[$cookieName])) {
        if (empty($_GET[$cookieName])) {
            $id = uniqidV4();
            $_GET[$cookieName] = $id;
        }
        if (empty($_SESSION[$cookieName])) {
            _session_start();
            $_SESSION[$cookieName] = $_GET[$cookieName];
        } else {
            $_GET[$cookieName] = $_SESSION[$cookieName];
        }
        if (!_setcookie($cookieName, $_GET[$cookieName], strtotime("+ 1 year"))) {
            return "getDeviceIDError";
        }
        $_COOKIE[$cookieName] = $_GET[$cookieName];
    }
    return preg_replace($pattern, '-', $_COOKIE[$cookieName]);
}

function fakeBrowser($url)
{
    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);
    return $output;
}

function getUserAgentInfo($user_agent = ''){
    return get_browser_name($user_agent) . '/' . getOS($user_agent). ' ('.(isMobile($user_agent) ? "Mobile" : "PC").')';
}

function get_browser_name($user_agent = "")
{
    if(isCommandLineInterface()){
        return 'Commandline';
    }
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return 'Unknow';
    }
    // Make case insensitive.
    $t = mb_strtolower($user_agent);

    // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
    // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
    //     http://php.net/manual/en/function.strpos.php
    $t = " " . $t;

    // Humans / Regular Users
    if (isAVideoStreamer($t)) {
        return 'AVideo Mobile App';
    } elseif ($url = isAVideoEncoder($t)) {
        return 'AVideo Encoder ' . $url;
    } elseif ($url = isAVideoStreamer($t)) {
        return 'AVideo Streamer ' . $url;
    } elseif (strpos($t, 'crkey')) {
        return 'Chromecast';
    } elseif (strpos($t, 'opera') || strpos($t, 'opr/')) {
        return 'Opera';
    } elseif (strpos($t, 'edge')) {
        return 'Edge';
    } elseif (strpos($t, 'chrome')) {
        return 'Chrome';
    } elseif (strpos($t, 'safari')) {
        return 'Safari';
    } elseif (strpos($t, 'firefox')) {
        return 'Firefox';
    } elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) {
        return 'Internet Explorer';
    } elseif (strpos($t, 'applecoremedia')) {
        return 'Native Apple Player';
    }

    // Search Engines
    elseif (strpos($t, 'google')) {
        return '[Bot] Googlebot';
    } elseif (strpos($t, 'bing')) {
        return '[Bot] Bingbot';
    } elseif (strpos($t, 'slurp')) {
        return '[Bot] Yahoo! Slurp';
    } elseif (strpos($t, 'duckduckgo')) {
        return '[Bot] DuckDuckBot';
    } elseif (strpos($t, 'baidu')) {
        return '[Bot] Baidu';
    } elseif (strpos($t, 'yandex')) {
        return '[Bot] Yandex';
    } elseif (strpos($t, 'sogou')) {
        return '[Bot] Sogou';
    } elseif (strpos($t, 'exabot')) {
        return '[Bot] Exabot';
    } elseif (strpos($t, 'msn')) {
        return '[Bot] MSN';
    }

    // Common Tools and Bots
    elseif (strpos($t, 'mj12bot')) {
        return '[Bot] Majestic';
    } elseif (strpos($t, 'ahrefs')) {
        return '[Bot] Ahrefs';
    } elseif (strpos($t, 'semrush')) {
        return '[Bot] SEMRush';
    } elseif (strpos($t, 'rogerbot') || strpos($t, 'dotbot')) {
        return '[Bot] Moz or OpenSiteExplorer';
    } elseif (strpos($t, 'frog') || strpos($t, 'screaming')) {
        return '[Bot] Screaming Frog';
    }

    // Miscellaneous
    elseif (strpos($t, 'facebook')) {
        return '[Bot] Facebook';
    } elseif (strpos($t, 'pinterest')) {
        return '[Bot] Pinterest';
    }

    // Check for strings commonly used in bot user agents
    elseif (
        strpos($t, 'crawler') || strpos($t, 'api') ||
        strpos($t, 'spider') || strpos($t, 'http') ||
        strpos($t, 'bot') || strpos($t, 'archive') ||
        strpos($t, 'info') || strpos($t, 'data')
    ) {
        return '[Bot] Other '.$user_agent;
    }
    //_error_log("Unknow user agent ($t) IP=" . getRealIpAddr() . " URI=" . getRequestURI());
    return 'Other (Unknown) '.$user_agent;
}

/**
 * Due some error on old chrome browsers (version < 70) on decrypt HLS keys with the videojs versions greater then 7.9.7
 * we need to detect the chrome browser and load an older version
 *
 */
function isOldChromeVersion()
{
    global $global;
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    if (!empty($global['forceOldChrome'])) {
        return true;
    }
    if (preg_match('/Chrome\/([0-9.]+)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
        return version_compare($matches[1], '80', '<=');
    }
    return false;
}

function getOS($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }

    $os_platform = "Unknown OS Platform";

    if (!empty($user_agent)) {
        $os_array = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
            // Additional TV devices and mobile devices
            '/roku/i' => 'Roku',
            '/android tv/i' => 'Android TV',
            '/apple tv/i' => 'Apple TV',
            '/fire tv|firestick|fire stick/i' => 'Amazon Fire TV',
            '/windows phone/i' => 'Windows Phone',
            '/symbian/i' => 'Symbian',
            '/tizen/i' => 'Tizen',
            '/webos tv/i' => 'WebOS TV'
        ];
        

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
                break;
            }
        }
    }

    return $os_platform;
}