<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneSite.php';
session_write_close();
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";

error_log("Clone Start");

if(!User::isAdmin()){
    $resp->msg = "You cant do this";
    error_log("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$obj = YouPHPTubePlugin::getObjectDataIfEnabled("CloneSite");
if(empty($obj->cloneSiteURL)){
    $resp->msg = "Your Clone Site URL is empty, please click on the Edit parameters buttons and place an YouPHPTube URL";
    error_log("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$clonesDir = $global['systemRootPath']."videos/cache/clones/";

if (!file_exists($clonesDir)) {
    mkdir($clonesDir, 0777, true);
    file_put_contents($clonesDir."index.html", '');
}


$url = $obj->cloneSiteURL."plugin/CloneSite/cloneIt.php?url=".urlencode($global['webSiteRootURL'])."&key={$obj->myKey}";
// check if it respond
error_log("Clone: check URL {$url}");
$content = url_get_contents($url);
//var_dump($content);
$json = json_decode($content);

// get dump file
$cmd = "wget -O {$clonesDir}{$json->sqlFile} {$obj->cloneSiteURL}videos/cache/clones/{$json->sqlFile}";
error_log("Clone: Get Dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}
// restore dump
$cmd = "mysql -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} youPHPTube < {$clonesDir}{$json->sqlFile}";
error_log("Clone: restore dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}
// get files
$cmd = "wget -O {$clonesDir}{$json->videosFile} {$obj->cloneSiteURL}videos/cache/clones/{$json->videosFile}";
error_log("Clone: get files {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}
// overwrite files
$cmd = "tar -xf {$clonesDir}{$json->videosFile} -C {$global['systemRootPath']}videos/";
error_log("Clone: overwrite files {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}
// remove sql 

//remove tar


// restore clone plugin configuration
$plugin = new CloneSite();
$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($obj, JSON_UNESCAPED_UNICODE ));
$p->save();

echo json_encode($json);