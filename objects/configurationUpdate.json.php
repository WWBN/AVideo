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

_error_log("save configuration {$_POST['language']}");

$config = new AVideoConf();
$config->setContactEmail($_POST['contactEmail']);
$config->setLanguage($_POST['language']);
$config->setWebSiteTitle($_POST['webSiteTitle']);
$config->setDescription($_POST['description']);
$config->setAuthCanComment($_POST['authCanComment']);
$config->setAuthCanUploadVideos($_POST['authCanUploadVideos']);
$config->setAuthCanViewChart($_POST['authCanViewChart']);
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
$config->setMode('Youtube');

$config->setAutoplay($_POST['autoplay']);
$config->setTheme($_POST['theme']);

$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!file_exists($global['systemRootPath'] . $imagePath)) {
    mkdir($global['systemRootPath'] . $imagePath, 0755, true);
}
/*
if (!is_writable($global['systemRootPath'] . $imagePath)) {
    $response = array(
        "status" => 'error',
        "message" => 'No write Access'
    );
    print json_encode($response);
    return;
}
 *
 */
$response = [];
if (!empty($_POST['logoImgBase64'])) {
    $fileData = base64DataToImage($_POST['logoImgBase64']);
    $fileName = 'logo.png';
    $photoURL = $imagePath . $fileName;
    $bytes = file_put_contents($global['systemRootPath'] . $photoURL, $fileData);
    if ($bytes > 10) {
        $response = [
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL,
        ];
        $config->setLogo($photoURL);
    } else {
        $response = [
            "status" => 'error',
            "msg" => 'We could not save logo',
            "url" => $global['systemRootPath'] . $photoURL,
        ];
    }
}
if (!empty($_POST['faviconBase64'])) {
    $imagePath = "videos/";
    $fileData = base64DataToImage($_POST['faviconBase64']);
    $fileName = 'favicon.png';
    $photoURL = $imagePath . $fileName;
    $bytes = file_put_contents($global['systemRootPath'] . $photoURL, $fileData);
    if ($bytes > 10) {
        $response2 = [
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL,
        ];

        $sizes = [16, 24, 32, 48, 144];
        $input = $global['systemRootPath'] . $photoURL;
        $output = $global['systemRootPath'] . $imagePath . 'favicon.ico';

        // Check if the `convert` command is available (ImageMagick)
        $convertPath = trim(shell_exec('which convert'));

        if (empty($convertPath)) {
            error_log("[favicon] ImageMagick 'convert' command not found. Please install it using:\n  sudo apt update && sudo apt install imagemagick");
            echo "Error: ImageMagick is not installed. Please install it with:\n";
            echo "sudo apt update && sudo apt install imagemagick\n";
            return;
        }

        // Prepare auto-resize sizes for favicon
        $sizesStr = implode(',', $sizes);

        // Build and execute the `convert` command
        $cmd = escapeshellcmd("convert {$input} -define icon:auto-resize={$sizesStr} {$output}");
        exec($cmd, $outputLog, $returnCode);
    } else {
        $response2 = [
            "status" => 'error',
            "msg" => 'We could not save favicon',
            "url" => $global['systemRootPath'] . $photoURL,
        ];
    }
}

echo '{"status":"' . $config->save() . '", "respnseLogo": ' . json_encode($response) . ', "respnseFavicon": ' . json_encode($response2) . '}';
