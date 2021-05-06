<?php

//streamer config
require_once '../videos/configuration.php';
AVideoPlugin::loadPlugin('YPTStorage');
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
error_reporting(E_ALL);
ini_set('display_errors', '1');
$filename = '_YPTuniqid_5f80cfc9990a82.31784835';
$size = YPTStorage::getUsageFromFilename($filename);
var_dump($size, humanFileSize($size));