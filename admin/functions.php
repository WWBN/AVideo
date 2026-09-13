<?php
function createTable($pluginName, $filter = [])
{
    $plugin = AVideoPlugin::getObjectData($pluginName);
    $form = is_object($plugin) ? jsonToFormElements($plugin, $filter) : [];
    echo '<form class="adminOptionsForm">';
    echo '<input type="hidden" value="' . htmlspecialchars($pluginName, ENT_QUOTES, 'UTF-8') . '" name="pluginName"/>';
    echo '<input type="hidden" value="' . htmlspecialchars(implode('|', array_keys($form)), ENT_QUOTES, 'UTF-8') . '" name="pluginsList"/>';
    echo AVideoPlugin::getDependencyWarningHTML($pluginName);
    echo '<table class="table table-hover admin-settings-table">';
    if (!AVideoPlugin::exists($pluginName)) {
        echo "<tr><td colspan='2'> " . __('Sorry you do not have the plugin') . " </td></tr>";
    } else {
        echo implode('', $form);
        if (!empty($form)) {
            echo "<tr><td colspan='2'><button type='submit' class='btn btn-primary'><i class='fa fa-save'></i> " . __('Save') . "</button></td></tr>";
        }
    }
    echo '</table><div class="admin-save-status" role="status" aria-live="polite"></div></form>';
}

// Merge only the fields represented by this form; unchecked checkboxes are absent from POST.
function applyAdminPluginValues($plugin, array $values, array $fields)
{
    $result = clone $plugin;
    foreach ($fields as $key) {
        if (!property_exists($result, $key)) {
            continue;
        }
        $current = $result->$key;
        if (is_bool($current)) {
            $result->$key = isset($values[$key]) && in_array($values[$key], [true, 1, '1', 'true', 'on'], true);
        } elseif (array_key_exists($key, $values) && is_scalar($values[$key])) {
            if (is_object($current) && property_exists($current, 'value')) {
                $result->$key = clone $current;
                $result->$key->value = $values[$key];
            } elseif (is_scalar($current) || $current === null) {
                $result->$key = $values[$key];
            }
        }
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
