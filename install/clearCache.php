<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$obj = new stdClass();
$obj->error = false;
$obj->msg = '';
$obj->deleteALLCache = ObjectYPT::deleteALLCache();

die(json_encode($obj));
