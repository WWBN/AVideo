<?php
$configFile = 'videos/configuration.php';
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
if (empty($config)) {
    // update config file for version 2.8
    $txt = 'require_once $global[\'systemRootPath\'].\'objects/include_config.php\';';
    $myfile = file_put_contents($configFile, $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
    require_once 'objects/include_config.php';
}

require_once $global['systemRootPath'].'plugin/YouPHPTubePlugin.php';
$firstPage = YouPHPTubePlugin::getFirstPage();
if (empty($firstPage) || !empty($_GET['videoName']) || !empty($_GET['playlist_id']) || !empty($_GET['liveVideoName'])) {
    require $global['systemRootPath'].'view/modeYoutube.php';
}else{
    require $firstPage;
}
include $global['systemRootPath'].'objects/include_end.php';