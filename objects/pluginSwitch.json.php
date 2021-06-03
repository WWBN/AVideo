<?php

header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if(empty($_POST['name'])){
    die('{"error":"'.__("Name can't be blank").'"}');
}
if(empty($_POST['uuid'])){
    die('{"error":"'.__("UUID can't be blank").'"}');
}
$obj = new Plugin(0);
$obj->loadFromUUID($_POST['uuid']);
$obj->setName($_POST['name']);
$obj->setDirName($_POST['dir']);

$_POST['status'] = (empty($_POST['enable']) || $_POST['enable'] === 'false') ? 'inactive' : 'active';

$obj->setStatus($_POST['status']);

if(empty($obj->pluginversion)||is_null($obj->pluginversion)){
    require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
    $p=AVideoPlugin::loadPlugin($_POST['dir']);
    if(is_object($p)){
        $currentVersion=$p->getPluginVersion();
        $obj->setPluginversion($currentVersion);
        Plugin::setCurrentVersionByUuid($_POST['uuid'], $currentVersion);
    }

}

echo '{"status":"'.$obj->save().'"}';
