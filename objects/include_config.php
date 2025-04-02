<?php
global $global, $doNotConnectDatabaseIncludeConfig;
if (!isset($global['skippPlugins'])) {
    $global['skippPlugins'] = array();
}


if (!empty($global['stopHeadRequests'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
        // Optionally, you can send a specific status code or header before exiting
        http_response_code(200); // Set response code if needed
        error_log('stopIfIsHeadRequest '.$_SERVER['HTTP_USER_AGENT']);
        die();
    }
}

/**
 * Global variables.
 *
 * @var array $global An array of global variables.
 * @property \mysqli $global['mysqli'] A MySQLi connection object.
 * @property mixed $global[] Dynamically loaded variables.
 */
if (!empty($doNotIncludeConfig)) {
    //error_log('AVideo includeconfig ignored');
    // in this line the mysql connection will not be opened and session will not be opened
    require_once $global['systemRootPath'] . 'objects/plugin.php';
    require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
    require_once $global['systemRootPath'] . 'objects/functions.php';
    require_once $global['systemRootPath'] . 'objects/images.php';

    includeSecurityChecks();
    return false;
}

/*
if($_SERVER["HTTP_HOST"] === 'localhost' || $_SERVER["HTTP_HOST"] === '127.0.0.1'){
    $global["webSiteRootURL"] = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$global["webSiteRootPath"];
}
 *
 */
//if(!empty($global['debugMemmory'])){return false;}
//if(!empty($global['debugMemmory'])){var_dump(__LINE__);exit;}
//var_dump($_SERVER, $global);exit;
//$global['stopBotsList'] = array('headless', 'bot','spider','rouwler','Nuclei','MegaIndex','NetSystemsResearch','CensysInspect','slurp','crawler','curl','fetch','loader');
//$global['stopBotsWhiteList'] = array('facebook','google','bing','yahoo','yandex','twitter');
if (!empty($global['stopBotsList']) && is_array($global['stopBotsList'])) {
    foreach ($global['stopBotsList'] as $value) {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            break;
        }
        if (stripos($_SERVER['HTTP_USER_AGENT'], $value) !== false) {
            if (!empty($global['stopBotsWhiteList']) && is_array($global['stopBotsWhiteList'])) {
                // check if it is whitelisted
                foreach ($global['stopBotsWhiteList'] as $key => $value2) {
                    if (stripos($_SERVER['HTTP_USER_AGENT'], $value2) !== false) {
                        break 2;
                    }
                }
            }
            die("Bot Found [{$value}] " . $_SERVER['HTTP_USER_AGENT']);
        }
    }
}

$global['avideoStartMicrotime'] = microtime(true);

function includeConfigLog($line, $desc = '')
{
    if (empty($_REQUEST['debug'])) {
        return false;
    }
    global $global, $_includeConfigLogID, $_includeConfigLogLastCheck;
    if (!isset($_includeConfigLogID)) {
        $_includeConfigLogID = date('H:i:s');
    }
    $_includeConfigLogLastCheck = microtime(true);
    $seconds = number_format($_includeConfigLogLastCheck - $global['avideoStartMicrotime'], 4);
    $msg = "includeConfigLog[$_includeConfigLogID]: {$seconds} seconds line={$line} {$desc}";
    //echo $msg."<br>".PHP_EOL;
    error_log($msg);
}
includeConfigLog(__LINE__);
try {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_DEPRECATED);
    $urandom = '/dev/urandom';
    if (file_exists($urandom)) { //https://stackoverflow.com/a/138748/2478180
        ini_set("session.entropy_file", $urandom);
        ini_set("session.entropy_length", "512");
    }
    includeConfigLog(__LINE__, 'autoload start');
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
    includeConfigLog(__LINE__, 'autoload done');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} catch (Exception $exc) {
    echo $exc->getTraceAsString();
}
includeConfigLog(__LINE__);

$global['webSiteRootURL'] .= (substr($global['webSiteRootURL'], -1) == '/' ? '' : '/');
$global['systemRootPath'] .= (substr($global['systemRootPath'], -1) == '/' ? '' : '/');
$global['session_name'] = md5($global['systemRootPath']);

session_name($global['session_name']);


global $global, $config, $advancedCustom, $advancedCustomUser;

$global['docker_vars'] = '/var/www/docker_vars.json';

if (file_exists($global['docker_vars'])) {
    // Log to stdout only if docker_vars.json exists and stdout is available
    $global['logfile'] = '/dev/stdout';
} else if (empty($global['logfile'])) {
    // Fall back to file-based logging if stdout is not available
    $global['logfile'] = $global['systemRootPath'] . 'videos/avideo.log';
}

ini_set('error_log', $global['logfile']);

if (empty($global['mysqli_charset'])) {
    //$global['mysqli_charset'] = 'latin1';
}

