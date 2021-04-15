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

$o = new CombineSitesDB($_REQUEST['combine_sites_id']);

if(empty($o->getSite_url())){
    $obj->msg = "Site not found";
    die(json_encode($obj));
}

$status = ($_REQUEST['status']==='a'?'a':'i');

// check the site
if($status==='a'){
    $urlInfo = $o->getSite_url().'plugin/API/status.json.php';
    $info = _json_decode(url_get_contents($urlInfo));
    if(empty($info)){
        $obj->msg = "It is not a valid Streamer site";
        die(json_encode($obj));
    }
    if($info->error){
        $obj->msg = $info->message;
        die(json_encode($obj));
    }
}

$o->setStatus($status);

if($o->save()){
    $obj->error = false;
}
die(json_encode($obj));