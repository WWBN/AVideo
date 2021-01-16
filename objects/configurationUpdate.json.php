<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
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
$config->setContactEmail($_POST['contactEmail']);
$config->setLanguage($_POST['language']);
$config->setWebSiteTitle($_POST['webSiteTitle']);
$config->setAuthCanComment($_POST['authCanComment']);
$config->setAuthCanUploadVideos($_POST['authCanUploadVideos']);
$config->setAuthCanViewChart($_POST['authCanViewChart']);
if (empty($global['disableAdvancedConfigurations'])) {
    $config->setDisable_analytics($_POST['disable_analytics']);
    $config->setDisable_youtubeupload($_POST['disable_youtubeupload']);
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
$config->setMode('Youtube');

$config->setAutoplay($_POST['autoplay']);
$config->setTheme($_POST['theme']);

$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!file_exists($global['systemRootPath'] . $imagePath)) {
    mkdir($global['systemRootPath'] . $imagePath, 0755, true);
}

if (!is_writable($global['systemRootPath'] . $imagePath)) {
    $response = array(
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
if (!empty($_POST['faviconBase64'])) {
    $imagePath = "videos/";
    $fileData = base64DataToImage($_POST['faviconBase64']);
    $fileName = 'favicon.png';
    $photoURL = $imagePath . $fileName;
    $bytes = file_put_contents($global['systemRootPath'] . $photoURL, $fileData);
    if ($bytes > 10) {
        $response2 = array(
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL
        );

        $sizes = array(
            array(16, 16),
            array(24, 24),
            array(32, 32),
            array(48, 48),
            array(144, 144)
        );

        $ico_lib = new PHP_ICO($global['systemRootPath'] . $photoURL, $sizes);
        $ico_lib->save_ico($global['systemRootPath'] . $imagePath.'favicon.ico');
    } else {
        $response2 = array(
            "status" => 'error',
            "msg" => 'We could not save favicon',
            "url" => $global['systemRootPath'] . $photoURL
        );
    }
}

echo '{"status":"' . $config->save() . '", "respnseLogo": ' . json_encode($response) . ', "respnseFavicon": ' . json_encode($response2) . '}';
