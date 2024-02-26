<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
        
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$objAutoPostOnSocialMedia = AVideoPlugin::getDataObjectIfEnabled('AutoPostOnSocialMedia');
$objSchedule = AVideoPlugin::getDataObjectIfEnabled('Scheduler');

if(empty($objSchedule)){
    $obj->msg = 'Plugin Scheduler disabled';
    die(json_encode($obj));
}

if(empty($objAutoPostOnSocialMedia)){
    $obj->msg = 'Plugin AutoPostOnSocialMedia disabled';
    die(json_encode($obj));
}

if(!isCommandLineInterface() && !User::isAdmin()){
    $obj->msg = 'Forbbiden';
    die(json_encode($obj));
}

Scheduler_commands::deleteFromType(AutoPostOnSocialMedia::$scheduleType);
$obj->callbackURL = "{$global['webSiteRootURL']}plugin/AutoPostOnSocialMedia/autopost.json.php";

$obj->response = array();

foreach ($_REQUEST['checkedItems'] as $key => $value) {
    
    $parts = explode('_', $value);
    
    $repeat_hour = intval($parts[1]);
    $repeat_day_of_week = intval($parts[0]);
    
    $schedule = new Scheduler_commands(0);
    $schedule->setCallbackURL($obj->callbackURL);
    $schedule->setStatus(Scheduler_commands::$statusRepeat);
    $schedule->setRepeat_minute(0);
    $schedule->setRepeat_hour($repeat_hour);
    $schedule->setRepeat_day_of_week($repeat_day_of_week);
    $schedule->setType(AutoPostOnSocialMedia::$scheduleType);
    $id = $schedule->save();
    
    $obj->response[] = array(
        'repeat_hour'=>$repeat_hour,
        'repeat_day_of_week'=>$repeat_day_of_week,
        'id'=>$id);
}

$obj->error = false;
die(json_encode($obj));