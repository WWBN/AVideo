<?php

require_once '../../videos/configuration.php';
require_once '../YouPHPTubePlugin.php';
error_log("Record Finish");
$plugin = YouPHPTubePlugin::loadPluginIfEnabled('SendRecordedToEncoder');
if($plugin){
    $plugin->on_record_done();
}