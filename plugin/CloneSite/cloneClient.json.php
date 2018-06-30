<?php
require_once '../../videos/configuration.php';
set_time_limit(0);
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneSite.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneLog.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/functions.php';
session_write_close();
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";

$log = new CloneLog();

$log->add("Clone Start");

if(!User::isAdmin()){
    $resp->msg = "You cant do this";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$obj = YouPHPTubePlugin::getObjectDataIfEnabled("CloneSite");
if(empty($obj->cloneSiteURL)){
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
    file_put_contents($clonesDir."index.html", '');
}
if (!file_exists($photosDir)) {
    mkdir($photosDir, 0777, true);
}

$url = $obj->cloneSiteURL."plugin/CloneSite/cloneServer.json.php?url=".urlencode($global['webSiteRootURL'])."&key={$obj->myKey}";
// check if it respond
$log->add("Clone: check URL {$url}");
$content = url_get_contents($url);
//var_dump($url, $content);exit;
$json = json_decode($content);

if(!empty($json->error)){
    $resp->msg = "Clone Server message: {$json->msg}";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

// get dump file
$cmd = "wget -O {$clonesDir}{$json->sqlFile} {$obj->cloneSiteURL}videos/cache/clones/{$json->sqlFile}";
$log->add("Clone: Get Dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error: ". print_r($output, true));
}

// remove the first warning line
$file = "{$clonesDir}{$json->sqlFile}";
$contents = file($file, FILE_IGNORE_NEW_LINES);
$first_line = array_shift($contents);
file_put_contents($file, implode("\r\n", $contents));

// restore dump
$cmd = "mysql -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} < {$clonesDir}{$json->sqlFile}";
$log->add("Clone: restore dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error: ". print_r($output, true));
}

$videoFiles = getCloneFilesInfo($videosDir);
$newVideoFiles = detectNewFiles($json->videoFiles, $videoFiles);
$photoFiles = getCloneFilesInfo($photosDir, "userPhoto/");
$newPhotoFiles = detectNewFiles($json->photoFiles, $photoFiles);

$total = count($newVideoFiles);
$count = 0;
// copy videos
foreach ($newVideoFiles as $value) {
    $count++;
    $log->add("Clone: Copying Videos {$count} of {$total} {$value->url}");
    file_put_contents("{$videosDir}{$value->filename}", fopen("$value->url", 'r'));
}

$total2 = count($newPhotoFiles);
$count2 = 0;
// copy Photos
foreach ($newPhotoFiles as $value) {
    $count2++;
    $log->add("Clone: Copying Photos {$count2} of {$total2} {$value->url}");
    file_put_contents("{$photosDir}{$value->filename}", fopen("$value->url", 'r'));
}

// notify to delete dump
$url = $url."&deleteDump={$json->sqlFile}";
// check if it respond
$log->add("Clone: Notify Server to Delete Dump {$url}");
$content2 = url_get_contents($url);
//var_dump($url, $content);exit;
$json2 = json_decode($content);
if(!empty($json2->error)){
    $log->add("Clone: Dump NOT deleted");
}else{
    $log->add("Clone: Dump DELETED");
}

// restore clone plugin configuration
$plugin = new CloneSite();
$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($obj, JSON_UNESCAPED_UNICODE ));
$p->save();

echo json_encode($json);
$log->add("Clone: Complete, Database, {$total} Videos and {$total2} Photos");