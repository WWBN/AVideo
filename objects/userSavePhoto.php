<?php

header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$obj = new stdClass();
if (!User::isLogged()) {
    $obj->error = __("You must be logged");
    die(json_encode($obj));
}
$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!file_exists($global['systemRootPath'].$imagePath)) {
    mkdir($global['systemRootPath'].$imagePath, 0777, true);
}

if (!is_writable($global['systemRootPath'].$imagePath)) {
    $response = Array(
        "status" => 'error',
        "message" => 'No write Access'
    );
    print json_encode($response);
    return;
}

$img = $_POST['imgBase64'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$fileData = base64_decode($img);
$fileName = 'photo'. User::getId().'.png';
$photoURL = $imagePath.$fileName;
$bytes = file_put_contents($global['systemRootPath'].$photoURL, $fileData);
if($bytes){
    $response = array(
        "status" => 'success',
        "url" => $global['systemRootPath'].$photoURL
    );
}else{
    $response = array(
        "status" => 'error',
        "msg" => 'We could not save this file',
        "url" => $global['systemRootPath'].$photoURL
    );
}

$user = new User(User::getId());
$user->setPhotoURL($photoURL);
$user->save();
User::updateSessionInfo();
print json_encode($response);
?>
