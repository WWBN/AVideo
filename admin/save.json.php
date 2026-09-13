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
if (!isGlobalTokenValid()) {
    die('{"error":"' . __("Invalid or missing CSRF token") . '"}');
}

require_once __DIR__ . '/functions.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = __('An error occurred');
$obj->save = false;
try {
    $pluginName = $_POST['pluginName'] ?? '';
    $pluginDO = AVideoPlugin::getObjectData($pluginName);
    $pluginDB = Plugin::getPluginByName($pluginName);
    if (!is_object($pluginDO) || empty($pluginDB['id'])) {
        throw new RuntimeException('Plugin settings are unavailable');
    }
    $fields = empty($_POST['pluginsList']) ? array_keys($_POST) : explode('|', $_POST['pluginsList']);
    $pluginDO = applyAdminPluginValues($pluginDO, $_POST, $fields);
    $json = json_encode($pluginDO, JSON_THROW_ON_ERROR);
    $p = new Plugin($pluginDB['id']);
    $p->setObject_data($json);
    $obj->save = $p->save();
    $obj->pluginName = $pluginName;
    $obj->dataObject = $pluginDO;
    if (empty($obj->save)) {
        throw new RuntimeException('Could not save plugin settings');
    }
    $obj->error = false;
    $obj->msg = __('Saved');
} catch (\Throwable $th) {
    _error_log('admin/save.json.php: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($obj);
