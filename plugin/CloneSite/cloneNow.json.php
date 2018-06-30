<?php
require_once '../../videos/configuration.php';
set_time_limit(0);
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

// remove the first warning line
$file = "{$clonesDir}{$json->sqlFile}";
$contents = file($file, FILE_IGNORE_NEW_LINES);
$first_line = array_shift($contents);
file_put_contents($file, implode("\r\n", $contents));

// restore dump
$cmd = "mysql -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} < {$clonesDir}{$json->sqlFile}";
error_log("Clone: restore dump {$cmd}");
exec($cmd." 2>&1", $output, $return_val);
if ($return_val !== 0) {
    error_log("Clone Error: ". print_r($output, true));
}

$destination = "{$global['systemRootPath']}videos/";
$videosList = strip_tags(file_get_contents($videosSite));
foreach(preg_split("/((\r?\n)|(\r\n?))/", $videosList) as $line){    
    preg_match("/(.*)[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}.*/", $line, $matches);
    if(!empty($matches[1])){
        if($matches[1]=='configuration.php'){
            continue;
        }
        if(file_exists("{$destination}{$matches[1]}") && filesize("{$destination}{$matches[1]}")>10){
            error_log("Clone: SKIP Copying Photo {$destination}{$matches[1]}");
            continue;
        }
        error_log("Clone: Copying {$destination}{$matches[1]}");
        file_put_contents("{$destination}{$matches[1]}", fopen("{$videosSite}{$matches[1]}", 'r'));
    }    
} 

$destination = "{$global['systemRootPath']}videos/userPhoto/";
$videosList = strip_tags(file_get_contents($photosSite));
foreach(preg_split("/((\r?\n)|(\r\n?))/", $videosList) as $line){    
    preg_match("/(.*)[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}.*/", $line, $matches);
    if(!empty($matches[1])){
        if($matches[1]=='configuration.php'){
            continue;
        }
        if(file_exists("{$destination}{$matches[1]}") && filesize("{$destination}{$matches[1]}")>10){
            error_log("Clone: SKIP Copying Photo {$destination}{$matches[1]}");
            continue;
        }
        error_log("Clone: Copying Photo {$destination}{$matches[1]}");
        file_put_contents("{$destination}{$matches[1]}", fopen("{$photosSite}{$matches[1]}", 'r'));
    }    
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