<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
$config = new Configuration();
$config->setWebSiteTitle($_POST['webSiteTitle']);
$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!file_exists($global['systemRootPath'] . $imagePath)) {
    mkdir($global['systemRootPath'] . $imagePath, 0755, true);
}

if (!is_writable($global['systemRootPath'] . $imagePath)) {
    $response = Array(
        "status" => 'error',
        "message" => 'No write Access'
    );
    print json_encode($response);
    return;
}
$response = array();
if (!empty($_POST['logoImgBase64'])) {
    $fileData = base64DataToImage($_POST['logoImgBase64']);
    $fileName = 'logo.png';
    $photoURL = $imagePath . $fileName;
    $bytes = file_put_contents($global['systemRootPath'] . $photoURL, $fileData);
    if ($bytes > 10) {
        $response = array(
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL
        );
        $config->setLogo($photoURL);
    } else {
        $response = array(
            "status" => 'error',
            "msg" => 'We could not save logo',
            "url" => $global['systemRootPath'] . $photoURL
        );
    }
}

echo '{"status":"' . $config->save() . '", "respnseLogo": ' . json_encode($response) . '}';
