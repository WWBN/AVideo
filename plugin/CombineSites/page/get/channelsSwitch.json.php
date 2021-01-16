<?php


global $global, $config;

require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGet.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = new stdClass();

if (!User::isAdmin()) {
    $obj->msg = "Not admin";
    die(json_encode($obj));
}

if (empty($_REQUEST['status'])) {
    $obj->msg = "Empty status";
    die(json_encode($obj));
}

if (empty($_REQUEST['combine_sites_id'])) {
    $obj->msg = "Empty combine_sites_id";
    die(json_encode($obj));
}

if (empty($_REQUEST['id'])) {
    $obj->msg = "Empty id";
    die(json_encode($obj));
}

$o = new CombineSitesDB($_REQUEST['combine_sites_id']);

if (empty($o->getSite_url())) {
    $obj->msg = "Site not found";
    die(json_encode($obj));
}

$status = ($_REQUEST['status'] === 'a' ? 'a' : 'i');

if(CombineSitesGet::addChannel($_REQUEST['combine_sites_id'], $_REQUEST['id'], $status)){
    $obj->error = false;
}

die(json_encode($obj));
