<?php

header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (empty($_POST['name'])) {
    die('{"error":"' . __("Name can't be blank") . '"}');
}
ini_set('max_execution_time', 300);
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

$res=YouPHPTubePlugin::updatePlugin($_POST['name']); 



die(json_encode($res));
