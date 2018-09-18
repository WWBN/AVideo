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


if(empty($_POST['enable']) || $_POST['enable']==="false"){
    $_POST['status'] = "inactive";
}else{
    $_POST['status'] = "active";
}
$obj->setStatus($_POST['status']);


if(empty($obj->pluginversion)||is_null($obj->pluginversion)){
    require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
    $p=YouPHPTubePlugin::loadPlugin($_POST['name']); 
    $currentVersion=$p->getPluginVersion();
    $obj->pluginversion=$currentVersion;
    Plugin::setCurrentVersionByUuid($_POST['uuid'], $currentVersion);
    
}



echo '{"status":"'.$obj->save().'"}';
