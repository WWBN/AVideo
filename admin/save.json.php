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

$pluginName = $_POST['pluginName'];

if (empty($_POST['pluginsList'])) {
    unset($_POST['pluginsList']);
    unset($_POST['pluginName']);
    $pluginValues = $_POST;
} else {
    $pluginsList = explode("|", $_POST['pluginsList']);

    $pluginValues = array();
    foreach ($pluginsList as $value) {
        $pluginValues[$value] = empty($_POST[$value]) ? false : ($_POST[$value]==1||$_POST[$value]=="true"?true:$_POST[$value]);
    }
}

$pluginDO = YouPHPTubePlugin::getObjectData($pluginName);
$pluginDB = Plugin::getPluginByName($pluginName);

foreach ($pluginDO as $key => $value) {
    if (isset($pluginValues[$key])) {
        if(is_bool($pluginDO->$key)){
            $pluginDO->$key = empty($pluginValues[$key])?false:true;
        }else{
            //$pluginDO->$key = str_replace('"', '\\"', $pluginValues[$key]);
            $pluginDO->$key = $pluginValues[$key];
        }
    }
}

$p = new Plugin($pluginDB['id']);
$p->setObject_data(json_encode($pluginDO));

$obj = new stdClass();
$obj->save = $p->save();
if($obj->save === false) error_log("[ERROR] Error saving plugin $pluginName data. Maybe plugin is not enabled?");

echo (json_encode($obj));
