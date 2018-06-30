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
$last_clone_request = $canClone->clone->getLast_clone_request();
$lastRequest = str_replace('+00:00', 'Z', gmdate('c', strtotime($last_clone_request)));

// get mysql dump
$cmd = "mysqldump -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} > {$resp->sqlFile}";
exec($cmd);

// get videos newer then last clone
$cmd = "find . -newermt '{$lastRequest}' -print | xargs tar -rf {$resp->videosFile}";
exec($cmd);

// update this clone last request
$resp->error = !$canClone->clone->updateLastCloneRequest();

echo json_encode($resp);