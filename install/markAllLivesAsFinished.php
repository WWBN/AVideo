<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}


$obj = AVideoPlugin::getDataObject('Live');
$result = !LiveTransmitionHistory::finishALL();

var_dump($result);