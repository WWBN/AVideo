<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once '../videos/configuration.php';
$mode = $config->getMode();
if(!empty($_GET['videoName'])){
    $mode = "Youtube";
}
require 'mode'.$mode.".php";