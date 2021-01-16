<?php

global $global, $config;

require_once '../../videos/configuration.php';
session_write_close();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');


$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->site_url = "";
$obj->site_is_enabled = "";
$obj->site_title = "";
$obj->site_logo = "";
if(empty($_GET['site_url'])){
    $obj->msg = "site_url is empty";
}else if (!AVideoPlugin::loadPluginIfEnabled('CombineSites')) {
    $obj->msg = "CombineSites plugin is NOT enabled";
}else{
    $obj->error = false;
    $obj->site_url = $_GET['site_url'];
    $obj->site_is_enabled = CombineSitesDB::sitesIsEnable($_GET['site_url']);
    $obj->site_title = $config->getWebSiteTitle();
    $obj->site_logo = $global['webSiteRootURL'].$config->getLogo(true);
}

die(json_encode($obj));