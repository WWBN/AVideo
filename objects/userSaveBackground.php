<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
$obj = new stdClass();
if (!User::isLogged()) {
    $obj->error = __("You must be logged");
    die(json_encode($obj));
}
$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!file_exists($global['systemRootPath'].$imagePath)) {
    mkdir($global['systemRootPath'].$imagePath, 0755, true);
}

if (!is_writable($global['systemRootPath'].$imagePath)) {
    $response = Array(
        "status" => 'error',
        "message" => 'No write Access'
    );
    print json_encode($response);
    return;
}

$fileData = base64DataToImage($_POST['imgBase64']);
$fileName = 'background'. User::getId().'.png';
$photoURL = $imagePath.$fileName;
$bytes = file_put_contents($global['systemRootPath'].$photoURL, $fileData);
if ($bytes) {
    $response = array(
        "status" => 'success',
        "url" => $global['systemRootPath'].$photoURL
    );
} else {
    $response = array(
        "status" => 'error',
        "msg" => 'We could not save this file',
        "url" => $global['systemRootPath'].$photoURL
    );
}

$user = new User(User::getId());
$user->setBackgroundURL($photoURL);
$user->save();
User::updateSessionInfo();
print json_encode($response);
