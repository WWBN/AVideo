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
_log("There are {$total} active requests php_sapi_name=".php_sapi_name()); 

$rows = Scheduler_commands::getAllActiveAndReady();
_log("getAllActiveAndReady found ".count($rows)); 
foreach ($rows as $value) {
    _log("getAllActiveAndReady run ". json_encode($value)); 
    $id = Scheduler::run($value['id']);
    if(empty($id)){
        _log("error [{$value['id']}] callbackURL={$value['callbackURL']}"); 
    }
}


$rows = Scheduler_commands::getAllScheduledTORepeat();
_log("getAllScheduledTORepeat found ".count($rows) . ' on time '. json_encode(Scheduler_commands::getTimesNow())); 
foreach ($rows as $value) {
    _log("getAllScheduledTORepeat run ". json_encode($value)); 
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