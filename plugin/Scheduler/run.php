<?php

//streamer config
require_once '../../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

if(!AVideoPlugin::isEnabledByName('Scheduler')){
    return die('Scheduler is disabled');
}

$rows = Scheduler_commands::getAllActiveAndReady();
$time = getDatabaseTime();
//var_dump($time, date('Y-m-d H:i:s', $time), $rows);
foreach ($rows as $value) {
    $id = Scheduler::run($value['id']);
    if(empty($id)){
        _error_log("Scheduler::run error [{$value['id']}] callbackURL={$value['callbackURL']}"); 
    }
}