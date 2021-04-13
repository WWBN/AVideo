<?php

//$global['stopBotsList'] = array('bot','spider','rouwler','Nuclei','MegaIndex','NetSystemsResearch','CensysInspect','slurp','crawler','curl','fetch','loader');
//$global['stopBotsWhiteList'] = array('google','bing','yahoo','yandex');
if (!empty($global['stopBotsList']) && is_array($global['stopBotsList'])) {
    foreach ($global['stopBotsList'] as $value) {
        if (stripos($_SERVER['HTTP_USER_AGENT'], $value) !== false) {
            if (!empty($global['stopBotsWhiteList']) && is_array($global['stopBotsWhiteList'])) {
                // check if it is whitelisted
                foreach ($global['stopBotsWhiteList'] as $key => $value2) {
                    if (stripos($_SERVER['HTTP_USER_AGENT'], $value2) !== false) {
                        break 2;
                    }
                }
            }
            die('Bot Found ' . $_SERVER['HTTP_USER_AGENT']);
        }
    }
}

$global['avideoStartMicrotime'] = microtime(true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

$global['webSiteRootURL'] .= (substr($global['webSiteRootURL'], -1) == '/' ? '' : '/');
$global['systemRootPath'] .= (substr($global['systemRootPath'], -1) == '/' ? '' : '/');
$global['session_name'] = md5($global['systemRootPath']);

session_name($global['session_name']);

if (empty($global['logfile'])) {
    $global['logfile'] = $global['systemRootPath'] . 'videos/avideo.log';
}

ini_set('error_log', $global['logfile']);
global $global, $config, $advancedCustom, $advancedCustomUser;

$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);

if ($global['mysqli'] === false || !empty($global['mysqli']->connect_errno)) {
    _error_log("MySQL connect_errno[{$global['mysqli']->connect_errno}] {$global['mysqli']->connect_error}", AVideoLog::$ERROR);
    include $global['systemRootPath'] . 'view/include/offlinePage.php';
    exit;
}

// if you set it on configuration file it will help you to encode
if (!empty($global['mysqli_charset'])) {
    $global['mysqli']->set_charset($global['mysqli_charset']);
}

require_once $global['systemRootPath'] . 'objects/mysql_dal.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'objects/security.php';
$config = new Configuration();

// for update config from old versions 2020-05-11
if (empty($global['webSiteRootPath']) || $global['configurationVersion'] < 3.1) {
    Configuration::rewriteConfigFile();
}

$global['dont_show_us_flag'] = false;
// this is for old versions
session_write_close();

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', $config->getSession_timeout());

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params($config->getSession_timeout());

//Fix “set SameSite cookie to none” warning
if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
    setcookie('key', 'value', ['samesite' => 'None', 'secure' => true]);
} else {
    header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
    setcookie('key', 'value', time() + $config->getSession_timeout(), '/; SameSite=None; Secure');
}

session_start();

// DDOS protection can be disabled in video/configuration.php
if (!empty($global['enableDDOSprotection'])) {
    ddosProtection();
}

// set the referrer for aVideo
$url1['host'] = '';
$global['HTTP_REFERER'] = '';
if (!empty($_SERVER['HTTP_REFERER'])) {
    if ((
            strpos($_SERVER['HTTP_REFERER'], '/video/') !== false || strpos($_SERVER['HTTP_REFERER'], '/v/') !== false
            ) &&
            !empty($_SESSION['LAST_HTTP_REFERER'])) {
        if (strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/css/') !== false ||
                strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/js/') !== false ||
                strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/img/') !== false) {
            $_SESSION['LAST_HTTP_REFERER'] = $global['webSiteRootURL'];
        }
        $global['HTTP_REFERER'] = $_SESSION['LAST_HTTP_REFERER'];
        $url1 = parse_url($global['HTTP_REFERER']);
    } else {
        $global['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        $url1 = parse_url($global['HTTP_REFERER']);
    }
}
//var_dump($global['HTTP_REFERER']);exit;
if (!isset($_POST['redirectUri'])) {
    $_POST['redirectUri'] = '';
}

if (!empty($_POST['redirectUri']) && strpos($_POST['redirectUri'], 'logoff.php') !== false) {
    $_POST['redirectUri'] = '';
}
if (!empty($_GET['redirectUri']) && strpos($_GET['redirectUri'], 'logoff.php') !== false) {
    $_GET['redirectUri'] = '';
}

$url2 = parse_url($global['webSiteRootURL']);
if (!empty($url1['host']) && !empty($url2['host']) && $url1['host'] !== $url2['host']) {
    $global['HTTP_REFERER'] = $global['webSiteRootURL'];
}
$_SESSION['LAST_HTTP_REFERER'] = $global['HTTP_REFERER'];
//var_dump($global['HTTP_REFERER'], $url1);exit;

$output = ob_get_clean();
ob_start('ob_gzhandler');
echo $output;
$_SESSION['lastUpdate'] = time();
$_SESSION['savedQuerys'] = 0;
require_once $global['systemRootPath'] . 'objects/Object.php';
require_once $global['systemRootPath'] . 'locale/function.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
fixSystemPath();
ObjectYPT::checkSessionCacheBasedOnLastDeleteALLCacheTime();
getDeviceID();
allowOrigin();

$baseName = basename($_SERVER['SCRIPT_FILENAME']);
if ($baseName !== 'xsendfile.php' && class_exists('Plugin')) {
    AVideoPlugin::getStart();
} elseif ($baseName !== 'xsendfile.php') {
    _error_log("Class Plugin Not found: {$_SERVER['REQUEST_URI']}");
}
if (empty($global['bodyClass'])) {
    $global['bodyClass'] = '';
}
$global['allowedExtension'] = array('gif', 'jpg', 'mp4', 'webm', 'mp3', 'm4a', 'ogg', 'zip', 'm3u8');

if (empty($global['avideo_resolutions'])) {
    $global['avideo_resolutions'] = array(240, 360, 480, 540, 720, 1080, 1440, 2160);
}

sort($global['avideo_resolutions']);

$advancedCustom = AVideoPlugin::getObjectData('CustomizeAdvanced');

if (empty($global['disableTimeFix'])) {
    /*
      $now = new DateTime();
      $mins = $now->getOffset() / 60;
      $sgn = ($mins < 0 ? -1 : 1);
      $mins = abs($mins);
      $hrs = floor($mins / 60);
      $mins -= $hrs * 60;
      $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
      $global['mysqli']->query("SET time_zone='$offset';");
     */
    ObjectYPT::setTimeZone();
}

$avideoLayout = AVideoPlugin::getObjectData('Layout');
$avideoCustomizeUser = $advancedCustomUser = AVideoPlugin::getObjectData('CustomizeUser');
$avideoCustomize = $customizePlugin = AVideoPlugin::getObjectData('Customize');
$avideoPermissions = $permissionsPlugin = AVideoPlugin::getObjectData('Permissions');
$avideoPlayerSkins = AVideoPlugin::getObjectData('PlayerSkins');

if (!empty($_GET['type'])) {
    $metaDescription = " {$_GET['type']}";
} elseif (!empty($_GET['showOnly'])) {
    $metaDescription = " {$_GET['showOnly']}";
}
