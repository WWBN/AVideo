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

$imagePath = "videos/userPhoto/";
//Check write Access to Directory
$dirPath = $global['systemRootPath'] . $imagePath;

make_path($dirPath);


$fileName = 'photo' . User::getId() . '.png';
$photoURL = $imagePath . $fileName;
$photoFullPath = $dirPath . $fileName;
$obj->url = $photoURL;


$obj->imagePNGResponse = saveCroppieImage($photoFullPath, "image");
if ($obj->imagePNGResponse) {
    $user = new User(User::getId());
    $user->setPhotoURL($photoURL);
    if ($user->save()) {
        User::deleteOGImage(User::getId());
        User::updateSessionInfo();
        clearCache(true);
        $obj->error = false;
    }
}

die(json_encode($obj));

