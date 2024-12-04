<?php
$configFile = __DIR__.'/../../videos/configuration.php';
require_once $configFile;
header('Content-Type: application/json');

$obj = testFFMPEGRemote();

die(json_encode($obj));
