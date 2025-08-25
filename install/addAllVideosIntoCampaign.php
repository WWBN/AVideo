<?php
//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$adServer = AVideoPlugin::loadPluginIfEnabled('AD_Server');

if (empty($adServer)) {
    return die('AD_Server plugin not enabled');
}

require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';

$campaigns = VastCampaigns::getAllActive();

if(empty($campaigns)){
    return die('there is no active campaign');
}

$vast_campaigns_id = 0;
if(count($campaigns) == 1){
    $vast_campaigns_id = $campaigns[0]['id'];
}else{
    echo "What campaign should I add videos to?".PHP_EOL;
    foreach ($campaigns as $key => $value) {
        $status = $value['status'] == 'a' ? 'Active' : 'Inactive';
        $startDate = !empty($value['start_date']) ? date('Y-m-d', strtotime($value['start_date'])) : 'N/A';
        $endDate = !empty($value['end_date']) ? date('Y-m-d', strtotime($value['end_date'])) : 'N/A';
        $printsLeft = $value['cpm_max_prints'] - $value['cpm_current_prints'];
        echo "{$value['id']} => {$value['name']} (Status: {$status}, Start: {$startDate}, End: {$endDate}, Prints Left: {$printsLeft})".PHP_EOL;
    }
    echo "Enter campaign ID: ";
    $vast_campaigns_id = intval(readline(""));
}

// Validate campaign ID
$campaignExists = false;
foreach ($campaigns as $campaign) {
    if ($campaign['id'] == $vast_campaigns_id) {
        $campaignExists = true;
        $selectedCampaign = $campaign;
        break;
    }
}

if (!$campaignExists) {
    return die('Invalid campaign ID selected');
}

echo "Will add videos to campaign: {$selectedCampaign['name']} (ID: {$vast_campaigns_id})".PHP_EOL;

if (!empty($vast_campaigns_id)) {

    $videos = Video::getAllVideosLight('', false, true);

    $addedCount = 0;
    $skippedCount = 0;

    foreach ($videos as $value) {
        // Check if video already exists in campaign to prevent duplicates
        $campaignVideo = new VastCampaignsVideos(0);
        $existingRecord = $campaignVideo->loadFromCampainVideo($vast_campaigns_id, $value['id']);

        if ($existingRecord) {
            echo "Video ID {$value['id']} ('{$value['title']}') already exists in campaign - skipped".PHP_EOL;
            $skippedCount++;
            continue;
        }

        // Create new campaign video entry
        $campaignVideo = new VastCampaignsVideos(0);
        $campaignVideo->setVast_campaigns_id($vast_campaigns_id);
        $campaignVideo->setVideos_id($value['id']);
        $campaignVideo->setStatus('a');

        $result = $campaignVideo->save();

        if ($result) {
            echo "Video ID {$value['id']} ('{$value['title']}') added to campaign".PHP_EOL;
            $addedCount++;
        } else {
            echo "Error adding Video ID {$value['id']} ('{$value['title']}') to campaign".PHP_EOL;
        }
    }

    echo PHP_EOL;
    echo "Summary:".PHP_EOL;
    echo "- Videos added: {$addedCount}".PHP_EOL;
    echo "- Videos skipped (already in campaign): {$skippedCount}".PHP_EOL;
    echo "- Total videos processed: " . count($videos) . PHP_EOL;
}

echo "Bye";
echo "\n";
die();
