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
    $obj->type = "error";
    $obj->title = __("Sorry!");
    $obj->text = sprintf(__("We could not get your video (%s)"), $output[0]);
    $obj->command = $cmd;
    die(json_encode($obj));
} else {
    $title = end($output);
    $filename = preg_replace("/[^A-Za-z0-9]/", "", $title);
    $filename = uniqid("{$filename}_", true).".mp4";
    $userId = User::getId();
    $cmd = "/usr/bin/php -f youtubeDl.php {$filename} {$_POST['videoURL']} {$userId} > /dev/null 2>/dev/null &";
    exec($cmd);
    $obj->type = "success";
    $obj->title = __("Congratulations!");
    $obj->text = sprintf(__("Your video (%s) is downloading"), $title);
    $obj->command = $cmd;
    $obj->filename = $filename;
    $obj->response = print_r($output, true);
    die(json_encode($obj));
}

