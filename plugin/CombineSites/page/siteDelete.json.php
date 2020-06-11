<?php

error_reporting(0);
global $global, $config;

require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = "Not admin";
    die(json_encode($obj));
}

if (empty($_REQUEST['id'])) {
    $obj->msg = "Empty id";
    die(json_encode($obj));
}


$o = new CombineSitesDB($_REQUEST['id']);

if($o->delete()){
    $obj->error = false;
}
die(json_encode($obj));