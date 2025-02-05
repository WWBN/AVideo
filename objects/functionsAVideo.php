<?php

$AVideoMobileAPPLivestreamer_UA = "AVideoMobileAppLiveStreamer";
$AVideoMobileAPP_UA = "AVideoMobileApp";
$AVideoEncoder_UA = "AVideoEncoder";
$AVideoEncoderNetwork_UA = "AVideoEncoderNetwork";
$AVideoStreamer_UA = "AVideoStreamer";
$AVideoStorage_UA = "AVideoStorage";
$AVideoRestreamer_UA = "AVideoRestreamer";

function isAVideoMobileApp($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoMobileAPP_UA;
    if (preg_match("/{$AVideoMobileAPP_UA}(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isAVideoEncoder($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoEncoder_UA;
    if (preg_match("/{$AVideoEncoder_UA}(.*)/", $user_agent, $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isCDN()
{
    if (empty($_SERVER['HTTP_CDN_HOST'])) {
        return false;
    }
    return isFromCDN($_SERVER['HTTP_CDN_HOST']);
}

function isFromCDN($url)
{
    if (preg_match('/cdn.ypt.me/i', $url)) {
        return true;
    }
    return false;
}

function isAVideo($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoEncoder_UA;
    if (preg_match("/AVideo(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isAVideoEncoderOnSameDomain()
{
    $url = isAVideoEncoder();
    if (empty($url)) {
        return false;
    }
    $url = "http://{$url}";
    return isSameDomainAsMyAVideo($url);
}

function isSameDomainAsMyAVideo($url)
{
    global $global;
    if (empty($url)) {
        return false;
    }
    return isSameDomain($url, $global['webSiteRootURL']) || isSameDomain($url, getCDN());
}

function isAVideoStreamer($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoStreamer_UA, $global;
    $md5 = md5($global['salt']);
    if (preg_match("/{$AVideoStreamer_UA}_{$md5}/", $_SERVER["HTTP_USER_AGENT"])) {
        return true;
    }
    return false;
}

function isAVideoUserAgent($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoMobileAPP_UA, $AVideoEncoder_UA, $AVideoEncoderNetwork_UA, $AVideoStreamer_UA, $AVideoStorage_UA, $AVideoRestreamer_UA, $global;

    // Lavf = ffmpeg
    //$agents = [$AVideoMobileAPP_UA, $AVideoEncoder_UA, $AVideoEncoderNetwork_UA, $AVideoStreamer_UA, $AVideoStorage_UA, 'Lavf'];
    $agents = [$AVideoMobileAPP_UA, $AVideoEncoder_UA, $AVideoEncoderNetwork_UA, $AVideoStreamer_UA, $AVideoStorage_UA, $AVideoRestreamer_UA];

    foreach ($agents as $value) {
        if (preg_match("/{$value}/", $user_agent)) {
            return true;
        }
    }

    return false;
}

function isAVideoStorage($user_agent = "")
{
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoStorage_UA;
    if (preg_match("/{$AVideoStorage_UA}(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}


function getSelfUserAgent()
{
    global $global, $AVideoStreamer_UA;
    if(empty($AVideoStreamer_UA)){
        $AVideoStreamer_UA = 'AVideoStreamer';
    }
    $agent = $AVideoStreamer_UA . "_";
    $agent .= md5($global['salt'].date('i'));
    return $agent;
}

function isSelfUserAgent()
{
    global $global, $AVideoStreamer_UA;

    if (preg_match('/GStreamer souphttpsrc/', $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }

    // Generate the current and 1-minute previous user agent strings
    $currentAgent = $AVideoStreamer_UA . "_" . md5($global['salt'] . date('i'));
    $previousAgent = $AVideoStreamer_UA . "_" . md5($global['salt'] . date('i', strtotime('-1 minute')));

    // Check if the provided user agent matches either the current or previous
    if ($_SERVER['HTTP_USER_AGENT'] === $currentAgent || $_SERVER['HTTP_USER_AGENT'] === $previousAgent) {
        return true;
    }

    return false;
}


function requestComesFromSameDomainAsMyAVideo()
{
    global $global;
    $url = getRefferOrOrigin();
    //var_dump($_SERVER);exit;
    //_error_log("requestComesFromSameDomainAsMyAVideo: ({$url}) == ({$global['webSiteRootURL']})");
    return isSameDomain($url, $global['webSiteRootURL']) || isSameDomain($url, getCDN()) || isFromCDN($url);
}

define('E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
    E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
if (!isCommandLineInterface() && !isAVideoEncoder()) {
    register_shutdown_function('avideoShutdown');
}

function avideoShutdown()
{
    global $global;
    $error = error_get_last();
    if ($error && ($error['type'] & E_FATAL)) {
        var_dump($error);
        _error_log($error, AVideoLog::$ERROR);
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        if (!User::isAdmin()) {
            if (!preg_match('/json\.php$/i', $_SERVER['PHP_SELF'])) {
                echo '<!-- This page means an error 500 Internal Server Error, check your log file -->' . PHP_EOL;
                include $global['systemRootPath'] . 'view/maintanance.html';
            } else {
                $o = new stdClass();
                $o->error = true;
                $o->msg = ('Under Maintenance');
                echo json_encode($o);
            }
        } else {
            echo '<pre>';
            var_dump($error);
            var_dump(debug_backtrace());
            echo '</pre>';
        }
        exit;
    }else{
        if(class_exists('Cache')){
            Cache::saveCache();
        }
    }
}
