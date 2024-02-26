<?php
error_reporting(0);
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
header('Content-Type: application/json');

$object = new stdClass();
$object->error = true;
$object->msg = "";
$object->scheduler_commands_id = 0;


if (!User::isAdmin()) {
    forbiddenPage('Only admin can restart socket');
}
if (!AVideoPlugin::isEnabledByName('Scheduler')) {
    forbiddenPage('Scheduler plugin disabled');
}
if(!Scheduler::isActive()){
    forbiddenPage('<b>Attention: Scheduler Plugin Inactive</b><br><br>Please verify that the plugin is added to the cron tab.');
}

if (!AVideoPlugin::isEnabledByName('YPTSocket')) {
    forbiddenPage('Socket plugin disabled');
}

$object->scheduler_commands_id = YPTSocket::scheduleRestart();

$object->error = empty($object->scheduler_commands_id);
if ($object->error) {
    $object->msg = "Error on save schedule";
} else {
    $object->msg = "We have scheduled a restart for your socket, which will take place shortly";
}

echo json_encode($object);
