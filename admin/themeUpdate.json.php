<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    forbiddenPage('Admin Only');
}

if(empty($_REQUEST['themeLight'])){
    $_REQUEST['themeLight'] = 'default';
}

if(empty($_REQUEST['themeDark'])){
    $_REQUEST['themeDark'] = 'netflix';
}


$config = new AVideoConf();
if(!empty($_REQUEST['theme'])){
    $config->setTheme($_REQUEST['theme'], @$_REQUEST["defaultTheme"]);
}else if(!empty($_REQUEST['themeLight'])){
    $config->setThemes($_REQUEST["themeLight"], $_REQUEST["themeDark"], $_REQUEST["defaultTheme"]);
}

$obj = new stdClass();
$obj->error = empty($config->save());

$config = new AVideoConf();
$config->load('', true);
$obj->themes = $config->getThemes();

echo json_encode($obj);
