<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

ob_end_flush();
_mysql_close();
session_write_close();

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if(empty($_REQUEST['msg'])){
    $obj->msg = "The message is empty";
    die(json_encode($obj));
}

if(!AVideoPlugin::isEnabledByName("YPTSocket")){
    $obj->msg = "Socket plugin not enabled";
    die(json_encode($obj));
}

$obj = sendSocketMessage($_REQUEST['msg']);


die(json_encode($obj));