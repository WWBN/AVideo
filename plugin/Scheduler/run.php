<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface() && !User::isAdmin()) {
    return die('Command Line only');
}

if (!AVideoPlugin::isEnabledByName('Scheduler')) {
    return die('Scheduler is disabled');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_end_flush();

$rowActive = Scheduler_commands::getAllActiveOrToRepeat();
if(empty($rowActive)){
    $rowActive = array();
}
$total1 = count($rowActive);

$rows = Scheduler_commands::getAllActiveAndReady();
$total2 = count($rows);
if (!isCommandLineInterface()) {
    echo '<pre>';
}
if (empty($rows)) {
    //echo ("Scheduler row is empty".PHP_EOL); 
}

$rows2 = Scheduler_commands::getAllScheduledTORepeat();
if (empty($rows)) {
    //echo ("Scheduler row2 is empty".PHP_EOL); 
}
$total3 = count($rows2);
//_log("There are {$total1} active requests; getAllActiveAndReady={$total2} getAllScheduledTORepeat={$total3} on time ". json_encode(Scheduler_commands::getTimesNow())); 

foreach ($rows as $value) {
    _log("getAllActiveAndReady run " . json_encode($value));
    $id = Scheduler::run($value['id']);
    if (empty($id)) {
        _log("error [{$value['id']}] callbackURL={$value['callbackURL']}");
    }
}

foreach ($rows2 as $value) {
    _log("getAllScheduledTORepeat run " . json_encode($value));
    $id = Scheduler::run($value['id']);
    if (empty($id)) {
        _log("error [{$value['id']}] callbackURL={$value['callbackURL']} " . json_encode($value));
    }
}
$lastVisitFile = Scheduler::setLastVisit();
if (!empty($lastVisitFile) && !empty($lastVisitFile['size'])) {
    //echo 'Saved '.json_encode($lastVisitFile); 
    //_error_log("Last visit set {$lastVisitFile}");
} else {
    $msg = 'ERROR: Last visit NOT set ' . json_encode($lastVisitFile);
    echo $msg . PHP_EOL;
    _error_log($msg);
}

function _log($msg)
{

    if (!isCommandLineInterface()) {
        echo date('Y-m-d H:i:s') . ' ' . $msg . '<br>';
    }

    _error_log("Scheduler::run {$msg}");
}

echo ("Scheduler watchDog".PHP_EOL); 
include $global['systemRootPath'] . 'plugin/Scheduler/watchDog.php';
echo ("Scheduler watchDog done".PHP_EOL); 

echo ("Scheduler sendEmails".PHP_EOL); 
Scheduler::sendEmails();
echo ("Scheduler sendEmails done".PHP_EOL); 

echo ("Scheduler executeEveryMinute".PHP_EOL); 
AVideoPlugin::executeEveryMinute();
echo ("Scheduler executeEveryMinute done".PHP_EOL); 

// This script runs every minute
$current_minute = date('i'); // Get the current minute (00-59)
$current_hour = date('G'); // Get the current hour (0-23, 24-hour format)
$current_day = date('j'); // Get the current day of the month (1-31)

// Block to execute every hour
if ($current_minute == '00') {
    echo ("Scheduler executeEveryHour".PHP_EOL); 
    AVideoPlugin::executeEveryHour();
    echo ("Scheduler executeEveryHour done".PHP_EOL); 
}

// Block to execute every day (at midnight)
if ($current_hour == '0' && $current_minute == '00') {
    echo ("Scheduler executeEveryDay".PHP_EOL); 
    AVideoPlugin::executeEveryDay();
    echo ("Scheduler executeEveryDay done".PHP_EOL); 
}

// Block to execute every month (at midnight)
if ($current_day == '1' && $current_hour == '0' && $current_minute == '00') {
    echo ("Scheduler executeEveryMonth".PHP_EOL); 
    AVideoPlugin::executeEveryMonth();
    echo ("Scheduler executeEveryMonth done".PHP_EOL); 
}
if (!isCommandLineInterface()) {
    echo '</pre>';
}
