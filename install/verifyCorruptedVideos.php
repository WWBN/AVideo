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
        echo "ERROR   Video ID={$value['id']} Title={$value['title']} - reason: {$result['msg']}" . PHP_EOL;
    }else{
        echo "SUCCESS Video ID={$value['id']} Title={$value['title']}" . PHP_EOL;
    }
}

echo "Bye\n";
die();
