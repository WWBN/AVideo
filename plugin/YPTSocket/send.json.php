<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

if (!isCommandLineInterface()) {
    die('Command line only');
}

if(count($argv) < 5){
    die('Please pass all argumments');
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$msg = $argv[1];
$callbackJSFunction = $argv[2];
$users_id = intval($argv[3]);
$send_to_uri_pattern = $argv[4];

if (AVideoPlugin::isEnabledByName('YPTSocket')) {
    $obj = YPTSocket::send($msg, $callbackJSFunction, $users_id, $send_to_uri_pattern);
    if ($obj->error) {
        _error_log("YPTSocket::send.json.php " . $obj->msg, AVideoLog::$ERROR);
    }
    return $obj;
}

die(json_encode($obj));
