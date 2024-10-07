<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

global $global, $config;

$response = new stdClass();

$response->canModerateVideos = Permissions::canModerateVideos();
$response->isAdmin = User::isAdmin();
$response->userCanUpload = AVideoPlugin::userCanUpload(User::getId());
$response->onlyVerifiedEmailCanUpload = $advancedCustomUser->onlyVerifiedEmailCanUpload;
$response->isVerified = User::isVerified();
$response->getAuthCanUploadVideos = $config->getAuthCanUploadVideos();
$response->canUpload = self::isLogged() && !empty($_SESSION['user']['canUpload']);

echo json_encode($response);