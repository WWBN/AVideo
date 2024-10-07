<?php

header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

global $global, $config;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../videos/configuration.php';

error_reporting(E_ALL);

AVideoPlugin::loadPlugin('Permissions');

$response = new stdClass();

$response->canModerateVideos = Permissions::canModerateVideos();
$response->isAdmin = User::isAdmin();
$response->onlyVerifiedEmailCanUpload = $advancedCustomUser->onlyVerifiedEmailCanUpload;
$response->isVerified = User::isVerified();
$response->getAuthCanUploadVideos = $config->getAuthCanUploadVideos();
$response->canUpload = User::isLogged() && !empty($_SESSION['user']['canUpload']);

$response->userCanUpload = AVideoPlugin::userCanUpload(User::getId());
$response->userCanUploadPlugins = array();
$plugins = Plugin::getAllEnabled();
foreach ($plugins as $value) {
    $p = AVideoPlugin::loadPlugin($value['dirName']);
    if (is_object($p)) {
        $response->userCanUploadPlugins[$value['dirName']] = $p->userCanUpload($users_id);
    }
}


$response->finalDecision = $response->canModerateVideos  || $response->isAdmin || $response->canUpload || $response->userCanUpload;
echo json_encode($response);
