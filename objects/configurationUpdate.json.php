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

forbidIfIsUntrustedRequest('configurationUpdate');

require_once __DIR__ . '/configurationForm.php';
$config = new AVideoConf();
applySiteConfigurationValues($config, $_POST, empty($global['disableAdvancedConfigurations']));

$imagePath = "videos/userPhoto/";

//Check write Access to Directory
if (!empty($_POST['logoImgBase64']) && !file_exists($global['systemRootPath'] . $imagePath)) {
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
$response2 = [];
$warning = null;
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

        pwaIconsArray($global['systemRootPath'] . $photoURL, true);

        $response2 = [
            "status" => 'success',
            "url" => $global['systemRootPath'] . $photoURL,
        ];

        $sizes = [16, 24, 32, 48, 144];
        $input = $global['systemRootPath'] . $photoURL;
        $output = $global['systemRootPath'] . $imagePath . 'favicon.ico';

        // An optional ICO conversion must not abort saving the other settings.
        $convertPath = function_exists('shell_exec') ? trim((string) shell_exec('command -v convert')) : '';
        if (empty($convertPath) || !function_exists('exec')) {
            $warning = __('Settings saved, but favicon.ico could not be generated. Check ImageMagick.');
        } else {
            $sizesStr = implode(',', $sizes);
            $cmd = escapeshellarg($convertPath) . ' ' . escapeshellarg($input)
                . ' -define icon:auto-resize=' . escapeshellarg($sizesStr) . ' ' . escapeshellarg($output);
            exec($cmd, $outputLog, $returnCode);
            if ($returnCode !== 0) {
                $warning = __('Settings saved, but favicon.ico could not be generated. Check ImageMagick.');
            }
        }
    } else {
        $response2 = [
            "status" => 'error',
            "msg" => 'We could not save favicon',
            "url" => $global['systemRootPath'] . $photoURL,
        ];
    }
}

$saved = $config->save();
$imageError = ($response['status'] ?? '') === 'error' || ($response2['status'] ?? '') === 'error';
echo json_encode([
    'status' => (string) $saved,
    'error' => empty($saved) || $imageError,
    'warning' => $warning,
    // Keep the existing response keys for integrations.
    'respnseLogo' => $response,
    'respnseFavicon' => $response2,
]);
