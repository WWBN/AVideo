<?php
// streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$videos = Video::getAllVideosLight('', false, true);

foreach ($videos as $value) {
    // Check if the video file is corrupted
    $result = Video::isVideoFileCorrupted($value['id']);
    if (!$result['isValid']) {
        echo "Video ID={$value['id']} Title={$value['title']} - reason: {$result['msg']}" . PHP_EOL;
        continue;
    }
}

echo "Bye\n";
die();
