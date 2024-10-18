<?php

header('Content-Type: application/json');

require_once '../../videos/configuration.php';

// Fetch request parameters with safety checks
$startDate = !empty($_REQUEST['startDate']) ? $_REQUEST['startDate'] . ' 00:00:00' : null;
$endDate = !empty($_REQUEST['endDate']) ? $_REQUEST['endDate'] . ' 23:59:59' : null;
$reportType = isset($_REQUEST['reportType']) ? $_REQUEST['reportType'] : null;

// Additional request parameters with default fallbacks and validation
$vast_campaigns_id = intval(@$_REQUEST['vast_campaigns_id']); // Optional campaign ID
$videos_id = isset($_REQUEST['videos_id']) ? intval($_REQUEST['videos_id']) : null; // Optional video ID
$users_id = isset($_REQUEST['users_id']) ? intval($_REQUEST['users_id']) : null; // Optional user ID
$event_type = isset($_REQUEST['eventType']) ? $_REQUEST['eventType'] : null; // Optional event type
$external_referrer = isset($_REQUEST['referrerType']) ? $_REQUEST['referrerType'] : null; // Optional event type
$campaign_type = isset($_REQUEST['campaignType']) ? $_REQUEST['campaignType'] : 'all'; // Default to 'all' if not specified

$reportData = [];

// Generate report based on selected report type
switch ($reportType) {
    case 'adsByVideo':
        // Get ads by video, with optional filtering
        $reportData = VastCampaignsLogs::getAdsByVideoAndPeriod($vast_campaigns_id, $startDate, $endDate, $videos_id, $event_type, $campaign_type, $external_referrer);
        break;

    case 'adTypes':
        // Get ad types overview, optionally filtering by event type and campaign type
        $reportData = VastCampaignsLogs::getAdTypesByPeriod($vast_campaigns_id, $startDate, $endDate, $event_type, $campaign_type, $external_referrer);
        break;

    case 'adsByUser':
        // Ensure user ID is provided for fetching ads by user
        if (!empty($users_id)) {
            $reportData = VastCampaignsLogs::getAdsByVideoForUser($users_id, $startDate, $endDate, $event_type, $campaign_type, $external_referrer);
        } else {
            $reportData = ['error' => 'Missing users_id parameter'];
        }
        break;
    case 'listVideosByUser':
        // Ensure user ID is provided for fetching ads by user
        if (!empty($users_id)) {
            $reportData = VastCampaignsLogs::getAdsByVideoForUser($users_id, $startDate, $endDate, $event_type, $campaign_type, $external_referrer);
        } else {
            $reportData = ['error' => 'Missing users_id parameter'];
        }
        break;

    case 'adsForSingleVideo':
        // Ensure video ID is provided for fetching ads for a single video
        if (!empty($videos_id)) {
            $reportData = VastCampaignsLogs::getAdsByVideoAndEventType($videos_id, $startDate, $endDate, $event_type, $campaign_type, $external_referrer);
        } else {
            $reportData = ['error' => 'Missing videos_id parameter'];
        }
        break;

    default:
        $reportData = ['error' => 'Invalid or missing reportType parameter'];
        break;
}

// Return the JSON response
echo json_encode($reportData);
