<?php
// Resume or cancel a YouTube upload of the logged user (publisher_video_publisher_logs row)
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
if (empty($plugin)) {
    forbiddenPage('SocialMediaPublisher is disabled');
}
if (!User::isLogged()) {
    forbiddenPage('You must login');
}
forbidIfNotPost();

$obj = array('error' => true, 'msg' => '');
try {
    $logs_id = intval(@$_POST['logs_id']);
    if (empty($logs_id)) {
        throw new Exception('empty logs_id');
    }
    switch (@$_POST['action']) {
        case 'resume':
            $obj = YouTubePublisher::resume($logs_id, User::getId());
            break;
        case 'cancel':
            $obj = YouTubePublisher::cancel($logs_id, User::getId());
            break;
        default:
            throw new Exception('invalid action');
    }
} catch (\Throwable $th) {
    _error_log('youtubeJob.json.php ' . $th->getMessage(), AVideoLog::$ERROR);
    $obj = array('error' => true, 'msg' => __('Something went wrong. Please try again.'));
}

echo json_encode($obj);
