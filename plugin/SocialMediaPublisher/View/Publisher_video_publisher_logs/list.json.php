<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_video_publisher_logs.php';
header('Content-Type: application/json');

$videos_id = getVideos_id();

if(empty($videos_id) && !User::isAdmin()){
    forbiddenPage('Admin only');
}

if(!Video::canEdit($videos_id)){
    forbiddenPage('You cannot edit this video');
}

if(empty($videos_id)){
    $rows = Publisher_video_publisher_logs::getAll();
    $total = Publisher_video_publisher_logs::getTotal();
}else{
    $rows = Publisher_video_publisher_logs::getAllFromVideosId($videos_id);
    $total = Publisher_video_publisher_logs::getTotalFromVideosId($videos_id);
}
if (!is_array($rows)) {
    $rows = array();
}

$refreshed = 0;
foreach ($rows as $key => $row) {
    $provider = isset($row['name']) ? $row['name'] : '';
    if (!empty($videos_id) && YouTubePublisher::isYouTubeProvider($provider)) {
        $job = YouTubePublisher::getJobFromDetails($row['details']);
        if (!empty($job) && @$job['state'] === YouTubePublisher::STATE_PROCESSING && $refreshed < 3) {
            // asks YouTube whether it finished processing (throttled inside)
            $job = YouTubePublisher::refreshProcessingStatus($row['id']);
            $refreshed++;
        }
        if (!empty($job)) {
            $rows[$key]['job'] = YouTubePublisher::getPublicJob($job, empty($job['legacy']) ? YouTubePublisher::isWorkerRunning($row['id']) : false);
            // resume/cancel only work on your own uploads (youtubeJob.json.php checks it too)
            $rows[$key]['job']['isMine'] = intval($row['users_id']) === intval(User::getId());
        }
        // the job replaces the raw details for YouTube rows
        unset($rows[$key]['json'], $rows[$key]['details']);
        continue;
    }
    if (isset($rows[$key]['details'])) {
        $rows[$key]['details'] = YouTubePublisher::redactDetails($rows[$key]['details']);
    }
    if (!empty($videos_id)) {
        // the per-video view only renders "msg"
        unset($rows[$key]['json']);
    }
}

$response = array(
    'data' => array_values($rows),
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>
