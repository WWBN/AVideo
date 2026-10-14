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
    $requested = empty($_POST['pluginsList']) ? array_keys($_POST) : explode('|', $_POST['pluginsList']);
    // The server decides which fields are editable; the posted list can only narrow it.
    $editable = getAdminPluginEditableFields($pluginDO);
    $fields = array_values(array_intersect($requested, $editable));
    if (!empty($_POST['pluginsList']) && count($fields) !== count($requested)) {
        _error_log('admin/save.json.php: ignored fields not editable for ' . $pluginName . ': ' . json_encode(array_values(array_diff($requested, $editable))), AVideoLog::$SECURITY);
    }
    if (isAdvancedConfigurationsDisabled()) {
        // disableAdvancedConfigurations: these settings keep their stored value.
        $locked = array_values(array_intersect($fields, getDangerousConfigKeys($pluginDB['name'] ?? $pluginName)));
        if (!empty($locked)) {
            $fields = array_values(array_diff($fields, $locked));
            _error_log('admin/save.json.php: disableAdvancedConfigurations kept the stored value of ' . $pluginName . ': ' . json_encode($locked), AVideoLog::$SECURITY);
        }
    }
    $pluginDO = applyAdminPluginValues($pluginDO, $_POST, $fields, $pluginDB['name'] ?? $pluginName);
    $json = json_encode($pluginDO, JSON_THROW_ON_ERROR);
    $p = new Plugin($pluginDB['id']);
    $p->setObject_data(addcslashes($json, '\\'));
    $obj->save = $p->save();
    $obj->pluginName = $pluginName;
    $obj->dataObject = $pluginDO;
    if (empty($obj->save)) {
        throw new RuntimeException('Could not save plugin settings');
    }
    $obj->error = false;
    $obj->msg = __('Saved');
} catch (AdminPluginSettingsValidationException $th) {
    // Nothing was saved, so the previous values stay in place.
    $obj->msg = $th->getMessage();
    _error_log('admin/save.json.php: ' . $pluginName . ' ' . $th->getMessage(), AVideoLog::$SECURITY);
} catch (\Throwable $th) {
    _error_log('admin/save.json.php: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($obj);
