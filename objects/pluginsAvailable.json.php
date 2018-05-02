<?php
require_once './plugin.php';
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');
$row = Plugin::getAvailablePlugins();
$total = count($row);

if(!User::isAdmin()){
    foreach ($row as $key => $value) {
        if(!empty($row[$key]->installedPlugin['object_data'])){
            $row[$key]->installedPlugin['object_data'] = "";
        }
    }
}

echo '{  "current": 1,"rowCount": '.$total.', "total": '.$total.', "rows":'. json_encode($row).'}';