<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsLogs.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';

if(!empty($_SERVER['HTTP_ORIGIN'])){
    if($_SERVER['HTTP_ORIGIN'] == 'https://imasdk.googleapis.com'){
        die('Ignore this log, it may give us fake records');
    }
}

$users_id = 'null';
if (User::isLogged()) {
    $users_id = User::getId();
}

$response = new stdClass();
$response->videos_id = intval(@$_REQUEST['videos_id']);
$response->label = $_REQUEST['label'];
$response->users_id = $users_id;
$response->campaign_has_videos_id = intval(@$_REQUEST['campaign_has_videos_id']);
$response->video_position = intval($_REQUEST['video_position']);
$response->externalReferrer = storeAndGetExternalReferrer();

$log = new VastCampaignsLogs(0);
$log->setType($response->label);
$log->setUsers_id($response->users_id);
$log->setVast_campaigns_has_videos_id($response->campaign_has_videos_id);
$log->setVideos_id($response->videos_id);
$log->setVideo_position($response->video_position);
$log->setExternal_referrer($response->externalReferrer);
$response->save = $log->save();

if(!empty($response->campaign_has_videos_id)){
    $campaignVideo = new VastCampaignsVideos($response->campaign_has_videos_id);
    if ($response->label === AD_Server::STATUS_THAT_DETERMINE_AD_WAS_PLAYED) {
        $campaign = new VastCampaigns($campaignVideo->getVast_campaigns_id());
        $campaign->addView();
    }
    if ($response->label === AD_Server::AD_CLICKED) {
        // get the URL
        $link = $campaignVideo->getLink();
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            header("Location: ".$link);
            exit;
        }
    }
}


echo json_encode($response);