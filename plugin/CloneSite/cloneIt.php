<?php

require_once '../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";
$resp->url = $_GET['url'];
$resp->key = $_GET['key'];
$resp->sqlFile = "";
$resp->videosFile = "";
$resp->userPhoto = "";

// check if the url is allowed to clone it
$canClone = Clones::thisURLCanCloneMe($resp->url, $resp->key);
if(empty($canClone->canClone)){
    $resp->msg = $canClone->msg;
    die(json_encode($resp));
}

$clonesDir = $global['systemRootPath']."videos/cache/clones/";

if (!file_exists($clonesDir)) {
    mkdir($clonesDir, 0777, true);
    file_put_contents($clonesDir."index.html", '');
}

$resp->sqlFile = uniqid('Clone_mysqlDump_').".sql";
$resp->videosFile = uniqid('Clone_videos_').".tar";
$resp->userPhoto = uniqid('Clone_userPhoto_').".tar";
$last_clone_request = $canClone->clone->getLast_clone_request();
$lastRequest = str_replace('+00:00', 'Z', gmdate('c', strtotime($last_clone_request)));


// update this clone last request
$resp->error = !$canClone->clone->updateLastCloneRequest();

// get mysql dump
$cmd = "mysqldump -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} > {$clonesDir}{$resp->sqlFile}";
error_log("Clone: Dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}
// get videos newer then last clone
$cmd = "find . -newermt '{$lastRequest}' -print | xargs tar -rf {$clonesDir}{$resp->videosFile}";
error_log("Clone: Find Videos {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}

// get usersPhotos 
$cmd = "tar -zcvf {$clonesDir}{$resp->userPhoto} {$global['systemRootPath']}videos/userPhoto";
error_log("Clone: Get Photos {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}


echo json_encode($resp);