<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
$mode = $config->getMode();
if(!empty($_GET['videoName'])){
    $mode = "Youtube";
}
require 'mode'.$mode.".php";