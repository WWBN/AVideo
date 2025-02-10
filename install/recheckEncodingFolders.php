<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$basePath = getVideosDir();

// Ensure the directory exists
if (!is_dir($basePath)) {
    die("Directory does not exist: $basePath\n");
}

// Scan the base directory
$folders = scandir($basePath);

$_500MB = 524288000;

// Loop through each item in the directory
foreach ($folders as $folder) {
    // Skip special directories "." and ".."
    if ($folder === '.' || $folder === '..') {
        continue;
    }

    // Build the full path
    $fullPath = $basePath . $folder;

    // Check if it's a directory and starts with "v_" or "video_"
    if (is_dir($fullPath) && (str_starts_with($folder, 'v_') || str_starts_with($folder, 'video_'))) {
        $video = Video::getVideoFromFileNameLight($folder);
        if (!empty($video)) {
            if ($video['status'] === Video::$statusEncoding) {
                if (strtotime($video['created']) < strtotime('-2 days')) {
                    if (getDirSize($fullPath) > $_500MB) {
                        $v = new Video('', '', $video['id']);
                        $v->setAutoStatus();
                        $v->save(false, true);
                        echo "Set status {$video['id']} {$video['title']}" . PHP_EOL;
                    }
                }
            }
        }
    }
}
