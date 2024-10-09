<?php

header('Content-Type: application/json');

require_once '../../videos/configuration.php';

// Fetch request parameters
$startDate = $_REQUEST['startDate'] . ' 00:00:00';
$endDate = $_REQUEST['endDate'] . ' 23:59:59';
$reportType = $_REQUEST['reportType'];

$vast_campaigns_id = intval(@$_REQUEST['vast_campaigns_id']); // Campaign ID (optional)
$videos_id = isset($_REQUEST['videos_id']) ? intval($_REQUEST['videos_id']) : null; // Video ID (optional)
$users_id = isset($_REQUEST['users_id']) ? intval($_REQUEST['users_id']) : null; // User ID (optional)
$event_type = isset($_REQUEST['eventType']) ? $_REQUEST['eventType'] : null; // Event type filter (optional)
$campaign_type = isset($_REQUEST['campaignType']) ? $_REQUEST['campaignType'] : 'all'; // Campaign type filter (optional)

$reportData = [];

// Generate report based on selected report type
if ($reportType === 'adsByVideo') {
    // Get ads by video, with optional filtering by video ID, event type, and campaign type
    $reportData = VastCampaignsLogs::getAdsByVideoAndPeriod($vast_campaigns_id, $startDate, $endDate, $videos_id, $event_type, $campaign_type);

} elseif ($reportType === 'adTypes') {
    // Get ad types overview, optionally filtering by event type and campaign type
    $reportData = VastCampaignsLogs::getAdTypesByPeriod($vast_campaigns_id, $startDate, $endDate, $event_type, $campaign_type);

} elseif ($reportType === 'adsByUser') {
    // Get ads by user, filtering by user ID, event type, and campaign type
    if (!empty($users_id)) {
        $reportData = VastCampaignsLogs::getAdsByUserAndEventType($users_id, $startDate, $endDate, $event_type, $campaign_type); // Fetch ads by user with event types
    } else {
        $reportData = ['error' => 'Missing users_id parameter'];
    }

} elseif ($reportType === 'adsForSingleVideo') {
    // Get ads for a single video, broken down by event types and campaign type
    if (!empty($videos_id)) {
        $reportData = VastCampaignsLogs::getAdsByVideoAndEventType($videos_id, $startDate, $endDate, $event_type, $campaign_type); // Fetch ads for a single video with event types
    } else {
        $reportData = ['error' => 'Missing videos_id parameter'];
    }
}

// Return the JSON response
echo json_encode($reportData);
