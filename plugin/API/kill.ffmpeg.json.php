<?php
$configFile = __DIR__.'/../../videos/configuration.php';
require_once $configFile;
header('Content-Type: application/json');

if(!User::isAdmin()){
    forbiddenPage('Must be admin');
}

$pid = intval($_REQUEST['pid']);

if(empty($pid)){
    forbiddenPage('PID is invalid');
}

$obj = killFFMPEGRemote($pid);

die(json_encode($obj));
