<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once '../videos/configuration.php';
if(empty($config)){
    require_once '../objects/include_config.php';
}
$mode = $config->getMode();
if(!empty($_GET['videoName'])){
    $mode = "Youtube";
}
require 'mode'.$mode.".php";