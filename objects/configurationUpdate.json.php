<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
$config->setContactEmail($_POST['contactEmail']);
$config->setLanguage($_POST['language']);
$config->setVideo_resolution($_POST['video_resolution']);
$config->setWebSiteTitle($_POST['webSiteTitle']);
$config->setAuthCanComment($_POST['authCanComment']);
$config->setAuthCanUploadVideos($_POST['authCanUploadVideos']);
$config->setAuthFacebook_enabled($_POST['authFacebook_enabled']);
$config->setAuthFacebook_id($_POST['authFacebook_id']);
$config->setAuthFacebook_key($_POST['authFacebook_key']);
$config->setAuthGoogle_enabled($_POST['authGoogle_enabled']);
$config->setAuthGoogle_id($_POST['authGoogle_id']);
$config->setAuthGoogle_key($_POST['authGoogle_key']);
$config->setFfprobeDuration($_POST['ffprobeDuration']);
$config->setFfmpegImage($_POST['ffmpegImage']);
$config->setFfmpegMp4($_POST['ffmpegMp4']);
$config->setFfmpegWebm($_POST['ffmpegWebm']);
$config->setFfmpegMp3($_POST['ffmpegMp3']);
$config->setFfmpegOgg($_POST['ffmpegOgg']);
$config->setYoutubedl($_POST['youtubeDl']);
echo '{"status":"'.$config->save().'"}';
