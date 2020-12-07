<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->balance = 0;

if (!User::isLogged()) {
    $obj->msg = "Please login first";
    die(json_encode($obj));
}

$obj->balance = YPTWallet::getUserBalance();

$obj->error = false;
die(json_encode($obj));
?>