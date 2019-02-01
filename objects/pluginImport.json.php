<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->uploaded = false;
$obj->filename = $_FILES['input-b1']['name'];

if (!empty($global['disableAdvancedConfigurations'])) {
    die(json_encode($obj));
}

if (!User::isAdmin()) {
    $obj->msg = "You are not admin";
    die(json_encode($obj));
}

$allowed = array('zip');
$path_parts = pathinfo($_FILES['input-b1']['name']);
$extension = $path_parts['extension'];

if (!in_array(strtolower($extension), $allowed)) {
    $obj->msg = "File extension error (" . $_FILES['input-b1']['name'] . "), we allow only (" . implode(",", $global['allowed']) . ")";
    die(json_encode($obj));
}


if (strcasecmp($extension, 'zip') == 0) {
    //$id =  File::encodeAndsaveZipFile($_FILES['input-b1']['tmp_name'], $_FILES['input-b1']['name'], $key);
    $destination = "{$global['systemRootPath']}plugin/";
    $obj->destination = $destination;
    $path = $_FILES['input-b1']['tmp_name'];    
    $dir = "{$destination}/{$path_parts['filename']}";
    if(is_dir($dir)){
        exec("rm -R {$dir}");
    }
    exec("unzip {$path} -d {$destination}");
}
die(json_encode($obj));
