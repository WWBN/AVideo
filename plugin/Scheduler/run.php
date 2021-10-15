<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface() && !User::isAdmin()) {
    return die('Command Line only');
}

if(!AVideoPlugin::isEnabledByName('Scheduler')){
    return die('Scheduler is disabled');
}

$rowActive = Scheduler_commands::getAllActiveOrToRepeat();
$total = count($rowActive);
_log("There are {$total} active requests"); 

$rows = Scheduler_commands::getAllActiveAndReady();
foreach ($rows as $value) {
    $id = Scheduler::run($value['id']);
    if(empty($id)){
        _log("error [{$value['id']}] callbackURL={$value['callbackURL']}"); 
    }
}


$rows = Scheduler_commands::getAllScheduledTORepeat();
foreach ($rows as $value) {
    $id = Scheduler::run($value['id']);
    if(empty($id)){
        _log("error [{$value['id']}] callbackURL={$value['callbackURL']}"); 
    }
}

function _log($msg){
    
    if(!isCommandLineInterface()){
        echo date('Y-m-d H:i:s').' '.$msg.'<br>';
    }
    
    _error_log("Scheduler::run {$msg}");
}