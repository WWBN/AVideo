<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
forbidIfIsUntrustedRequest('pluginAddDataObject');
if (empty($_POST['id'])) {
    die('{"error":"'.__("ID can't be blank").'"}');
}
$obj = new Plugin($_POST['id']);

// Same per-setting validators as admin/save.json.php (e.g. CustomizeAdvanced ffmpegParameters).
require_once $global['systemRootPath'] . 'admin/functions.php';
require_once __DIR__ . '/dangerousConfigurations.php';
$objectData = json_decode($_POST['object_data'] ?? '');
if (isAdvancedConfigurationsDisabled()) {
    if (!is_object($objectData)) {
        die(json_encode(['error' => 'Settings not saved. Plugin settings must be a JSON object.']));
    }
    // disableAdvancedConfigurations: settings from getDangerousPluginSettings() keep their stored value.
    $storedData = PluginAbstract::getObjectDataFromDatabase($obj->getUuid());
    $objectData = keepStoredDangerousPluginSettings($obj->getName(), $objectData, $storedData, $lockedKeys);
    $_POST['object_data'] = json_encode($objectData);
    if (!empty($lockedKeys)) {
        _error_log('pluginAddDataObject: disableAdvancedConfigurations kept the stored value of ' . $obj->getName() . ': ' . json_encode($lockedKeys), AVideoLog::$SECURITY);
    }
}
if (is_object($objectData)) {
    foreach (getAdminPluginFieldValidators() as $validatorPlugin => $validators) {
        if (strcasecmp($validatorPlugin, (string) $obj->getName()) !== 0) {
            continue;
        }
        foreach ($validators as $field => $validator) {
            if (isAdvancedConfigurationsDisabled() && isDangerousConfigKey($validatorPlugin, $field)) {
                continue; // stored value kept above, not a submitted change
            }
            if (property_exists($objectData, $field) && !call_user_func_array($validator, [$objectData->$field, &$validationError])) {
                _error_log("pluginAddDataObject: {$validatorPlugin} {$field} rejected: {$validationError}", AVideoLog::$SECURITY);
                die(json_encode(['error' => "Settings not saved. {$field}: {$validationError}"]));
            }
        }
    }
}

// verify if some field needs to be encrypted
$_POST['object_data'] = Plugin::encryptIfNeed($_POST['object_data']);

$obj->setObject_data(addcslashes($_POST['object_data'], '\\'));

echo '{"status":"'.$obj->save().'"}';
