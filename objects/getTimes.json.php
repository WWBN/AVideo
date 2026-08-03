<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
$closeSessionEarlyIncludeConfig = 1;
require_once $global['systemRootPath'] . 'videos/configuration.php';
allowOrigin(true);

$obj = new stdClass();
$obj->_serverTime = time();
$obj->_serverDBTime = getDatabaseTime();
$obj->_serverTimeString = date('Y-m-d H:i:s');
$obj->_serverDBTimeString = date('Y-m-d H:i:s', getDatabaseTime());
$obj->_serverTimezone = date_default_timezone_get();
$obj->_serverDBTimezone = getDatabaseTimezoneName();
$obj->_serverSystemTimezone = getSystemTimezone();

die(json_encode($obj));
