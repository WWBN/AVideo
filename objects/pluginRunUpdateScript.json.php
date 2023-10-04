<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    forbiddenPage('Permission denied');
}
if (empty($_POST['name'])) {
    forbiddenPage('Name can\'t be blank');
}
ini_set('max_execution_time', 300);
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

$obj = new stdClass();
$obj->error = !AVideoPlugin::updatePlugin($_POST['name']);
$obj->msg = '';
die(json_encode($obj));
