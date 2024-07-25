<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

if (!isCommandLineInterface()) {
    die('Command line only');
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$obj->port = intval($argv[1]);

if(empty($obj->port)){
    $obj->msg = "Invalid port";
    die(json_encode($obj));
}

$p = AVideoPlugin::loadPluginIfEnabled("YPTSocket");
$obj->saved = $p->setDataObjectParameter("port", $obj->port);
$obj->error = empty($obj->saved);

die(json_encode($obj));
