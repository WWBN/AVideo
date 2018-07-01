<?php
$time_start = microtime(true); 
require_once '../../videos/configuration.php';
set_time_limit(0);
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneSite.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneLog.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/functions.php';
session_write_close();
header('Content-Type: application/json');

$totalSteps = 7;

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";

$log = new CloneLog();

$log->add("Clone: Clone Start");

if (!User::isAdmin()) {
    $resp->msg = "You cant do this";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$obj = YouPHPTubePlugin::getObjectDataIfEnabled("CloneSite");
if (empty($obj->cloneSiteURL)) {
    $resp->msg = "Your Clone Site URL is empty, please click on the Edit parameters buttons and place an YouPHPTube URL";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$videosSite = "{$obj->cloneSiteURL}videos/";
$videosDir = "{$global['systemRootPath']}videos/";
$clonesDir = "{$videosDir}cache/clones/";
$photosDir = "{$videosDir}userPhoto/";
$photosSite = "{$videosSite}userPhoto/";
if (!file_exists($clonesDir)) {
    mkdir($clonesDir, 0777, true);
    file_put_contents($clonesDir . "index.html", '');
}
if (!file_exists($photosDir)) {
    mkdir($photosDir, 0777, true);
}

$url = $obj->cloneSiteURL . "plugin/CloneSite/cloneServer.json.php?url=" . urlencode($global['webSiteRootURL']) . "&key={$obj->myKey}";
// check if it respond
$log->add("Clone (1 of {$totalSteps}): Asking the Server the database and the files");
$content = url_get_contents($url);
//var_dump($url, $content);exit;
$json = json_decode($content);

if (empty($json)) {
    $resp->msg = "Clone Server Unknow ERROR";
    $log->add("Clone: Server Unknow ERROR");
    die(json_encode($resp));
}

if (!empty($json->error)) {
    $resp->msg = "Clone Server message: {$json->msg}";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$log->add("Clone: Good start! the server has answered");

// get dump file
$cmd = "wget -O {$clonesDir}{$json->sqlFile} {$obj->cloneSiteURL}videos/cache/clones/{$json->sqlFile}";
$log->add("Clone (2 of {$totalSteps}): Geting MySQL Dump file");
exec($cmd . " 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error: " . print_r($output, true));
}
$log->add("Clone: Nice! we got the MySQL Dump file");

// remove the first warning line
$file = "{$clonesDir}{$json->sqlFile}";
$contents = file($file, FILE_IGNORE_NEW_LINES);
$first_line = array_shift($contents);
file_put_contents($file, implode("\r\n", $contents));

$log->add("Clone (3 of {$totalSteps}): Overwriting our database with the server database");
// restore dump
$cmd = "mysql -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} < {$clonesDir}{$json->sqlFile}";
exec($cmd . " 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error: " . print_r($output, true));
}
$log->add("Clone: Great! we overwrite it with success.");

$videoFiles = getCloneFilesInfo($videosDir);
$newVideoFiles = detectNewFiles($json->videoFiles, $videoFiles);
$photoFiles = getCloneFilesInfo($photosDir, "userPhoto/");
$newPhotoFiles = detectNewFiles($json->photoFiles, $photoFiles);

$total = count($newVideoFiles);
$count = 0;

if (!empty($total)) {
    $log->add("Clone (4 of {$totalSteps}): Now we will copy {$total} new video files, usually this takes a while.");
    // copy videos
    foreach ($newVideoFiles as $value) {
        $count++;
        $log->add("Clone: Copying Videos {$count} of {$total} {$value->url}");
        file_put_contents("{$videosDir}{$value->filename}", fopen("$value->url", 'r'));
    }
    $log->add("Clone: Copying video files done.");
} else {
    $log->add("Clone (4 of {$totalSteps}): There is no new video file to copy.");
}

$total2 = count($newPhotoFiles);
$count2 = 0;

if (!empty($total2)) {
    $log->add("Clone (5 of {$totalSteps}): Now we will copy {$total2} new user photo files.");
// copy Photos
    foreach ($newPhotoFiles as $value) {
        $count2++;
        $log->add("Clone: Copying Photos {$count2} of {$total2} {$value->url}");
        file_put_contents("{$photosDir}{$value->filename}", fopen("$value->url", 'r'));
    }
    $log->add("Clone: Copying user photo files done.");
} else {
    $log->add("Clone (5 of {$totalSteps}): There is no new user photo file to copy.");
}

// notify to delete dump
$url = $url . "&deleteDump={$json->sqlFile}";
// check if it respond
$log->add("Clone (6 of {$totalSteps}): Notify Server to Delete Dump");
$content2 = url_get_contents($url);
//var_dump($url, $content);exit;
$json2 = json_decode($content);
if (!empty($json2->error)) {
    $log->add("Clone: Dump NOT deleted");
} else {
    $log->add("Clone: Dump DELETED");
}


$log->add("Clone (7 of {$totalSteps}): Resotre the Clone Configuration");
// restore clone plugin configuration
$plugin = new CloneSite();
$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($obj, JSON_UNESCAPED_UNICODE));
$p->save();

echo json_encode($json);
$log->add("Clone: Complete, Database, {$total} Videos and {$total2} Photos");

$time_end = microtime(true);
//dividing with 60 will give the execution time in minutes otherwise seconds
$execution_time = ($time_end - $time_start)/60;
//execution time of the script
$log->add('Total Execution Time: '.$execution_time.' Mins');