includeConfigLog(__LINE__);
require_once $global['systemRootPath'] . 'objects/functions.php';
includeConfigLog(__LINE__);
set_error_reporting();
if (empty($doNotConnectDatabaseIncludeConfig)) {
    _mysql_connect();
} else {
    $mysql_connect_was_closed = 1;
}
$global['webSiteRootURL'] = fixTestURL($global['webSiteRootURL']);
require_once $global['systemRootPath'] . 'objects/mysql_dal.php';
includeConfigLog(__LINE__);
require_once $global['systemRootPath'] . 'objects/configuration.php';
includeConfigLog(__LINE__);
require_once $global['systemRootPath'] . 'objects/security.php';
includeConfigLog(__LINE__);
require_once $global['systemRootPath'] . 'objects/images.php';
includeConfigLog(__LINE__);
// for update config from old versions 2020-05-11
if (empty($global['saltV2'])) {
    $additions = [
        '/\$global\[\'salt\'\].*/' => "\$global['saltV2'] = '"._uniqid()."';", // Add this line below the line that matches the pattern
    ];

    $replacements = [];

    Configuration::updateConfigFile($additions, $replacements, 4.0);
}

includeConfigLog(__LINE__);
$global['dont_show_us_flag'] = false;
// this is for old versions

if (empty($doNotStartSessionIncludeConfig)) {
    _session_start();
    storeAndGetExternalReferrer();
    // DDOS protection can be disabled in video/configuration.php
    if (!empty($global['enableDDOSprotection'])) {
        ddosProtection();
    }
}


includeSecurityChecks();
includeConfigLog(__LINE__);
// set the referrer for aVideo
$url1['host'] = '';
$global['HTTP_REFERER'] = '';
if (!empty($_SERVER['HTTP_REFERER'])) {
    if ((strpos($_SERVER['HTTP_REFERER'], '/video/') !== false || strpos($_SERVER['HTTP_REFERER'], '/v/') !== false
        ) &&
        !empty($_SESSION['LAST_HTTP_REFERER'])
    ) {
        if (
            strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/css/') !== false ||
            strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/js/') !== false ||
            strpos($_SESSION['LAST_HTTP_REFERER'], 'cache/img/') !== false
        ) {
            $_SESSION['LAST_HTTP_REFERER'] = $global['webSiteRootURL'];
        }
        $global['HTTP_REFERER'] = $_SESSION['LAST_HTTP_REFERER'];
        $url1 = parse_url($global['HTTP_REFERER']);
    } else {
        $global['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        $url1 = parse_url($global['HTTP_REFERER']);
    }
}
includeConfigLog(__LINE__);
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

includeConfigLog(__LINE__);
$url2 = parse_url($global['webSiteRootURL']);
if (!empty($url1['host']) && !empty($url2['host']) && $url1['host'] !== $url2['host']) {
    $global['HTTP_REFERER'] = $global['webSiteRootURL'];
}
$_SESSION['LAST_HTTP_REFERER'] = @$global['HTTP_REFERER'];
includeConfigLog(__LINE__);
//var_dump($global['HTTP_REFERER'], $url1);exit;

_ob_end_clean();
//$output = _ob_get_clean();
_ob_start(true);
//echo $output;

$_SESSION['lastUpdate'] = time();
$_SESSION['savedQuerys'] = 0;
require_once $global['systemRootPath'] . 'objects/Object.php';
require_once $global['systemRootPath'] . 'locale/function.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
require_once $global['systemRootPath'] . 'objects/Page.php';
setSiteLang();
includeConfigLog(__LINE__);

adminSecurityCheck();
fixSystemPath();
ObjectYPT::checkSessionCacheBasedOnLastDeleteALLCacheTime();
getDeviceID();
allowOrigin();

includeConfigLog(__LINE__);
$baseName = basename($_SERVER['SCRIPT_FILENAME']);
if (empty($doNotConnectDatabaseIncludeConfig) && $baseName !== 'xsendfile.php' && class_exists('Plugin')) {
    includeConfigLog(__LINE__, 'AVideoPlugin::getStart start');
    AVideoPlugin::getStart();
    includeConfigLog(__LINE__, 'AVideoPlugin::getStart done');
} elseif (empty($doNotConnectDatabaseIncludeConfig) && $baseName !== 'xsendfile.php') {
    _error_log("Class Plugin Not found: {$_SERVER['REQUEST_URI']}");
}
if (empty($global['bodyClass'])) {
    $global['bodyClass'] = '';
}
$global['allowedExtension'] = ['gif', 'jpg', 'mp4', 'webm', 'mp3', 'm4a', 'ogg', 'zip', 'm3u8'];

if (empty($global['avideo_resolutions']) || !is_array($global['avideo_resolutions'])) {
    $global['avideo_resolutions'] = [240, 360, 480, 540, 720, 1080, 1440, 2160, 'offline'];
}

$global['avideo_possible_resolutions'] = array_merge($global['avideo_resolutions'],  ['HD', 'SD', 'Low']);

includeConfigLog(__LINE__);
sort($global['avideo_resolutions']);
if (!empty($doNotConnectDatabaseIncludeConfig)) {
    return false;
}
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
    ObjectYPT::setGlobalTimeZone();
}

includeConfigLog(__LINE__);
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

includeConfigLog(__LINE__);
