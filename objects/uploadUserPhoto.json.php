<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage('Login required');
}
require_once $global['systemRootPath'] . 'objects/channelImageUpload.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = __('An error occurred');

$imagePath = "videos/userPhoto/";
//Check write Access to Directory
$dirPath = $global['systemRootPath'] . $imagePath;

make_path($dirPath);


$fileName = 'photo' . User::getId() . '.png';
$photoURL = $imagePath . $fileName;
$photoFullPath = $dirPath . $fileName;
$obj->url = $photoURL;


try {
    if (saveChannelImageUpload($photoFullPath, 150, 150, [], 'image', function () use ($photoURL) {
        $user = new User(User::getId());
        $user->setPhotoURL($photoURL);
        return $user->save();
    })) {
        $obj->imagePNGResponse = filesize($photoFullPath);
        User::deleteOGImage(User::getId());
        User::updateSessionInfo();
        clearCache(true);
        $obj->error = false;
        $obj->msg = __('Saved');
        $obj->previewURL = getURL($photoURL, true);
    }
} catch (InvalidArgumentException $th) {
    $obj->msg = $th->getMessage();
} catch (Throwable $th) {
    _error_log('uploadUserPhoto: ' . $th->getMessage());
}

die(json_encode($obj));

