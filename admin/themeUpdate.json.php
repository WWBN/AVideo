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

$config = new AVideoConf();
if(!empty($_POST['theme'])){
    $config->setTheme($_POST['theme'], @$_REQUEST["defaultTheme"]);
}else if(!empty($_POST['themeLight'])){
    $config->setThemes($_REQUEST["themeLight"], $_REQUEST["themeDark"], $_REQUEST["defaultTheme"]);
}

$obj = new stdClass();
$obj->error = empty($config->save());

$config = new AVideoConf();
$obj->themes = $config->getThemes();

echo json_encode($obj);
