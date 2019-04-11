<?php

ini_set('error_log', $global['systemRootPath'] . 'videos/youphptube.log');
global $global, $config, $advancedCustom, $advancedCustomUser;

$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);

$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
$global['mysqli']->query("SET time_zone='$offset';");

require_once $global['systemRootPath'] . 'objects/mysql_dal.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'objects/security.php';
$config = new Configuration();

// for update config from old versions
if (function_exists("getAllFlags")) {
    Configuration::rewriteConfigFile();
}

// for update config to v5.3
if (empty($global['salt'])) {
    Configuration::rewriteConfigFile();
}

$global['dont_show_us_flag'] = false;
// this is for old versions
session_write_close();

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', $config->getSession_timeout());

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params($config->getSession_timeout());


session_start();

// DDOS protection can be disabled in video/configuration.php
if($global['enableDDOSprotection']) ddosProtection();

// set the reffer for youPHPTube
$url1['host'] = "";
if (!empty($_SERVER["HTTP_REFERER"])) {
    if((strpos($_SERVER["HTTP_REFERER"], '/video/') !== false || strpos($_SERVER["HTTP_REFERER"], '/v/') !== false) && !empty($_SESSION["LAST_HTTP_REFERER"])){
        $global["HTTP_REFERER"] = $_SESSION["LAST_HTTP_REFERER"];
        $url1 = parse_url($global["HTTP_REFERER"]);
    }else{
        $global["HTTP_REFERER"] = $_SERVER["HTTP_REFERER"];
        $url1 = parse_url($global["HTTP_REFERER"]);
    }
}

if(!isset($_POST['redirectUri'])){
    $_POST['redirectUri'] = "";
}

$url2 = parse_url($global['webSiteRootURL']);
if ($url1['host'] !== $url2['host']) {
    $global["HTTP_REFERER"] = $global['webSiteRootURL'];
}
$_SESSION["LAST_HTTP_REFERER"] = $global["HTTP_REFERER"];
//var_dump($global["HTTP_REFERER"], $url1);exit;

$output = ob_get_clean();
ob_start("ob_gzhandler");
echo $output;
$_SESSION['lastUpdate'] = time();
$_SESSION['savedQuerys'] = 0;
require_once $global['systemRootPath'] . 'objects/Object.php';
require_once $global['systemRootPath'] . 'locale/function.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
allowOrigin();
if (class_exists("Plugin")) {
    YouPHPTubePlugin::getStart();
} else {
    error_log("Class Plugin Not found: {$_SERVER['REQUEST_URI']}");
}
if (empty($global['bodyClass'])) {
    $global['bodyClass'] = "";
}
$global['allowedExtension'] = array('gif', 'jpg', 'mp4', 'webm', 'mp3', 'ogg', 'zip');
$advancedCustom = YouPHPTubePlugin::getObjectData("CustomizeAdvanced");
$advancedCustomUser = YouPHPTubePlugin::getObjectData("CustomizeUser");
$sitemapFile = "{$global['systemRootPath']}sitemap.xml";
