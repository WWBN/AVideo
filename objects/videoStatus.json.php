<?php

error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

$time0 = microtime();

if (!User::canUpload() || empty($_POST['id'])) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (!is_array($_POST['id'])) {
    $_POST['id'] = array($_POST['id']);
}
$finish = microtime();
$total_time = round(($finish - $time0), 4);
error_log("Video Status {$total_time} Line = " . __LINE__);
$time = microtime();

require_once 'video.php';

$id = 0;
foreach ($_POST['id'] as $value) {
    $finish = microtime();
    $total_time = round(($finish - $time), 4);
    error_log("Video Status {$total_time} Line = " . __LINE__);
    $time = microtime();
    $obj = new Video("", "", $value);
    if (empty($obj)) {
        die("Object not found");
    }
    if (!$obj->userCanManageVideo()) {
        $obj->msg = __("You can not Manage This Video");
        die(json_encode($obj));
    }
    $obj->setStatus($_POST['status']);
    $resp = $value;
    $finish = microtime();
    $total_time = round(($finish - $time), 4);
    error_log("Video Status {$total_time} Line = " . __LINE__);
    $time = microtime();
}
$finish = microtime();
$total_time = round(($finish - $time0), 4);
error_log("Video Status {$total_time} Line = " . __LINE__);
echo '{"status":"' . !empty($resp) . '"}';
