<?php
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->uploaded = false;
$obj->filename = $_FILES['input-b1']['name'];

$obj->error = true;

if(!User::isAdmin()){
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
    $destination = "{$global['systemRootPath']}plugin/{$path_parts['filename']}";
    $zip = new ZipArchive;
    if ($zip->open($_FILES['input-b1']['tmp_name'], ZipArchive::CREATE) === TRUE) {
        $zip->extractTo($destinationFolder);
        $zip->close();
        $id = true;
    } else {
        $id = false;
    }
}
if($id){
    $obj->msg = "Your file was ok";
    $obj->uploaded = true;
    unset($obj->error);
}else{    
    $obj->msg = "Your file was NOT ok";
}
die(json_encode($obj));