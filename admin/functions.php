<?php
require_once dirname(__DIR__) . '/objects/dangerousConfigurations.php';

function getAdminPageWarningHTML($filename)
{
    global $global;
    $relativePath = substr(str_replace('\\', '/', $filename), strlen(str_replace('\\', '/', $global['systemRootPath'])));
    $pageMissing = !file_exists($filename);
    $messages = [];
    $link = '';
    $class = 'alert-danger';
    if (strpos($relativePath, 'plugin/') === 0) {
        $parts = explode('/', $relativePath);
        $pluginName = $parts[1];
        $label = htmlspecialchars($pluginName, ENT_QUOTES, 'UTF-8');
        if (!AVideoPlugin::exists($pluginName)) {
            $messages[] = sprintf(__('The %s plugin is not installed. Install it to use this feature.'), $label);
            $link = '<a class="btn btn-danger" href="https://streamphp.com/marketplace/">' . __('Plugin Store') . '</a>';
        } else {
            if (!AVideoPlugin::isEnabledByName($pluginName)) {
                $messages[] = sprintf(__('The %s plugin is installed but disabled. Enable it in Plugins to use this feature.'), $label);
                $class = 'alert-warning';
            }
            if ($pageMissing) {
                $messages[] = sprintf(__('The %s plugin is installed, but its administration page is unavailable. Update the plugin and try again.'), $label);
                $class = 'alert-danger';
            }
            if (!empty($messages)) {
                $link = '<a class="btn btn-default" href="' . htmlspecialchars($global['webSiteRootURL'] . 'admin/?browse_page=plugins', ENT_QUOTES, 'UTF-8') . '">' . __('Plugins') . '</a>';
            }
        }
    } elseif ($pageMissing) {
        $messages[] = __('This administration page is unavailable. Update AVideo and try again.');
    }
    if (empty($messages)) {
        return '';
    }
    return '<div class="alert ' . $class . '">' . implode(' ', $messages) . ' ' . $link . '</div>';
}

function createTable($pluginName, $filter = [])
{
    $plugin = AVideoPlugin::getObjectData($pluginName);
    $form = is_object($plugin) ? jsonToFormElements($plugin, $filter) : [];
    $disabledRows = [];
    if (isAdvancedConfigurationsDisabled()) {
        // Not editable on this site (see getDangerousPluginSettings()); listed without an input.
        foreach (getDangerousConfigKeys($pluginName) as $key) {
            if (isset($form[$key])) {
                unset($form[$key]);
                $disabledRows[] = "<tr><td>" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "</td><td><small class='text-muted'>" . __('Disabled on this site') . "</small></td></tr>";
            }
        }
    }
    echo '<form class="adminOptionsForm">';
    echo '<input type="hidden" value="' . htmlspecialchars($pluginName, ENT_QUOTES, 'UTF-8') . '" name="pluginName"/>';
    echo '<input type="hidden" value="' . htmlspecialchars(implode('|', array_keys($form)), ENT_QUOTES, 'UTF-8') . '" name="pluginsList"/>';
    echo AVideoPlugin::getDependencyWarningHTML($pluginName);
    echo '<table class="table table-hover admin-settings-table">';
    if (!AVideoPlugin::exists($pluginName)) {
        echo "<tr><td colspan='2'> " . sprintf(__('The %s plugin is not installed. Install it to use this feature.'), htmlspecialchars($pluginName, ENT_QUOTES, 'UTF-8')) . " </td></tr>";
    } else {
        echo implode('', $form) . implode('', $disabledRows);
        if (!empty($form)) {
            echo "<tr><td colspan='2'><button type='submit' class='btn btn-primary'><i class='fa fa-save'></i> " . __('Save') . "</button></td></tr>";
        }
    }
    echo '</table><div class="admin-save-status" role="status" aria-live="polite"></div></form>';
}

if (!class_exists('AdminPluginSettingsValidationException')) {
    // Rejected admin setting; the message is built only from setting names and fixed reasons.
    class AdminPluginSettingsValidationException extends InvalidArgumentException
    {
    }
}

