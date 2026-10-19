<?php
// Background upload to YouTube. Started by YouTubePublisher::startWorker(): php youtubeUploadWorker.php <publisher_video_publisher_logs id>
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

set_time_limit(0);
ignore_user_abort(true);

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
if (empty($plugin)) {
    _error_log('youtubeUploadWorker: SocialMediaPublisher is disabled');
    exit(1);
}

$logs_id = intval(@$argv[1]);
if (empty($logs_id)) {
    _error_log('youtubeUploadWorker: missing logs id');
    exit(1);
}

YouTubePublisher::runWorker($logs_id);
