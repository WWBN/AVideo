<?php

require_once '../../videos/configuration.php';
set_time_limit(0);
_session_write_close();
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/functions.php';
header('Content-Type: application/json');

$videosDir = Video::getStoragePath() . "";
$clonesDir = "{$videosDir}clones/";
$photosDir = "{$videosDir}userPhoto/";

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";
$resp->url = $_GET['url'];
$resp->key = $_GET['key'];
$resp->useRsync = intval($_GET['useRsync']);
$resp->videosDir = Video::getStoragePath() . "";
$resp->sqlFile = "";
$resp->videoFiles = [];
$resp->photoFiles = [];

$objClone = AVideoPlugin::getObjectDataIfEnabled("CloneSite");
if (empty($objClone)) {
    $resp->msg = "CloneSite is not enabled on the Master site";
    die(json_encode($resp));
}


if (empty($resp->key)) {
    $resp->msg = "Key cannot be blank";
    die(json_encode($resp));
}

// check if the url is allowed to clone it
$canClone = Clones::thisURLCanCloneMe($resp->url, $resp->key);
if (empty($canClone->canClone)) {
    $resp->msg = $canClone->msg;
    die(json_encode($resp));
}

if (!empty($_GET['deleteDump'])) {
    $resp->error = !unlink("{$clonesDir}{$_GET['deleteDump']}");
    $resp->msg = "Delete Dump {$_GET['deleteDump']}";
    die(json_encode($resp));
}

if (!is_dir($clonesDir)) {
    $mkdir = mkdir($clonesDir, 0777, true);
    $put = file_put_contents($clonesDir . "index.html", '');
    _error_log("Clone: create dir {$clonesDir} ".json_encode(array(is_dir($clonesDir), $mkdir, $put)));
}else{
    _error_log("Clone: dir {$clonesDir} already exists");
}

$resp->sqlFile = uniqid('Clone_mysqlDump_') . ".sql";
// update this clone last request
$resp->error = !$canClone->clone->updateLastCloneRequest();

// get mysql dump
// Get a list of all tables except CachesInDB
$tables = array();
$res = sqlDAL::readSql("SHOW TABLES");
$row = sqlDAL::fetchAllAssoc($res);
foreach ($row as $value) {    
    $firstElement = reset($value);
    if ($firstElement != 'CachesInDB') {
        $tables[] = $firstElement;
    }
}
$tablesList = implode(" ", $tables);
$sqlFile = "{$clonesDir}{$resp->sqlFile}";
// Then use that list in the mysqldump command
$cmd = "mysqldump -u {$mysqlUser} -p'{$mysqlPass}' --host {$mysqlHost} ".
" --default-character-set=utf8mb4 {$mysqlDatabase} {$tablesList} > $sqlFile";
//$cmd = "mysqldump -u {$mysqlUser} -p'{$mysqlPass}' --host {$mysqlHost} --skip-set-charset -N --routines --skip-triggers --databases {$mysqlDatabase} > {$clonesDir}{$resp->sqlFile}";
_error_log("Clone: Dump to $sqlFile");
exec($cmd . " 2>&1", $output, $return_val);
if (!file_exists($sqlFile)) {
    _error_log("Clone: error {$sqlFile} does not exists" . json_encode($output));
}else{
    _error_log("Clone: {$sqlFile} exists ");
}
if ($return_val !== 0) {
    _error_log("Clone Error: " . print_r($output, true));
}

if (empty($resp->useRsync)) {
    $resp->videoFiles = getCloneFilesInfo($videosDir);
    $resp->photoFiles = getCloneFilesInfo($photosDir, "userPhoto/");
}

echo json_encode($resp);
