<?php
global $global, $config;

require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGive.php';
session_write_close();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = new stdClass();

if (empty($_REQUEST['site_url'])) {
    $obj->msg = "Empty site_url";
    die(json_encode($obj));
}

$row = CombineSitesDB::sitesGetGivePermissionsFromSiteURL($_REQUEST['site_url'], 'give', @$_REQUEST['type']);

if($row){
    $obj->error = false;
    $obj->response = $row['give'];
}

die(json_encode($obj));
