<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

_mysql_close();
session_write_close();

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->pid = "none";
$obj->time = time();


$videosDir = getVideosDir();
$pidFile = "{$videosDir}socketPID.log";

if(file_exists($pidFile)){
    $oldObj = json_decode(file_get_contents($pidFile));
    if(!empty($oldObj)){
        killProcess($oldObj->pid);
    }
}

$cmd = "E:/xampp/php/php.exe {$global['systemRootPath']}plugin/Socket/server.php";
$obj->pid = exec("php {$global['systemRootPath']}plugin/Socket/server.php", $o, $i);
var_dump($cmd, $o, $i);

file_put_contents($pidFile, json_encode($obj));

die(json_encode($obj));