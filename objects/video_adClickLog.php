<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require $global['systemRootPath'] . 'objects/video_ad.php';
Video_ad::clickLog($_GET['video_ads_logs_id']);
Video_ad::redirect($_GET['adId']);
