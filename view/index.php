<?php
$configFile = '../videos/configuration.php';
if (!file_exists($configFile)) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once $configFile;
if(empty($config)){
    // update config file for version 2.8
    $txt = 'require_once $global[\'systemRootPath\'].\'objects/include_config.php\';';
    $myfile = file_put_contents($configFile, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    require_once '../objects/include_config.php';
}
$mode = $config->getMode();
if(!empty($_GET['videoName'])){
    $mode = "Youtube";
}
require 'mode'.$mode.".php";