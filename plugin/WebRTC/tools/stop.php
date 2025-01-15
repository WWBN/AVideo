<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';

if(!isCommandLineInterface()){
    forbiddenPage('Must be a command line');
}

$objP = AVideoPlugin::getDataObject('WebRTC');

WebRTC::stopServer();
?>
