<?php

require_once '../../videos/configuration.php';
require_once '../AVideoPlugin.php';
_error_log("Record Finish");
$plugin = AVideoPlugin::loadPluginIfEnabled('SendRecordedToEncoder');
if ($plugin) {
    $plugin->on_record_done();
}
