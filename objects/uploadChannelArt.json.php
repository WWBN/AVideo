<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
if (!User::isLogged()) {
    $obj->msg = 'You can\'t edit this file';
    die(json_encode($obj));
}

$users_id = User::getId();

$channelArtRelativePath = User::getBackgroundURLFromUserID($users_id, '', true);
$obj->imageJPG = $global['systemRootPath'] . $channelArtRelativePath;
$obj->imagePNG = "{$obj->imageJPG}.png";

$obj->imagePNGResponse = saveCroppieImage($obj->imagePNG, "image");
$obj->imageJPGResponse = convertImage($obj->imagePNG, $obj->imageJPG, 70);
$obj->variations = array();
//var_dump($obj);
if (file_exists($obj->imagePNG)) {
    unlink($obj->imagePNG);
}
try {
    _error_log("uploadChannelArt {$obj->imageJPG} ". json_encode($obj->imageJPGResponse));
    if(preg_match('/.png$/i', $obj->imageJPG)){
        $im = imagecreatefrompng($obj->imageJPG);
    }else{
        $im = imagecreatefromjpeg($obj->imageJPG);
    }
    $width = imagesx($im);
    $height = imagesy($im);
} catch (Exception $exc) {
    $obj->msg = $exc->getTraceAsString();
    $obj->imageJPG = $obj->imageJPG;
    die(json_encode($obj));
}


foreach (User::$channel_art as $value) {
    $x = ($width - $value[1])/2;
    $y = ($height - $value[2])/2;
    $obj->variations[$value[0]] = false;
    $im2 = imagecrop($im, ['x' => $x , 'y' => $y , 'width' => $value[1], 'height' => $value[2]]);
    if ($im2 !== FALSE) {
        $obj->variations[$value[0]] = User::getBackgroundURLFromUserID($users_id, $value[0], true);
        imagejpeg($im2, $global['systemRootPath'] . $obj->variations[$value[0]]);
        imagedestroy($im2);
    }
    imagedestroy($im);
}

if(!User::isAdmin()){
    unset($obj->imageJPG);
    unset($obj->imagePNG);
}
$obj->error = false;
die(json_encode($obj));
