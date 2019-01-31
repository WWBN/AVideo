<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$pluginName = 'PlayerSkins';

$pluginDO = YouPHPTubePlugin::getObjectData($pluginName);
$pluginDB = Plugin::getOrCreatePluginByName($pluginName, 'active');

$pluginDO->skin = $_POST['skin'];

$p = new Plugin($pluginDB['id']);
$p->setObject_data(json_encode($pluginDO));

$obj = new stdClass();
$obj->save = $p->save();

echo (json_encode($obj));
