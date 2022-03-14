<?php

require_once '../../videos/configuration.php';
require_once '../AVideoPlugin.php';
_error_log("on_record_done start");
$plugin = AVideoPlugin::loadPluginIfEnabled('SendRecordedToEncoder');
if ($plugin) {
    _error_log("on_record_done SendRecordedToEncoder");
    $plugin->on_record_done();
}
_error_log("on_record_done end");
