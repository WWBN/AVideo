<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');
$row = Plugin::getAll();
if(!User::isAdmin()){
    foreach ($row as $key => $value) {
        if(!empty($row[$key]->installedPlugin['object_data'])){
            $row[$key]->installedPlugin['object_data'] = "";
        }
    }
}
$total = Plugin::getTotal();
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($row).'}';