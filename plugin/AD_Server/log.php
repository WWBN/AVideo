<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';

if (empty($_GET['campaign_has_videos_id'])) {
    die('campaign_has_videos_id Can not be empty');
}

if(!empty($_SERVER['HTTP_ORIGIN'])){
    if($_SERVER['HTTP_ORIGIN'] == 'https://imasdk.googleapis.com'){
        die('Ignore this log, it may give us fake records');
    }
}

$users_id = 'null';
if (User::isLogged()) {
    $users_id = User::getId();
}

$log = new VastCampaignsLogs(0);
$log->setType($_GET['label']);
$log->setUsers_id($users_id);
$log->setVast_campaigns_has_videos_id($_GET['campaign_has_videos_id']);
$log->setVideos_id($_GET['videos_id']);
$log->save();

$campaignVideo = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
if ($_GET['label'] === AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED) {
    $campaign = new VastCampaigns($campaignVideo->getVast_campaigns_id());
    $campaign->addView();
}
if ($_GET['label'] === 'ClickThrough') {
    // get the URL
    $link = $campaignVideo->getLink();
    if (filter_var($link, FILTER_VALIDATE_URL)) {
        header("Location: ".$link);
    }
}
