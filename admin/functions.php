<?php

function createTable($pluginName, $filter = array()){
    echo '<form class="adminOptionsForm"><table class="table table-hover">';
    echo '<input type="hidden" value="'.$pluginName.'" name="pluginName"/>';
    echo '<input type="hidden" value="'.implode("|", array_keys($filter)).'" name="pluginsList"/>';
    $pluginsList = array();
    if (!YouPHPTubePlugin::exists($pluginName)) {
        echo "<tr><td colspan='2'> Sorry you do not have the plugin </td></tr>";
    }else{
        $plugin = YouPHPTubePlugin::getObjectData($pluginName);
        if(!empty($plugin)){
            $form = jsonToFormElements($plugin,$filter);
            //var_dump($form);
            echo implode("", $form);
        }
        
        echo "<tr><td colspan='2'> <button class='btn btn-block btn-primary'><i class='fa fa-save'></i> Save</button> </td></tr>";
    }
    echo '</table></form>';
}

function jsonToFormElements($json, $filter = array()) {

    $elements = array();
    foreach ($json as $key => $value) {
        if (!empty($filter) && empty($filter[$key])) {
            continue;
        }
        $label = "<label>{$key}</label>";
        $help = "";
        if (!empty($filter[$key])) {
            $help = "<small class=\"form-text text-muted\">{$filter[$key]}</small>";
        }
        $input = "";
        if (is_object($value)) {
            if ($value->type === 'textarea') {
                $input = "<textarea class='form-control jsonElement' name='{$key}' pluginType='object'>{$value->value}</textarea>";
            } else {
                $input = "<input class='form-control jsonElement' name='{$key}' pluginType='object' type='{$value->type}' value='{$value->value}'/>";
            }
            $elements[] = "<tr><td>{$label} </td><td>{$input}{$help}</td></tr>";
        } else if (is_bool($value)) {
            $id = uniqid();
            $input = '<div class="material-switch">
                                <input data-toggle="toggle" type="checkbox" id="' . $key . $id . '" name="' . $key . '" value="1" ' . ($value ? "checked" : "") . ' >
                                <label for="' . $key . $id . '" class="label-primary"></label>
                            </div>';
            $elements[] = "<tr><td>{$input}</td><td>{$label}{$help}</td></tr>";
        } else {
            $input = "<input class='form-control jsonElement' name='{$key}' type='text' value='{$value}'/>";
            $elements[] = "<tr><td>{$label} </td><td>{$input}{$help}</td></tr>";
        }
    }
    return $elements;
}

function getPluginSwitch($pluginName) {
    if(!YouPHPTubePlugin::exists($pluginName)){
       $input = '<a href="https://www.youphptube.com/plugins/" class="btn btn-danger btn-sm btn-xs">Buy this plugin now</a>'; 
    }else{
        $plugin = YouPHPTubePlugin::loadPluginIfEnabled($pluginName);
        $pluginForced = YouPHPTubePlugin::loadPlugin($pluginName);
        $id = uniqid();
        $uuid = $pluginForced->getUUID();
        $input = '<div class="material-switch">
                                <input class="pluginSwitch" data-toggle="toggle" type="checkbox" id="' . $id . '" uuid="' . $uuid . '" name="' . $pluginName . '" value="1" ' . (!empty($plugin) ? "checked" : "") . ' >
                                <label for="' . $id . '" class="label-primary"></label>
                            </div>';
    }
    return $input;
}
