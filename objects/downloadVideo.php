<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

$obj = new stdClass();

header('Content-Type: application/json');
$cmd = "youtube-dl -e {$_POST['videoURL']}";
exec($cmd . "  2>&1", $output, $return_val);
if ($return_val !== 0) {
    $obj->error = "youtube-dl get title ERROR** ". print_r($output, true);
    die(json_encode($obj));
} else {
    $filename = preg_replace("/[^A-Za-z0-9]/", "", $output[0]);
    $userId = User::getId();
    $cmd = "/usr/bin/php -f youtubeDl.php {$filename} {$_POST['videoURL']} {$userId} > /dev/null 2>/dev/null &";
    exec($cmd);
    
    $obj->command = $cmd;
    die(json_encode($obj));
}

