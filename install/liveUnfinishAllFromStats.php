<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
AVideoPlugin::loadPlugin('Live');
Live::unfinishAllFromStats();

$cacheHandler = new LiveCacheHandler();
$cacheHandler->deleteCache();