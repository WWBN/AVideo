<?php
ini_set('error_log', $global['systemRootPath'].'videos/youphptube.log');
global $global;
global $config;


$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser,$mysqlPass,$mysqlDatabase,$mysqlPort);

$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
$global['mysqli']->query("SET time_zone='$offset';");

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

// for update config from old versions
if(function_exists("getAllFlags")){
    Configuration::rewriteConfigFile();
}

$global['dont_show_us_flag'] = false;
// this is for old versions
session_write_close ();

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', $config->getSession_timeout());

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params($config->getSession_timeout());

session_start();

require_once $global['systemRootPath'].'objects/Object.php';
require_once $global['systemRootPath'].'locale/function.php';
