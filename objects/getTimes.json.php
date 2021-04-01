<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$obj = new stdClass();
$obj->_serverTime = time();
$obj->_serverDBTime = getDatabaseTime();
$obj->_serverTimeString = date('Y-m-d H:i:s');
$obj->_serverDBTimeString = date('Y-m-d H:i:s', getDatabaseTime());

die(json_encode($obj));