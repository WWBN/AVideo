<?php
header('Content-Type: application/json');

if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] .'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

$type = !empty($_POST['type'])?$_POST['type']:"";

require_once 'video.php';

$obj = new Video("", "", $_POST['id']);
if(empty($obj)){
    croak(["error" => "Video not found"]);
}

$currentRotation = $obj->getRotation();
$newRotation = $currentRotation;
$status = ["success" => "video rotated"];

switch ($type) {
case 'left':
    $newRotation = ($currentRotation - 90) % 360;
    $obj->setRotation($newRotation);
    break;
case 'right':
    $newRotation = ($currentRotation + 90) % 360;
    $obj->setRotation($newRotation);
    break;
default:
    $status = ["error" => "I don't know how to rotate '{$type}'"];
    break;
}

status($status);
