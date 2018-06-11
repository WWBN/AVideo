<?php

header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (empty($_POST['name'])) {
    die('{"error":"' . __("Name can't be blank") . '"}');
}
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->name = $_POST['name'];
$templine = '';
$fileName = Plugin::getDatabaseFileName($_POST['name']);
$obj->fileName = $fileName;
if ($fileName) {
    $lines = file($fileName);
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;
        $templine .= $line;
        if (substr(trim($line), -1, 1) == ';') {
            if (!$global['mysqli']->query($templine)) {
                $obj->msg = ('Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                die(json_encode($obj));
            }
            $templine = '';
        }
    }
    $obj->error = false;
    $obj->msg = "All queries executed";
} else {
    $obj->msg = "File not found";
}

die(json_encode($obj));
