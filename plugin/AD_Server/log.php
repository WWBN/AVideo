<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';

if(empty($_GET['campaign_has_videos_id'])){
    die('campaign_has_videos_id Can not be empty');
}

$users_id = 'null';
if(User::isLogged()){
    $users_id = User::getId();
}

$log = new VastCampaignsLogs(0);
$log->setType($_GET['label']);
$log->setUsers_id($users_id);
$log->setVast_campaigns_has_videos_id($_GET['campaign_has_videos_id']);
$log->save();

$campaignVideo = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
if($_GET['label'] === 'Impression'){
    $campaign = new VastCampaigns($campaignVideo->getVast_campaigns_id());
    $campaign->addView();
}
if($_GET['label'] === 'ClickThrough'){
    // get the URL
    $link = $campaignVideo->getLink();
    if(filter_var($link, FILTER_VALIDATE_URL) ){
        header("Location: ".$link);        
    }
}