// A setting is editable from an admin form only in the shapes jsonToFormElements() renders.
function isAdminPluginFieldEditable($value)
{
    if (is_scalar($value) || $value === null) {
        return true;
    }
    if (is_object($value) && isset($value->type) && property_exists($value, 'value')) {
        return $value->type === 'textarea' || is_array($value->type) || is_object($value->type) || (is_string($value->type) && is_scalar($value->value));
    }
    return false;
}

// The editable fields of a plugin come from its own data object; a posted pluginsList can only
// narrow this list, never add to it.
function getAdminPluginEditableFields($plugin)
{
    $fields = [];
    foreach (get_object_vars($plugin) as $key => $value) {
        if (isAdminPluginFieldEditable($value)) {
            $fields[] = $key;
        }
    }
    return $fields;
}

// Settings that need more than a type check before they can be stored.
function getAdminPluginFieldValidators()
{
    return [
        'CustomizeAdvanced' => [
            'ffmpegParameters' => 'validateAdminFFmpegParametersSetting',
        ],
    ];
}

function validateAdminFFmpegParametersSetting($value, &$error = '')
{
    if (!function_exists('parseFFmpegConversionParameters')) {
        require_once dirname(__DIR__) . '/objects/functionsFFMPEG.php';
    }
    if (parseFFmpegConversionParameters($value, $reason) === false) {
        $error = "{$reason}. Use only allowed FFmpeg output options, for example: " . getFFmpegConversionDefaultParameters();
        return false;
    }
    return true;
}

/**
 * Checks a submitted value against the setting it replaces: selects must use one of their
 * options, numbers must be numeric, text has a size limit and no NUL bytes, and some settings
 * have their own validator (getAdminPluginFieldValidators()).
 */
function validateAdminPluginValue($pluginName, $key, $current, $value, &$error = '')
{
    $error = '';
    if (!is_scalar($value)) {
        $error = 'invalid value';
        return false;
    }
    $value = (string) $value;
    if (strlen($value) > 65535) {
        $error = 'is too long';
        return false;
    }
    if (strpos($value, "\0") !== false) {
        $error = 'contains a NUL byte';
        return false;
    }
    if (is_object($current)) {
        if (is_array($current->type) || is_object($current->type)) {
            $options = array_map('strval', array_keys((array) $current->type));
            if (!in_array($value, $options, true)) {
                $error = 'must be one of the listed options';
                return false;
            }
        } elseif ($current->type === 'number' && $value !== '' && !is_numeric($value)) {
            $error = 'must be a number';
            return false;
        }
    } elseif ((is_int($current) || is_float($current)) && $value !== '' && !is_numeric($value)) {
        $error = 'must be a number';
        return false;
    }
    // Plugin names are matched without case, like the database lookup that loaded the plugin.
    foreach (getAdminPluginFieldValidators() as $validatorPlugin => $validators) {
        if (strcasecmp($validatorPlugin, (string) $pluginName) === 0 && !empty($validators[$key])
            && !call_user_func_array($validators[$key], [$value, &$error])) {
            return false;
        }
    }
    return true;
}

// Merge only the fields represented by this form; unchecked checkboxes are absent from POST.
// Throws AdminPluginSettingsValidationException, without changing anything, if a value is rejected.
function applyAdminPluginValues($plugin, array $values, array $fields, $pluginName = '')
{
    $result = clone $plugin;
    $errors = [];
    foreach ($fields as $key) {
        if (!is_string($key) || !property_exists($result, $key) || !isAdminPluginFieldEditable($result->$key)) {
            continue;
        }
        $current = $result->$key;
        if (is_bool($current)) {
            $result->$key = isset($values[$key]) && in_array($values[$key], [true, 1, '1', 'true', 'on'], true);
        } elseif (array_key_exists($key, $values)) {
            if (!validateAdminPluginValue($pluginName, $key, $current, $values[$key], $error)) {
                $errors[] = "{$key}: {$error}";
                continue;
            }
            if (is_object($current)) {
                $result->$key = clone $current;
                $result->$key->value = $values[$key];
            } else {
                $result->$key = $values[$key];
            }
        }
    }
    if (!empty($errors)) {
        throw new AdminPluginSettingsValidationException('Settings not saved. ' . implode('; ', $errors));
    }
    return $result;
}

