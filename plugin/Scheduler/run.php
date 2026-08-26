<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface() && !User::isAdmin()) {
    return die('Command Line only');
}

// Keep avideo.log writable without checking on every request.
ensureAVideoLogWritable(false, 300);

if (!AVideoPlugin::isEnabledByName('Scheduler')) {
    return die('Scheduler is disabled');
}

// Prevent overlapping runs (duplicate/overlapping cron entries, a webcron hitting this URL while the
// system cron also fires, or an admin clicking "Run now" mid-tick) from racing on the claim logic below.
$schedulerLockFile = $global['systemRootPath'] . 'videos/scheduler_run.lock';
// On POSIX, close this descriptor when exec() starts an asynchronous child (for example FFmpeg).
// Without close-on-exec, the child inherits the flock and can block every Scheduler tick until
// the entire broadcast ends, even though this PHP process has already completed.
$schedulerLockMode = DIRECTORY_SEPARATOR === '/' ? 'c+e' : 'c+';
$schedulerLockHandle = fopen($schedulerLockFile, $schedulerLockMode);
if (!$schedulerLockHandle) {
    $lastError = error_get_last();
    _error_log('Scheduler::run ERROR - could not open lock file ' . $schedulerLockFile . ' error=' . json_encode($lastError));
    return die('Scheduler lock file could not be opened');
}
if (!flock($schedulerLockHandle, LOCK_EX | LOCK_NB)) {
    rewind($schedulerLockHandle);
    $schedulerLockOwnerRaw = trim(stream_get_contents($schedulerLockHandle));
    $schedulerLockOwner = json_decode($schedulerLockOwnerRaw, true);
    $schedulerOwnerPidAlive = null;
    if (is_array($schedulerLockOwner) && !empty($schedulerLockOwner['pid']) && DIRECTORY_SEPARATOR === '/') {
        $schedulerOwnerPidAlive = file_exists('/proc/' . intval($schedulerLockOwner['pid']));
    }
    _error_log(
        'Scheduler::run skipped - another instance is already running lockOwner=' .
        json_encode($schedulerLockOwner ?: array('raw' => substr($schedulerLockOwnerRaw, 0, 1000))) .
        ' ownerPidAlive=' . json_encode($schedulerOwnerPidAlive)
    );
    fclose($schedulerLockHandle);
    return die('Scheduler is already running');
}

$schedulerRunStartedAt = microtime(true);
$schedulerLockState = array(
    'pid' => function_exists('getmypid') ? getmypid() : null,
    'host' => function_exists('gethostname') ? gethostname() : php_uname('n'),
    'startedAt' => date('c'),
    'timezone' => date_default_timezone_get(),
    'sapi' => PHP_SAPI,
    'phase' => 'startup',
    'phaseStartedAt' => date('c'),
    'updatedAt' => date('c'),
);
$schedulerWriteLockState = function ($phase, $phaseStartedAt = null) use ($schedulerLockHandle, &$schedulerLockState) {
    $schedulerLockState['phase'] = $phase;
    $schedulerLockState['phaseStartedAt'] = $phaseStartedAt ?: date('c');
    $schedulerLockState['updatedAt'] = date('c');
    rewind($schedulerLockHandle);
    ftruncate($schedulerLockHandle, 0);
    fwrite($schedulerLockHandle, json_encode($schedulerLockState));
    fflush($schedulerLockHandle);
};
$schedulerLogPhase = function ($phase, $event, $phaseStartedAt = null) use ($schedulerWriteLockState, &$schedulerLockState) {
    if ($event === 'start') {
        unset($schedulerLockState['lastPhase'], $schedulerLockState['lastPhaseElapsed']);
        $schedulerWriteLockState($phase, date('c'));
        return microtime(true);
    }

    $elapsed = is_numeric($phaseStartedAt) ? microtime(true) - $phaseStartedAt : 0;
    $schedulerLockState['lastPhase'] = $phase;
    $schedulerLockState['lastPhaseElapsed'] = round($elapsed, 3);
    $schedulerWriteLockState($phase . ':done', date('c'));
};
$schedulerWriteLockState('startup', $schedulerLockState['phaseStartedAt']);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_end_flush();

