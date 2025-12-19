<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
// To suppress the warning during the date() invocation in logs, we would default the timezone to GMT.
if (!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}
// Include the composer autoloader
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->add('PayPal\\Test', __DIR__);
if (!defined("PP_CONFIG_PATH")) {
    define("PP_CONFIG_PATH", __DIR__);
}