function jsonToFormElements($json, $filter = [])
{
    //var_dump($json, $filter);exit;
    $elements = [];
    foreach ($json as $keyJson => $valueJson) {
        if (!empty($filter) && empty($filter[$keyJson])) {
            continue;
        }
        $inputId = 'admin-setting-' . uniqid();
        $fieldName = htmlspecialchars($keyJson, ENT_QUOTES, 'UTF-8');
        $label = "<label for='{$inputId}'>{$fieldName}</label>";
        $help = '';
        if (!empty($filter[$keyJson])) {
            $help = "<small class=\"form-text text-muted\">{$filter[$keyJson]}</small>";
        }
        $input = '';
        if (is_object($valueJson)) {
            if (!isset($valueJson->type) || !property_exists($valueJson, 'value')) {
                continue;
            }
            if ($valueJson->type === 'textarea') {
                $input = "<textarea class='form-control jsonElement' id='{$inputId}' name='{$fieldName}' pluginType='object'>" . htmlspecialchars((string)$valueJson->value, ENT_QUOTES, 'UTF-8') . "</textarea>";
            } elseif (is_array($valueJson->type) || is_object($valueJson->type)) {
                $input = "<select class='form-control jsonElement' id='{$inputId}' name='{$fieldName}'  pluginType='object'>";
                foreach ($valueJson->type as $key => $value) {
                    $select = '';
                    if ($valueJson->value == $key) {
                        $select = "selected";
                    }
                    $input .= "<option value='" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "' {$select}>" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</option>";
                }
                $input .= "</select>";
            } else {
                if (!is_string($valueJson->type) || !is_scalar($valueJson->value)) {
                    continue;
                }
                $input = "<input class='form-control jsonElement' id='{$inputId}' name='{$fieldName}' "
                . "pluginType='object' type='" . htmlspecialchars($valueJson->type, ENT_QUOTES, 'UTF-8') . "' value='" . htmlspecialchars((string)$valueJson->value, ENT_QUOTES, 'UTF-8') . "'/>";
            }
            $elements[$keyJson] = "<tr><td>{$label} </td><td>{$input}{$help}</td></tr>";
        } elseif (is_bool($valueJson)) {
            //var_dump($keyJson, $valueJson, '---<br>');

            $input = '<div class="material-switch">
                                <input data-toggle="toggle" type="checkbox" id="' . $inputId . '" name="' . $fieldName . '" value="1" ' . ($valueJson ? "checked" : "") . ' >
                                <label for="' . $inputId . '" class="label-primary"></label>
                            </div>';
            $elements[$keyJson] = "<tr><td>{$input}</td><td>{$label}<br>{$help}</td></tr>";
        } elseif (is_scalar($valueJson) || $valueJson === null) {
            $input = "<input class='form-control jsonElement' id='{$inputId}' name='{$fieldName}' type='text' value='" . htmlspecialchars((string)$valueJson, ENT_QUOTES, 'UTF-8') . "'/>";
            $elements[$keyJson] = "<tr><td>{$label} </td><td>{$input}{$help}</td></tr>";
        }
    }
    return $elements;
}

function getPluginSwitch($pluginName)
{
    if (!AVideoPlugin::exists($pluginName)) {
        $input = '<a href="https://streamphp.com/marketplace/" class="btn btn-danger btn-sm btn-xs">'.__('Buy this plugin now').'</a>';
    } else {
        $plugin = AVideoPlugin::loadPluginIfEnabled($pluginName);
        $pluginForced = AVideoPlugin::loadPlugin($pluginName);
        $id = uniqid();
        $uuid = $pluginForced->getUUID();
        $input = '<div class="material-switch">
                                <input class="pluginSwitch" aria-label="' . htmlspecialchars($pluginName, ENT_QUOTES, 'UTF-8') . '" data-toggle="toggle" type="checkbox" id="' . $id . '" uuid="' . $uuid . '" name="' . $pluginName . '" value="1" ' . (!empty($plugin) ? "checked" : "") . ' >
                                <label for="' . $id . '" class="label-primary"></label>
                            </div>';
    }
    return $input;
}