$schedulerPhaseStartedAt = $schedulerLogPhase('scheduledCommands', 'start');
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
$schedulerLogPhase('scheduledCommands', 'end', $schedulerPhaseStartedAt);

function _log($msg)
{

    if (!isCommandLineInterface()) {
        echo date('Y-m-d H:i:s') . ' ' . $msg . '<br>';
    }

    _error_log("Scheduler::run {$msg}");
}

echo ("Scheduler watchDog".PHP_EOL);
$schedulerPhaseStartedAt = $schedulerLogPhase('watchDog', 'start');
include $global['systemRootPath'] . 'plugin/Scheduler/watchDog.php';
$schedulerLogPhase('watchDog', 'end', $schedulerPhaseStartedAt);
echo ("Scheduler watchDog done".PHP_EOL);

echo ("Scheduler sendEmails".PHP_EOL);
$schedulerPhaseStartedAt = $schedulerLogPhase('sendEmails', 'start');
Scheduler::sendEmails();
$schedulerLogPhase('sendEmails', 'end', $schedulerPhaseStartedAt);
echo ("Scheduler sendEmails done".PHP_EOL);

echo ("Scheduler executeEveryMinute".PHP_EOL);
$schedulerPhaseStartedAt = $schedulerLogPhase('executeEveryMinute', 'start');
AVideoPlugin::executeEveryMinute();
$schedulerLogPhase('executeEveryMinute', 'end', $schedulerPhaseStartedAt);
echo ("Scheduler executeEveryMinute done".PHP_EOL);

// This script runs every minute
$current_minute = date('i'); // Get the current minute (00-59)
$current_hour = date('G'); // Get the current hour (0-23, 24-hour format)
$current_day = date('j'); // Get the current day of the month (1-31)

// Block to execute every hour
if ($current_minute == '00') {
    echo ("Scheduler executeEveryHour".PHP_EOL);
    $schedulerPhaseStartedAt = $schedulerLogPhase('executeEveryHour', 'start');
    AVideoPlugin::executeEveryHour();
    $schedulerLogPhase('executeEveryHour', 'end', $schedulerPhaseStartedAt);
    echo ("Scheduler executeEveryHour done".PHP_EOL);
}

// Block to execute every day (at midnight)
if ($current_hour == '0' && $current_minute == '00') {
    echo ("Scheduler executeEveryDay".PHP_EOL);
    $schedulerPhaseStartedAt = $schedulerLogPhase('executeEveryDay', 'start');
    AVideoPlugin::executeEveryDay();
    $schedulerLogPhase('executeEveryDay', 'end', $schedulerPhaseStartedAt);
    echo ("Scheduler executeEveryDay done".PHP_EOL);
}

// Block to execute every month (at midnight)
if ($current_day == '1' && $current_hour == '0' && $current_minute == '00') {
    echo ("Scheduler executeEveryMonth".PHP_EOL);
    $schedulerPhaseStartedAt = $schedulerLogPhase('executeEveryMonth', 'start');
    AVideoPlugin::executeEveryMonth();
    $schedulerLogPhase('executeEveryMonth', 'end', $schedulerPhaseStartedAt);
    echo ("Scheduler executeEveryMonth done".PHP_EOL);
}
if (!isCommandLineInterface()) {
    echo '</pre>';
}

$schedulerWriteLockState('completed', date('c'));
$schedulerRunElapsed = microtime(true) - $schedulerRunStartedAt;
if ($schedulerRunElapsed >= 30) {
    _error_log(
        'Scheduler::run slow execution completed pid=' . json_encode($schedulerLockState['pid']) .
        ' elapsed=' . number_format($schedulerRunElapsed, 3, '.', '') . 's'
    );
}
flock($schedulerLockHandle, LOCK_UN);
fclose($schedulerLockHandle);
