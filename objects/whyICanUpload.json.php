<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

global $global, $config;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = new stdClass();

$response->canModerateVideos = Permissions::canModerateVideos();
$response->isAdmin = User::isAdmin();
$response->userCanUpload = AVideoPlugin::userCanUpload(User::getId());
$response->onlyVerifiedEmailCanUpload = $advancedCustomUser->onlyVerifiedEmailCanUpload;
$response->isVerified = User::isVerified();
$response->getAuthCanUploadVideos = $config->getAuthCanUploadVideos();
$response->canUpload = User::isLogged() && !empty($_SESSION['user']['canUpload']);

echo json_encode($response);