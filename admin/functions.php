<?php

function createTable($pluginName, $filter = array()){
    $plugin = YouPHPTubePlugin::getObjectData($pluginName);
    if(empty($filter)){
        foreach ($plugin as $keyJson => $valueJson) {
            $filter[$keyJson] = "&nbsp;";
        }
    }
    //var_dump($filter);exit;
    echo '<form class="adminOptionsForm">';
    echo '<input type="hidden" value="'.$pluginName.'" name="pluginName"/>';
    echo '<input type="hidden" value="'.implode("|", array_keys($filter)).'" name="pluginsList"/>';
    echo '<table class="table table-hover">';
    $pluginsList = array();
    if (!YouPHPTubePlugin::exists($pluginName)) {
        echo "<tr><td colspan='2'> Sorry you do not have the plugin </td></tr>";
    }else{
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
    //var_dump($json, $filter);exit;
    $elements = array();
    foreach ($json as $keyJson => $valueJson) {
        if (!empty($filter) && empty($filter[$keyJson])) {
            continue;
        }
        $label = "<label>{$keyJson}</label>";
        $help = "";
        if (!empty($filter[$keyJson])) {
            $help = "<small class=\"form-text text-muted\">{$filter[$keyJson]}</small>";
        }
        $input = "";
        if (is_object($valueJson)) {
            if ($valueJson->type === 'textarea') {
                $input = "<textarea class='form-control jsonElement' name='{$keyJson}' pluginType='object'>{$valueJson->value}</textarea>";
            } else {                var_dump($keyJson, $valueJson);
                $input = "<input class='form-control jsonElement' name='{$keyJson}' pluginType='object' type='{$valueJson->type}' value='{$valueJson->value}'/>";
            }
            $elements[] = "<tr><td>{$label} </td><td>{$input}{$help}</td></tr>";
        } else if (is_bool($valueJson)) {
            $id = uniqid();
            $input = '<div class="material-switch">
                                <input data-toggle="toggle" type="checkbox" id="' . $keyJson . $id . '" name="' . $keyJson . '" value="1" ' . ($valueJson ? "checked" : "") . ' >
                                <label for="' . $keyJson . $id . '" class="label-primary"></label>
                            </div>';
            $elements[] = "<tr><td>{$input}</td><td>{$label}<br>{$help}</td></tr>";
        } else {
            $input = "<input class='form-control jsonElement' name='{$keyJson}' type='text' value='{$valueJson}'/>";
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
