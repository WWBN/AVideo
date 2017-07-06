<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$userId = User::getId();
$obj = new stdClass();
if(empty($userId)){
    $obj->type = "error";
    $obj->title = __("Sorry!");
    $obj->text = sprintf(__("Your user is invalid"));
    die(json_encode($obj));
}

header('Content-Type: application/json');
$cmd = "youtube-dl -e {$_POST['videoURL']}";
exec($cmd . "  2>&1", $output, $return_val);
if ($return_val !== 0) {
    $obj->error = "youtube-dl get title ERROR** ". print_r($output, true);
    $obj->type = "warning";
    $obj->title = __("Sorry!");
    $obj->text = sprintf(__("We could not get the title of your video (%s) go to %s to fix it"), $output[0], "<a href='https://github.com/DanielnetoDotCom/YouPHPTube/wiki/youdtube-dl-failed-to-extract-signature'>https://github.com/DanielnetoDotCom/YouPHPTube/wiki/youdtube-dl-failed-to-extract-signature</a>");
    $obj->command = $cmd;
    $output[] = "youdtube-dl failed to extract signature";
    die(json_encode($obj));
}else{
    $title = end($output);
    $obj->type = "success";
    $obj->title = __("Congratulations!");
    $obj->text = sprintf(__("Your video (%s) is downloading"), $title);
    $filename = preg_replace("/[^A-Za-z0-9]+/", "_", $title);
    $filename = uniqid("{$filename}_", true).".mp4";
    $cmd = PHP_BINDIR." -f youtubeDl.php {$filename} {$_POST['videoURL']} {$userId} > /dev/null 2>/dev/null &";
    exec($cmd);
    $obj->command = $cmd;
    $obj->filename = $filename;
    $obj->response = print_r($output, true);
    die(json_encode($obj));
}

