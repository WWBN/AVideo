<?php

header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
$config = new Configuration();
$config->setContactEmail($_POST['contactEmail']);
$config->setLanguage($_POST['language']);
$config->setWebSiteTitle($_POST['webSiteTitle']);
$config->setAuthCanComment($_POST['authCanComment']);
$config->setAuthCanUploadVideos($_POST['authCanUploadVideos']);
if (empty($global['disableAdvancedConfigurations'])) {
    $config->setDisable_analytics($_POST['disable_analytics']);
    $config->setAllow_download($_POST['allow_download']);
    $config->setSession_timeout($_POST['session_timeout']);
    $config->setEncoderURL($_POST['encoder_url']);
    $config->setSmtp($_POST['smtp']);
    $config->setSmtpAuth($_POST['smtpAuth']);
    $config->setSmtpSecure($_POST['smtpSecure']);
    $config->setSmtpHost($_POST['smtpHost']);
    $config->setSmtpUsername($_POST['smtpUsername']);
    $config->setSmtpPassword($_POST['smtpPassword']);
    $config->setSmtpPort($_POST['smtpPort']);
}

$config->setHead($_POST['head']);
$config->setAdsense($_POST['adsense']);
$config->setMode($_POST['mode']);

$config->setAutoplay($_POST['autoplay']);
$config->setTheme($_POST['theme']);

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
$response = $responseSmall = array();
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
if (!empty($_POST['logoSmallImgBase64'])) {
    $fileData = base64DataToImage($_POST['logoSmallImgBase64']);
    $fileName = 'logoSmall.png';
    $photoURL = $imagePath . $fileName;
    $bytes = file_put_contents($global['systemRootPath'] . $photoURL, $fileData);
    if ($bytes > 10) {
        $responseSmall = array(
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL
        );
        $config->setLogo_small($photoURL);
    } else {
        $responseSmall = array(
            "status" => 'error',
            "msg" => 'We could not save small logo',
            "url" => $global['systemRootPath'] . $photoURL
        );
    }
}
echo '{"status":"' . $config->save() . '", "respnseLogo": ' . json_encode($response) . ', "respnseLogoSmall": ' . json_encode($responseSmall) . '}';
